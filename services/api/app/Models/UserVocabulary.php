<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserVocabulary extends Model
{
    protected $table = 'user_vocabulary';

    protected $fillable = [
        'user_id',
        'vocabulary_item_id',
        'mastery_level',
        'repetitions',
        'interval_days',
        'ease_factor',
        'next_review_at',
        'last_reviewed_at',
        'successful_reviews',
        'failed_reviews',
    ];

    protected $casts = [
        'repetitions' => 'integer',
        'interval_days' => 'integer',
        'ease_factor' => 'float',
        'next_review_at' => 'datetime',
        'last_reviewed_at' => 'datetime',
        'successful_reviews' => 'integer',
        'failed_reviews' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vocabularyItem()
    {
        return $this->belongsTo(VocabularyItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(VocabularyReview::class);
    }
}
