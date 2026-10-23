<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WritingAnalysis extends Model
{
    use HasFactory;

    protected $fillable = [
        'journal_entry_id',
        'user_id',
        'corrected_content',
        'overall_feedback_ar',
        'overall_feedback_en',
        'fluency_score',
        'total_errors',
        'total_suggestions',
    ];

    protected $casts = [
        'fluency_score' => 'integer',
        'total_errors' => 'integer',
        'total_suggestions' => 'integer',
    ];

    public function journalEntry(): BelongsTo
    {
        return $this->belongsTo(JournalEntry::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function suggestions(): HasMany
    {
        return $this->hasMany(WritingSuggestion::class);
    }
}
