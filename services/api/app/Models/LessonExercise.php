<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonExercise extends Model
{
    protected $fillable = [
        'lesson_id',
        'exercise_type',
        'prompt',
        'prompt_ar',
        'target_phrase',
        'audio_url',
        'options',
        'correct_answers',
        'explanation_ar',
        'hint_ar',
        'xp_value',
        'order_index',
    ];

    protected $casts = [
        'options' => 'array',
        'correct_answers' => 'array',
        'xp_value' => 'integer',
        'order_index' => 'integer',
    ];

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }
}
