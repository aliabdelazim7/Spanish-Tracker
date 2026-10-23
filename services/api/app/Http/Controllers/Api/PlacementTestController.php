<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PlacementAnswerRequest;
use App\Models\PlacementAttempt;
use App\Models\PlacementQuestion;
use App\Services\PlacementEngineService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlacementTestController extends Controller
{
    protected PlacementEngineService $placementService;

    public function __construct(PlacementEngineService $placementService)
    {
        $this->placementService = $placementService;
    }

    public function start(Request $request): JsonResponse
    {
        $user = $request->user();
        $attempt = $this->placementService->startAttempt($user);
        $questionIds = $attempt->question_ids ?? [];

        // Fetch active questions preserving progressive difficulty order without exposing answers
        $query = PlacementQuestion::where('is_active', true)
            ->select(['id', 'skill_category', 'difficulty_level', 'question_type', 'question_text', 'context_text', 'prompt_ar', 'audio_url', 'options', 'points']);

        if (!empty($questionIds)) {
            $questions = $query->whereIn('id', $questionIds)
                ->get()
                ->sortBy(function ($q) use ($questionIds) {
                    return array_search($q->id, $questionIds);
                })
                ->values();
        } else {
            $questions = $query->orderBy('id')->get();
        }

        $answeredQuestionIds = $attempt->answers()->pluck('placement_question_id')->toArray();

        return response()->json([
            'attempt' => $attempt,
            'questions' => $questions,
            'answered_question_ids' => $answeredQuestionIds,
        ]);
    }

    public function getAttempt(Request $request, int $attemptId): JsonResponse
    {
        $attempt = PlacementAttempt::where('user_id', $request->user()->id)
            ->with(['answers.question', 'recommendedUnit'])
            ->findOrFail($attemptId);

        return response()->json([
            'attempt' => $attempt,
        ]);
    }

    public function submitAnswer(PlacementAnswerRequest $request, int $attemptId): JsonResponse
    {
        $attempt = PlacementAttempt::where('user_id', $request->user()->id)
            ->where('status', 'in_progress')
            ->findOrFail($attemptId);

        $result = $this->placementService->recordAnswer(
            $attempt,
            $request->question_id,
            $request->user_answer,
            $request->time_spent_seconds
        );

        return response()->json([
            'message' => 'تم حفظ الإجابة',
            'result' => $result,
        ]);
    }

    public function finish(Request $request, int $attemptId): JsonResponse
    {
        $attempt = PlacementAttempt::where('user_id', $request->user()->id)
            ->findOrFail($attemptId);

        $completedAttempt = $this->placementService->finishAttempt($attempt);

        return response()->json([
            'message' => 'تم إنهاء اختبار تحديد المستوى وحساب النتائج بنجاح',
            'attempt' => $completedAttempt,
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $attempts = PlacementAttempt::where('user_id', $request->user()->id)
            ->with('recommendedUnit')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'attempts' => $attempts,
        ]);
    }
}
