<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('in_progress'); // in_progress, completed, abandoned
            $table->integer('score_percent')->default(0);
            $table->integer('xp_awarded')->default(0);
            $table->boolean('is_reward_claimed')->default(false);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'lesson_id']);
        });

        Schema::create('exercise_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lesson_exercise_id')->constrained()->cascadeOnDelete();
            $table->text('user_answer')->nullable();
            $table->boolean('is_correct')->default(false);
            $table->integer('attempt_number')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exercise_attempts');
        Schema::dropIfExists('lesson_attempts');
    }
};
