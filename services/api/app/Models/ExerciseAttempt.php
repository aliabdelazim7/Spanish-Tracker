<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExerciseAttempt extends Model
{
    protected $fillable = [
        'lesson_attempt_id',
        'user_id',
        'lesson_exercise_id',
        'user_answer',
        'is_correct',
        'attempt_number',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'attempt_number' => 'integer',
    ];

    public function lessonAttempt()
    {
        return $this->belongsTo(LessonAttempt::class);
    }

    public function exercise()
    {
        return $this->belongsTo(LessonExercise::class, 'lesson_exercise_id');
    }
}
