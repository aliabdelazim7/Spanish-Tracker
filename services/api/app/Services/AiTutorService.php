<?php

namespace App\Services;

use App\Models\AiTutorLog;
use App\Models\User;
use App\Services\Ai\LanguageTutorProviderInterface;
use App\Services\Ai\MockLanguageTutorProvider;
use Exception;
use Illuminate\Support\Facades\Log;

class AiTutorService
{
    protected LanguageTutorProviderInterface $provider;

    public function __construct()
    {
        // Provider resolution based on environment configuration
        $driver = config('services.ai.provider', 'mock');

        if ($driver === 'mock' || empty(config('services.ai.api_key'))) {
            $this->provider = new MockLanguageTutorProvider();
        } else {
            // Future vendor classes implement LanguageTutorProviderInterface
            $this->provider = new MockLanguageTutorProvider();
        }
    }

    public function explainConcept(?User $user, string $concept, string $dialect = 'egyptian_arabic'): array
    {
        try {
            $result = $this->provider->explainGrammar($concept, $dialect);

            if ($user) {
                AiTutorLog::create([
                    'user_id' => $user->id,
                    'action_type' => 'explain',
                    'prompt_summary' => mb_substr($concept, 0, 200),
                    'response_summary' => mb_substr($result['explanation_ar'] ?? '', 0, 200),
                    'model_used' => $result['provider'] ?? 'mock',
                    'tokens_used' => 50,
                    'status' => 'success',
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('AI Tutor explain error: ' . $e->getMessage());
            $mock = new MockLanguageTutorProvider();
            return $mock->explainGrammar($concept, $dialect);
        }
    }

    public function correctUserText(?User $user, string $text, ?string $context = null): array
    {
        try {
            $result = $this->provider->correctText($text, $context);

            if ($user) {
                AiTutorLog::create([
                    'user_id' => $user->id,
                    'action_type' => 'correct',
                    'prompt_summary' => mb_substr($text, 0, 200),
                    'response_summary' => mb_substr($result['corrected_text'] ?? '', 0, 200),
                    'model_used' => $result['provider'] ?? 'mock',
                    'tokens_used' => 75,
                    'status' => 'success',
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('AI Tutor correction error: ' . $e->getMessage());
            $mock = new MockLanguageTutorProvider();
            return $mock->correctText($text, $context);
        }
    }

    public function generatePractice(?User $user, string $topic, string $level = 'A1'): array
    {
        try {
            $result = $this->provider->generatePractice($topic, $level);

            if ($user) {
                AiTutorLog::create([
                    'user_id' => $user->id,
                    'action_type' => 'practice',
                    'prompt_summary' => "topic: {$topic}, level: {$level}",
                    'response_summary' => 'Generated practice questions',
                    'model_used' => $result['provider'] ?? 'mock',
                    'tokens_used' => 120,
                    'status' => 'success',
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('AI Tutor practice error: ' . $e->getMessage());
            $mock = new MockLanguageTutorProvider();
            return $mock->generatePractice($topic, $level);
        }
    }

    public function evaluateSpeaking(?User $user, string $transcript, string $targetPhrase): array
    {
        try {
            $result = $this->provider->evaluateSpeaking($transcript, $targetPhrase);

            if ($user) {
                AiTutorLog::create([
                    'user_id' => $user->id,
                    'action_type' => 'speaking_feedback',
                    'prompt_summary' => "target: {$targetPhrase}, heard: {$transcript}",
                    'response_summary' => mb_substr($result['feedback_ar'] ?? '', 0, 200),
                    'model_used' => $result['provider'] ?? 'mock',
                    'tokens_used' => 30,
                    'status' => 'success',
                ]);
            }

            return $result;
        } catch (Exception $e) {
            Log::error('AI Speaking evaluation error: ' . $e->getMessage());
            $mock = new MockLanguageTutorProvider();
            return $mock->evaluateSpeaking($transcript, $targetPhrase);
        }
    }
}
