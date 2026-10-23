import { Lesson, LessonExercise } from '../types';
import { curriculumApi } from '../api/curriculum';

export interface OfflineLessonData {
  lesson: Lesson;
  exercises: LessonExercise[];
  savedAt: string;
}

export interface QueuedProgressItem {
  id: string;
  lessonId: number;
  scorePercent: number;
  xpEarned: number;
  completedAt: string;
}

const OFFLINE_LESSONS_PREFIX = 'ruta_offline_lesson_';
const OFFLINE_LESSONS_INDEX_KEY = 'ruta_offline_lessons_index';
const OFFLINE_PROGRESS_QUEUE_KEY = 'ruta_offline_progress_queue';

/**
 * Save a lesson and its exercises for offline practice.
 */
export function saveLessonOffline(lesson: Lesson, exercises: LessonExercise[]): void {
  try {
    const data: OfflineLessonData = {
      lesson,
      exercises,
      savedAt: new Date().toISOString(),
    };
    localStorage.setItem(`${OFFLINE_LESSONS_PREFIX}${lesson.id}`, JSON.stringify(data));

    // Update index
    const index = listOfflineLessonIds();
    if (!index.includes(lesson.id)) {
      index.push(lesson.id);
      localStorage.setItem(OFFLINE_LESSONS_INDEX_KEY, JSON.stringify(index));
    }
  } catch (err) {
    console.error('Failed to save lesson offline:', err);
  }
}

/**
 * Retrieve cached offline lesson data.
 */
export function getOfflineLesson(lessonId: number): OfflineLessonData | null {
  try {
    const raw = localStorage.getItem(`${OFFLINE_LESSONS_PREFIX}${lessonId}`);
    if (!raw) return null;
    return JSON.parse(raw);
  } catch {
    return null;
  }
}

/**
 * Check if a specific lesson is saved offline.
 */
export function isLessonSavedOffline(lessonId: number): boolean {
  return listOfflineLessonIds().includes(lessonId);
}

/**
 * List all saved lesson IDs.
 */
export function listOfflineLessonIds(): number[] {
  try {
    const raw = localStorage.getItem(OFFLINE_LESSONS_INDEX_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

/**
 * Remove a cached offline lesson.
 */
export function removeOfflineLesson(lessonId: number): void {
  try {
    localStorage.removeItem(`${OFFLINE_LESSONS_PREFIX}${lessonId}`);
    const index = listOfflineLessonIds().filter((id) => id !== lessonId);
    localStorage.setItem(OFFLINE_LESSONS_INDEX_KEY, JSON.stringify(index));
  } catch (err) {
    console.error('Failed to remove offline lesson:', err);
  }
}

/**
 * Queue an offline lesson completion attempt for later server synchronization.
 */
export function queueOfflineProgress(lessonId: number, scorePercent: number, xpEarned: number): void {
  try {
    const queue = getQueuedProgress();
    const item: QueuedProgressItem = {
      id: `queue_${Date.now()}_${Math.random().toString(36).substring(2, 7)}`,
      lessonId,
      scorePercent,
      xpEarned,
      completedAt: new Date().toISOString(),
    };
    queue.push(item);
    localStorage.setItem(OFFLINE_PROGRESS_QUEUE_KEY, JSON.stringify(queue));
  } catch (err) {
    console.error('Failed to queue offline progress:', err);
  }
}

/**
 * Get all pending synchronization items.
 */
export function getQueuedProgress(): QueuedProgressItem[] {
  try {
    const raw = localStorage.getItem(OFFLINE_PROGRESS_QUEUE_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

/**
 * Synchronize queued offline progress when connection returns.
 */
export async function syncQueuedProgress(): Promise<{ syncedCount: number; errors: number }> {
  if (!navigator.onLine) {
    return { syncedCount: 0, errors: 0 };
  }

  const queue = getQueuedProgress();
  if (queue.length === 0) {
    return { syncedCount: 0, errors: 0 };
  }

  let syncedCount = 0;
  let errors = 0;
  const remainingQueue: QueuedProgressItem[] = [];

  for (const item of queue) {
    try {
      await curriculumApi.completeLesson(item.lessonId);
      syncedCount++;
    } catch (err) {
      console.warn(`Failed to sync queued progress for lesson ${item.lessonId}:`, err);
      errors++;
      remainingQueue.push(item); // Keep for next sync pass
    }
  }

  localStorage.setItem(OFFLINE_PROGRESS_QUEUE_KEY, JSON.stringify(remainingQueue));
  return { syncedCount, errors };
}

/**
 * Clear all private offline learning caches on user logout to prevent data leakage.
 */
export function clearPrivateOfflineData(): void {
  try {
    const lessonIds = listOfflineLessonIds();
    for (const id of lessonIds) {
      localStorage.removeItem(`${OFFLINE_LESSONS_PREFIX}${id}`);
    }
    localStorage.removeItem(OFFLINE_LESSONS_INDEX_KEY);
    localStorage.removeItem(OFFLINE_PROGRESS_QUEUE_KEY);
  } catch (err) {
    console.error('Failed to clear private offline data:', err);
  }
}
