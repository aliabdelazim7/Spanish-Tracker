import { apiClient } from './client';
import { Achievement, Lesson, UserProfile } from '../types';

export interface DashboardData {
  profile: UserProfile;
  stats: {
    total_xp: number;
    current_streak: number;
    longest_streak: number;
    daily_goal_minutes: number;
    estimated_level: string;
    lessons_completed_count: number;
    vocabulary_count: number;
    due_reviews_count: number;
    stories_completed_count: number;
  };
  recommended_lesson: Lesson | null;
  recent_activity: any[];
}

export const dashboardApi = {
  getDashboard: () => apiClient<DashboardData>('/dashboard'),

  getProgress: () =>
    apiClient<{
      skills: { reading: number; vocabulary: number; grammar: number; listening: number; speaking: number };
      recent_xp: { amount: number; source_type: string; created_at: string }[];
    }>('/progress'),

  getAchievements: () => apiClient<{ achievements: Achievement[] }>('/achievements'),
};
