<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearningRecommendation extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title_ar',
        'title_en',
        'reason_ar',
        'reason_en',
        'action_type',
        'action_payload',
        'is_dismissed',
    ];

    protected $casts = [
        'action_payload' => 'array',
        'is_dismissed' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
