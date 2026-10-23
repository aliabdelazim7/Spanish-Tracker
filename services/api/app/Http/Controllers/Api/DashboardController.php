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
use App\Models\VocabularyItem;
use App\Models\XpTransaction;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }
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

    /**
     * Generate 5 calibrated rapid questions for the Daily 3-Minute Quick Sprint.
     */
    public function dailySprint(Request $request): JsonResponse
    {
        $user = $request->user();
        $userLevel = $user->profile?->max_unlocked_level ?? ($user->profile?->estimated_cefr_level ?? 'A1');

        // Check if sprint already completed today
        $todayDate = Carbon::today()->toDateString();
        $alreadyCompletedToday = XpTransaction::where('user_id', $user->id)
            ->where('source_type', 'daily_sprint')
            ->whereDate('created_at', $todayDate)
            ->exists();

        // Pull 5 diverse vocabulary items for this sprint
        $items = VocabularyItem::where('cefr_level', $userLevel)
            ->where('is_published', true)
            ->inRandomOrder()
            ->take(5)
            ->get();

        if ($items->count() < 5) {
            $items = VocabularyItem::where('is_published', true)
                ->inRandomOrder()
                ->take(5)
                ->get();
        }

        $allMeanings = VocabularyItem::where('is_published', true)
            ->pluck('arabic_meaning')
            ->unique()
            ->toArray();

        $questions = [];
        $order = 1;

        foreach ($items as $item) {
            // Pick 3 distractors
            $distractors = array_values(array_diff($allMeanings, [$item->arabic_meaning]));
            shuffle($distractors);
            $selectedDistractors = array_slice($distractors, 0, 3);

            $options = array_merge([$item->arabic_meaning], $selectedDistractors);
            shuffle($options);

            $questionType = ($item->part_of_speech === 'verb') ? 'verb_speed' : (($order % 2 === 0) ? 'listening' : 'vocab');

            $questions[] = [
                'id' => $item->id,
                'order' => $order++,
                'type' => $questionType,
                'spanish_word' => $item->spanish_word,
                'audio_text' => $item->spanish_word,
                'example_sentence' => $item->example_sentence_es,
                'prompt_ar' => $questionType === 'listening' 
                    ? 'استمع للنطق الإسباني واختر المعنى الصحيح:' 
                    : "ما هو المعنى الصحيح لكلمة \"{$item->spanish_word}\"؟",
                'options' => $options,
            ];
        }

        return response()->json([
            'already_completed_today' => $alreadyCompletedToday,
            'sprint_date' => $todayDate,
            'level' => $userLevel,
            'xp_reward' => 25,
            'questions' => $questions,
        ]);
    }

    /**
     * Submit answers for the Daily 3-Minute Quick Sprint and generate shareable accomplishment card.
     */
    public function submitDailySprint(Request $request): JsonResponse
    {
        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer',
            'answers.*.selected_option' => 'required|string',
        ]);

        $user = $request->user();
        $submittedAnswers = $request->input('answers');
        $questionIds = collect($submittedAnswers)->pluck('question_id')->toArray();

        $items = VocabularyItem::whereIn('id', $questionIds)->get()->keyBy('id');

        $correctCount = 0;
        $totalQuestions = count($submittedAnswers);

        foreach ($submittedAnswers as $ans) {
            $qId = $ans['question_id'];
            $selected = trim($ans['selected_option']);

            if (isset($items[$qId])) {
                $correct = trim($items[$qId]->arabic_meaning);
                if ($selected === $correct) {
                    $correctCount++;
                }
            }
        }

        $accuracyPercent = $totalQuestions > 0 ? (int) round(($correctCount / $totalQuestions) * 100) : 0;
        $passed = $accuracyPercent >= 60;

        $todayDate = Carbon::today()->toDateString();
        $idempotencyKey = (int) Carbon::today()->format('Ymd');
        $xpAwarded = 0;

        if ($passed) {
            $transaction = $this->gamificationService->awardXp(
                $user,
                25,
                'daily_sprint',
                $idempotencyKey,
                'إكمال تحدي الـ 3 دقائق اليومي'
            );
            $xpAwarded = $transaction ? 25 : 0;
        }

        $profile = $user->profile()->firstOrCreate(['user_id' => $user->id]);
        $streak = $profile->current_streak;
        $level = $profile->max_unlocked_level ?? 'A1';

        // Milestone shareable badge payload
        $shareableCard = [
            'learner_name' => $user->name,
            'streak_days' => max(1, $streak),
            'longest_streak' => max(1, $profile->longest_streak),
            'cefr_level' => $level,
            'total_xp' => $profile->total_xp,
            'words_mastered' => UserVocabulary::where('user_id', $user->id)->count(),
            'accuracy_percent' => $accuracyPercent,
            'correct_count' => $correctCount,
            'total_questions' => $totalQuestions,
            'date_formatted' => Carbon::now()->format('d M Y'),
            'badge_title' => $streak >= 7 ? '🔥 بطل الاستمرارية الأسبوعية' : '⚡ نجم التدريب اليومي السريع',
            'share_caption' => "حققت إنجازاً جديداً في الإسبانية اليوم! 🔥 سلسلة متواصلة لـ {$streak} أيام في منصة Ruta Español. 🇪🇸✨",
        ];

        return response()->json([
            'success' => true,
            'passed' => $passed,
            'correct_count' => $correctCount,
            'total_questions' => $totalQuestions,
            'accuracy_percent' => $accuracyPercent,
            'xp_awarded' => $xpAwarded,
            'shareable_card' => $shareableCard,
        ]);
    }
}
