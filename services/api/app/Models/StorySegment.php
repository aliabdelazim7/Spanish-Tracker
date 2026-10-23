<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorySegment extends Model
{
    protected $fillable = [
        'story_id',
        'order_index',
        'speaker_name',
        'spanish_text',
        'arabic_translation',
        'explanation_ar',
        'audio_url',
        'vocabulary_highlights',
    ];

    protected $casts = [
        'order_index' => 'integer',
        'vocabulary_highlights' => 'array',
    ];

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
