import { apiClient } from './client';
import { JournalEntry, WritingAnalysis } from '../types';

export const journalApi = {
  getEntries: async (params?: { status?: string; page?: number }) => {
    const query = new URLSearchParams();
    if (params?.status) query.append('status', params.status);
    if (params?.page) query.append('page', params.page.toString());
    const queryString = query.toString() ? `?${query.toString()}` : '';
    return apiClient<{
      entries: { data: JournalEntry[]; total: number; current_page: number; last_page: number };
      stats: { total_entries: number; total_words_written: number; analyzed_count: number };
    }>(`/journal${queryString}`);
  },

  createEntry: async (data: {
    title?: string;
    content: string;
    prompt_topic?: string;
    cefr_level?: string;
    entry_date?: string;
  }) => {
    return apiClient<{ message: string; entry: JournalEntry }>('/journal', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  getEntry: async (id: number) => {
    return apiClient<{ entry: JournalEntry }>(`/journal/${id}`);
  },

  updateEntry: async (id: number, data: { title?: string; content: string; entry_date?: string }) => {
    return apiClient<{ message: string; entry: JournalEntry }>(`/journal/${id}`, {
      method: 'PATCH',
      body: JSON.stringify(data),
    });
  },

  deleteEntry: async (id: number) => {
    return apiClient<{ message: string }>(`/journal/${id}`, {
      method: 'DELETE',
    });
  },

  analyzeEntry: async (id: number, lang = 'ar') => {
    return apiClient<{
      message: string;
      analysis: WritingAnalysis;
      entry: JournalEntry;
      ai_status: string;
      is_ai_generated: boolean;
    }>(`/journal/${id}/analyze`, {
      method: 'POST',
      body: JSON.stringify({ lang }),
    });
  },

  getFeedback: async (id: number) => {
    return apiClient<{
      analysis: WritingAnalysis;
      entry: JournalEntry;
    }>(`/journal/${id}/feedback`);
  },

  getPrompts: async (level?: string, mode?: string) => {
    const query = new URLSearchParams();
    if (level) query.append('level', level);
    if (mode) query.append('mode', mode);
    const qs = query.toString() ? `?${query.toString()}` : '';
    return apiClient<{
      level: string;
      prompts: import('../types').JournalPrompt[];
      daily_prompt: import('../types').JournalPrompt;
    }>(`/journal/prompts${qs}`);
  },

  getDailyPrompt: async (level?: string, date?: string) => {
    const query = new URLSearchParams();
    if (level) query.append('level', level);
    if (date) query.append('date', date);
    const qs = query.toString() ? `?${query.toString()}` : '';
    return apiClient<{
      daily_prompt: import('../types').JournalPrompt;
    }>(`/journal/daily-prompt${qs}`);
  },

  updateSuggestionStatus: async (
    id: number,
    status: 'understood' | 'review_later' | 'pending',
    user_notes?: string
  ) => {
    return apiClient<{
      message: string;
      suggestion: import('../types').WritingSuggestion;
    }>(`/journal/suggestions/${id}/status`, {
      method: 'PATCH',
      body: JSON.stringify({ status, user_notes }),
    });
  },

  getFollowUpExercises: async () => {
    return apiClient<{
      exercises: import('../types').FollowUpExercise[];
    }>('/journal/follow-up-exercises');
  },

  submitFollowUp: async (data: { concept_slug: string; user_answer: string; correct_answer: string }) => {
    return apiClient<{
      is_correct: boolean;
      correct_answer: string;
      xp_awarded: number;
      message: string;
    }>('/journal/submit-follow-up', {
      method: 'POST',
      body: JSON.stringify(data),
    });
  },

  getRecurringMistakes: async () => {
    return apiClient<{
      recurring_mistakes: import('../types').LearnerErrorConcept[];
      all_tracked_concepts: import('../types').LearnerErrorConcept[];
      total_errors: number;
    }>('/journal/recurring-mistakes');
  },

  getRecommendedLessons: async () => {
    return apiClient<{
      recommendations: import('../types').LearningRecommendation[];
    }>('/journal/recommended-lessons');
  },

  getProgressStats: async () => {
    return apiClient<{
      stats: {
        total_entries: number;
        total_words_written: number;
        active_writing_days: number;
        concepts_monitored: number;
        concepts_mastered: number;
        average_words_per_entry: number;
      };
      recent_timeline: any[];
      disclaimer_en: string;
      disclaimer_ar: string;
    }>('/journal/progress-stats');
  },
};
