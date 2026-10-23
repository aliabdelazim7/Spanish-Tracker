<?php

namespace Database\Seeders;

use App\Models\VocabularyItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class VocabularySeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('data/all_vocabulary.json');

        if (!File::exists($jsonPath)) {
            $this->command->warn("Vocabulary data file not found at: {$jsonPath}");
            return;
        }

        $items = json_decode(File::get($jsonPath), true);

        if (!is_array($items)) {
            $this->command->error("Invalid JSON in vocabulary data file.");
            return;
        }

        foreach ($items as $item) {
            VocabularyItem::updateOrCreate(
                [
                    'spanish_word' => $item['spanish_word'],
                    'cefr_level' => $item['cefr_level'],
                ],
                [
                    'phonetic' => $item['phonetic'] ?? null,
                    'part_of_speech' => $item['part_of_speech'] ?? 'noun',
                    'gender' => $item['gender'] ?? null,
                    'plural_form' => $item['plural_form'] ?? null,
                    'is_irregular' => (bool) ($item['is_irregular'] ?? false),
                    'arabic_meaning' => $item['arabic_meaning'],
                    'english_meaning' => $item['english_meaning'] ?? null,
                    'example_sentence_es' => $item['example_sentence_es'] ?? '',
                    'example_sentence_ar' => $item['example_sentence_ar'] ?? '',
                    'example_sentence_en' => $item['example_sentence_en'] ?? '',
                    'topic' => $item['topic'] ?? 'general',
                    'notes' => $item['notes'] ?? null,
                    'conjugation_data' => $item['conjugation_data'] ?? null,
                    'is_published' => true,
                ]
            );
        }

        $this->command->info("Seeded " . count($items) . " rich vocabulary items successfully.");
    }
}
