<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CefrLevel;
use App\Models\LevelExamAttempt;
use App\Models\LevelExamQuestion;
use App\Models\LessonAttempt;
use App\Models\Unit;
use App\Services\GamificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelExamController extends Controller
{
    private const LEVEL_ORDER = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
    private const NEXT_LEVEL = [
        'A1' => 'A2',
        'A2' => 'B1',
        'B1' => 'B2',
        'B2' => 'C1',
        'C1' => 'C2',
        'C2' => 'MAESTRIA',
    ];

    public const PASS_THRESHOLD_PERCENT = 80; // 80% passing threshold as required

    /**
     * Get balanced exam questions for a specific CEFR level.
     */
    public function getExam(Request $request, string $levelCode): JsonResponse
    {
        $levelCode = strtoupper($levelCode);
        $user = $request->user();
        $profile = $user->profile;

        $level = CefrLevel::where('code', $levelCode)->firstOrFail();

        // Check user unlocked level
        $userMaxLevel = $profile?->max_unlocked_level ?? ($profile?->estimated_cefr_level ?? 'A1');
        $userLevelRank = self::LEVEL_ORDER[$userMaxLevel] ?? 1;
        $targetLevelRank = self::LEVEL_ORDER[$levelCode] ?? 1;
        $completedLevels = $profile?->completed_levels ?? [];

        if ($targetLevelRank > $userLevelRank && !in_array($levelCode, $completedLevels)) {
            return response()->json([
                'message' => "هذا المستوى ({$levelCode}) مغلق حالياً. يجب اجتياز المستويات السابقة أولاً.",
                'is_unlocked' => false,
            ], 403);
        }

        // Count level lessons and user completed lessons
        $unitIds = Unit::where('cefr_level_id', $level->id)->pluck('id');
        $totalLessons = \App\Models\Lesson::whereIn('unit_id', $unitIds)->count();
        $completedLessons = LessonAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->whereIn('lesson_id', function ($q) use ($unitIds) {
                $q->select('id')->from('lessons')->whereIn('unit_id', $unitIds);
            })
            ->distinct('lesson_id')
            ->count('lesson_id');

        // Balanced skill-based random sampling (2 per skill category)
        $skills = ['vocabulary', 'grammar', 'verbs', 'listening', 'reading', 'practical'];
        $sampledQuestions = collect();

        foreach ($skills as $skill) {
            $pool = LevelExamQuestion::where('cefr_level', $levelCode)
                ->where('skill_category', $skill)
                ->inRandomOrder()
                ->take(2)
                ->get();
            $sampledQuestions = $sampledQuestions->merge($pool);
        }

        // If less than 12, fill up from remaining pool
        if ($sampledQuestions->count() < 12) {
            $existingIds = $sampledQuestions->pluck('id')->toArray();
            $extra = LevelExamQuestion::where('cefr_level', $levelCode)
                ->whereNotIn('id', $existingIds)
                ->inRandomOrder()
                ->take(12 - $sampledQuestions->count())
                ->get();
            $sampledQuestions = $sampledQuestions->merge($extra);
        }

        // Fallback to all if pool is smaller
        if ($sampledQuestions->isEmpty()) {
            $sampledQuestions = LevelExamQuestion::where('cefr_level', $levelCode)->get();
        }

        $formattedQuestions = $sampledQuestions->shuffle()->map(function ($q, $index) {
            return [
                'id' => $q->id,
                'skill_category' => $q->skill_category,
                'question_text' => $q->question_text,
                'prompt_ar' => $q->prompt_ar,
                'audio_url' => $q->audio_url,
                'options' => $q->options,
                'points' => $q->points,
                'order_index' => $index + 1,
            ];
        });

        $alreadyPassed = in_array($levelCode, $completedLevels);

        return response()->json([
            'level' => [
                'code' => $level->code,
                'name' => $level->name,
                'name_ar' => $level->name_ar,
            ],
            'total_lessons' => $totalLessons,
            'completed_lessons' => $completedLessons,
            'completion_percent' => $totalLessons > 0 ? (int) round(($completedLessons / $totalLessons) * 100) : 100,
            'already_passed' => $alreadyPassed,
            'questions' => $formattedQuestions,
            'pass_threshold_percent' => self::PASS_THRESHOLD_PERCENT,
        ]);
    }

    /**
     * Submit answers for the level graduation exam.
     */
    public function submitExam(Request $request, string $levelCode): JsonResponse
    {
        $levelCode = strtoupper($levelCode);
        $user = $request->user();
        $profile = $user->profile;

        $request->validate([
            'answers' => 'required|array|min:1',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer' => 'required|string',
        ]);

        $submittedQuestionIds = collect($request->input('answers'))->pluck('question_id')->toArray();
        $questions = LevelExamQuestion::where('cefr_level', $levelCode)
            ->whereIn('id', $submittedQuestionIds)
            ->get()
            ->keyBy('id');

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'لا توجد أسئلة صالحة للتقييم في هذا الطلب.'], 404);
        }

        $totalEarned = 0;
        $totalPossible = 0;
        $skillBreakdown = [];
        $detailedResults = [];

        foreach ($request->input('answers') as $ans) {
            $qId = $ans['question_id'];
            $userAns = trim($ans['answer']);

            if (!isset($questions[$qId])) {
                continue;
            }

            $question = $questions[$qId];
            $isCorrect = mb_strtolower(trim($question->correct_answer)) === mb_strtolower($userAns);
            $points = $isCorrect ? $question->points : 0;

            $totalEarned += $points;
            $totalPossible += $question->points;

            $skill = $question->skill_category;
            if (!isset($skillBreakdown[$skill])) {
                $skillBreakdown[$skill] = ['earned' => 0, 'total' => 0];
            }
            $skillBreakdown[$skill]['earned'] += $points;
            $skillBreakdown[$skill]['total'] += $question->points;

            $detailedResults[] = [
                'question_id' => $qId,
                'skill_category' => $skill,
                'question_text' => $question->question_text,
                'user_answer' => $userAns,
                'correct_answer' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'explanation_ar' => $question->explanation_ar,
            ];
        }

        $passThreshold = (int) config('learning.exam_pass_threshold', self::PASS_THRESHOLD_PERCENT);
        $scorePercent = $totalPossible > 0 ? (int) round(($totalEarned / $totalPossible) * 100) : 0;
        $passed = $scorePercent >= $passThreshold;
        $nextLevel = self::NEXT_LEVEL[$levelCode] ?? 'MAESTRIA';

        // Calculate structured section performance with percentages
        $sectionPerformance = [];
        $weakSkills = [];
        foreach ($skillBreakdown as $skill => $data) {
            $pct = $data['total'] > 0 ? (int) round(($data['earned'] / $data['total']) * 100) : 0;
            $sectionPerformance[$skill] = [
                'earned' => $data['earned'],
                'total' => $data['total'],
                'percent' => $pct,
            ];
            if ($pct < $passThreshold) {
                $weakSkills[] = $skill;
            }
        }

        // Query targeted remedial lessons based on actual weak skills or mistakes
        $recommendedLessons = [];
        $unitIds = Unit::whereHas('level', function ($q) use ($levelCode) {
            $q->where('code', $levelCode);
        })->pluck('id');

        if (!empty($weakSkills)) {
            $recommendedLessons = \App\Models\Lesson::whereIn('unit_id', $unitIds)
                ->where(function ($q) use ($weakSkills) {
                    $q->whereIn('skill_category', $weakSkills)
                      ->orWhereNull('skill_category');
                })
                ->select(['id', 'title', 'title_ar', 'skill_category', 'estimated_minutes'])
                ->take(4)
                ->get()
                ->toArray();
        }

        if (empty($recommendedLessons)) {
            $recommendedLessons = \App\Models\Lesson::whereIn('unit_id', $unitIds)
                ->select(['id', 'title', 'title_ar', 'skill_category', 'estimated_minutes'])
                ->take(2)
                ->get()
                ->toArray();
        }

        return DB::transaction(function () use ($user, $profile, $levelCode, $scorePercent, $passed, $nextLevel, $passThreshold, $sectionPerformance, $detailedResults, $recommendedLessons) {
            $xpAwarded = 0;
            $feedbackAr = '';
            $completedLevels = $profile?->completed_levels ?? [];
            $alreadyCompleted = in_array($levelCode, $completedLevels);

            if ($passed) {
                if (!$alreadyCompleted) {
                    $completedLevels[] = $levelCode;

                    // Promote max unlocked level
                    $currentMaxRank = self::LEVEL_ORDER[$profile?->max_unlocked_level ?? 'A1'] ?? 1;
                    $nextRank = self::LEVEL_ORDER[$nextLevel] ?? $currentMaxRank;
                    $newMaxLevel = $nextRank > $currentMaxRank ? $nextLevel : ($profile?->max_unlocked_level ?? $levelCode);

                    if ($profile) {
                        $profile->update([
                            'completed_levels' => $completedLevels,
                            'max_unlocked_level' => $newMaxLevel,
                            'estimated_cefr_level' => $newMaxLevel,
                        ]);
                    }

                    // Award Level Graduation XP (+100 XP) with idempotency protection
                    $xpAwarded = 100;
                    app(GamificationService::class)->awardXp(
                        $user,
                        $xpAwarded,
                        'level_graduation',
                        $profile->id ?? $user->id,
                        "اجتياز اختبار المستوى {$levelCode} بنجاح بنسبة {$scorePercent}% والتأهل للمستوى {$nextLevel}"
                    );
                }

                $feedbackAr = "تهانينا! لقد حققت نسبة {$scorePercent}% (الحد الأدنى المطلوب {$passThreshold}%) واجتزت اختبار المستوى {$levelCode}. تم فتح المستوى التالي ({$nextLevel}) بنجاح.";
            } else {
                $feedbackAr = "لقد حصلت على {$scorePercent}%. الحد الأدنى المطلوب لاجتياز المستوى هو {$passThreshold}%. يوصى بمراجعة الدروس الموصى بها أدناه لتقوية المهارات التي واجهت فيها صعوبة قبل إعادة المحاولة.";
            }

            $previousAttempts = LevelExamAttempt::where('user_id', $user->id)->where('cefr_level', $levelCode)->count();
            $attemptNumber = $previousAttempts + 1;

            // Persist full assessment attempt history
            $attempt = LevelExamAttempt::create([
                'user_id' => $user->id,
                'cefr_level' => $levelCode,
                'attempt_number' => $attemptNumber,
                'score_percent' => $scorePercent,
                'passed' => $passed,
                'skill_breakdown' => $sectionPerformance,
                'feedback_ar' => $feedbackAr,
                'recommended_lessons' => $recommendedLessons,
                'xp_awarded' => $xpAwarded,
                'completed_at' => now(),
            ]);

            return response()->json([
                'attempt_id' => $attempt->id,
                'attempt_number' => $attemptNumber,
                'passed' => $passed,
                'score_percent' => $scorePercent,
                'pass_threshold_percent' => $passThreshold,
                'current_level' => $levelCode,
                'next_level' => $passed ? $nextLevel : null,
                'feedback_ar' => $feedbackAr,
                'xp_awarded' => $xpAwarded,
                'section_performance' => $sectionPerformance,
                'recommended_lessons' => $recommendedLessons,
                'detailed_results' => $detailedResults,
                'unlocked_levels' => $completedLevels,
                'max_unlocked_level' => $profile?->max_unlocked_level ?? $levelCode,
                'academic_disclaimer_ar' => 'الاختبار متعدد الخيارات يقيم بدقة الكفاءات المعجمية والقواعدية والاستيعاب القرائي والسمعي. ولا يحل بمفرده محل التقييم الشفوي والمقالي المباشر (المتاح عبر استوديو اليوميات التعبيرية والتحدث الصوتي التفاعلي بالمنصة).',
            ]);
        });
    }

    /**
     * Get exam attempt history for a specific level.
     */
    public function history(Request $request, string $levelCode): JsonResponse
    {
        $levelCode = strtoupper($levelCode);
        $user = $request->user();

        $attempts = LevelExamAttempt::where('user_id', $user->id)
            ->where('cefr_level', $levelCode)
            ->orderBy('attempt_number', 'desc')
            ->get();

        return response()->json([
            'level_code' => $levelCode,
            'total_attempts' => $attempts->count(),
            'best_score' => $attempts->max('score_percent') ?? 0,
            'is_passed' => $attempts->where('passed', true)->isNotEmpty(),
            'attempts' => $attempts,
        ]);
    }
}
