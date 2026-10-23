<?php

namespace Tests\Feature;

use App\Models\Lesson;
use App\Models\LessonAttempt;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LessonProgressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_user_can_start_lesson_and_answer_exercise(): void
    {
        $user = User::factory()->create();
        $user->profile()->create();

        $lesson = Lesson::first();
        $exercises = $lesson->exercises;

        // 1. Start lesson
        $startResp = $this->actingAs($user)->postJson("/api/v1/lessons/{$lesson->id}/start");
        $startResp->assertStatus(200)
            ->assertJsonStructure(['attempt', 'lesson', 'exercises']);

        // 2. Answer all exercises with their correct answers
        foreach ($exercises as $exercise) {
            $answer = $exercise->exercise_type === 'matching_pairs' 
                ? $exercise->correct_answers 
                : $exercise->correct_answers[0];

            $ansResp = $this->actingAs($user)->postJson(
                "/api/v1/lessons/{$lesson->id}/exercises/{$exercise->id}/answer",
                ['user_answer' => $answer]
            );

            $ansResp->assertStatus(200)
                ->assertJsonPath('is_correct', true);
        }

        // 3. Complete lesson
        $compResp = $this->actingAs($user)->postJson("/api/v1/lessons/{$lesson->id}/complete");
        $compResp->assertStatus(200)
            ->assertJsonPath('attempt.status', 'completed')
            ->assertJsonPath('score_percent', 100);

        // Check XP awarded to user profile
        $user->refresh();
        $this->assertGreaterThan(0, $user->profile->total_xp);

        // 4. Test duplicate completion does not double-award XP
        $secondAttempt = LessonAttempt::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'status' => 'in_progress',
        ]);

        $prevXp = $user->profile->total_xp;
        $dupResp = $this->actingAs($user)->postJson("/api/v1/lessons/{$lesson->id}/complete");
        $dupResp->assertStatus(200)
            ->assertJsonPath('is_first_completion', false)
            ->assertJsonPath('xp_awarded', 0);

        $user->refresh();
        $this->assertEquals($prevXp, $user->profile->total_xp);
    }
}
