<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelExamAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'cefr_level',
        'attempt_number',
        'score_percent',
        'passed',
        'skill_breakdown',
        'feedback_ar',
        'recommended_lessons',
        'xp_awarded',
        'completed_at',
    ];

    protected $casts = [
        'attempt_number' => 'integer',
        'score_percent' => 'integer',
        'passed' => 'boolean',
        'skill_breakdown' => 'array',
        'recommended_lessons' => 'array',
        'xp_awarded' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
