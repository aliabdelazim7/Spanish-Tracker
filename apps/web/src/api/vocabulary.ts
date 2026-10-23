import { apiClient } from './client';
import { UserVocabulary, VocabularyItem } from '../types';

export const vocabularyApi = {
  getVocabulary: (page = 1, level?: string) =>
    apiClient<{
      user_vocabulary: { data: UserVocabulary[]; current_page: number; last_page: number };
      available_catalog: VocabularyItem[];
      current_level?: string;
      user_max_level?: string;
      stats: { total_saved: number; mastered_count: number; learning_count: number; due_count: number };
    }>(`/vocabulary?page=${page}${level ? `&level=${encodeURIComponent(level)}` : ''}`),

  getDueReviews: () =>
    apiClient<{ due_items: UserVocabulary[]; count: number }>('/vocabulary/reviews/due'),

  saveItem: (vocabularyId: number) =>
    apiClient<{ message: string; user_vocabulary: UserVocabulary }>(`/vocabulary/${vocabularyId}/save`, {
      method: 'POST',
    }),

  unsaveItem: (vocabularyId: number) =>
    apiClient<{ message: string }>(`/vocabulary/${vocabularyId}/save`, {
      method: 'DELETE',
    }),

  reviewItem: (vocabularyId: number, qualityRating: number) =>
    apiClient<{ message: string; result: any }>(`/vocabulary/${vocabularyId}/review`, {
      method: 'POST',
      body: JSON.stringify({ quality_rating: qualityRating }),
    }),
};
