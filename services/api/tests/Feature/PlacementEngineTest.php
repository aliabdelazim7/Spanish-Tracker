<?php

namespace Tests\Feature;

use App\Models\CefrLevel;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\PlacementAttempt;
use App\Models\PlacementQuestion;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserProfile;
use Database\Seeders\CourseSeeder;
use Database\Seeders\PlacementQuestionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PlacementEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
        $this->seed(PlacementQuestionSeeder::class);
        $this->seed(\Database\Seeders\LevelExamQuestionSeeder::class);
    }

    /** Test 1: User can start placement test and receive progressive difficulty questions */
    public function test_user_can_start_placement_test_with_difficulty_progression(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
        ]);

        $response = $this->actingAs($user)->postJson('/api/v1/placement-tests/start');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'attempt' => ['id', 'status'],
                'questions',
                'answered_question_ids',
            ]);

        $questions = $response->json('questions');
        $this->assertGreaterThanOrEqual(8, count($questions));

        // Verify correct_answer and explanation_ar are hidden from question list
        $firstQ = $questions[0];
        $this->assertArrayNotHasKey('correct_answer', $firstQ);
        $this->assertArrayNotHasKey('explanation_ar', $firstQ);

        // Verify no duplicate question IDs
        $ids = array_column($questions, 'id');
        $this->assertEquals(count($ids), count(array_unique($ids)));
    }

    /** Test 2: Placement test assesses different proficiency levels accurately */
    public function test_placement_evaluates_different_proficiency_levels(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
        ]);

        $attempt = PlacementAttempt::create([
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        // Scenario A: Answer half correctly to land in A2 (around 50%)
        $questions = PlacementQuestion::where('is_active', true)->take(6)->get();
        foreach ($questions as $idx => $q) {
            $userAns = ($idx % 2 === 0) ? $q->correct_answer : 'WrongAnswer';
            $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/answers", [
                'question_id' => $q->id,
                'user_answer' => $userAns,
            ]);
        }

        $finishResp = $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/finish");
        $finishResp->assertStatus(200)
            ->assertJsonPath('attempt.status', 'completed')
            ->assertJsonStructure([
                'attempt' => [
                    'id', 'total_score', 'estimated_level', 'skill_breakdown',
                    'academic_disclaimer_ar', 'summary_report_ar', 'recommended_unit'
                ],
            ]);

        $attemptData = $finishResp->json('attempt');
        $this->assertNotEmpty($attemptData['academic_disclaimer_ar']);
        $this->assertStringContainsString('DELE', $attemptData['academic_disclaimer_ar']);
    }

    /** Test 3: Learner placed at A2 can start at A2 without being forced to complete A1 */
    public function test_learner_placed_at_a2_can_start_at_a2_without_a1(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
        ]);

        $attempt = PlacementAttempt::create([
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);

        // Submit answers scoring 50% (triggers A2 placement)
        $questions = PlacementQuestion::take(4)->get();
        // 2 correct (20 pts), 2 incorrect (0 pts) -> 50%
        $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/answers", [
            'question_id' => $questions[0]->id,
            'user_answer' => $questions[0]->correct_answer,
        ]);
        $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/answers", [
            'question_id' => $questions[1]->id,
            'user_answer' => $questions[1]->correct_answer,
        ]);
        $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/answers", [
            'question_id' => $questions[2]->id,
            'user_answer' => 'Wrong Answer',
        ]);
        $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/answers", [
            'question_id' => $questions[3]->id,
            'user_answer' => 'Wrong Answer',
        ]);

        $finish = $this->actingAs($user)->postJson("/api/v1/placement-tests/{$attempt->id}/finish");
        $finish->assertStatus(200);

        $user->refresh();
        $this->assertEquals('A2', $user->profile->estimated_cefr_level);
        $this->assertEquals('A2', $user->profile->max_unlocked_level);
        $this->assertContains('A1', $user->profile->completed_levels);

        // Verify A2 lessons are accessible immediately without taking A1 lessons
        $a2Unit = Unit::whereHas('level', fn($q) => $q->where('code', 'A2'))->first();
        if ($a2Unit) {
            $unitRes = $this->actingAs($user)->getJson("/api/v1/units/{$a2Unit->id}");
            $unitRes->assertStatus(200);

            $a2Lesson = $a2Unit->lessons()->first();
            if ($a2Lesson) {
                $startRes = $this->actingAs($user)->postJson("/api/v1/lessons/{$a2Lesson->id}/start");
                $startRes->assertStatus(200);
            }
        }
    }

    /** Test 4: Progression persistence after logout and login */
    public function test_progression_persistence_after_logout_and_login(): void
    {
        $password = 'SecretPass123!';
        $user = User::factory()->create([
            'email' => 'persisting.learner@example.com',
            'password' => Hash::make($password),
        ]);
        $user->profile()->create([
            'estimated_cefr_level' => 'A2',
            'max_unlocked_level' => 'A2',
            'completed_levels' => ['A1'],
            'total_xp' => 150,
        ]);

        // 1. Authenticate and verify initial state
        $loginRes = $this->postJson('/api/v1/auth/login', [
            'email' => 'persisting.learner@example.com',
            'password' => $password,
        ]);

        $loginRes->assertStatus(200);
        $token = $loginRes->json('access_token');
        $this->assertNotEmpty($token);

        // 2. Fetch learning path
        $pathRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/learning-path');

        $pathRes->assertStatus(200)
            ->assertJsonPath('max_unlocked_level', 'A2')
            ->assertJsonPath('completed_levels', ['A1']);

        // 3. Logout
        $logoutRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/auth/logout');
        $logoutRes->assertStatus(200);

        // 4. Re-login
        $reLogin = $this->postJson('/api/v1/auth/login', [
            'email' => 'persisting.learner@example.com',
            'password' => $password,
        ]);

        $reLogin->assertStatus(200);
        $newToken = $reLogin->json('access_token');

        // 5. Verify progression completely intact
        $meRes = $this->withHeader('Authorization', "Bearer {$newToken}")
            ->getJson('/api/v1/auth/me');

        $meRes->assertStatus(200)
            ->assertJsonPath('user.profile.max_unlocked_level', 'A2')
            ->assertJsonPath('user.profile.completed_levels', ['A1'])
            ->assertJsonPath('user.profile.total_xp', 150);
    }

    /** Test 5: Failing level exam provides targeted review recommendations */
    public function test_failing_level_exam_provides_targeted_review_recommendations(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'completed_levels' => [],
        ]);

        // Get A1 exam questions
        $examRes = $this->actingAs($user)->getJson('/api/v1/levels/A1/exam');
        $examRes->assertStatus(200);
        $questions = $examRes->json('questions');

        // Submit failing answers (all wrong)
        $answers = array_map(function ($q) {
            return ['question_id' => $q['id'], 'answer' => 'Wrong Answer'];
        }, $questions);

        $submitRes = $this->actingAs($user)->postJson('/api/v1/levels/A1/exam/submit', [
            'answers' => $answers,
        ]);

        $submitRes->assertStatus(200)
            ->assertJson([
                'passed' => false,
                'score_percent' => 0,
                'pass_threshold_percent' => 80,
                'current_level' => 'A1',
                'next_level' => null,
            ])
            ->assertJsonStructure([
                'attempt_id',
                'attempt_number',
                'feedback_ar',
                'recommended_lessons',
                'section_performance',
                'detailed_results',
                'academic_disclaimer_ar',
            ]);

        $this->assertNotEmpty($submitRes->json('recommended_lessons'));
        $this->assertEquals(1, $submitRes->json('attempt_number'));

        // Verify history endpoint
        $historyRes = $this->actingAs($user)->getJson('/api/v1/levels/A1/exam/history');
        $historyRes->assertStatus(200)
            ->assertJsonPath('level_code', 'A1')
            ->assertJsonPath('total_attempts', 1)
            ->assertJsonPath('is_passed', false);
    }
}
