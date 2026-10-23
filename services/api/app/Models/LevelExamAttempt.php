<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelExamAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'cefr_level',
        'score_percent',
        'passed',
        'skill_breakdown',
        'feedback_ar',
        'xp_awarded',
        'completed_at',
    ];

    protected $casts = [
        'score_percent' => 'integer',
        'passed' => 'boolean',
        'skill_breakdown' => 'array',
        'xp_awarded' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
