<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WritingSuggestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'writing_analysis_id',
        'original_text',
        'suggested_text',
        'error_category',
        'severity',
        'explanation_ar',
        'explanation_en',
        'example_sentence_es',
        'related_concept_slug',
    ];

    public function analysis(): BelongsTo
    {
        return $this->belongsTo(WritingAnalysis::class, 'writing_analysis_id');
    }
}
