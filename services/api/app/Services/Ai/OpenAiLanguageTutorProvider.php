<?php

namespace App\Services\Ai;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenAiLanguageTutorProvider implements LanguageTutorProviderInterface
{
    protected string $apiKey;
    protected string $model;
    protected string $baseUrl;
    protected int $timeoutSeconds;

    public function __construct(?string $apiKey = null, ?string $model = null, int $timeoutSeconds = 10)
    {
        $this->apiKey = $apiKey ?? config('services.ai.api_key', '');
        $this->model = $model ?? config('services.ai.model', 'gpt-4o-mini');
        $this->baseUrl = config('services.ai.base_url', 'https://api.openai.com/v1');
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function isOperational(): bool
    {
        return !empty($this->apiKey);
    }

    public function explainGrammar(
        string $concept,
        string $lang = 'ar',
        string $dialect = 'egyptian_arabic',
        string $level = 'A1',
        ?array $learnerContext = null
    ): array {
        $this->ensureConfigured();

        $systemPrompt = "You are an expert, compassionate Spanish pedagogical tutor. Explain the grammar concept in " .
            ($lang === 'en' ? 'English' : ($dialect === 'egyptian_arabic' ? 'Egyptian Arabic' : 'Modern Standard Arabic')) .
            ". Tailor the explanation strictly to CEFR Level {$level}. Include natural Spanish examples with translations. " .
            "Distinguish between universal grammar and regional variations. Make uncertainty explicit when usage depends on context. " .
            "Return valid JSON matching keys: concept, level, explanation, examples (array of {es, translation}), common_mistakes, regional_variations, is_context_dependent (bool).";

        $userPrompt = "Explain Spanish grammar concept: '{$concept}'." .
            (!empty($learnerContext['recent_errors']) ? " The learner recently struggled with: " . implode(', ', $learnerContext['recent_errors']) : "");

        $response = $this->callModel($systemPrompt, $userPrompt);

        return array_merge($response, [
            'provider' => 'openai',
            'model' => $this->model,
            'is_ai_generated' => true,
            'explanation_lang' => $lang,
        ]);
    }

    public function correctText(
        string $userSpanishText,
        ?string $context = null,
        string $lang = 'ar',
        string $level = 'A1'
    ): array {
        $this->ensureConfigured();

        $systemPrompt = "You are an expert Spanish linguistic editor. Review the learner's Spanish text. " .
            "Language of explanation: " . ($lang === 'en' ? 'English' : 'Arabic') . ". CEFR Level: {$level}. " .
            "Carefully categorize every error into one of: 'grammar_error', 'spelling_mistake', 'unnatural_phrasing', 'regional_variation'. " .
            "Provide why it is wrong and a natural alternative. Return valid JSON matching: " .
            "original_text, corrected_text, is_grammatically_correct (bool), feedback, errors (array of {error_category, original, correction, reason, is_context_dependent (bool), regional_note}), natural_alternative.";

        $userPrompt = "Text: '{$userSpanishText}'" . ($context ? " Context: '{$context}'" : "");

        $response = $this->callModel($systemPrompt, $userPrompt);

        return array_merge($response, [
            'provider' => 'openai',
            'model' => $this->model,
            'is_ai_generated' => true,
        ]);
    }

    public function generatePractice(
        string $topic,
        string $level = 'A1',
        string $lang = 'ar',
        ?array $learnerContext = null
    ): array {
        $this->ensureConfigured();

        $systemPrompt = "Generate 2 level-appropriate exercises for CEFR Level {$level} on topic '{$topic}'. " .
            "Language for prompts and hints: " . ($lang === 'en' ? 'English' : 'Arabic') . ". " .
            "Return valid JSON: topic, level, questions (array of {id, prompt, prompt_translated, sentence, options, correct, hint, explanation}).";

        $userPrompt = "Create 2 Spanish exercises for topic '{$topic}' at level {$level}.";

        $response = $this->callModel($systemPrompt, $userPrompt);

        return array_merge($response, [
            'provider' => 'openai',
            'model' => $this->model,
            'is_ai_generated' => true,
        ]);
    }

    public function evaluateSpeaking(string $transcript, string $targetPhrase, string $lang = 'ar'): array
    {
        // Use local deterministic phoneme & word distance evaluator first
        $local = new MockLanguageTutorProvider();
        return $local->evaluateSpeaking($transcript, $targetPhrase, $lang);
    }

    public function generateHint(
        string $questionOrConcept,
        string $level = 'A1',
        string $lang = 'ar',
        int $hintStage = 1
    ): array {
        $this->ensureConfigured();

        $systemPrompt = "Provide a pedagogical hint in " . ($lang === 'en' ? 'English' : 'Arabic') . " for a learner at CEFR {$level}. " .
            ($hintStage === 1 ? "Stage 1: A gentle guiding nudge without revealing the answer." : "Stage 2: A more direct grammatical clue.") . " " .
            "Return valid JSON: concept_or_question, hint, hint_stage (int).";

        $response = $this->callModel($systemPrompt, "Provide hint for: '{$questionOrConcept}'");

        return array_merge($response, [
            'provider' => 'openai',
            'is_ai_generated' => true,
        ]);
    }

    public function evaluateExerciseResponse(
        string $question,
        string $userAnswer,
        string $correctAnswer,
        string $level = 'A1',
        string $lang = 'ar'
    ): array {
        $this->ensureConfigured();

        $systemPrompt = "Evaluate learner response to a Spanish question. Explain in " . ($lang === 'en' ? 'English' : 'Arabic') . " " .
            "specifically why an incorrect answer is wrong rather than simply repeating the correct answer. " .
            "Return valid JSON: is_correct (bool), why_incorrect, guidance.";

        $userPrompt = "Question: '{$question}', Correct answer: '{$correctAnswer}', Learner answer: '{$userAnswer}'.";

        $response = $this->callModel($systemPrompt, $userPrompt);

        return array_merge($response, [
            'provider' => 'openai',
            'is_ai_generated' => true,
        ]);
    }

    protected function ensureConfigured(): void
    {
        if (!$this->isOperational()) {
            throw new Exception("AI Provider credentials unavailable: AI_API_KEY is not configured.");
        }
    }

    protected function callModel(string $systemPrompt, string $userPrompt): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout($this->timeoutSeconds)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => $this->model,
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $userPrompt],
                    ],
                    'temperature' => 0.2,
                    'response_format' => ['type' => 'json_object'],
                ]);

            if ($response->failed()) {
                Log::warning("AI Provider returned HTTP error", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);
                throw new Exception("AI provider error: HTTP " . $response->status());
            }

            $content = $response->json('choices.0.message.content');
            $data = json_decode($content, true);

            if (!is_array($data)) {
                throw new Exception("Invalid JSON received from AI provider.");
            }

            return $data;
        } catch (ConnectionException $e) {
            Log::error("AI Provider connection timeout: " . $e->getMessage());
            throw new Exception("AI service timed out after {$this->timeoutSeconds} seconds.");
        }
    }
}
