<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlacementAttempt extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'total_score',
        'estimated_level',
        'skill_breakdown',
        'summary_report_ar',
        'recommended_unit_id',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'total_score' => 'integer',
        'skill_breakdown' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recommendedUnit()
    {
        return $this->belongsTo(Unit::class, 'recommended_unit_id');
    }

    public function answers()
    {
        return $this->hasMany(PlacementAnswer::class);
    }
}
