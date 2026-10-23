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

  analyzeEntry: async (id: number) => {
    return apiClient<{
      message: string;
      analysis: WritingAnalysis;
      entry: JournalEntry;
    }>(`/journal/${id}/analyze`, {
      method: 'POST',
    });
  },

  getFeedback: async (id: number) => {
    return apiClient<{
      analysis: WritingAnalysis;
      entry: JournalEntry;
    }>(`/journal/${id}/feedback`);
  },
};
