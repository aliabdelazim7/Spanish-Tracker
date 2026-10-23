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

    /**
     * Get exam details and questions for a specific CEFR level.
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

        if ($targetLevelRank > $userLevelRank) {
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

        $questions = LevelExamQuestion::where('cefr_level', $levelCode)
            ->orderBy('order_index')
            ->get()
            ->map(function ($q) {
                return [
                    'id' => $q->id,
                    'skill_category' => $q->skill_category,
                    'question_text' => $q->question_text,
                    'prompt_ar' => $q->prompt_ar,
                    'audio_url' => $q->audio_url,
                    'options' => $q->options,
                    'points' => $q->points,
                    'order_index' => $q->order_index,
                ];
            });

        $alreadyPassed = in_array($levelCode, $profile?->completed_levels ?? []);

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
            'questions' => $questions,
            'pass_threshold_percent' => 70,
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
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|integer',
            'answers.*.answer' => 'required|string',
        ]);

        $questions = LevelExamQuestion::where('cefr_level', $levelCode)->get()->keyBy('id');

        if ($questions->isEmpty()) {
            return response()->json(['message' => 'لا توجد أسئلة مخصصة لاختبار هذا المستوى حالياً.'], 404);
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
                'question_text' => $question->question_text,
                'user_answer' => $userAns,
                'correct_answer' => $question->correct_answer,
                'is_correct' => $isCorrect,
                'explanation_ar' => $question->explanation_ar,
            ];
        }

        $scorePercent = $totalPossible > 0 ? (int) round(($totalEarned / $totalPossible) * 100) : 0;
        $passed = $scorePercent >= 70;
        $nextLevel = self::NEXT_LEVEL[$levelCode] ?? 'MAESTRIA';

        return DB::transaction(function () use ($user, $profile, $levelCode, $scorePercent, $passed, $nextLevel, $skillBreakdown, $detailedResults) {
            $xpAwarded = 0;
            $feedbackAr = '';

            if ($passed) {
                $completedLevels = $profile?->completed_levels ?? [];
                if (!in_array($levelCode, $completedLevels)) {
                    $completedLevels[] = $levelCode;
                }

                // Check if current unlocked level rank is less than next level rank
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

                // Award Level Graduation XP (+100 XP)
                $xpAwarded = 100;
                app(GamificationService::class)->awardXp(
                    $user,
                    $xpAwarded,
                    'level_graduation',
                    $profile->id ?? $user->id,
                    "اجتياز اختبار المستوى {$levelCode} بنجاح والتأهل للمستوى {$nextLevel}"
                );

                $feedbackAr = "تهانينا الحارة! لقد حققت نسبة {$scorePercent}% واجتزت اختبار المستوى {$levelCode} بجدارة. تم رسمياً فتح المستوى التالي ({$nextLevel}) بكامل محتوياته!";
            } else {
                $feedbackAr = "لقد حصلت على {$scorePercent}%. نسبة الاجتياز المطلوبة هي 70%. راجع الملاحظات والدروس أدناه، ثم أعد الاختبار لفتح المستوى التالي!";
            }

            $attempt = LevelExamAttempt::create([
                'user_id' => $user->id,
                'cefr_level' => $levelCode,
                'score_percent' => $scorePercent,
                'passed' => $passed,
                'skill_breakdown' => $skillBreakdown,
                'feedback_ar' => $feedbackAr,
                'xp_awarded' => $xpAwarded,
                'completed_at' => now(),
            ]);

            return response()->json([
                'passed' => $passed,
                'score_percent' => $scorePercent,
                'current_level' => $levelCode,
                'next_level' => $passed ? $nextLevel : null,
                'feedback_ar' => $feedbackAr,
                'xp_awarded' => $xpAwarded,
                'skill_breakdown' => $skillBreakdown,
                'detailed_results' => $detailedResults,
                'unlocked_levels' => $profile?->completed_levels ?? [],
                'max_unlocked_level' => $profile?->max_unlocked_level ?? $levelCode,
            ]);
        });
    }
}
