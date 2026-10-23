<?php

namespace Tests\Feature;

use App\Models\PlacementAttempt;
use App\Models\PlacementQuestion;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\CourseSeeder;
use Database\Seeders\PlacementQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlacementEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
        $this->seed(PlacementQuestionSeeder::class);
    }

    public function test_user_can_start_placement_test(): void
    {
        $user = User::factory()->create();
        $user->profile()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/placement-tests/start');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'attempt' => ['id', 'status'],
                'questions',
                'answered_question_ids',
            ]);

        // Verify correct_answer is hidden from question list
        $firstQ = $response->json('questions.0');
        $this->assertArrayNotHasKey('correct_answer', $firstQ);
    }

    public function test_user_can_submit_answer_and_finish_test(): void
    {
        $user = User::factory()->create();
        $user->profile()->create();

        $attempt = PlacementAttempt::create([
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        $question = PlacementQuestion::first();

        // Submit correct answer
        $ansResp = $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/answers", [
            'question_id' => $question->id,
            'user_answer' => $question->correct_answer,
        ]);

        $ansResp->assertStatus(200)
            ->assertJsonPath('result.is_correct', true);

        // Finish attempt
        $finishResp = $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/finish");

        $finishResp->assertStatus(200)
            ->assertJsonPath('attempt.status', 'completed')
            ->assertJsonStructure([
                'attempt' => ['id', 'total_score', 'estimated_level', 'skill_breakdown', 'recommended_unit'],
            ]);

        // User profile estimated level should be set
        $this->assertNotNull($user->fresh()->profile->estimated_cefr_level);
    }
}
