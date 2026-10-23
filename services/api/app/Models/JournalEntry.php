<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class JournalEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'content',
        'prompt_topic',
        'cefr_level',
        'status',
        'word_count',
        'entry_date',
    ];

    protected $casts = [
        'entry_date' => 'date:Y-m-d',
        'word_count' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function analysis(): HasOne
    {
        return $this->hasOne(WritingAnalysis::class)->latestOfMany();
    }
}
