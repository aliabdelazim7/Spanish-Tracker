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

  speakingFeedback: (transcript: string, targetPhrase: string) =>
    apiClient<{
      result: {
        transcript: string;
        target_phrase: string;
        similarity_score: number;
        passed: boolean;
        feedback_ar: string;
        provider: string;
      };
    }>('/tutor/speaking-feedback', {
      method: 'POST',
      body: JSON.stringify({ transcript, target_phrase: targetPhrase }),
    }),
};
