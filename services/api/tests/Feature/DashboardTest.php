<?php

namespace Tests\Feature;

use App\Models\LearnerErrorConcept;
use App\Models\Lesson;
use App\Models\LessonAttempt;
use App\Models\LevelExamAttempt;
use App\Models\PlacementAttempt;
use App\Models\User;
use App\Models\UserVocabulary;
use App\Models\VocabularyItem;
use App\Models\XpTransaction;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_unauthenticated_user_cannot_access_dashboard(): void
    {
        $response = $this->getJson('/api/v1/dashboard');
        $response->assertStatus(401);
    }

    public function test_dashboard_returns_authentic_persisted_metrics(): void
    {
        $user = User::factory()->create();
        $profile = $user->profile()->create([
            'current_streak' => 5,
            'longest_streak' => 12,
            'total_xp' => 450,
            'estimated_cefr_level' => 'A1',
            'max_unlocked_level' => 'A1',
            'daily_goal_minutes' => 20,
        ]);

        // Complete 1 lesson
        $lesson = Lesson::first();
        LessonAttempt::create([
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'status' => 'completed',
            'score_percent' => 95,
            'xp_earned' => 30,
            'completed_at' => now(),
        ]);

        // Add today's XP transaction
        XpTransaction::create([
            'user_id' => $user->id,
            'amount' => 50,
            'source_type' => 'lesson_completion',
            'source_id' => $lesson->id,
            'description' => 'Completed lesson 1',
            'idempotency_key' => 'test-xp-' . uniqid(),
            'created_at' => now(),
        ]);

        // Add user vocabulary with review due
        $vocab = VocabularyItem::firstOrCreate([
            'spanish_word' => 'amigo',
        ], [
            'part_of_speech' => 'noun',
            'arabic_meaning' => 'صديق',
            'english_meaning' => 'friend',
            'example_sentence_es' => 'Juan es mi amigo.',
            'example_sentence_ar' => 'خوان هو صديقي.',
            'example_sentence_en' => 'Juan is my friend.',
            'topic' => 'people',
            'cefr_level' => 'A1',
            'is_published' => true,
        ]);

        UserVocabulary::create([
            'user_id' => $user->id,
            'vocabulary_item_id' => $vocab->id,
            'srs_stage' => 2,
            'ease_factor' => 2.5,
            'interval_days' => 1,
            'next_review_at' => now()->subHour(),
        ]);

        // Add placement attempt
        PlacementAttempt::create([
            'user_id' => $user->id,
            'status' => 'completed',
            'estimated_level' => 'A1',
            'total_score' => 85,
            'completed_at' => now(),
        ]);

        // Add level exam attempt
        LevelExamAttempt::create([
            'user_id' => $user->id,
            'cefr_level' => 'A1',
            'score_percent' => 88,
            'passed' => true,
            'completed_at' => now(),
            'attempt_number' => 1,
            'answers_payload' => [],
        ]);

        // Add weak area concept
        LearnerErrorConcept::create([
            'user_id' => $user->id,
            'concept_slug' => 'ser-vs-estar',
            'concept_name_ar' => 'الفرق بين Ser و Estar',
            'concept_name_en' => 'Ser vs Estar',
            'category' => 'grammar',
            'total_errors_count' => 4,
            'mastery_score' => 45,
        ]);

        $response = $this->actingAs($user)->getJson('/api/v1/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'profile',
                'stats' => [
                    'total_xp',
                    'current_streak',
                    'longest_streak',
                    'daily_goal_minutes',
                    'estimated_level',
                    'max_unlocked_level',
                    'lessons_completed_count',
                    'vocabulary_count',
                    'due_reviews_count',
                ],
                'course_progress' => [
                    'level_code',
                    'level_name_ar',
                    'completed_lessons',
                    'total_lessons',
                    'progress_percent',
                ],
                'daily_goal' => [
                    'goal_minutes',
                    'today_minutes',
                    'today_xp',
                    'progress_percent',
                    'is_goal_met',
                ],
                'recent_assessments' => [
                    'placement',
                    'level_exam',
                ],
                'weak_areas',
                'recommended_lesson',
            ]);

        // Verify accurate metrics
        $response->assertJsonPath('stats.current_streak', 5);
        $response->assertJsonPath('stats.longest_streak', 12);
        $response->assertJsonPath('stats.total_xp', 450);
        $response->assertJsonPath('stats.lessons_completed_count', 1);
        $response->assertJsonPath('stats.due_reviews_count', 1);
        $response->assertJsonPath('course_progress.level_code', 'A1');
        $response->assertJsonPath('course_progress.completed_lessons', 1);
        $this->assertGreaterThan(0, $response->json('course_progress.progress_percent'));
        $response->assertJsonPath('daily_goal.today_xp', 50);
        $response->assertJsonPath('recent_assessments.placement.estimated_level', 'A1');
        $response->assertJsonPath('recent_assessments.level_exam.score_percent', 88);
        $response->assertJsonCount(1, 'weak_areas');
        $response->assertJsonPath('weak_areas.0.concept_slug', 'ser-vs-estar');
    }

    public function test_dashboard_progress_endpoint_returns_skills_and_history(): void
    {
        $user = User::factory()->create();
        $user->profile()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/progress');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'skills' => [
                    'reading',
                    'vocabulary',
                    'grammar',
                    'listening',
                    'speaking',
                ],
                'recent_xp',
            ]);
    }

    public function test_daily_sprint_returns_questions_and_submits_with_shareable_card(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['current_streak' => 3, 'total_xp' => 120]);

        // Seed 5 items for sprint
        for ($i = 1; $i <= 5; $i++) {
            VocabularyItem::create([
                'spanish_word' => "palabra_{$i}",
                'arabic_meaning' => "معنى_{$i}",
                'english_meaning' => "meaning_{$i}",
                'part_of_speech' => 'noun',
                'example_sentence_es' => "Esta es una palabra_{$i} de prueba.",
                'example_sentence_ar' => "هذه كلمة_{$i} للتجربة.",
                'example_sentence_en' => "This is a test word_{$i}.",
                'topic' => 'general',
                'cefr_level' => 'A1',
                'is_published' => true,
            ]);
        }

        // 1. Fetch Sprint
        $res = $this->actingAs($user)->getJson('/api/v1/dashboard/daily-sprint');
        $res->assertStatus(200)
            ->assertJsonStructure([
                'already_completed_today',
                'sprint_date',
                'level',
                'xp_reward',
                'questions' => [
                    '*' => ['id', 'order', 'spanish_word', 'prompt_ar', 'options']
                ]
            ]);

        $questions = $res->json('questions');
        $this->assertCount(5, $questions);

        // 2. Submit Sprint with answers
        $answers = [];
        foreach ($questions as $q) {
            $item = VocabularyItem::find($q['id']);
            $answers[] = [
                'question_id' => $q['id'],
                'selected_option' => $item->arabic_meaning,
            ];
        }

        $submitRes = $this->actingAs($user)->postJson('/api/v1/dashboard/daily-sprint/submit', [
            'answers' => $answers,
        ]);

        $submitRes->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('passed', true)
            ->assertJsonPath('accuracy_percent', 100)
            ->assertJsonStructure([
                'shareable_card' => [
                    'learner_name',
                    'streak_days',
                    'cefr_level',
                    'total_xp',
                    'accuracy_percent',
                    'date_formatted',
                    'badge_title',
                    'share_caption',
                ]
            ]);

        $this->assertEquals(25, $submitRes->json('xp_awarded'));
    }
}
