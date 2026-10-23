<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $fillable = [
        'unit_id',
        'title',
        'title_ar',
        'objective_ar',
        'objective_en',
        'grammar_notes_ar',
        'grammar_notes_en',
        'skill_category',
        'estimated_minutes',
        'xp_reward',
        'order_index',
        'is_published',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'xp_reward' => 'integer',
        'order_index' => 'integer',
        'is_published' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function exercises()
    {
        return $this->hasMany(LessonExercise::class)->orderBy('order_index');
    }

    public function attempts()
    {
        return $this->hasMany(LessonAttempt::class);
    }
}
