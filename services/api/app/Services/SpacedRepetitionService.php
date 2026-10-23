<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserVocabulary;
use App\Models\VocabularyItem;
use App\Models\VocabularyReview;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SpacedRepetitionService
{
    /**
     * Add a vocabulary item to user's revision queue.
     */
    public function saveVocabulary(User $user, int $vocabularyItemId): UserVocabulary
    {
        return UserVocabulary::firstOrCreate(
            [
                'user_id' => $user->id,
                'vocabulary_item_id' => $vocabularyItemId,
            ],
            [
                'mastery_level' => 'learning',
                'repetitions' => 0,
                'interval_days' => 1,
                'ease_factor' => 2.50,
                'next_review_at' => Carbon::now(),
                'successful_reviews' => 0,
                'failed_reviews' => 0,
            ]
        );
    }

    /**
     * Process a flashcard / recall review using the SuperMemo SM-2 algorithm.
     * 
     * @param int $rating 0 (complete blackout) to 5 (perfect recall)
     */
    public function processReview(User $user, int $userVocabularyId, int $rating): array
    {
        $rating = max(0, min(5, $rating));

        return DB::transaction(function () use ($user, $userVocabularyId, $rating) {
            $userVocab = UserVocabulary::where('user_id', $user->id)
                ->where('id', $userVocabularyId)
                ->lockForUpdate()
                ->firstOrFail();

            $prevRepetitions = $userVocab->repetitions;
            $prevInterval = $userVocab->interval_days;
            $prevEase = $userVocab->ease_factor;

            $isSuccess = $rating >= 3;

            if ($isSuccess) {
                if ($prevRepetitions === 0) {
                    $newInterval = 1;
                } elseif ($prevRepetitions === 1) {
                    $newInterval = 6;
                } else {
                    $newInterval = (int) round($prevInterval * $prevEase);
                }
                $newRepetitions = $prevRepetitions + 1;
                $userVocab->successful_reviews += 1;
            } else {
                $newRepetitions = 0;
                $newInterval = 1;
                $userVocab->failed_reviews += 1;
            }

            // SM-2 Ease Factor calculation
            $newEase = $prevEase + (0.1 - (5 - $rating) * (0.08 + (5 - $rating) * 0.02));
            if ($newEase < 1.30) {
                $newEase = 1.30;
            }

            // Determine mastery status
            $masteryLevel = 'learning';
            if ($newRepetitions >= 5) {
                $masteryLevel = 'mastered';
            } elseif ($newRepetitions >= 2) {
                $masteryLevel = 'reviewing';
            }

            $userVocab->repetitions = $newRepetitions;
            $userVocab->interval_days = $newInterval;
            $userVocab->ease_factor = round($newEase, 2);
            $userVocab->mastery_level = $masteryLevel;
            $userVocab->last_reviewed_at = Carbon::now();
            $userVocab->next_review_at = Carbon::now()->addDays($newInterval);
            $userVocab->save();

            // Log individual review record
            $review = VocabularyReview::create([
                'user_id' => $user->id,
                'user_vocabulary_id' => $userVocab->id,
                'quality_rating' => $rating,
                'repetition_number' => $newRepetitions,
                'interval_applied' => $newInterval,
            ]);

            // Award small gamification XP for completing reviews
            app(GamificationService::class)->awardXp(
                $user,
                5,
                'review',
                $review->id,
                'مراجعة بطاقة مفردات'
            );

            return [
                'user_vocabulary' => $userVocab->load('vocabularyItem'),
                'interval_days' => $newInterval,
                'next_review_at' => $userVocab->next_review_at,
                'mastery_level' => $masteryLevel,
            ];
        });
    }

    /**
     * Get items due for review today.
     */
    public function getDueReviews(User $user, int $limit = 20)
    {
        return UserVocabulary::with('vocabularyItem')
            ->where('user_id', $user->id)
            ->where('next_review_at', '<=', Carbon::now())
            ->orderBy('next_review_at', 'asc')
            ->limit($limit)
            ->get();
    }
}
