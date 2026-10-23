<?php

namespace App\Services;

use App\Models\AiTutorLog;
use App\Models\LearnerErrorConcept;
use App\Models\User;
use App\Services\Ai\LanguageTutorProviderInterface;
use App\Services\Ai\MockLanguageTutorProvider;
use App\Services\Ai\OpenAiLanguageTutorProvider;
use Exception;
use Illuminate\Support\Facades\Log;

class AiTutorService
{
    protected LanguageTutorProviderInterface $provider;
    protected MockLanguageTutorProvider $fallbackProvider;

    public function __construct(?LanguageTutorProviderInterface $provider = null)
    {
        $this->fallbackProvider = new MockLanguageTutorProvider();

        if ($provider) {
            $this->provider = $provider;
            return;
        }

        $driver = config('services.ai.provider', 'mock');
        $apiKey = config('services.ai.api_key');

        // Only instantiate external AI provider if valid API key is explicitly configured
        if ($driver === 'openai' && !empty($apiKey)) {
            $this->provider = new OpenAiLanguageTutorProvider($apiKey);
        } else {
            $this->provider = $this->fallbackProvider;
        }
    }

    public function getProviderName(): string
    {
        return ($this->provider instanceof OpenAiLanguageTutorProvider && $this->provider->isOperational())
            ? 'openai'
            : 'local-expert-tutor';
    }

    public function isExternalAiOperational(): bool
    {
        return ($this->provider instanceof OpenAiLanguageTutorProvider && $this->provider->isOperational());
    }

    /**
     * Gather concise learner context (CEFR level and top recurring error concepts) without sending entire history.
     */
    protected function resolveLearnerContext(?User $user): array
    {
        $level = $user?->profile?->estimated_cefr_level ?? ($user?->profile?->max_unlocked_level ?? 'A1');
        $recentErrors = [];

        if ($user) {
            $recentErrors = LearnerErrorConcept::where('user_id', $user->id)
                ->where('total_errors_count', '>=', 2)
                ->orderBy('last_occurred_at', 'desc')
                ->take(3)
                ->pluck('concept_slug')
                ->toArray();
        }

        return [
            'level' => $level,
            'recent_errors' => $recentErrors,
        ];
    }

    public function explainConcept(
        ?User $user,
        string $concept,
        string $lang = 'ar',
        string $dialect = 'egyptian_arabic'
    ): array {
        $context = $this->resolveLearnerContext($user);
        $level = $context['level'];

        try {
            $result = $this->provider->explainGrammar($concept, $lang, $dialect, $level, $context);
            $this->logAction($user, 'explain', $concept, $result['explanation'] ?? ($result['explanation_ar'] ?? ''));
            return $result;
        } catch (Exception $e) {
            Log::warning('AI Tutor explain fallback triggered: ' . $e->getMessage());
            $result = $this->fallbackProvider->explainGrammar($concept, $lang, $dialect, $level, $context);
            $result['fallback_used'] = true;
            $this->logAction($user, 'explain', $concept, $result['explanation'] ?? '', 'fallback');
            return $result;
        }
    }

    public function correctUserText(
        ?User $user,
        string $text,
        ?string $context = null,
        string $lang = 'ar'
    ): array {
        $learnerCtx = $this->resolveLearnerContext($user);
        $level = $learnerCtx['level'];

        try {
            $result = $this->provider->correctText($text, $context, $lang, $level);
            $this->logAction($user, 'correct', $text, $result['corrected_text'] ?? '');
            return $result;
        } catch (Exception $e) {
            Log::warning('AI Tutor correct fallback triggered: ' . $e->getMessage());
            $result = $this->fallbackProvider->correctText($text, $context, $lang, $level);
            $result['fallback_used'] = true;
            $this->logAction($user, 'correct', $text, $result['corrected_text'] ?? '', 'fallback');
            return $result;
        }
    }

    public function generatePractice(
        ?User $user,
        string $topic,
        ?string $level = null,
        string $lang = 'ar'
    ): array {
        $learnerCtx = $this->resolveLearnerContext($user);
        $resolvedLevel = $level ?? $learnerCtx['level'];

        try {
            $result = $this->provider->generatePractice($topic, $resolvedLevel, $lang, $learnerCtx);
            $this->logAction($user, 'practice', "topic: {$topic}, level: {$resolvedLevel}", 'practice generated');
            return $result;
        } catch (Exception $e) {
            Log::warning('AI Tutor practice fallback triggered: ' . $e->getMessage());
            $result = $this->fallbackProvider->generatePractice($topic, $resolvedLevel, $lang, $learnerCtx);
            $result['fallback_used'] = true;
            $this->logAction($user, 'practice', "topic: {$topic}, level: {$resolvedLevel}", 'practice generated', 'fallback');
            return $result;
        }
    }

    public function evaluateSpeaking(
        ?User $user,
        string $transcript,
        string $targetPhrase,
        string $lang = 'ar'
    ): array {
        try {
            $result = $this->provider->evaluateSpeaking($transcript, $targetPhrase, $lang);
            $this->logAction($user, 'speaking_feedback', "heard: {$transcript}", $result['feedback'] ?? '');
            return $result;
        } catch (Exception $e) {
            Log::warning('AI Tutor speaking evaluation error: ' . $e->getMessage());
            return $this->fallbackProvider->evaluateSpeaking($transcript, $targetPhrase, $lang);
        }
    }

    public function generateHint(
        ?User $user,
        string $questionOrConcept,
        int $hintStage = 1,
        string $lang = 'ar'
    ): array {
        $learnerCtx = $this->resolveLearnerContext($user);
        $level = $learnerCtx['level'];

        try {
            return $this->provider->generateHint($questionOrConcept, $level, $lang, $hintStage);
        } catch (Exception $e) {
            Log::warning('AI Tutor hint fallback triggered: ' . $e->getMessage());
            return $this->fallbackProvider->generateHint($questionOrConcept, $level, $lang, $hintStage);
        }
    }

    public function evaluateExercise(
        ?User $user,
        string $question,
        string $userAnswer,
        string $correctAnswer,
        string $lang = 'ar'
    ): array {
        $learnerCtx = $this->resolveLearnerContext($user);
        $level = $learnerCtx['level'];

        try {
            return $this->provider->evaluateExerciseResponse($question, $userAnswer, $correctAnswer, $level, $lang);
        } catch (Exception $e) {
            Log::warning('AI Tutor exercise evaluation fallback triggered: ' . $e->getMessage());
            return $this->fallbackProvider->evaluateExerciseResponse($question, $userAnswer, $correctAnswer, $level, $lang);
        }
    }

    protected function logAction(?User $user, string $actionType, string $prompt, string $responseSummary, string $status = 'success'): void
    {
        if (!$user) return;

        AiTutorLog::create([
            'user_id' => $user->id,
            'action_type' => $actionType,
            'prompt_summary' => mb_substr(strip_tags($prompt), 0, 200),
            'response_summary' => mb_substr(strip_tags($responseSummary), 0, 200),
            'model_used' => $this->getProviderName(),
            'tokens_used' => 50,
            'status' => $status,
        ]);
    }
}
