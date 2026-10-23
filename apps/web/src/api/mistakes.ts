import { apiClient } from './client';
import { LearnerErrorConcept, LearningRecommendation } from '../types';

export const mistakesApi = {
  getMistakes: async () => {
    return apiClient<{
      concepts: LearnerErrorConcept[];
      recommendations: LearningRecommendation[];
      categories: Record<string, number>;
      total_recorded_mistakes: number;
    }>('/my-mistakes');
  },

  getConcept: async (id: number) => {
    return apiClient<{ concept: LearnerErrorConcept }>(`/my-mistakes/${id}`);
  },

  getPracticeQuiz: async (id: number) => {
    return apiClient<{
      concept: LearnerErrorConcept;
      quiz: {
        prompt: string;
        prompt_ar: string;
        options: string[];
        correct: string;
        explanation_ar: string;
      };
    }>(`/my-mistakes/${id}/practice`, {
      method: 'POST',
      body: JSON.stringify({}),
    });
  },

  submitPracticeAnswer: async (id: number, answer: string) => {
    return apiClient<{
      is_correct: boolean;
      correct_answer: string;
      explanation_ar: string;
      mastery_score: number;
      xp_awarded: number;
    }>(`/my-mistakes/${id}/practice`, {
      method: 'POST',
      body: JSON.stringify({ answer }),
    });
  },
};
