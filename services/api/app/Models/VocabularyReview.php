<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VocabularyReview extends Model
{
    protected $fillable = [
        'user_id',
        'user_vocabulary_id',
        'quality_rating',
        'repetition_number',
        'interval_applied',
    ];

    protected $casts = [
        'quality_rating' => 'integer',
        'repetition_number' => 'integer',
        'interval_applied' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userVocabulary()
    {
        return $this->belongsTo(UserVocabulary::class);
    }
}
