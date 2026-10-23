<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\User;
use App\Models\XpTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GamificationService
{
    /**
     * Award XP to a user with idempotency guarantee.
     */
    public function awardXp(User $user, int $amount, string $sourceType, ?int $sourceId, string $description): ?XpTransaction
    {
        if ($amount <= 0) {
            return null;
        }

        $idempotencyKey = "{$sourceType}_{$sourceId}_{$user->id}";

        return DB::transaction(function () use ($user, $amount, $sourceType, $sourceId, $idempotencyKey, $description) {
            // Check if already awarded
            $existing = XpTransaction::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }

            $transaction = XpTransaction::create([
                'user_id' => $user->id,
                'amount' => $amount,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'idempotency_key' => $idempotencyKey,
                'description' => $description,
            ]);

            $profile = $user->profile()->firstOrCreate([
                'user_id' => $user->id,
            ]);

            $profile->increment('total_xp', $amount);

            $this->updateStreak($user);
            $this->checkAchievements($user);

            return $transaction;
        });
    }

    /**
     * Update user daily activity and streak accurately using the user's timezone.
     */
    public function updateStreak(User $user): void
    {
        $profile = $user->profile()->firstOrCreate([
            'user_id' => $user->id,
        ]);

        $timezone = $profile->timezone ?? 'Africa/Cairo';
        $today = Carbon::now($timezone)->toDateString();

        if ($profile->last_activity_date) {
            $lastDate = Carbon::parse($profile->last_activity_date, $timezone)->toDateString();

            if ($lastDate === $today) {
                // Already counted today
                return;
            }

            $yesterday = Carbon::now($timezone)->subDay()->toDateString();
            if ($lastDate === $yesterday) {
                // Consecutive day
                $profile->current_streak += 1;
            } else {
                // Streak broken
                $profile->current_streak = 1;
            }
        } else {
            $profile->current_streak = 1;
        }

        if ($profile->current_streak > $profile->longest_streak) {
            $profile->longest_streak = $profile->current_streak;
        }

        $profile->last_activity_date = $today;
        $profile->save();
    }

    /**
     * Evaluate and unlock eligible achievements.
     */
    public function checkAchievements(User $user): array
    {
        $profile = $user->profile;
        if (!$profile) {
            return [];
        }

        $unlockedNow = [];
        $existingCodes = $user->achievements()->pluck('code')->toArray();

        // Check first lesson
        if (!in_array('first_lesson', $existingCodes)) {
            $completedCount = $user->lessonAttempts()->where('status', 'completed')->count();
            if ($completedCount >= 1) {
                $unlockedNow[] = $this->unlockBadge($user, 'first_lesson');
            }
        }

        // Check 3 day streak
        if (!in_array('streak_3', $existingCodes) && $profile->current_streak >= 3) {
            $unlockedNow[] = $this->unlockBadge($user, 'streak_3');
        }

        // Check 7 day streak
        if (!in_array('streak_7', $existingCodes) && $profile->current_streak >= 7) {
            $unlockedNow[] = $this->unlockBadge($user, 'streak_7');
        }

        // Check vocabulary count
        if (!in_array('vocab_10', $existingCodes)) {
            $vocabCount = $user->userVocabulary()->count();
            if ($vocabCount >= 10) {
                $unlockedNow[] = $this->unlockBadge($user, 'vocab_10');
            }
        }

        // Check story reader
        if (!in_array('story_reader', $existingCodes)) {
            $storyCount = $user->storyProgress()->where('is_completed', true)->count();
            if ($storyCount >= 1) {
                $unlockedNow[] = $this->unlockBadge($user, 'story_reader');
            }
        }

        return array_filter($unlockedNow);
    }

    private function unlockBadge(User $user, string $code): ?Achievement
    {
        $achievement = Achievement::where('code', $code)->first();
        if ($achievement && !$user->achievements()->where('achievements.id', $achievement->id)->exists()) {
            $user->achievements()->attach($achievement->id, [
                'unlocked_at' => now(),
            ]);

            // Award bonus XP if defined
            if ($achievement->xp_reward > 0) {
                XpTransaction::create([
                    'user_id' => $user->id,
                    'amount' => $achievement->xp_reward,
                    'source_type' => 'achievement',
                    'source_id' => $achievement->id,
                    'idempotency_key' => "achievement_{$achievement->id}_{$user->id}",
                    'description' => "وسام إنجاز: {$achievement->title_ar}",
                ]);
                $user->profile->increment('total_xp', $achievement->xp_reward);
            }

            return $achievement;
        }

        return null;
    }
}
