<?php

namespace App\Services\Ai;

interface LanguageTutorProviderInterface
{
    /**
     * Explain a grammatical concept or vocabulary item in Arabic or English adapted to the learner's level.
     *
     * @param string $concept The grammatical topic or lexical item.
     * @param string $lang 'ar' or 'en'
     * @param string $dialect 'egyptian_arabic' or 'msa'
     * @param string $level CEFR level (A1, A2, B1, B2, C1, C2)
     * @param array|null $learnerContext Relevant prior errors and vocabulary context
     */
    public function explainGrammar(
        string $concept,
        string $lang = 'ar',
        string $dialect = 'egyptian_arabic',
        string $level = 'A1',
        ?array $learnerContext = null
    ): array;

    /**
     * Correct user-written Spanish text with categorized errors (grammar, spelling, unnatural phrasing, regional).
     *
     * @param string $userSpanishText
     * @param string|null $context
     * @param string $lang 'ar' or 'en'
     * @param string $level CEFR level
     */
    public function correctText(
        string $userSpanishText,
        ?string $context = null,
        string $lang = 'ar',
        string $level = 'A1'
    ): array;

    /**
     * Generate level-appropriate practice questions with pedagogical criteria.
     *
     * @param string $topic
     * @param string $level
     * @param string $lang 'ar' or 'en'
     * @param array|null $learnerContext
     */
    public function generatePractice(
        string $topic,
        string $level = 'A1',
        string $lang = 'ar',
        ?array $learnerContext = null
    ): array;

    /**
     * Evaluate pronunciation/speaking transcript against target phrase.
     *
     * @param string $transcript
     * @param string $targetPhrase
     * @param string $lang 'ar' or 'en'
     */
    public function evaluateSpeaking(
        string $transcript,
        string $targetPhrase,
        string $lang = 'ar'
    ): array;

    /**
     * Generate a progressive hint for an exercise before giving away the answer.
     *
     * @param string $questionOrConcept
     * @param string $level
     * @param string $lang 'ar' or 'en'
     * @param int $hintStage 1 (gentle nudge) or 2 (targeted hint)
     */
    public function generateHint(
        string $questionOrConcept,
        string $level = 'A1',
        string $lang = 'ar',
        int $hintStage = 1
    ): array;

    /**
     * Evaluate an exercise response and explain why an incorrect answer is wrong.
     *
     * @param string $question
     * @param string $userAnswer
     * @param string $correctAnswer
     * @param string $level
     * @param string $lang 'ar' or 'en'
     */
    public function evaluateExerciseResponse(
        string $question,
        string $userAnswer,
        string $correctAnswer,
        string $level = 'A1',
        string $lang = 'ar'
    ): array;
}
