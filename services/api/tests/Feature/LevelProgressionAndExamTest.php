<?php

namespace Tests\Feature;

use App\Models\CefrLevel;
use App\Models\Course;
use App\Models\LevelExamQuestion;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LevelProgressionAndExamTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Course $course;
    protected CefrLevel $levelA1;
    protected CefrLevel $levelA2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'learner']);
        UserProfile::create([
            'user_id' => $this->user->id,
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
            'total_xp' => 0,
        ]);

        $this->course = Course::create([
            'slug' => 'test-spanish',
            'title' => 'Test Spanish',
            'title_ar' => 'إسبانية تجريبية',
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

        // Seed 2 exam questions for A1
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

    public function test_user_can_fetch_exam_for_unlocked_level(): void
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/levels/A1/exam');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'level' => ['code', 'name', 'name_ar'],
                'questions',
                'pass_threshold_percent',
            ])
            ->assertJsonCount(2, 'questions');
    }

    public function test_user_cannot_access_exam_for_locked_level(): void
    {
        // A2 is locked because user max_unlocked_level is A1
        $response = $this->actingAs($this->user)
            ->getJson('/api/v1/levels/A2/exam');

        $response->assertStatus(403);
    }

    public function test_failing_exam_does_not_unlock_next_level(): void
    {
        $questions = LevelExamQuestion::where('cefr_level', 'A1')->get();

        $response = $this->actingAs($this->user)
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
            ]);

        $this->user->refresh();
        $this->assertEquals('A1', $this->user->profile->max_unlocked_level);
        $this->assertEmpty($this->user->profile->completed_levels ?? []);
    }

    public function test_passing_exam_unlocks_a2_and_awards_xp(): void
    {
        $questions = LevelExamQuestion::where('cefr_level', 'A1')->get();

        $response = $this->actingAs($this->user)
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
            ]);

        $this->user->refresh();
        $this->assertEquals('A2', $this->user->profile->max_unlocked_level);
        $this->assertContains('A1', $this->user->profile->completed_levels);
        $this->assertEquals(100, $this->user->profile->total_xp);

        // Verify learningPath shows A2 as unlocked now
        $pathResponse = $this->actingAs($this->user)->getJson('/api/v1/learning-path');
        $pathResponse->assertStatus(200);

        $levels = $pathResponse->json('course.levels');
        $a1Data = collect($levels)->firstWhere('code', 'A1');
        $a2Data = collect($levels)->firstWhere('code', 'A2');

        $this->assertTrue($a1Data['is_completed']);
        $this->assertTrue($a2Data['is_unlocked']);
    }
}
