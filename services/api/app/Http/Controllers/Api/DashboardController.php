<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\CefrLevel;
use App\Models\LearnerErrorConcept;
use App\Models\Lesson;
use App\Models\LessonAttempt;
use App\Models\LevelExamAttempt;
use App\Models\PlacementAttempt;
use App\Models\StoryProgress;
use App\Models\UserVocabulary;
use App\Models\XpTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Get authentic learner dashboard state backed by real database records.
     */
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

        // Recent lesson attempts
        $recentAttempts = LessonAttempt::where('user_id', $user->id)
            ->with('lesson.unit')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Assessments: Placement & Level Exam
        $latestPlacement = PlacementAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->latest('completed_at')
            ->first(['id', 'estimated_level', 'total_score', 'completed_at']);

        $latestLevelExam = LevelExamAttempt::where('user_id', $user->id)
            ->latest('completed_at')
            ->first(['id', 'cefr_level', 'score_percent', 'passed', 'completed_at']);

        // Weak areas from error profile
        $weakAreas = LearnerErrorConcept::where('user_id', $user->id)
            ->where('total_errors_count', '>=', 1)
            ->orderBy('total_errors_count', 'desc')
            ->limit(3)
            ->get(['id', 'concept_slug', 'concept_name_ar', 'concept_name_en', 'category', 'total_errors_count', 'mastery_score']);

        // Daily goal calculation from real activity
        $todayXp = (int) XpTransaction::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->sum('amount');

        $dailyGoalMinutes = $profile->daily_goal_minutes ?? 15;
        $todayAttemptsCount = LessonAttempt::where('user_id', $user->id)
            ->whereDate('created_at', now()->toDateString())
            ->count();
        $todayMinutes = max($todayAttemptsCount * 7, (int) round($todayXp / 4));
        $dailyGoalProgress = min(100, (int) round(($todayMinutes / max(1, $dailyGoalMinutes)) * 100));

        // Current Level progress calculation
        $currentLevelCode = $profile->estimated_cefr_level ?? ($profile->max_unlocked_level ?? 'A1');
        $levelModel = CefrLevel::where('code', $currentLevelCode)->first();
        $totalLevelLessons = 0;
        $completedLevelLessons = 0;
        if ($levelModel) {
            $levelLessonIds = Lesson::whereHas('unit', fn($q) => $q->where('cefr_level_id', $levelModel->id))->pluck('id')->toArray();
            $totalLevelLessons = count($levelLessonIds);
            $completedLevelLessons = count(array_intersect($completedLessonIds, $levelLessonIds));
        }
        $levelProgressPercent = $totalLevelLessons > 0 ? (int) round(($completedLevelLessons / $totalLevelLessons) * 100) : 0;

        return response()->json([
            'profile' => $profile,
            'stats' => [
                'total_xp' => $profile->total_xp,
                'current_streak' => $profile->current_streak,
                'longest_streak' => $profile->longest_streak,
                'daily_goal_minutes' => $dailyGoalMinutes,
                'estimated_level' => $currentLevelCode,
                'max_unlocked_level' => $profile->max_unlocked_level ?? 'A1',
                'lessons_completed_count' => $completedLessonsCount,
                'vocabulary_count' => $vocabularyCount,
                'due_reviews_count' => $dueReviewsCount,
                'stories_completed_count' => $storiesCompletedCount,
            ],
            'course_progress' => [
                'level_code' => $currentLevelCode,
                'level_name_ar' => $levelModel?->name_ar ?? 'المستوى الحالي',
                'completed_lessons' => $completedLevelLessons,
                'total_lessons' => $totalLevelLessons,
                'progress_percent' => $levelProgressPercent,
            ],
            'daily_goal' => [
                'goal_minutes' => $dailyGoalMinutes,
                'today_minutes' => $todayMinutes,
                'today_xp' => $todayXp,
                'progress_percent' => $dailyGoalProgress,
                'is_goal_met' => $todayMinutes >= $dailyGoalMinutes,
            ],
            'recent_assessments' => [
                'placement' => $latestPlacement,
                'level_exam' => $latestLevelExam,
            ],
            'weak_areas' => $weakAreas,
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

        // Skill breakdown calculations from real user activity
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
                'listening' => min(100, $completedAttempts * 8 + 15),
                'speaking' => min(100, $completedAttempts * 6 + 10),
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
