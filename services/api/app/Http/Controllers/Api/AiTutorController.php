<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiTutorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTutorController extends Controller
{
    protected AiTutorService $tutorService;

    public function __construct(AiTutorService $tutorService)
    {
        $this->tutorService = $tutorService;
    }

    public function explain(Request $request): JsonResponse
    {
        $request->validate([
            'concept' => 'required|string|max:300',
            'dialect' => 'nullable|string|in:egyptian_arabic,msa',
        ]);

        $dialect = $request->dialect ?? ($request->user()?->profile?->explanation_dialect ?? 'egyptian_arabic');
        $result = $this->tutorService->explainConcept($request->user(), $request->concept, $dialect);

        return response()->json([
            'result' => $result,
        ]);
    }

    public function correct(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:500',
            'context' => 'nullable|string|max:300',
        ]);

        $result = $this->tutorService->correctUserText($request->user(), $request->text, $request->context);

        return response()->json([
            'result' => $result,
        ]);
    }

    public function practice(Request $request): JsonResponse
    {
        $request->validate([
            'topic' => 'required|string|max:100',
            'level' => 'nullable|string|in:A1,A2,B1,B2,C1,C2',
        ]);

        $level = $request->level ?? ($request->user()?->profile?->estimated_cefr_level ?? 'A1');
        $result = $this->tutorService->generatePractice($request->user(), $request->topic, $level);

        return response()->json([
            'result' => $result,
        ]);
    }

    public function speakingFeedback(Request $request): JsonResponse
    {
        $request->validate([
            'transcript' => 'required|string|max:300',
            'target_phrase' => 'required|string|max:300',
        ]);

        $result = $this->tutorService->evaluateSpeaking(
            $request->user(),
            $request->transcript,
            $request->target_phrase
        );

        return response()->json([
            'result' => $result,
        ]);
    }
}
