<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Curriculum & Course
        $this->call([
            CourseSeeder::class,
            PlacementQuestionSeeder::class,
            VocabularySeeder::class,
            StorySeeder::class,
            AchievementSeeder::class,
        ]);

        // 2. Demo User for quick test and development
        $demoUser = User::firstOrCreate(
            ['email' => 'learner@example.com'],
            [
                'name' => 'طارق المصري',
                'password' => Hash::make('password123'),
                'role' => 'learner',
            ]
        );

        UserProfile::firstOrCreate(
            ['user_id' => $demoUser->id],
            [
                'learning_reason' => 'travel',
                'daily_goal_minutes' => 15,
                'experience_level' => 'zero',
                'explanation_dialect' => 'egyptian_arabic',
                'target_variant' => 'spain',
                'estimated_cefr_level' => 'A1',
                'onboarding_completed' => true,
                'total_xp' => 45,
                'current_streak' => 2,
                'longest_streak' => 2,
                'timezone' => 'Africa/Cairo',
            ]
        );

        // Demo Admin
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'مشرف النظام',
                'password' => Hash::make('admin12345'),
                'role' => 'admin',
            ]
        );
    }
}
