<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelExamQuestion extends Model
{
    protected $fillable = [
        'cefr_level',
        'skill_category',
        'question_text',
        'prompt_ar',
        'audio_url',
        'options',
        'correct_answer',
        'explanation_ar',
        'points',
        'order_index',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'order_index' => 'integer',
    ];
}
