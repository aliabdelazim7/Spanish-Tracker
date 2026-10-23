<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalAndWritingAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'learner']);
        UserProfile::create([
            'user_id' => $this->user->id,
            'learning_reason' => 'travel',
            'daily_goal_minutes' => 15,
            'experience_level' => 'A1',
            'explanation_dialect' => 'egyptian_arabic',
            'target_variant' => 'spain',
            'total_xp' => 100,
        ]);

        $this->otherUser = User::factory()->create(['role' => 'learner']);
        UserProfile::create([
            'user_id' => $this->otherUser->id,
            'learning_reason' => 'career',
            'daily_goal_minutes' => 15,
            'experience_level' => 'A1',
            'explanation_dialect' => 'egyptian_arabic',
            'target_variant' => 'spain',
            'total_xp' => 50,
        ]);
    }

    public function test_user_can_create_and_list_journal_entries(): void
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/v1/journal', [
                'title' => 'Mi primer día en Madrid',
                'content' => 'Hoy yo soy en Madrid y quiero comer paella con mis amigos.',
                'prompt_topic' => 'daily_life',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('entry.title', 'Mi primer día en Madrid')
            ->assertJsonPath('entry.status', 'draft');

        $this->assertDatabaseHas('journal_entries', [
            'user_id' => $this->user->id,
            'title' => 'Mi primer día en Madrid',
        ]);

        $list = $this->actingAs($this->user)->getJson('/api/v1/journal');
        $list->assertStatus(200)
            ->assertJsonPath('stats.total_entries', 1);
    }

    public function test_journal_privacy_and_data_isolation(): void
    {
        $entry = JournalEntry::create([
            'user_id' => $this->user->id,
            'title' => 'Entrada privada',
            'content' => 'Este es mi diario secreto.',
            'entry_date' => now()->toDateString(),
        ]);

        // Other user cannot see this entry
        $response = $this->actingAs($this->otherUser)->getJson("/api/v1/journal/{$entry->id}");
        $response->assertStatus(404);

        // Other user cannot update this entry
        $update = $this->actingAs($this->otherUser)->patchJson("/api/v1/journal/{$entry->id}", [
            'content' => 'Intento malicioso',
        ]);
        $update->assertStatus(404);
    }

    public function test_writing_analysis_detects_errors_and_updates_error_profile(): void
    {
        $entry = JournalEntry::create([
            'user_id' => $this->user->id,
            'title' => 'Un día difícil',
            'content' => 'Hoy yo soy en la estación y la problema es que no tengo dinero.',
            'entry_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->user)->postJson("/api/v1/journal/{$entry->id}/analyze");

        $response->assertStatus(200)
            ->assertJsonPath('entry.status', 'analyzed')
            ->assertJsonStructure([
                'analysis' => [
                    'fluency_score',
                    'total_errors',
                    'suggestions' => [
                        '*' => ['original_text', 'suggested_text', 'error_category', 'explanation_ar']
                    ]
                ]
            ]);

        // Original text is strictly preserved
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'content' => 'Hoy yo soy en la estación y la problema es que no tengo dinero.',
        ]);

        // Error concepts are recorded in learner's persistent profile
        $this->assertDatabaseHas('learner_error_concepts', [
            'user_id' => $this->user->id,
            'concept_slug' => 'ser-vs-estar',
        ]);

        $this->assertDatabaseHas('learner_error_concepts', [
            'user_id' => $this->user->id,
            'concept_slug' => 'gender-agreement',
        ]);

        // My mistakes endpoint lists these concepts
        $mistakes = $this->actingAs($this->user)->getJson('/api/v1/my-mistakes');
        $mistakes->assertStatus(200)
            ->assertJsonPath('total_recorded_mistakes', 2);
    }

    public function test_practice_error_concept_awards_xp_and_increases_mastery(): void
    {
        $entry = JournalEntry::create([
            'user_id' => $this->user->id,
            'title' => 'Prueba',
            'content' => 'Yo soy en Madrid.',
            'entry_date' => now()->toDateString(),
        ]);

        $this->actingAs($this->user)->postJson("/api/v1/journal/{$entry->id}/analyze");

        $mistakes = $this->actingAs($this->user)->getJson('/api/v1/my-mistakes');
        $conceptId = $mistakes->json('concepts.0.id');

        // Submit correct practice answer
        $practice = $this->actingAs($this->user)->postJson("/api/v1/my-mistakes/{$conceptId}/practice", [
            'answer' => 'estoy',
        ]);

        $practice->assertStatus(200)
            ->assertJsonPath('is_correct', true)
            ->assertJsonPath('xp_awarded', 15);
    }
}
