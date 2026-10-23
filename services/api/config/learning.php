<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CEFR Learning & Assessment Settings
    |--------------------------------------------------------------------------
    | Server-enforced passing threshold for level progression exams.
    */
    'exam_pass_threshold' => (int) env('LEARNING_EXAM_PASS_THRESHOLD', 80),

    /*
    | Maximum questions sampled for diagnostic placement test.
    */
    'placement_max_questions' => (int) env('LEARNING_PLACEMENT_MAX_QUESTIONS', 12),

    /*
    | Balanced questions sampled per skill category in level exams.
    */
    'exam_questions_per_skill' => (int) env('LEARNING_EXAM_QUESTIONS_PER_SKILL', 2),
];
