<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryQuestion extends Model
{
    protected $fillable = [
        'story_id',
        'after_segment_index',
        'question_es',
        'question_ar',
        'options',
        'correct_answer',
        'explanation_ar',
    ];

    protected $casts = [
        'after_segment_index' => 'integer',
        'options' => 'array',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
