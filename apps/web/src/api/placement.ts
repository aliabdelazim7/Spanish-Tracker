import { apiClient } from './client';
import { PlacementAttempt, PlacementQuestion } from '../types';

export const placementApi = {
  start: () =>
    apiClient<{
      attempt: PlacementAttempt;
      questions: PlacementQuestion[];
      answered_question_ids: number[];
    }>('/placement-tests/start', { method: 'POST' }),

  getAttempt: (attemptId: number) =>
    apiClient<{ attempt: PlacementAttempt }>(`/placement-tests/${attemptId}`),

  submitAnswer: (attemptId: number, questionId: number, answer: string, timeSpentSeconds?: number) =>
    apiClient<{
      message: string;
      result: { is_correct: boolean; explanation_ar: string; points: number };
    }>(`/placement-tests/${attemptId}/answers`, {
      method: 'POST',
      body: JSON.stringify({
        question_id: questionId,
        user_answer: answer,
        time_spent_seconds: timeSpentSeconds,
      }),
    }),

  finish: (attemptId: number) =>
    apiClient<{
      message: string;
      attempt: PlacementAttempt;
    }>(`/placement-tests/${attemptId}/finish`, { method: 'POST' }),

  getHistory: () =>
    apiClient<{ attempts: PlacementAttempt[] }>('/placement-tests/history'),
};
