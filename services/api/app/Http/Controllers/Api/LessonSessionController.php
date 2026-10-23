<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExerciseAnswerRequest;
use App\Models\ExerciseAttempt;
use App\Models\Lesson;
use App\Models\LessonAttempt;
use App\Models\LessonExercise;
use App\Services\GamificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LessonSessionController extends Controller
{
    protected GamificationService $gamificationService;

    public function __construct(GamificationService $gamificationService)
    {
        $this->gamificationService = $gamificationService;
    }

    public function start(Request $request, int $lessonId): JsonResponse
    {
        $user = $request->user();
        $lesson = Lesson::where('is_published', true)->with('unit.level')->findOrFail($lessonId);

        $ranks = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
        $profile = $user->profile;
        if ($profile) {
            $userMaxLevel = $profile->max_unlocked_level ?? ($profile->estimated_cefr_level ?? 'A1');
            $userRank = $ranks[$userMaxLevel] ?? 1;
            $levelCode = $lesson->unit?->level?->code ?? 'A1';
            $levelRank = $ranks[$levelCode] ?? 1;
            $completedLevels = $profile->completed_levels ?? [];

            if ($levelRank > $userRank && !in_array($levelCode, $completedLevels)) {
                return response()->json([
                    'message' => "لا يمكنك بدء درس في مستوى مقفل ({$levelCode}).",
                    'is_unlocked' => false,
                ], 403);
            }
        }

        // Resume in-progress or create new attempt
        $attempt = LessonAttempt::firstOrCreate(
            [
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'status' => 'in_progress',
            ],
            [
                'started_at' => Carbon::now(),
                'score_percent' => 0,
                'xp_awarded' => 0,
                'is_reward_claimed' => false,
            ]
        );

        $answeredExerciseIds = ExerciseAttempt::where('lesson_attempt_id', $attempt->id)
            ->where('is_correct', true)
            ->pluck('lesson_exercise_id')
            ->toArray();

        // Load exercises for the lesson (without revealing correct answers upfront)
        $exercises = LessonExercise::where('lesson_id', $lesson->id)
            ->select([
                'id', 'lesson_id', 'exercise_type', 'prompt', 'prompt_ar',
                'target_phrase', 'audio_url', 'options', 'xp_value', 'order_index'
            ])
            ->orderBy('order_index')
            ->get();

        return response()->json([
            'attempt' => $attempt,
            'lesson' => $lesson->load('unit.level'),
            'exercises' => $exercises,
            'answered_exercise_ids' => $answeredExerciseIds,
        ]);
    }

    public function answerExercise(ExerciseAnswerRequest $request, int $lessonId, int $exerciseId): JsonResponse
    {
        $user = $request->user();

        $attempt = LessonAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $exercise = LessonExercise::where('lesson_id', $lessonId)->findOrFail($exerciseId);

        $userAnswer = $request->user_answer;
        $correctAnswers = (array) $exercise->correct_answers;

        $isCorrect = false;

        if ($exercise->exercise_type === 'matching_pairs') {
            // Compare key-value pairs
            if (is_array($userAnswer)) {
                $isCorrect = true;
                foreach ($correctAnswers as $k => $v) {
                    if (!isset($userAnswer[$k]) || trim(mb_strtolower((string)$userAnswer[$k])) !== trim(mb_strtolower((string)$v))) {
                        $isCorrect = false;
                        break;
                    }
                }
            }
        } elseif ($exercise->exercise_type === 'word_order') {
            // Compare arrays of tokens or joined string
            if (is_array($userAnswer)) {
                $userStr = implode(' ', array_map('trim', $userAnswer));
            } else {
                $userStr = trim((string)$userAnswer);
            }
            $cleanUser = trim(preg_replace('/\s+/', ' ', mb_strtolower($userStr)));

            foreach ($correctAnswers as $ans) {
                $cleanAns = trim(preg_replace('/\s+/', ' ', mb_strtolower((string)$ans)));
                if ($cleanUser === $cleanAns) {
                    $isCorrect = true;
                    break;
                }
            }
        } else {
            // General string comparison with accents / punctuation trimmed
            $userClean = trim(mb_strtolower((string)$userAnswer));
            // strip leading/trailing inverted question/exclamation marks
            $userClean = trim($userClean, " ¿?!¡.,");

            foreach ($correctAnswers as $ans) {
                $ansClean = trim(mb_strtolower((string)$ans));
                $ansClean = trim($ansClean, " ¿?!¡.,");
                if ($userClean === $ansClean) {
                    $isCorrect = true;
                    break;
                }
            }
        }

        ExerciseAttempt::create([
            'lesson_attempt_id' => $attempt->id,
            'user_id' => $user->id,
            'lesson_exercise_id' => $exercise->id,
            'user_answer' => is_array($userAnswer) ? json_encode($userAnswer) : (string)$userAnswer,
            'is_correct' => $isCorrect,
        ]);

        return response()->json([
            'is_correct' => $isCorrect,
            'explanation_ar' => $exercise->explanation_ar,
            'hint_ar' => $exercise->hint_ar,
            'correct_answers' => $isCorrect ? null : $correctAnswers,
        ]);
    }

    public function complete(Request $request, int $lessonId): JsonResponse
    {
        $user = $request->user();

        $attempt = LessonAttempt::where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->where('status', 'in_progress')
            ->firstOrFail();

        $lesson = Lesson::findOrFail($lessonId);
        $totalExercises = $lesson->exercises()->count();

        return DB::transaction(function () use ($attempt, $lesson, $user, $totalExercises) {
            $correctExercisesCount = ExerciseAttempt::where('lesson_attempt_id', $attempt->id)
                ->where('is_correct', true)
                ->distinct('lesson_exercise_id')
                ->count('lesson_exercise_id');

            $scorePercent = $totalExercises > 0
                ? (int) round(($correctExercisesCount / $totalExercises) * 100)
                : 100;

            $xpToAward = 0;

            // Check if this lesson was already rewarded to prevent infinite XP farming
            $alreadyRewarded = LessonAttempt::where('user_id', $user->id)
                ->where('lesson_id', $lesson->id)
                ->where('is_reward_claimed', true)
                ->exists();

            if (!$alreadyRewarded && $scorePercent >= 60) {
                $xpToAward = $lesson->xp_reward;
                $this->gamificationService->awardXp(
                    $user,
                    $xpToAward,
                    'lesson',
                    $attempt->id,
                    "إكمال درس: {$lesson->title_ar}"
                );
                $attempt->is_reward_claimed = true;
            }

            $attempt->status = 'completed';
            $attempt->score_percent = $scorePercent;
            $attempt->xp_awarded = $xpToAward;
            $attempt->completed_at = Carbon::now();
            $attempt->save();

            // Find next lesson recommendation
            $nextLesson = Lesson::where('unit_id', $lesson->unit_id)
                ->where('order_index', '>', $lesson->order_index)
                ->where('is_published', true)
                ->orderBy('order_index')
                ->first();

            return response()->json([
                'message' => 'تم إنهاء الدرس بنجاح!',
                'attempt' => $attempt,
                'score_percent' => $scorePercent,
                'xp_awarded' => $xpToAward,
                'is_first_completion' => !$alreadyRewarded,
                'next_lesson_id' => $nextLesson?->id,
            ]);
        });
    }

    public function progress(Request $request, int $lessonId): JsonResponse
    {
        $attempt = LessonAttempt::where('user_id', $request->user()->id)
            ->where('lesson_id', $lessonId)
            ->latest()
            ->first();

        return response()->json([
            'attempt' => $attempt,
        ]);
    }
}
