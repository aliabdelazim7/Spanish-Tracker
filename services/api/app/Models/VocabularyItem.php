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
        'is_irregular',
        'arabic_meaning',
        'example_sentence_es',
        'example_sentence_ar',
        'audio_url',
        'cefr_level',
        'topic',
        'is_published',
    ];

    protected $casts = [
        'is_irregular' => 'boolean',
        'is_published' => 'boolean',
    ];

    public function userVocabularies()
    {
        return $this->hasMany(UserVocabulary::class);
    }
}
