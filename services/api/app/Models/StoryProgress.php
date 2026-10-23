<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoryProgress extends Model
{
    protected $table = 'story_progress';

    protected $fillable = [
        'user_id',
        'story_id',
        'current_segment_index',
        'is_completed',
        'score_percent',
        'is_reward_claimed',
        'completed_at',
    ];

    protected $casts = [
        'current_segment_index' => 'integer',
        'is_completed' => 'boolean',
        'score_percent' => 'integer',
        'is_reward_claimed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function story()
    {
        return $this->belongsTo(Story::class);
    }
}
