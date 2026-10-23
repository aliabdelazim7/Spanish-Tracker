<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\Lesson;
use App\Models\LessonAttempt;
use App\Models\StoryProgress;
use App\Models\UserVocabulary;
use App\Models\XpTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);

        $completedLessonIds = LessonAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();

        $completedLessonsCount = count(array_unique($completedLessonIds));
        $vocabularyCount = UserVocabulary::where('user_id', $user->id)->count();
        $dueReviewsCount = UserVocabulary::where('user_id', $user->id)
            ->where('next_review_at', '<=', now())
            ->count();
        $storiesCompletedCount = StoryProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->count();

        // Calculate next recommended lesson
        $nextLesson = Lesson::where('is_published', true)
            ->whereNotIn('id', $completedLessonIds)
            ->with(['unit.level'])
            ->orderBy('id')
            ->first();

        // Fallback to first lesson if all completed or none
        if (!$nextLesson) {
            $nextLesson = Lesson::where('is_published', true)->with(['unit.level'])->first();
        }

        // Recent activity
        $recentAttempts = LessonAttempt::where('user_id', $user->id)
            ->with('lesson.unit')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'profile' => $profile,
            'stats' => [
                'total_xp' => $profile->total_xp,
                'current_streak' => $profile->current_streak,
                'longest_streak' => $profile->longest_streak,
                'daily_goal_minutes' => $profile->daily_goal_minutes,
                'estimated_level' => $profile->estimated_cefr_level ?? 'A1',
                'lessons_completed_count' => $completedLessonsCount,
                'vocabulary_count' => $vocabularyCount,
                'due_reviews_count' => $dueReviewsCount,
                'stories_completed_count' => $storiesCompletedCount,
            ],
            'recommended_lesson' => $nextLesson,
            'recent_activity' => $recentAttempts,
        ]);
    }

    public function progress(Request $request): JsonResponse
    {
        $user = $request->user();

        // Weekly XP breakdown from real transactions
        $recentXp = XpTransaction::where('user_id', $user->id)
            ->where('created_at', '>=', now()->subDays(7))
            ->orderBy('created_at', 'asc')
            ->get(['amount', 'source_type', 'created_at']);

        // Skill breakdown calculations
        $completedAttempts = LessonAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $readingScore = min(100, $completedAttempts * 12 + 10);
        $vocabScore = min(100, UserVocabulary::where('user_id', $user->id)->count() * 8);
        $grammarScore = min(100, $completedAttempts * 10 + 5);

        return response()->json([
            'skills' => [
                'reading' => $readingScore,
                'vocabulary' => $vocabScore,
                'grammar' => $grammarScore,
                'listening' => 15,
                'speaking' => 10,
            ],
            'recent_xp' => $recentXp,
        ]);
    }

    public function achievements(Request $request): JsonResponse
    {
        $user = $request->user();

        $allAchievements = Achievement::orderBy('id')->get();
        $unlocked = $user->achievements()->get()->keyBy('id');

        $result = $allAchievements->map(function ($ach) use ($unlocked) {
            $userAch = $unlocked->get($ach->id);
            return [
                'id' => $ach->id,
                'code' => $ach->code,
                'title_ar' => $ach->title_ar,
                'description_ar' => $ach->description_ar,
                'icon' => $ach->icon,
                'badge_category' => $ach->badge_category,
                'xp_reward' => $ach->xp_reward,
                'is_unlocked' => $userAch !== null,
                'unlocked_at' => $userAch?->pivot->unlocked_at,
            ];
        });

        return response()->json([
            'achievements' => $result,
        ]);
    }
}
