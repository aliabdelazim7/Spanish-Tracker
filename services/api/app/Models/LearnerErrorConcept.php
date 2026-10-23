<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LearnerErrorConcept extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'concept_slug',
        'concept_name_ar',
        'concept_name_en',
        'category',
        'total_errors_count',
        'successful_practices_count',
        'mastery_score',
        'last_occurred_at',
        'last_practiced_at',
    ];

    protected $casts = [
        'total_errors_count' => 'integer',
        'successful_practices_count' => 'integer',
        'mastery_score' => 'integer',
        'last_occurred_at' => 'datetime',
        'last_practiced_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
