import { apiClient } from './client';
import { Course, Lesson, Unit } from '../types';

export const curriculumApi = {
  getLearningPath: () =>
    apiClient<{ course: Course; completed_lesson_ids: number[]; user_level: string }>('/learning-path'),

  getUnit: (unitId: number) =>
    apiClient<{ unit: Unit; completed_lesson_ids: number[] }>(`/units/${unitId}`),

  getLesson: (lessonId: number) =>
    apiClient<{ lesson: Lesson }>(`/lessons/${lessonId}`),

  startLesson: (lessonId: number) =>
    apiClient<{ attempt: any; lesson: Lesson; exercises: any[]; answered_exercise_ids: number[] }>(
      `/lessons/${lessonId}/start`,
      { method: 'POST' }
    ),

  answerExercise: (lessonId: number, exerciseId: number, answer: any) =>
    apiClient<{ is_correct: boolean; explanation_ar: string; hint_ar?: string; correct_answers?: any }>(
      `/lessons/${lessonId}/exercises/${exerciseId}/answer`,
      {
        method: 'POST',
        body: JSON.stringify({ user_answer: answer }),
      }
    ),

  completeLesson: (lessonId: number) =>
    apiClient<{
      message: string;
      attempt: any;
      score_percent: number;
      xp_awarded: number;
      is_first_completion: boolean;
      next_lesson_id: number | null;
    }>(`/lessons/${lessonId}/complete`, { method: 'POST' }),
};
