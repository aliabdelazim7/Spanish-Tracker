<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementAnswer extends Model
{
    protected $fillable = [
        'placement_attempt_id',
        'placement_question_id',
        'user_answer',
        'is_correct',
        'points_awarded',
        'time_spent_seconds',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'points_awarded' => 'integer',
        'time_spent_seconds' => 'integer',
    ];

    public function attempt()
    {
        return $this->belongsTo(PlacementAttempt::class, 'placement_attempt_id');
    }

    public function question()
    {
        return $this->belongsTo(PlacementQuestion::class, 'placement_question_id');
    }
}
