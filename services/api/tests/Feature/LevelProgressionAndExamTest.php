<?php

namespace Tests\Feature;

use App\Models\CefrLevel;
use App\Models\Course;
use App\Models\JournalEntry;
use App\Models\Lesson;
use App\Models\LessonExercise;
use App\Models\LevelExamQuestion;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelProgressionAndExamTest extends TestCase
{
    use RefreshDatabase;

    protected User $userA1;
    protected User $userA2;
    protected User $otherUser;
    protected Course $course;
    protected CefrLevel $levelA1;
    protected CefrLevel $levelA2;
    protected Unit $unitA1;
    protected Unit $unitA2;
    protected Lesson $lessonA1;
    protected Lesson $lessonA2;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. User placed at A1
        $this->userA1 = User::factory()->create(['role' => 'learner']);
        UserProfile::create([
            'user_id' => $this->userA1->id,
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
            'total_xp' => 0,
        ]);

        // 2. User placed directly at A2
        $this->userA2 = User::factory()->create(['role' => 'learner']);
        UserProfile::create([
            'user_id' => $this->userA2->id,
            'estimated_cefr_level' => 'A2',
            'max_unlocked_level' => 'A2',
            'completed_levels' => ['A1'],
            'total_xp' => 50,
        ]);

        // 3. Another user
        $this->otherUser = User::factory()->create(['role' => 'learner']);
        UserProfile::create([
            'user_id' => $this->otherUser->id,
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
            'total_xp' => 0,
        ]);

        // 4. Curriculum
        $this->course = Course::create([
            'slug' => 'spanish-course',
            'title' => 'Spanish Course',
            'title_ar' => 'منهج الإسبانية',
            'is_published' => true,
        ]);

        $this->levelA1 = CefrLevel::create([
            'course_id' => $this->course->id,
            'code' => 'A1',
            'name' => 'Principiante',
            'name_ar' => 'مبتدئ أول',
            'order_index' => 1,
            'is_published' => true,
        ]);

        $this->levelA2 = CefrLevel::create([
            'course_id' => $this->course->id,
            'code' => 'A2',
            'name' => 'Elemental',
            'name_ar' => 'ابتدائي متقدم',
            'order_index' => 2,
            'is_published' => true,
        ]);

        $this->unitA1 = Unit::create([
            'cefr_level_id' => $this->levelA1->id,
            'title' => 'A1 Unit 1',
            'title_ar' => 'وحدة A1 الأولى',
            'order_index' => 1,
            'is_published' => true,
        ]);

        $this->unitA2 = Unit::create([
            'cefr_level_id' => $this->levelA2->id,
            'title' => 'A2 Unit 1',
            'title_ar' => 'وحدة A2 الأولى',
            'order_index' => 1,
            'is_published' => true,
        ]);

        $this->lessonA1 = Lesson::create([
            'unit_id' => $this->unitA1->id,
            'title' => 'A1 Lesson 1',
            'title_ar' => 'درس A1',
            'objective_ar' => 'هدف الدرس',
            'xp_reward' => 20,
            'order_index' => 1,
            'is_published' => true,
        ]);

        LessonExercise::create([
            'lesson_id' => $this->lessonA1->id,
            'exercise_type' => 'multiple_choice',
            'prompt' => 'Hola',
            'prompt_ar' => 'مرحبا',
            'correct_answers' => ['Hola'],
            'xp_value' => 5,
            'order_index' => 1,
        ]);

        $this->lessonA2 = Lesson::create([
            'unit_id' => $this->unitA2->id,
            'title' => 'A2 Lesson 1',
            'title_ar' => 'درس A2',
            'objective_ar' => 'هدف الدرس',
            'xp_reward' => 25,
            'order_index' => 1,
            'is_published' => true,
        ]);

        // Exam questions
        LevelExamQuestion::create([
            'cefr_level' => 'A1',
            'skill_category' => 'vocabulary',
            'question_text' => '¿Cómo se dice "صباح الخير"?',
            'prompt_ar' => 'اختر التحية الصحيحة',
            'options' => ['Buenos días', 'Buenas noches'],
            'correct_answer' => 'Buenos días',
            'points' => 10,
            'order_index' => 1,
        ]);

        LevelExamQuestion::create([
            'cefr_level' => 'A1',
            'skill_category' => 'grammar',
            'question_text' => 'Yo _____ estudiante.',
            'prompt_ar' => 'اختر التصريف الصحيح',
            'options' => ['soy', 'eres'],
            'correct_answer' => 'soy',
            'points' => 10,
            'order_index' => 2,
        ]);
    }

    /** Scenario 1: A user placed at A1 can access A1 */
    public function test_user_placed_at_a1_can_access_a1(): void
    {
        $response = $this->actingAs($this->userA1)->getJson("/api/v1/units/{$this->unitA1->id}");
        $response->assertStatus(200)
            ->assertJsonPath('unit.id', $this->unitA1->id);

        $lessonRes = $this->actingAs($this->userA1)->getJson("/api/v1/lessons/{$this->lessonA1->id}");
        $lessonRes->assertStatus(200);
    }

    /** Scenario 2: A user placed at A1 cannot access A2 before passing A1 */
    public function test_user_placed_at_a1_cannot_access_a2_before_passing_a1(): void
    {
        $response = $this->actingAs($this->userA1)->getJson("/api/v1/units/{$this->unitA2->id}");
        $response->assertStatus(403);

        $lessonRes = $this->actingAs($this->userA1)->getJson("/api/v1/lessons/{$this->lessonA2->id}");
        $lessonRes->assertStatus(403);

        $startRes = $this->actingAs($this->userA1)->postJson("/api/v1/lessons/{$this->lessonA2->id}/start");
        $startRes->assertStatus(403);
    }

    /** Scenario 3: A user placed at A2 can access A2 without being forced to complete A1 */
    public function test_user_placed_at_a2_can_access_a2_directly(): void
    {
        $response = $this->actingAs($this->userA2)->getJson("/api/v1/units/{$this->unitA2->id}");
        $response->assertStatus(200)
            ->assertJsonPath('unit.id', $this->unitA2->id);

        $lessonRes = $this->actingAs($this->userA2)->getJson("/api/v1/lessons/{$this->lessonA2->id}");
        $lessonRes->assertStatus(200);
    }

    /** Scenario 4: Completing lessons alone does not unlock the next level */
    public function test_completing_lessons_alone_does_not_unlock_next_level(): void
    {
        // Start lesson A1
        $this->actingAs($this->userA1)->postJson("/api/v1/lessons/{$this->lessonA1->id}/start");

        // Complete lesson A1
        $res = $this->actingAs($this->userA1)->postJson("/api/v1/lessons/{$this->lessonA1->id}/complete");
        $res->assertStatus(200);

        $this->userA1->refresh();
        $this->assertEquals('A1', $this->userA1->profile->max_unlocked_level);
        $this->assertEmpty($this->userA1->profile->completed_levels ?? []);

        // A2 unit is still locked
        $a2Res = $this->actingAs($this->userA1)->getJson("/api/v1/units/{$this->unitA2->id}");
        $a2Res->assertStatus(403);
    }

    /** Scenario 5: A failing final assessment does not unlock the next level */
    public function test_failing_final_assessment_does_not_unlock_next_level(): void
    {
        $questions = LevelExamQuestion::where('cefr_level', 'A1')->get();

        $response = $this->actingAs($this->userA1)
            ->postJson('/api/v1/levels/A1/exam/submit', [
                'answers' => [
                    ['question_id' => $questions[0]->id, 'answer' => 'Wrong Answer'],
                    ['question_id' => $questions[1]->id, 'answer' => 'Wrong Answer'],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'passed' => false,
                'score_percent' => 0,
                'pass_threshold_percent' => 80,
            ]);

        $this->userA1->refresh();
        $this->assertEquals('A1', $this->userA1->profile->max_unlocked_level);
        $this->assertEmpty($this->userA1->profile->completed_levels ?? []);
    }

    /** Scenario 6 & 7: A passing final assessment (>=80%) unlocks next level, calculated on server */
    public function test_passing_final_assessment_unlocks_next_level_evaluated_on_server(): void
    {
        $questions = LevelExamQuestion::where('cefr_level', 'A1')->get();

        $response = $this->actingAs($this->userA1)
            ->postJson('/api/v1/levels/A1/exam/submit', [
                'answers' => [
                    ['question_id' => $questions[0]->id, 'answer' => 'Buenos días'],
                    ['question_id' => $questions[1]->id, 'answer' => 'soy'],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'passed' => true,
                'score_percent' => 100,
                'next_level' => 'A2',
                'xp_awarded' => 100,
            ])
            ->assertJsonStructure([
                'attempt_id',
                'section_performance' => ['vocabulary', 'grammar'],
                'detailed_results',
                'academic_disclaimer_ar',
            ]);

        $this->userA1->refresh();
        $this->assertEquals('A2', $this->userA1->profile->max_unlocked_level);
        $this->assertContains('A1', $this->userA1->profile->completed_levels);

        // A2 unit is now unlocked and accessible!
        $a2Res = $this->actingAs($this->userA1)->getJson("/api/v1/units/{$this->unitA2->id}");
        $a2Res->assertStatus(200);
    }

    /** Scenario 8: Client cannot unlock a level by modifying request payloads */
    public function test_client_cannot_unlock_level_by_modifying_request_payloads(): void
    {
        // Try sending fake score or fake passed flag in payload
        $questions = LevelExamQuestion::where('cefr_level', 'A1')->get();

        $response = $this->actingAs($this->userA1)
            ->postJson('/api/v1/levels/A1/exam/submit', [
                'passed' => true,
                'score_percent' => 100,
                'max_unlocked_level' => 'C2',
                'answers' => [
                    ['question_id' => $questions[0]->id, 'answer' => 'Invalid Answer'],
                    ['question_id' => $questions[1]->id, 'answer' => 'Invalid Answer'],
                ],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'passed' => false,
                'score_percent' => 0,
            ]);

        $this->userA1->refresh();
        $this->assertEquals('A1', $this->userA1->profile->max_unlocked_level);
    }

    /** Scenario 9: XP and achievement rewards cannot be duplicated by submitting the same completion request repeatedly */
    public function test_rewards_cannot_be_duplicated_by_submitting_repeatedly(): void
    {
        $questions = LevelExamQuestion::where('cefr_level', 'A1')->get();
        $answers = [
            ['question_id' => $questions[0]->id, 'answer' => 'Buenos días'],
            ['question_id' => $questions[1]->id, 'answer' => 'soy'],
        ];

        // First submission (Passing)
        $res1 = $this->actingAs($this->userA1)->postJson('/api/v1/levels/A1/exam/submit', ['answers' => $answers]);
        $res1->assertStatus(200)->assertJson(['xp_awarded' => 100]);
        $this->assertEquals(100, $this->userA1->refresh()->profile->total_xp);

        // Second submission (Retrying after already passed)
        $res2 = $this->actingAs($this->userA1)->postJson('/api/v1/levels/A1/exam/submit', ['answers' => $answers]);
        $res2->assertStatus(200)->assertJson(['xp_awarded' => 0]); // Zero duplicate XP awarded!
        $this->assertEquals(100, $this->userA1->refresh()->profile->total_xp);
    }

    /** Scenario 10: Another user cannot access someone else's private progress or journal entries */
    public function test_user_cannot_access_other_users_private_journal(): void
    {
        $entryA = JournalEntry::create([
            'user_id' => $this->userA1->id,
            'title' => 'Private Diary of User A',
            'content' => 'Hoy fue un día maravilloso en Madrid.',
            'entry_date' => now()->toDateString(),
            'word_count' => 7,
            'cefr_level_target' => 'A1',
            'is_draft' => false,
            'is_analyzed' => false,
        ]);

        // Other user attempts to read User A's journal entry
        $response = $this->actingAs($this->otherUser)->getJson("/api/v1/journal/{$entryA->id}");
        $response->assertStatus(404);

        // Other user attempts to delete User A's journal entry
        $delRes = $this->actingAs($this->otherUser)->deleteJson("/api/v1/journal/{$entryA->id}");
        $delRes->assertStatus(404);
    }
}
