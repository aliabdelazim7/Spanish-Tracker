import { apiClient } from './client';
import { Story } from '../types';

export const storiesApi = {
  getStories: () => apiClient<{ stories: Story[] }>('/stories'),

  getStory: (storyId: number) =>
    apiClient<{ story: Story; progress: any }>(`/stories/${storyId}`),

  updateProgress: (storyId: number, segmentIndex: number) =>
    apiClient<{ progress: any }>(`/stories/${storyId}/progress`, {
      method: 'POST',
      body: JSON.stringify({ current_segment_index: segmentIndex }),
    }),

  completeStory: (storyId: number) =>
    apiClient<{ message: string; progress: any; xp_awarded: number }>(`/stories/${storyId}/complete`, {
      method: 'POST',
    }),
};
