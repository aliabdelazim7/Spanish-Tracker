<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\VocabularyItem;
use Illuminate\Support\Facades\DB;

echo "=== VOCABULARY AUDIT REPORT BY CEFR LEVEL ===" . PHP_EOL . PHP_EOL;

$levels = ['A1', 'A2', 'B1', 'B2', 'C1', 'C2'];

foreach ($levels as $level) {
    $total = VocabularyItem::where('cefr_level', $level)->count();
    
    // Normalized unique entries (lowercase, trimmed)
    $uniqueCount = VocabularyItem::where('cefr_level', $level)
        ->select(DB::raw('LOWER(TRIM(spanish_word)) as norm_word'))
        ->distinct()
        ->count();

    $verbs = VocabularyItem::where('cefr_level', $level)
        ->where('part_of_speech', 'verb')
        ->count();

    $phrases = VocabularyItem::where('cefr_level', $level)
        ->whereIn('part_of_speech', ['phrase', 'expression'])
        ->count();

    $withAr = VocabularyItem::where('cefr_level', $level)
        ->whereNotNull('arabic_meaning')
        ->where('arabic_meaning', '!=', '')
        ->count();

    $withEn = VocabularyItem::where('cefr_level', $level)
        ->whereNotNull('english_meaning')
        ->where('english_meaning', '!=', '')
        ->count();

    $withEsEx = VocabularyItem::where('cefr_level', $level)
        ->whereNotNull('example_sentence_es')
        ->where('example_sentence_es', '!=', '')
        ->count();

    $withArEx = VocabularyItem::where('cefr_level', $level)
        ->whereNotNull('example_sentence_ar')
        ->where('example_sentence_ar', '!=', '')
        ->count();

    $withEnEx = VocabularyItem::where('cefr_level', $level)
        ->whereNotNull('example_sentence_en')
        ->where('example_sentence_en', '!=', '')
        ->count();

    $withAudio = VocabularyItem::where('cefr_level', $level)
        ->where(function ($q) {
            $q->whereNotNull('audio_url')->where('audio_url', '!=', '');
        })
        ->count();

    echo "[$level]:" . PHP_EOL;
    echo "  - Total records: $total" . PHP_EOL;
    echo "  - Unique normalized entries: $uniqueCount" . PHP_EOL;
    echo "  - Verbs: $verbs" . PHP_EOL;
    echo "  - Practical expressions / phrases: $phrases" . PHP_EOL;
    echo "  - With Arabic translations: $withAr" . PHP_EOL;
    echo "  - With English translations: $withEn" . PHP_EOL;
    echo "  - With Spanish example sentences: $withEsEx" . PHP_EOL;
    echo "  - With Arabic example translations: $withArEx" . PHP_EOL;
    echo "  - With English example translations: $withEnEx" . PHP_EOL;
    echo "  - With dedicated audio references: $withAudio (All synthesize via Web Speech API)" . PHP_EOL;
    echo PHP_EOL;
}
