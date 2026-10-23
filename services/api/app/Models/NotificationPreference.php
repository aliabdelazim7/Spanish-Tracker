<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'daily_reminder_enabled',
        'review_due_enabled',
        'streak_saver_enabled',
        'new_content_enabled',
        'assessment_results_enabled',
        'preferred_reminder_time',
        'timezone',
    ];

    protected $casts = [
        'daily_reminder_enabled' => 'boolean',
        'review_due_enabled' => 'boolean',
        'streak_saver_enabled' => 'boolean',
        'new_content_enabled' => 'boolean',
        'assessment_results_enabled' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
