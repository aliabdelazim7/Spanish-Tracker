<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CefrLevel;
use App\Models\Lesson;
use App\Models\LessonExercise;
use App\Models\LevelExamQuestion;
use App\Models\Story;
use App\Models\Unit;
use App\Models\VocabularyItem;

$levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
$report = [];

foreach ($levels as $code) {
    $cefr = CefrLevel::where('code', $code)->first();
    $levelId = $cefr?->id;

    $units = $levelId ? Unit::where('cefr_level_id', $levelId)->get() : collect();
    $unitIds = $units->pluck('id');
    $lessons = Lesson::whereIn('unit_id', $unitIds)->get();
    $lessonIds = $lessons->pluck('id');
    $exercises = LessonExercise::whereIn('lesson_id', $lessonIds)->get();

    // Vocabulary
    $vocabCount = VocabularyItem::where('cefr_level', $code)->count();
    $verbsCount = VocabularyItem::where('cefr_level', $code)->where('part_of_speech', 'verb')->count();
    $phrasesCount = VocabularyItem::where('cefr_level', $code)
        ->whereIn('part_of_speech', ['sentence', 'phrase', 'expression'])
        ->count();

    // Exercises by type
    $mcq = $exercises->where('exercise_type', 'multiple_choice')->count();
    $translation = $exercises->where('exercise_type', 'translation')->count();
    $fillBlank = $exercises->where('exercise_type', 'fill_blank')->count();
    $wordOrder = $exercises->where('exercise_type', 'word_order')->count();
    $matching = $exercises->where('exercise_type', 'matching_pairs')->count();
    $listening = $exercises->where('exercise_type', 'listening')->count();
    $reading = $exercises->where('exercise_type', 'reading')->count();
    $speaking = $exercises->where('exercise_type', 'speaking')->count();

    // Stories (reading & listening)
    $storiesCount = Story::where('cefr_level', $code)->count();

    // Level Exam Questions
    $examQuestions = LevelExamQuestion::where('cefr_level', $code)->count();

    $report[$code] = [
        'level_name_ar' => $cefr?->name_ar ?? 'غير موجود',
        'units_count' => $units->count(),
        'lessons_count' => $lessons->count(),
        'exercises_total' => $exercises->count(),
        'exercise_breakdown' => [
            'multiple_choice' => $mcq,
            'translation' => $translation,
            'fill_blank' => $fillBlank,
            'word_order' => $wordOrder,
            'matching_pairs' => $matching,
            'listening' => $listening,
            'reading' => $reading,
            'speaking' => $speaking,
        ],
        'vocabulary_count' => $vocabCount,
        'verbs_count' => $verbsCount,
        'practical_expressions_count' => $phrasesCount,
        'stories_count' => $storiesCount,
        'final_exam_questions_count' => $examQuestions,
    ];
}

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
