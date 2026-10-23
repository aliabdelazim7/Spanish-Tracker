<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementQuestion extends Model
{
    protected $fillable = [
        'skill_category',
        'difficulty_level',
        'question_type',
        'question_text',
        'context_text',
        'prompt_ar',
        'audio_url',
        'options',
        'correct_answer',
        'explanation_ar',
        'points',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'points' => 'integer',
        'is_active' => 'boolean',
    ];
}
