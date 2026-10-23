<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CefrLevel;
use App\Models\Lesson;
use App\Models\LessonExercise;
use App\Models\LevelExamQuestion;
use App\Models\PlacementQuestion;
use App\Models\Story;
use App\Models\VocabularyItem;
use App\Services\JournalPromptService;
use Illuminate\Support\Facades\DB;

echo "==========================================================" . PHP_EOL;
echo "        COMPREHENSIVE CURRICULUM AUDIT REPORT             " . PHP_EOL;
echo "==========================================================" . PHP_EOL . PHP_EOL;

$levels = CefrLevel::orderBy('order_index')->get();

$promptService = app(JournalPromptService::class);
$reflector = new ReflectionClass($promptService);
$prop = $reflector->getProperty('prompts');
$prop->setAccessible(true);
$allPrompts = $prop->getValue($promptService);

foreach ($levels as $level) {
    $code = $level->code;
    $units = $level->units;
    $unitIds = $units->pluck('id');
    $lessons = Lesson::whereIn('unit_id', $unitIds)->get();
    $lessonIds = $lessons->pluck('id');
    $exercises = LessonExercise::whereIn('lesson_id', $lessonIds)->get();
    
    // Vocabulary counts & normalization
    $totalVocab = VocabularyItem::where('cefr_level', $code)->count();
    $uniqueVocab = VocabularyItem::where('cefr_level', $code)
        ->select(DB::raw('LOWER(TRIM(spanish_word)) as norm_word'))
        ->distinct()
        ->count();

    $verbs = VocabularyItem::where('cefr_level', $code)->where('part_of_speech', 'verb')->count();
    $expressions = VocabularyItem::where('cefr_level', $code)->whereIn('part_of_speech', ['phrase', 'expression'])->count();

    // Lesson skill distribution
    $skills = $lessons->groupBy('skill_category')->map->count()->toArray();
    $exerciseTypes = $exercises->groupBy('exercise_type')->map->count()->toArray();

    // Assessment questions
    $examQuestions = LevelExamQuestion::where('cefr_level', $code)->count();
    $levelPrompts = isset($allPrompts[$code]) ? count($allPrompts[$code]) : 0;

    echo "--- CEFR Level {$code} ({$level->name_ar} - {$level->name}) ---" . PHP_EOL;
    echo "  * Units: {$units->count()}" . PHP_EOL;
    echo "  * Lessons: {$lessons->count()}" . PHP_EOL;
    echo "  * Lesson Exercises: {$exercises->count()}" . PHP_EOL;
    echo "  * Vocabulary Records: {$totalVocab}" . PHP_EOL;
    echo "  * Unique Normalized Vocabulary: {$uniqueVocab}" . PHP_EOL;
    echo "  * Verbs in Lexicon: {$verbs}" . PHP_EOL;
    echo "  * Practical Expressions / Phrases: {$expressions}" . PHP_EOL;
    echo "  * Level Exam Questions: {$examQuestions}" . PHP_EOL;
    echo "  * Journal Prompts: {$levelPrompts}" . PHP_EOL;
    echo "  * Skills covered in lessons: " . json_encode($skills, JSON_UNESCAPED_UNICODE) . PHP_EOL;
    echo "  * Exercise types in lessons: " . json_encode($exerciseTypes, JSON_UNESCAPED_UNICODE) . PHP_EOL;

    // Check empty categories
    $missingSkills = [];
    foreach (['grammar', 'vocabulary', 'reading', 'listening', 'practical_expressions', 'verbs'] as $sk) {
        if (!isset($skills[$sk])) {
            $missingSkills[] = $sk;
        }
    }
    if (!empty($missingSkills)) {
        echo "  ! Note: Skills not directly tagged in unit lessons: " . implode(', ', $missingSkills) . PHP_EOL;
    }
    echo PHP_EOL;
}

echo "==========================================================" . PHP_EOL;
echo "GLOBAL EDUCATIONAL ASSETS:" . PHP_EOL;
echo "  * Total Placement Test Questions: " . PlacementQuestion::count() . PHP_EOL;
echo "  * Total Interactive Stories: " . Story::count() . PHP_EOL;
$totalPromptsCount = array_sum(array_map('count', $allPrompts));
echo "  * Total Journal Prompts: " . $totalPromptsCount . PHP_EOL;
echo "  * Total Exam Questions: " . LevelExamQuestion::count() . PHP_EOL;
echo "  * Total Vocabulary Items: " . VocabularyItem::count() . PHP_EOL;
$totalUniqueVocab = VocabularyItem::select(DB::raw('LOWER(TRIM(spanish_word)) as norm_word'))->distinct()->count();
echo "  * Total Unique Spanish Words: " . $totalUniqueVocab . PHP_EOL;
echo "==========================================================" . PHP_EOL;
