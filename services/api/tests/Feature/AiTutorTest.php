<?php

namespace Tests\Feature;

use App\Models\AiTutorLog;
use App\Models\LearnerErrorConcept;
use App\Models\User;
use App\Services\Ai\LanguageTutorProviderInterface;
use App\Services\AiTutorService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AiTutorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->user->profile()->create([
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'explanation_dialect' => 'egyptian_arabic',
        ]);
    }

    /** Test 1: Explanations in Arabic and English with translations */
    public function test_ai_tutor_explain_concept_bilingual(): void
    {
        // 1. In Arabic
        $resAr = $this->actingAs($this->user)->postJson('/api/v1/tutor/explain', [
            'concept' => 'ser vs estar',
            'lang' => 'ar',
            'dialect' => 'egyptian_arabic',
        ]);

        $resAr->assertStatus(200)
            ->assertJsonPath('result.concept', 'Ser vs Estar')
            ->assertJsonPath('result.explanation_lang', 'ar')
            ->assertJsonStructure([
                'result' => [
                    'concept', 'level', 'explanation', 'examples' => [
                        '*' => ['es', 'translation']
                    ],
                    'common_mistakes', 'regional_variations', 'is_context_dependent', 'provider', 'is_ai_generated'
                ],
            ]);

        // 2. In English
        $resEn = $this->actingAs($this->user)->postJson('/api/v1/tutor/explain', [
            'concept' => 'ser vs estar',
            'lang' => 'en',
        ]);

        $resEn->assertStatus(200)
            ->assertJsonPath('result.explanation_lang', 'en')
            ->assertJsonPath('result.is_ai_generated', false);

        $this->assertStringContainsString('ESTAR', $resEn->json('result.explanation'));
    }

    /** Test 2: Adapts explanations to learner's CEFR level and prior errors */
    public function test_ai_tutor_adapts_to_cefr_level_and_learner_context(): void
    {
        // Promote user to B1 and attach prior recurring mistakes
        $this->user->profile->update(['estimated_cefr_level' => 'B1', 'max_unlocked_level' => 'B1']);
        LearnerErrorConcept::create([
            'user_id' => $this->user->id,
            'concept_slug' => 'subjunctive_mood',
            'concept_name_ar' => 'صيغة الشك',
            'concept_name_en' => 'Subjunctive Mood',
            'category' => 'grammar',
            'total_errors_count' => 3,
            'last_occurred_at' => now(),
        ]);

        $response = $this->actingAs($this->user)->postJson('/api/v1/tutor/explain', [
            'concept' => 'subjuntivo',
            'lang' => 'ar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('result.level', 'B1');

        $this->assertDatabaseHas('ai_tutor_logs', [
            'user_id' => $this->user->id,
            'action_type' => 'explain',
        ]);
    }

    /** Test 3: Distinguish between grammar, spelling, unnatural phrasing, and regional variations */
    public function test_ai_tutor_distinguishes_error_categories(): void
    {
        // Sentence with multiple distinct error categories:
        // 1. Grammar: "soy en Madrid" -> "estoy en Madrid"
        // 2. Grammar: "yo gusto" -> "me gusta"
        // 3. Spelling: "tambien" -> "también"
        // 4. Regional: "el carro" -> "el coche" in Spain
        // 5. Unnatural phrasing: "soy 20 anos" -> "tengo 20 años"
        $input = "Yo soy en Madrid y yo gusto tambien el carro y soy 20 anos.";

        $response = $this->actingAs($this->user)->postJson('/api/v1/tutor/correct', [
            'text' => $input,
            'lang' => 'ar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('result.is_grammatically_correct', false)
            ->assertJsonStructure([
                'result' => [
                    'original_text',
                    'corrected_text',
                    'is_grammatically_correct',
                    'feedback',
                    'errors' => [
                        '*' => ['error_category', 'original', 'correction', 'reason', 'is_context_dependent']
                    ],
                    'natural_alternative',
                    'provider',
                ],
            ]);

        $errors = $response->json('result.errors');
        $categories = array_column($errors, 'error_category');

        $this->assertContains('grammar_error', $categories);
        $this->assertContains('spelling_mistake', $categories);
        $this->assertContains('regional_variation', $categories);
        $this->assertContains('unnatural_phrasing', $categories);
    }

    /** Test 4: Progressive hints before revealing the answer */
    public function test_ai_tutor_generates_guided_hints(): void
    {
        // Stage 1: Gentle guiding nudge
        $resStage1 = $this->actingAs($this->user)->postJson('/api/v1/tutor/hint', [
            'question_or_concept' => 'ser vs estar',
            'hint_stage' => 1,
            'lang' => 'en',
        ]);

        $resStage1->assertStatus(200)
            ->assertJsonPath('result.hint_stage', 1);
        $this->assertNotEmpty($resStage1->json('result.hint'));

        // Stage 2: Direct grammatical rule hint
        $resStage2 = $this->actingAs($this->user)->postJson('/api/v1/tutor/hint', [
            'question_or_concept' => 'ser vs estar',
            'hint_stage' => 2,
            'lang' => 'en',
        ]);

        $resStage2->assertStatus(200)
            ->assertJsonPath('result.hint_stage', 2);
        $this->assertStringContainsString('ESTAR', $resStage2->json('result.hint'));
    }

    /** Test 5: Explains why an answer is incorrect rather than merely showing the correct answer */
    public function test_ai_tutor_evaluates_exercise_explaining_why_incorrect(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/tutor/evaluate-exercise', [
            'question' => 'María ___ muy cansada hoy.',
            'user_answer' => 'es',
            'correct_answer' => 'está',
            'lang' => 'en',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('result.is_correct', false)
            ->assertJsonStructure([
                'result' => ['is_correct', 'why_incorrect', 'guidance', 'provider'],
            ]);

        // Asserts specific pedagogical reason why 'es' is wrong
        $why = $response->json('result.why_incorrect');
        $this->assertStringContainsString('Ser', $why);
        $this->assertStringContainsString('Estar', $why);
    }

    /** Test 6: Speaking pronunciation similarity evaluation */
    public function test_ai_tutor_speaking_feedback(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/v1/tutor/speaking-feedback', [
            'transcript' => 'Buenos dias como estas',
            'target_phrase' => 'Buenos días, ¿cómo estás?',
            'lang' => 'ar',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('result.passed', true)
            ->assertJsonStructure([
                'result' => ['similarity_score', 'passed', 'feedback', 'words_analysis'],
            ]);

        $this->assertGreaterThanOrEqual(70, $response->json('result.similarity_score'));
    }

    /** Test 7: Graceful error handling and fallback when external provider fails */
    public function test_ai_tutor_graceful_fallback_when_external_provider_fails(): void
    {
        // Mock a failing external provider that throws a connection timeout
        $failingMock = $this->createMock(LanguageTutorProviderInterface::class);
        $failingMock->method('explainGrammar')
            ->willThrowException(new Exception('Network timeout calling external API.'));

        $service = new AiTutorService($failingMock);

        // Service should catch the exception and fall back seamlessly without throwing
        $result = $service->explainConcept($this->user, 'ser vs estar', 'en');

        $this->assertIsArray($result);
        $this->assertEquals('local-expert-tutor', $result['provider']);
        $this->assertTrue($result['fallback_used'] ?? false);
    }

    /** Test 8: Character limits and request validation */
    public function test_ai_tutor_character_limits_and_validation(): void
    {
        // Overly long concept (>300 chars) should be rejected
        $longConcept = str_repeat('a', 305);
        $response = $this->actingAs($this->user)->postJson('/api/v1/tutor/explain', [
            'concept' => $longConcept,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['concept']);
    }
}
