<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularyItem extends Model
{
    protected $fillable = [
        'spanish_word',
        'phonetic',
        'part_of_speech',
        'gender',
        'plural_form',
        'is_irregular',
        'arabic_meaning',
        'english_meaning',
        'example_sentence_es',
        'example_sentence_ar',
        'example_sentence_en',
        'audio_url',
        'cefr_level',
        'topic',
        'notes',
        'conjugation_data',
        'is_published',
    ];

    protected $casts = [
        'is_irregular' => 'boolean',
        'is_published' => 'boolean',
        'conjugation_data' => 'array',
    ];

    public function userVocabularies()
    {
        return $this->hasMany(UserVocabulary::class);
    }
}
