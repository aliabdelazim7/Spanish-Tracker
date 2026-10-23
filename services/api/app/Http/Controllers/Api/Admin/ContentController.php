<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Story;
use App\Models\Unit;
use App\Models\VocabularyItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function overview(Request $request): JsonResponse
    {
        return response()->json([
            'stats' => [
                'courses' => Course::count(),
                'units' => Unit::count(),
                'lessons' => Lesson::count(),
                'stories' => Story::count(),
                'vocabulary' => VocabularyItem::count(),
            ],
            'recent_lessons' => Lesson::with('unit')->latest()->limit(5)->get(),
        ]);
    }

    public function toggleLessonPublish(Request $request, int $lessonId): JsonResponse
    {
        $lesson = Lesson::findOrFail($lessonId);
        $lesson->is_published = !$lesson->is_published;
        $lesson->save();

        return response()->json([
            'message' => 'تم تحديث حالة نشر الدرس',
            'lesson' => $lesson,
        ]);
    }

    public function toggleStoryPublish(Request $request, int $storyId): JsonResponse
    {
        $story = Story::findOrFail($storyId);
        $story->is_published = !$story->is_published;
        $story->save();

        return response()->json([
            'message' => 'تم تحديث حالة نشر القصة',
            'story' => $story,
        ]);
    }
}
