<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Story extends Model
{
    protected $fillable = [
        'cefr_level',
        'title_es',
        'title_ar',
        'summary_ar',
        'topic',
        'estimated_minutes',
        'difficulty',
        'audio_url',
        'is_published',
        'xp_reward',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'is_published' => 'boolean',
        'xp_reward' => 'integer',
    ];

    public function segments()
    {
        return $this->hasMany(StorySegment::class)->orderBy('order_index');
    }

    public function questions()
    {
        return $this->hasMany(StoryQuestion::class)->orderBy('after_segment_index');
    }

    public function progress()
    {
        return $this->hasMany(StoryProgress::class);
    }
}
