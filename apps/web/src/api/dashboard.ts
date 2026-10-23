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
    max_unlocked_level: string;
    lessons_completed_count: number;
    vocabulary_count: number;
    due_reviews_count: number;
    stories_completed_count: number;
  };
  course_progress?: {
    level_code: string;
    level_name_ar: string;
    completed_lessons: number;
    total_lessons: number;
    progress_percent: number;
  };
  daily_goal?: {
    goal_minutes: number;
    today_minutes: number;
    today_xp: number;
    progress_percent: number;
    is_goal_met: boolean;
  };
  recent_assessments?: {
    placement?: { id: number; estimated_level: string; total_score: number; completed_at: string } | null;
    level_exam?: { id: number; cefr_level: string; score_percent: number; passed: boolean; completed_at: string } | null;
  };
  weak_areas?: Array<{
    id: number;
    concept_slug: string;
    concept_name_ar: string;
    concept_name_en: string;
    category: string;
    total_errors_count: number;
    mastery_score: number;
  }>;
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
