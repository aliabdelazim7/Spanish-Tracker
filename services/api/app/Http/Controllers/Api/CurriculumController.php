<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CefrLevel;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonAttempt;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CurriculumController extends Controller
{
    public function learningPath(Request $request): JsonResponse
    {
        $user = $request->user();

        $course = Course::where('is_published', true)
            ->with(['levels' => function ($q) {
                $q->where('is_published', true)->with(['units' => function ($qu) {
                    $qu->where('is_published', true)->with(['lessons' => function ($ql) {
                        $ql->where('is_published', true)->select([
                            'id', 'unit_id', 'title', 'title_ar', 'objective_ar',
                            'grammar_notes_ar', 'estimated_minutes', 'xp_reward', 'order_index'
                        ])->orderBy('order_index');
                    }])->orderBy('order_index');
                }])->orderBy('order_index');
            }])
            ->first();

        // Get user completed lesson IDs
        $completedLessonIds = LessonAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->unique()
            ->toArray();

        $profile = $user->profile;
        $maxUnlockedLevel = $profile?->max_unlocked_level ?? ($profile?->estimated_cefr_level ?? 'A1');
        $completedLevels = $profile?->completed_levels ?? [];

        $ranks = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
        $userRank = $ranks[$maxUnlockedLevel] ?? 1;

        if ($course && $course->levels) {
            foreach ($course->levels as $level) {
                $lvlRank = $ranks[$level->code] ?? 1;
                $isUnlocked = ($lvlRank <= $userRank) || in_array($level->code, $completedLevels);
                $isCompleted = in_array($level->code, $completedLevels);

                $totalLessonsInLevel = 0;
                $completedLessonsInLevel = 0;

                foreach ($level->units as $unit) {
                    $uLessons = $unit->lessons;
                    $totalLessonsInLevel += $uLessons->count();
                    foreach ($uLessons as $lsn) {
                        if (in_array($lsn->id, $completedLessonIds)) {
                            $completedLessonsInLevel++;
                        }
                    }
                }

                $level->is_unlocked = $isUnlocked;
                $level->is_completed = $isCompleted;
                $level->total_lessons_count = $totalLessonsInLevel;
                $level->completed_lessons_count = $completedLessonsInLevel;
                $level->progress_percent = $totalLessonsInLevel > 0 
                    ? (int) round(($completedLessonsInLevel / $totalLessonsInLevel) * 100)
                    : ($isCompleted ? 100 : 0);
                $level->can_take_exam = $isUnlocked;
            }
        }

        return response()->json([
            'course' => $course,
            'completed_lesson_ids' => $completedLessonIds,
            'user_level' => $user->profile?->estimated_cefr_level ?? 'A1',
            'max_unlocked_level' => $maxUnlockedLevel,
            'completed_levels' => $completedLevels,
        ]);
    }

    public function levels(): JsonResponse
    {
        $levels = CefrLevel::where('is_published', true)
            ->withCount(['units'])
            ->orderBy('order_index')
            ->get();

        return response()->json([
            'levels' => $levels,
        ]);
    }

    public function unit(Request $request, int $unitId): JsonResponse
    {
        $user = $request->user();

        $unit = Unit::where('is_published', true)
            ->with(['level', 'lessons' => function ($q) {
                $q->where('is_published', true)->orderBy('order_index');
            }])
            ->findOrFail($unitId);

        $ranks = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
        $profile = $user->profile;
        $userMaxLevel = $profile?->max_unlocked_level ?? ($profile?->estimated_cefr_level ?? 'A1');
        $userRank = $ranks[$userMaxLevel] ?? 1;
        $unitLevelCode = $unit->level?->code ?? 'A1';
        $unitLevelRank = $ranks[$unitLevelCode] ?? 1;
        $completedLevels = $profile?->completed_levels ?? [];

        if ($unitLevelRank > $userRank && !in_array($unitLevelCode, $completedLevels)) {
            return response()->json([
                'message' => "الوحدة تابعة لمستوى مقفل ({$unitLevelCode}). يجب اجتياز المستويات السابقة أولاً.",
                'is_unlocked' => false,
            ], 403);
        }

        $completedLessonIds = LessonAttempt::where('user_id', $user->id)
            ->where('status', 'completed')
            ->pluck('lesson_id')
            ->toArray();

        return response()->json([
            'unit' => $unit,
            'completed_lesson_ids' => $completedLessonIds,
        ]);
    }

    public function lesson(Request $request, int $lessonId): JsonResponse
    {
        $user = $request->user();

        $lesson = Lesson::where('is_published', true)
            ->with(['unit.level', 'exercises' => function ($q) {
                // Return exercises structure for presentation
                $q->select([
                    'id', 'lesson_id', 'exercise_type', 'prompt', 'prompt_ar',
                    'target_phrase', 'audio_url', 'options', 'xp_value', 'order_index'
                ])->orderBy('order_index');
            }])
            ->findOrFail($lessonId);

        $ranks = ['A1' => 1, 'A2' => 2, 'B1' => 3, 'B2' => 4, 'C1' => 5, 'C2' => 6];
        $profile = $user?->profile;
        if ($profile) {
            $userMaxLevel = $profile->max_unlocked_level ?? ($profile->estimated_cefr_level ?? 'A1');
            $userRank = $ranks[$userMaxLevel] ?? 1;
            $lessonLevelCode = $lesson->unit?->level?->code ?? 'A1';
            $lessonLevelRank = $ranks[$lessonLevelCode] ?? 1;
            $completedLevels = $profile->completed_levels ?? [];

            if ($lessonLevelRank > $userRank && !in_array($lessonLevelCode, $completedLevels)) {
                return response()->json([
                    'message' => "الدرس تابع لمستوى مقفل ({$lessonLevelCode}). يجب اجتياز المستويات السابقة أولاً.",
                    'is_unlocked' => false,
                ], 403);
            }
        }

        return response()->json([
            'lesson' => $lesson,
        ]);
    }
}
