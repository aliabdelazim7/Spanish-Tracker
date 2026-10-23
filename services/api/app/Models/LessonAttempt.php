<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'lesson_id',
        'status',
        'score_percent',
        'xp_awarded',
        'is_reward_claimed',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'score_percent' => 'integer',
        'xp_awarded' => 'integer',
        'is_reward_claimed' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lesson()
    {
        return $this->belongsTo(Lesson::class);
    }

    public function exerciseAttempts()
    {
        return $this->hasMany(ExerciseAttempt::class);
    }
}
