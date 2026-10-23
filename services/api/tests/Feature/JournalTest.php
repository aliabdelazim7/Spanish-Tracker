<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\JournalEntry;
use App\Models\LearnerErrorConcept;
use App\Models\LearningRecommendation;
use App\Models\Lesson;
use App\Models\Unit;
use App\Models\User;
use App\Models\WritingAnalysis;
use App\Models\WritingSuggestion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA;
    protected User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\CourseSeeder::class);

        $this->userA = User::factory()->create();
        $this->userA->profile()->create([
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'total_xp' => 100,
        ]);

        $this->userB = User::factory()->create();
        $this->userB->profile()->create([
            'estimated_cefr_level' => 'B1',
            'max_unlocked_level' => 'B1',
            'total_xp' => 200,
        ]);
    }

    /** 1. CRUD Operations and Private Ownership */
    public function test_user_can_create_and_view_own_journal_entries(): void
    {
        $response = $this->actingAs($this->userA)->postJson('/api/v1/journal', [
            'title' => 'Mi primer día',
            'content' => 'Hoy es un buen día en la ciudad.',
            'prompt_topic' => 'daily_routine',
            'entry_date' => '2026-10-23',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('entry.title', 'Mi primer día')
            ->assertJsonPath('entry.status', 'draft')
            ->assertJsonPath('entry.word_count', 8);

        $entryId = $response->json('entry.id');

        // User A can view their own entry
        $this->actingAs($this->userA)->getJson("/api/v1/journal/{$entryId}")
            ->assertStatus(200)
            ->assertJsonPath('entry.title', 'Mi primer día');

        // Listing only contains User A's entries
        $listRes = $this->actingAs($this->userA)->getJson('/api/v1/journal');
        $listRes->assertStatus(200)
            ->assertJsonPath('stats.total_entries', 1)
            ->assertJsonPath('stats.total_words_written', 8);
    }

    /** 2. Privacy & Security: User B cannot access, edit, or delete User A's entries */
    public function test_journal_ownership_enforcement_prevents_unauthorized_access(): void
    {
        $entryA = JournalEntry::create([
            'user_id' => $this->userA->id,
            'title' => 'Secreto de User A',
            'content' => 'Contenido privado de usuario A.',
            'status' => 'draft',
            'entry_date' => now()->toDateString(),
        ]);

        // User B tries to view User A's entry -> 404 Not Found (or 403 Forbidden)
        $this->actingAs($this->userB)->getJson("/api/v1/journal/{$entryA->id}")
            ->assertStatus(404);

        // User B tries to update User A's entry -> 404
        $this->actingAs($this->userB)->patchJson("/api/v1/journal/{$entryA->id}", [
            'content' => 'Contenido hackeado',
        ])->assertStatus(404);

        // User B tries to delete User A's entry -> 404
        $this->actingAs($this->userB)->deleteJson("/api/v1/journal/{$entryA->id}")
            ->assertStatus(404);

        // User B tries to trigger analysis on User A's entry -> 404
        $this->actingAs($this->userB)->postJson("/api/v1/journal/{$entryA->id}/analyze")
            ->assertStatus(404);

        // Entry A remains intact
        $this->assertDatabaseHas('journal_entries', [
            'id' => $entryA->id,
            'title' => 'Secreto de User A',
        ]);
    }

    /** 3. Analysis preserves original writing and identifies multiple error categories */
    public function test_analysis_preserves_original_writing_and_categorizes_errors(): void
    {
        // Entry containing:
        // - Grammar: "yo soy en Madrid"
        // - Verb conjugation: "yo gusto"
        // - False friend: "estoy embarazado"
        // - Spelling: "tambien"
        // - Unnatural phrasing: "soy 20 anos"
        // - Sentence structure: "no yo quiero"
        $originalWriting = 'Yo soy en Madrid y yo gusto viajar. Tambien estoy embarazado porque soy 20 anos y no yo quiero problemas.';

        $entry = JournalEntry::create([
            'user_id' => $this->userA->id,
            'title' => 'Prueba de análisis',
            'content' => $originalWriting,
            'status' => 'draft',
            'word_count' => 19,
            'entry_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->userA)->postJson("/api/v1/journal/{$entry->id}/analyze", [
            'lang' => 'ar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('is_ai_generated', false)
            ->assertJsonPath('ai_status', 'local_rules_engine')
            ->assertJsonStructure([
                'analysis' => [
                    'id', 'fluency_score', 'total_errors', 'overall_feedback_ar', 'corrected_content', 'suggestions'
                ],
            ]);

        // CRITICAL REQUIREMENT 6: Original writing MUST NOT be overwritten
        $freshEntry = $entry->fresh();
        $this->assertEquals($originalWriting, $freshEntry->content);
        $this->assertEquals('analyzed', $freshEntry->status);

        // Structured corrections must be recorded
        $suggestions = $response->json('analysis.suggestions');
        $categories = array_column($suggestions, 'error_category');

        $this->assertContains('grammar', $categories);
        $this->assertContains('verb_conjugation', $categories);
        $this->assertContains('vocabulary', $categories);
        $this->assertContains('spelling', $categories);
        $this->assertContains('unnatural_phrasing', $categories);
        $this->assertContains('sentence_structure', $categories);

        // Verify each correction has original, suggested, explanation, concept, and natural alternative
        foreach ($suggestions as $s) {
            $this->assertNotEmpty($s['original_text']);
            $this->assertNotEmpty($s['suggested_text']);
            $this->assertNotEmpty($s['explanation_ar']);
            $this->assertNotEmpty($s['related_concept_slug']);
        }
    }

    /** 4. Recurring mistakes tracking and curriculum lesson recommendations */
    public function test_recurring_mistakes_identification_and_lesson_recommendation(): void
    {
        // First entry with ser-vs-estar mistake
        $entry1 = JournalEntry::create([
            'user_id' => $this->userA->id,
            'title' => 'Entrada 1',
            'content' => 'Yo soy en Madrid hoy.',
            'status' => 'draft',
            'entry_date' => now()->subDay()->toDateString(),
        ]);
        $this->actingAs($this->userA)->postJson("/api/v1/journal/{$entry1->id}/analyze");

        // Second entry with the same ser-vs-estar mistake
        $entry2 = JournalEntry::create([
            'user_id' => $this->userA->id,
            'title' => 'Entrada 2',
            'content' => 'Ayer también yo soy en Barcelona.',
            'status' => 'draft',
            'entry_date' => now()->toDateString(),
        ]);
        $this->actingAs($this->userA)->postJson("/api/v1/journal/{$entry2->id}/analyze");

        // Recurring mistakes endpoint
        $resMistakes = $this->actingAs($this->userA)->getJson('/api/v1/journal/recurring-mistakes');
        $resMistakes->assertStatus(200);

        $recurring = $resMistakes->json('recurring_mistakes');
        $this->assertNotEmpty($recurring);
        $this->assertEquals('ser-vs-estar', $recurring[0]['concept_slug']);
        $this->assertGreaterThanOrEqual(2, $recurring[0]['total_errors_count']);

        // Recommended curriculum lessons endpoint
        $resRecs = $this->actingAs($this->userA)->getJson('/api/v1/journal/recommended-lessons');
        $resRecs->assertStatus(200);

        $recommendations = $resRecs->json('recommendations');
        $this->assertNotEmpty($recommendations);
        $this->assertNotEmpty($recommendations[0]['action_payload']['lesson_id']);
        $this->assertStringContainsString('Ser vs Estar', $recommendations[0]['action_payload']['lesson_title']);
    }

    /** 5. Mark suggestions as understood or review later */
    public function test_learner_can_mark_corrections_as_understood_or_review_later(): void
    {
        $entry = JournalEntry::create([
            'user_id' => $this->userA->id,
            'title' => 'Nota',
            'content' => 'Yo soy en Sevilla.',
            'status' => 'draft',
            'entry_date' => now()->toDateString(),
        ]);

        $resAnalyze = $this->actingAs($this->userA)->postJson("/api/v1/journal/{$entry->id}/analyze");
        $suggestionId = $resAnalyze->json('analysis.suggestions.0.id');

        // User B cannot change status of User A's suggestion
        $this->actingAs($this->userB)->patchJson("/api/v1/journal/suggestions/{$suggestionId}/status", [
            'status' => 'understood',
        ])->assertStatus(404);

        // User A marks as review_later with note
        $resReview = $this->actingAs($this->userA)->patchJson("/api/v1/journal/suggestions/{$suggestionId}/status", [
            'status' => 'review_later',
            'user_notes' => 'Necesito repasar la diferencia entre ser y estar de ubicación.',
        ]);
        $resReview->assertStatus(200)
            ->assertJsonPath('suggestion.status', 'review_later')
            ->assertJsonPath('suggestion.is_understood', false);

        // User A later marks as understood
        $resUnderstood = $this->actingAs($this->userA)->patchJson("/api/v1/journal/suggestions/{$suggestionId}/status", [
            'status' => 'understood',
        ]);
        $resUnderstood->assertStatus(200)
            ->assertJsonPath('suggestion.status', 'understood')
            ->assertJsonPath('suggestion.is_understood', true);
    }

    /** 6. Follow-up exercises generated from past encountered mistakes */
    public function test_follow_up_exercises_generation_and_submission(): void
    {
        // Populate an error concept
        LearnerErrorConcept::create([
            'user_id' => $this->userA->id,
            'concept_slug' => 'ser-vs-estar',
            'concept_name_ar' => 'فعل Ser وفعل Estar',
            'concept_name_en' => 'Ser vs Estar',
            'category' => 'grammar',
            'total_errors_count' => 3,
            'mastery_score' => 20,
        ]);

        // Get exercises
        $resExercises = $this->actingAs($this->userA)->getJson('/api/v1/journal/follow-up-exercises');
        $resExercises->assertStatus(200)
            ->assertJsonStructure([
                'exercises' => [
                    '*' => ['concept_slug', 'type', 'prompt_es', 'sentence', 'options', 'correct_answer']
                ]
            ]);

        $this->assertEquals('ser-vs-estar', $resExercises->json('exercises.0.concept_slug'));

        // Submit correct answer to follow-up exercise
        $resSubmit = $this->actingAs($this->userA)->postJson('/api/v1/journal/submit-follow-up', [
            'concept_slug' => 'ser-vs-estar',
            'user_answer' => 'estamos',
            'correct_answer' => 'estamos',
        ]);

        $resSubmit->assertStatus(200)
            ->assertJsonPath('is_correct', true)
            ->assertJsonPath('xp_awarded', 15);

        // Concept mastery score increased
        $concept = LearnerErrorConcept::where('user_id', $this->userA->id)->where('concept_slug', 'ser-vs-estar')->first();
        $this->assertGreaterThan(20, $concept->mastery_score);
    }

    /** 7. CEFR Daily Prompts and Scaffolding */
    public function test_daily_prompts_and_cefr_levels(): void
    {
        $resA1 = $this->actingAs($this->userA)->getJson('/api/v1/journal/prompts?level=A1');
        $resA1->assertStatus(200)
            ->assertJsonPath('level', 'A1')
            ->assertJsonStructure([
                'prompts' => [
                    '*' => ['id', 'level', 'mode', 'title_es', 'description_es', 'starter_phrases', 'suggested_vocabulary', 'target_grammar', 'target_word_count']
                ],
                'daily_prompt',
            ]);

        $resDaily = $this->actingAs($this->userA)->getJson('/api/v1/journal/daily-prompt?level=A1');
        $resDaily->assertStatus(200)
            ->assertJsonPath('daily_prompt.level', 'A1')
            ->assertJsonPath('daily_prompt.is_daily_challenge', true);
    }

    /** 8. Progress Metrics with Honest Fluency Disclaimers */
    public function test_journal_progress_stats_with_honest_disclaimer(): void
    {
        JournalEntry::create([
            'user_id' => $this->userA->id,
            'title' => 'Entrada A',
            'content' => 'Texto en español número uno.',
            'word_count' => 5,
            'entry_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($this->userA)->getJson('/api/v1/journal/progress-stats');
        $response->assertStatus(200)
            ->assertJsonPath('stats.total_entries', 1)
            ->assertJsonPath('stats.total_words_written', 5)
            ->assertJsonStructure([
                'stats' => ['total_entries', 'total_words_written', 'active_writing_days', 'concepts_monitored', 'concepts_mastered', 'average_words_per_entry'],
                'recent_timeline',
                'disclaimer_en',
                'disclaimer_ar',
            ]);

        $this->assertStringContainsString('fluency', $response->json('disclaimer_en'));
        $this->assertStringContainsString('للطلاقة', $response->json('disclaimer_ar'));
    }
}
