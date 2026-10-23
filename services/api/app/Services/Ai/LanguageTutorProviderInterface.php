<?php

namespace App\Services\Ai;

interface LanguageTutorProviderInterface
{
    /**
     * Explain a grammatical concept or vocabulary item in Egyptian or standard Arabic.
     */
    public function explainGrammar(string $concept, string $dialect = 'egyptian_arabic'): array;

    /**
     * Correct user-written Spanish text and return educational feedback.
     */
    public function correctText(string $userSpanishText, ?string $context = null): array;

    /**
     * Generate additional level-appropriate practice questions.
     */
    public function generatePractice(string $topic, string $level = 'A1'): array;

    /**
     * Evaluate pronunciation/speaking transcript against target phrase.
     */
    public function evaluateSpeaking(string $transcript, string $targetPhrase): array;
}
