import { apiClient } from './client';

export const tutorApi = {
  explain: (concept: string, dialect?: string) =>
    apiClient<{
      result: {
        concept: string;
        explanation_ar: string;
        examples: { es: string; ar: string }[];
        common_mistakes_ar: string;
        provider: string;
      };
    }>('/tutor/explain', {
      method: 'POST',
      body: JSON.stringify({ concept, dialect }),
    }),

  correct: (text: string, context?: string) =>
    apiClient<{
      result: {
        original_text: string;
        corrected_text: string;
        is_grammatically_correct: boolean;
        errors: { type: string; original: string; correction: string; reason_ar: string }[];
        explanation_ar: string;
        provider: string;
      };
    }>('/tutor/correct', {
      method: 'POST',
      body: JSON.stringify({ text, context }),
    }),

  practice: (topic: string, level?: string) =>
    apiClient<{
      result: {
        topic: string;
        level: string;
        questions: any[];
        provider: string;
      };
    }>('/tutor/practice', {
      method: 'POST',
      body: JSON.stringify({ topic, level }),
    }),

  speakingFeedback: (transcript: string, targetPhrase: string, lang = 'ar') =>
    apiClient<{
      result: {
        transcript: string;
        target_phrase: string;
        similarity_score: number;
        passed: boolean;
        feedback: string;
        words_analysis: { word: string; status: 'correct' | 'missed' }[];
        provider: string;
      };
    }>('/tutor/speaking-feedback', {
      method: 'POST',
      body: JSON.stringify({ transcript, target_phrase: targetPhrase, lang }),
    }),

  hint: (questionOrConcept: string, hintStage = 1, lang = 'ar') =>
    apiClient<{
      result: {
        question_or_concept: string;
        hint_stage: number;
        hint: string;
        provider: string;
      };
    }>('/tutor/hint', {
      method: 'POST',
      body: JSON.stringify({ question_or_concept: questionOrConcept, hint_stage: hintStage, lang }),
    }),

  evaluateExercise: (question: string, userAnswer: string, correctAnswer: string, lang = 'ar') =>
    apiClient<{
      result: {
        question: string;
        user_answer: string;
        correct_answer: string;
        is_correct: boolean;
        why_incorrect: string | null;
        guidance: string;
        provider: string;
      };
    }>('/tutor/evaluate-exercise', {
      method: 'POST',
      body: JSON.stringify({ question, user_answer: userAnswer, correct_answer: correctAnswer, lang }),
    }),
};
