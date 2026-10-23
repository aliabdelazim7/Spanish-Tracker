<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\VocabularyItem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];
$columns = Schema::getColumnListing('vocabulary_items');
$hasEnTranslation = in_array('english_meaning', $columns);
$hasEnExample = in_array('example_sentence_en', $columns);

$results = [];

foreach ($levels as $level) {
    $items = VocabularyItem::where('cefr_level', $level)->get();
    $total = $items->count();

    // Normalization: trim, lowercase, remove punctuation ¿ ¡ ! ? . ,
    $uniqueNormalized = $items->map(function ($item) {
        $clean = mb_strtolower(trim($item->spanish_word));
        $clean = preg_replace('/[¿¡!?,.]/u', '', $clean);
        return trim($clean);
    })->unique()->count();

    $verbs = $items->where('part_of_speech', 'verb')->count();

    $practicalExpressionsAndSentences = $items->filter(function ($item) {
        return in_array($item->part_of_speech, ['sentence', 'phrase', 'expression']) 
            || $item->topic === 'essential_sentences' 
            || str_contains($item->spanish_word, ' ');
    })->count();

    $arabicTranslations = $items->filter(function ($item) {
        return !empty(trim($item->arabic_meaning ?? ''));
    })->count();

    $englishTranslations = $hasEnTranslation 
        ? $items->filter(function ($item) { return !empty(trim($item->english_meaning ?? '')); })->count() 
        : 0;

    $spanishExamples = $items->filter(function ($item) {
        return !empty(trim($item->example_sentence_es ?? ''));
    })->count();

    $arabicExamples = $items->filter(function ($item) {
        return !empty(trim($item->example_sentence_ar ?? ''));
    })->count();

    $englishExamples = $hasEnExample 
        ? $items->filter(function ($item) { return !empty(trim($item->example_sentence_en ?? '')); })->count() 
        : 0;

    // Working audio references: check if audio_url is present or if system uses TTS route / browser synthesis
    $audioRefs = $items->filter(function ($item) {
        return !empty(trim($item->audio_url ?? ''));
    })->count();

    $results[$level] = [
        'total' => $total,
        'unique_normalized' => $uniqueNormalized,
        'verbs' => $verbs,
        'practical_expressions_and_sentences' => $practicalExpressionsAndSentences,
        'arabic_translations' => $arabicTranslations,
        'english_translations' => $englishTranslations,
        'has_english_column' => $hasEnTranslation,
        'spanish_examples' => $spanishExamples,
        'arabic_examples' => $arabicExamples,
        'english_examples' => $englishExamples,
        'has_english_example_column' => $hasEnExample,
        'audio_url_records' => $audioRefs,
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
