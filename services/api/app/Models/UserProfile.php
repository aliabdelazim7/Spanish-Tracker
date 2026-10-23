<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'learning_reason',
        'daily_goal_minutes',
        'experience_level',
        'explanation_dialect',
        'target_variant',
        'estimated_cefr_level',
        'max_unlocked_level',
        'completed_levels',
        'onboarding_completed',
        'total_xp',
        'current_streak',
        'longest_streak',
        'last_activity_date',
        'timezone',
    ];

    protected $casts = [
        'onboarding_completed' => 'boolean',
        'daily_goal_minutes' => 'integer',
        'total_xp' => 'integer',
        'current_streak' => 'integer',
        'longest_streak' => 'integer',
        'last_activity_date' => 'date',
        'completed_levels' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
