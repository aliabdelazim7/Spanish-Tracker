<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AiTutorService;
use App\Services\RoleplayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AiTutorController extends Controller
{
    protected AiTutorService $tutorService;
    protected RoleplayService $roleplayService;

    public function __construct(AiTutorService $tutorService, RoleplayService $roleplayService)
    {
        $this->tutorService = $tutorService;
        $this->roleplayService = $roleplayService;
    }

    public function explain(Request $request): JsonResponse
    {
        $request->validate([
            'concept' => 'required|string|max:300',
            'lang' => 'nullable|string|in:ar,en',
            'dialect' => 'nullable|string|in:egyptian_arabic,msa',
        ]);

        $lang = $request->lang ?? ($request->header('X-Language') === 'en' ? 'en' : 'ar');
        $dialect = $request->dialect ?? ($request->user()?->profile?->explanation_dialect ?? 'egyptian_arabic');

        $result = $this->tutorService->explainConcept(
            $request->user(),
            $request->concept,
            $lang,
            $dialect
        );

        return response()->json([
            'result' => $result,
        ]);
    }

    public function correct(Request $request): JsonResponse
    {
        $request->validate([
            'text' => 'required|string|max:500',
            'context' => 'nullable|string|max:300',
            'lang' => 'nullable|string|in:ar,en',
        ]);

        $lang = $request->lang ?? ($request->header('X-Language') === 'en' ? 'en' : 'ar');

        $result = $this->tutorService->correctUserText(
            $request->user(),
            $request->text,
            $request->context,
            $lang
        );

        return response()->json([
            'result' => $result,
        ]);
    }

    public function practice(Request $request): JsonResponse
    {
        $request->validate([
            'topic' => 'required|string|max:100',
            'level' => 'nullable|string|in:A1,A2,B1,B2,C1,C2',
            'lang' => 'nullable|string|in:ar,en',
        ]);

        $lang = $request->lang ?? ($request->header('X-Language') === 'en' ? 'en' : 'ar');
        $level = $request->level ?? ($request->user()?->profile?->estimated_cefr_level ?? 'A1');

        $result = $this->tutorService->generatePractice(
            $request->user(),
            $request->topic,
            $level,
            $lang
        );

        return response()->json([
            'result' => $result,
        ]);
    }

    public function speakingFeedback(Request $request): JsonResponse
    {
        $request->validate([
            'transcript' => 'required|string|max:300',
            'target_phrase' => 'required|string|max:300',
            'lang' => 'nullable|string|in:ar,en',
        ]);

        $lang = $request->lang ?? ($request->header('X-Language') === 'en' ? 'en' : 'ar');

        $result = $this->tutorService->evaluateSpeaking(
            $request->user(),
            $request->transcript,
            $request->target_phrase,
            $lang
        );

        return response()->json([
            'result' => $result,
        ]);
    }

    public function hint(Request $request): JsonResponse
    {
        $request->validate([
            'question_or_concept' => 'required|string|max:300',
            'hint_stage' => 'nullable|integer|in:1,2',
            'lang' => 'nullable|string|in:ar,en',
        ]);

        $lang = $request->lang ?? ($request->header('X-Language') === 'en' ? 'en' : 'ar');
        $stage = (int) ($request->hint_stage ?? 1);

        $result = $this->tutorService->generateHint(
            $request->user(),
            $request->question_or_concept,
            $stage,
            $lang
        );

        return response()->json([
            'result' => $result,
        ]);
    }

    public function evaluateExercise(Request $request): JsonResponse
    {
        $request->validate([
            'question' => 'required|string|max:400',
            'user_answer' => 'required|string|max:200',
            'correct_answer' => 'required|string|max:200',
            'lang' => 'nullable|string|in:ar,en',
        ]);

        $lang = $request->lang ?? ($request->header('X-Language') === 'en' ? 'en' : 'ar');

        $result = $this->tutorService->evaluateExercise(
            $request->user(),
            $request->question,
            $request->user_answer,
            $request->correct_answer,
            $lang
        );

        return response()->json([
            'result' => $result,
        ]);
    }

    public function roleplays(Request $request): JsonResponse
    {
        $level = $request->query('level', $request->user()?->profile?->max_unlocked_level ?? 'A1');
        $scenarios = $this->roleplayService->getScenarios($level);

        return response()->json([
            'scenarios' => $scenarios,
        ]);
    }

    public function roleplayReply(Request $request): JsonResponse
    {
        $request->validate([
            'scenario_id' => 'required|string',
            'turn_index' => 'required|integer|min:1',
            'user_input' => 'required|string|min:1|max:400',
            'lang' => 'nullable|string|in:ar,en',
        ]);

        $lang = $request->input('lang', 'ar');

        $result = $this->roleplayService->evaluateTurn(
            $request->user(),
            $request->scenario_id,
            (int) $request->turn_index,
            $request->user_input,
            $lang
        );

        return response()->json($result);
    }
}
