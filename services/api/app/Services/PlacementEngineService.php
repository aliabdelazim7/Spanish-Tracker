<?php

namespace App\Services;

use App\Models\PlacementAnswer;
use App\Models\PlacementAttempt;
use App\Models\PlacementQuestion;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PlacementEngineService
{
    /**
     * Start or resume an active placement test.
     */
    public function startAttempt(User $user): PlacementAttempt
    {
        $existing = PlacementAttempt::where('user_id', $user->id)
            ->where('status', 'in_progress')
            ->latest()
            ->first();

        if ($existing) {
            return $existing;
        }

        return PlacementAttempt::create([
            'user_id' => $user->id,
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Record an answer for a question.
     */
    public function recordAnswer(PlacementAttempt $attempt, int $questionId, string $userAnswer, ?int $timeSpentSeconds = null): array
    {
        $question = PlacementQuestion::findOrFail($questionId);

        // Normalize answers for comparison
        $cleanUser = trim(mb_strtolower($userAnswer));
        $cleanCorrect = trim(mb_strtolower($question->correct_answer));

        $isCorrect = ($cleanUser === $cleanCorrect);
        $points = $isCorrect ? $question->points : 0;

        $answer = PlacementAnswer::updateOrCreate(
            [
                'placement_attempt_id' => $attempt->id,
                'placement_question_id' => $question->id,
            ],
            [
                'user_answer' => $userAnswer,
                'is_correct' => $isCorrect,
                'points_awarded' => $points,
                'time_spent_seconds' => $timeSpentSeconds,
            ]
        );

        return [
            'is_correct' => $isCorrect,
            'explanation_ar' => $question->explanation_ar,
            'points' => $points,
        ];
    }

    /**
     * Finish placement test, calculate skill breakdown, assign CEFR level and recommendation.
     */
    public function finishAttempt(PlacementAttempt $attempt): PlacementAttempt
    {
        return DB::transaction(function () use ($attempt) {
            $answers = $attempt->answers()->with('question')->get();

            $totalPossiblePoints = 0;
            $totalEarnedPoints = 0;
            $skillStats = [];

            foreach ($answers as $ans) {
                $q = $ans->question;
                if (!$q) continue;

                $skill = $q->skill_category;
                if (!isset($skillStats[$skill])) {
                    $skillStats[$skill] = [
                        'earned' => 0,
                        'total' => 0,
                        'correct_count' => 0,
                        'question_count' => 0,
                    ];
                }

                $skillStats[$skill]['earned'] += $ans->points_awarded;
                $skillStats[$skill]['total'] += $q->points;
                $skillStats[$skill]['question_count'] += 1;
                if ($ans->is_correct) {
                    $skillStats[$skill]['correct_count'] += 1;
                }

                $totalEarnedPoints += $ans->points_awarded;
                $totalPossiblePoints += $q->points;
            }

            $percentage = $totalPossiblePoints > 0 
                ? (int) round(($totalEarnedPoints / $totalPossiblePoints) * 100) 
                : 0;

            // Deterministic, explainable CEFR evaluation
            $level = 'A1';
            $recommendedUnitId = null;
            $reportAr = '';

            // Find default starting unit (Unit 1)
            $firstUnit = Unit::orderBy('order_index')->first();

            if ($percentage < 35) {
                $level = 'A1';
                $recommendedUnitId = $firstUnit?->id;
                $reportAr = 'مستواك الحالي هو مبتدئ (A1). ننصحك بالبدء من الوحدة الأولى لتعلم أساسيات النطق، التحيات، وبناء الجمل الإسبانية بطريقة متينة.';
            } elseif ($percentage < 65) {
                $level = 'A2';
                // Find unit 2 or 3
                $a2Unit = Unit::where('order_index', '>=', 2)->orderBy('order_index')->first() ?? $firstUnit;
                $recommendedUnitId = $a2Unit?->id;
                $reportAr = 'مستواك الحالي هو أساسي متقدم (A2). لديك معرفة جيدة بالقواعد الأساسية والمفردات اليومية، وتستطيع التخطي إلى وحدات التواصل المتقدم والمحادثة اليومية.';
            } elseif ($percentage < 85) {
                $level = 'B1';
                $b1Unit = Unit::orderBy('order_index', 'desc')->first() ?? $firstUnit;
                $recommendedUnitId = $b1Unit?->id;
                $reportAr = 'مستواك الحالي متوسط (B1). أظهرت قدرة عالية على فهم التراكيب المعقدة وتصريف الأفعال الشائعة. أنت جاهز لمحتوى متقدم والقصص التفاعلية.';
            } else {
                $level = 'B2';
                $b2Unit = Unit::orderBy('order_index', 'desc')->first() ?? $firstUnit;
                $recommendedUnitId = $b2Unit?->id;
                $reportAr = 'أداء ممتاز! مستواك يقارب فوق المتوسط (B2). لديك حصيلة لغوية قوية وإلمام بالقواعد الدقيقة.';
            }

            // Skill breakdown formatting
            $skillBreakdown = [];
            foreach ($skillStats as $sk => $data) {
                $skPct = $data['total'] > 0 ? (int) round(($data['earned'] / $data['total']) * 100) : 0;
                $skillBreakdown[$sk] = [
                    'earned' => $data['earned'],
                    'total' => $data['total'],
                    'percentage' => $skPct,
                    'correct' => $data['correct_count'],
                    'count' => $data['question_count'],
                ];
            }

            $attempt->update([
                'status' => 'completed',
                'total_score' => $percentage,
                'estimated_level' => $level,
                'skill_breakdown' => $skillBreakdown,
                'summary_report_ar' => $reportAr,
                'recommended_unit_id' => $recommendedUnitId,
                'completed_at' => now(),
            ]);

            // Update user profile
            $user = $attempt->user;
            if ($user && $user->profile) {
                $ranks = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
                $targetRank = $ranks[$level] ?? 1;
                $completed = [];
                foreach ($ranks as $lvl => $rnk) {
                    if ($rnk < $targetRank) {
                        $completed[] = $lvl;
                    }
                }

                $user->profile->update([
                    'estimated_cefr_level' => $level,
                    'max_unlocked_level' => $level,
                    'completed_levels' => $completed,
                ]);
            }

            // Award placement test completion XP
            app(GamificationService::class)->awardXp(
                $user,
                50,
                'placement',
                $attempt->id,
                'إكمال اختبار تحديد المستوى'
            );

            return $attempt->fresh(['recommendedUnit']);
        });
    }
}
