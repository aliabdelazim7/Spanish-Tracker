import { apiClient } from './client';
import { LevelExamData, LevelExamSubmitResponse } from '../types';

export const levelsApi = {
  getExam: async (levelCode: string): Promise<LevelExamData> => {
    return apiClient<LevelExamData>(`/levels/${levelCode}/exam`);
  },

  submitExam: async (
    levelCode: string,
    answers: { question_id: number; answer: string }[]
  ): Promise<LevelExamSubmitResponse> => {
    return apiClient<LevelExamSubmitResponse>(`/levels/${levelCode}/exam/submit`, {
      method: 'POST',
      body: JSON.stringify({ answers }),
    });
  },
};
