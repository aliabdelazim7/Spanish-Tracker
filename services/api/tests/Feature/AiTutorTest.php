<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiTutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_ai_tutor_explain_concept(): void
    {
        $user = User::factory()->create();
        $user->profile()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tutor/explain', [
            'concept' => 'ser vs estar',
            'dialect' => 'egyptian_arabic',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result' => ['concept', 'explanation_ar', 'examples', 'common_mistakes_ar', 'provider'],
            ]);

        $this->assertDatabaseHas('ai_tutor_logs', [
            'user_id' => $user->id,
            'action_type' => 'explain',
        ]);
    }

    public function test_ai_tutor_corrects_user_text(): void
    {
        $user = User::factory()->create();
        $user->profile()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/tutor/correct', [
            'text' => 'Yo soy en Madrid y yo gusto el café',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'result' => ['original_text', 'corrected_text', 'is_grammatically_correct', 'errors', 'explanation_ar'],
            ]);
    }
}
