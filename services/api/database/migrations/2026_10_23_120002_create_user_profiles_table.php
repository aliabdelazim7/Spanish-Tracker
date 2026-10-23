<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('learning_reason')->default('travel'); // travel, career, education, culture, personal
            $table->integer('daily_goal_minutes')->default(15);
            $table->string('experience_level')->default('zero'); // zero, basics, intermediate
            $table->string('explanation_dialect')->default('egyptian_arabic'); // egyptian_arabic, msa
            $table->string('target_variant')->default('spain'); // spain, latin_america
            $table->string('estimated_cefr_level')->nullable(); // A1, A2, B1, B2, C1, C2
            $table->boolean('onboarding_completed')->default(false);
            $table->integer('total_xp')->default(0);
            $table->integer('current_streak')->default(0);
            $table->integer('longest_streak')->default(0);
            $table->date('last_activity_date')->nullable();
            $table->string('timezone')->default('Africa/Cairo');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_profiles');
    }
};
