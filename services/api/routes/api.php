<?php

use App\Http\Controllers\Api\Admin\ContentController as AdminContentController;
use App\Http\Controllers\Api\AiTutorController;
use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CurriculumController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\LevelExamController;
use App\Http\Controllers\Api\LessonSessionController;
use App\Http\Controllers\Api\MyMistakesController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OnboardingController;
use App\Http\Controllers\Api\PlacementTestController;
use App\Http\Controllers\Api\StoryController;
use App\Http\Controllers\Api\VocabularyController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Public Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

    // Public Curriculum browsing
    Route::get('/levels', [CurriculumController::class, 'levels']);

    // Public High-Fidelity Audio / Pronunciation Stream & Capabilities
    Route::get('/audio/tts', [AudioController::class, 'tts']);
    Route::get('/audio/capabilities', [AudioController::class, 'capabilities']);

    // Protected Routes (requires Sanctum token)
    Route::middleware('auth:sanctum')->group(function () {
        // User & Auth
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Onboarding
        Route::get('/onboarding', [OnboardingController::class, 'show']);
        Route::patch('/onboarding', [OnboardingController::class, 'update']);

        // Placement Test
        Route::post('/placement-tests/start', [PlacementTestController::class, 'start']);
        Route::get('/placement-tests/history', [PlacementTestController::class, 'history']);
        Route::get('/placement-tests/{attemptId}', [PlacementTestController::class, 'getAttempt']);
        Route::post('/placement-tests/{attemptId}/answers', [PlacementTestController::class, 'submitAnswer']);
        Route::post('/placement-tests/{attemptId}/finish', [PlacementTestController::class, 'finish']);

        // Learning Path & Curriculum
        Route::get('/learning-path', [CurriculumController::class, 'learningPath']);
        Route::get('/units/{unitId}', [CurriculumController::class, 'unit']);
        Route::get('/lessons/{lessonId}', [CurriculumController::class, 'lesson']);

        // Level Graduation Exams & Assessment
        Route::get('/levels/{levelCode}/exam', [LevelExamController::class, 'getExam']);
        Route::post('/levels/{levelCode}/exam/submit', [LevelExamController::class, 'submitExam']);
        Route::get('/levels/{levelCode}/exam/history', [LevelExamController::class, 'history']);

        // Lesson Session
        Route::post('/lessons/{lessonId}/start', [LessonSessionController::class, 'start']);
        Route::post('/lessons/{lessonId}/exercises/{exerciseId}/answer', [LessonSessionController::class, 'answerExercise']);
        Route::post('/lessons/{lessonId}/complete', [LessonSessionController::class, 'complete']);
        Route::get('/lessons/{lessonId}/progress', [LessonSessionController::class, 'progress']);

        // Stories
        Route::get('/stories', [StoryController::class, 'index']);
        Route::get('/stories/{storyId}', [StoryController::class, 'show']);
        Route::post('/stories/{storyId}/progress', [StoryController::class, 'updateProgress']);
        Route::post('/stories/{storyId}/complete', [StoryController::class, 'complete']);

        // Vocabulary & Spaced Repetition
        Route::get('/vocabulary', [VocabularyController::class, 'index']);
        Route::get('/vocabulary/reviews/due', [VocabularyController::class, 'due']);
        Route::post('/vocabulary/{vocabularyId}/save', [VocabularyController::class, 'saveItem']);
        Route::delete('/vocabulary/{vocabularyId}/save', [VocabularyController::class, 'unsaveItem']);
        Route::post('/vocabulary/{vocabularyId}/review', [VocabularyController::class, 'reviewItem']);

        // AI Tutor (Protected by rate limiting and character validation)
        Route::middleware('throttle:60,1')->group(function () {
            Route::post('/tutor/explain', [AiTutorController::class, 'explain']);
            Route::post('/tutor/correct', [AiTutorController::class, 'correct']);
            Route::post('/tutor/practice', [AiTutorController::class, 'practice']);
            Route::post('/tutor/speaking-feedback', [AiTutorController::class, 'speakingFeedback']);
            Route::post('/tutor/hint', [AiTutorController::class, 'hint']);
            Route::post('/tutor/evaluate-exercise', [AiTutorController::class, 'evaluateExercise']);
        });

        // Dashboard & Gamification
        Route::get('/dashboard', [DashboardController::class, 'index']);
        Route::get('/progress', [DashboardController::class, 'progress']);
        Route::get('/achievements', [DashboardController::class, 'achievements']);

        // Spanish Journal & Writing Analysis
        Route::get('/journal', [JournalController::class, 'index']);
        Route::post('/journal', [JournalController::class, 'store']);
        Route::get('/journal/prompts', [JournalController::class, 'prompts']);
        Route::get('/journal/daily-prompt', [JournalController::class, 'dailyPrompt']);
        Route::get('/journal/follow-up-exercises', [JournalController::class, 'followUpExercises']);
        Route::post('/journal/submit-follow-up', [JournalController::class, 'submitFollowUp']);
        Route::get('/journal/recurring-mistakes', [JournalController::class, 'recurringMistakes']);
        Route::get('/journal/recommended-lessons', [JournalController::class, 'recommendedLessons']);
        Route::get('/journal/progress-stats', [JournalController::class, 'progressStats']);
        Route::patch('/journal/suggestions/{id}/status', [JournalController::class, 'updateSuggestionStatus']);
        Route::get('/journal/{id}', [JournalController::class, 'show']);
        Route::patch('/journal/{id}', [JournalController::class, 'update']);
        Route::delete('/journal/{id}', [JournalController::class, 'destroy']);
        Route::post('/journal/{id}/analyze', [JournalController::class, 'analyze']);
        Route::get('/journal/{id}/feedback', [JournalController::class, 'feedback']);

        // Error Profile & My Mistakes
        Route::get('/my-mistakes', [MyMistakesController::class, 'index']);
        Route::get('/my-mistakes/{id}', [MyMistakesController::class, 'show']);
        Route::post('/my-mistakes/{id}/practice', [MyMistakesController::class, 'practice']);

        // Web Push Notifications & Learning Reminders
        Route::get('/notifications/vapid-key', [NotificationController::class, 'getVapidKey']);
        Route::get('/notifications/preferences', [NotificationController::class, 'getPreferences']);
        Route::put('/notifications/preferences', [NotificationController::class, 'updatePreferences']);
        Route::post('/notifications/subscribe', [NotificationController::class, 'subscribe']);
        Route::post('/notifications/unsubscribe', [NotificationController::class, 'unsubscribe']);
        Route::post('/notifications/test', [NotificationController::class, 'sendTestNotification']);

        // Admin Routes
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/overview', [AdminContentController::class, 'overview']);
            Route::patch('/lessons/{lessonId}/toggle-publish', [AdminContentController::class, 'toggleLessonPublish']);
            Route::patch('/stories/{storyId}/toggle-publish', [AdminContentController::class, 'toggleStoryPublish']);
        });
    });
});
