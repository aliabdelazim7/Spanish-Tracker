<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('placement_questions', function (Blueprint $table) {
            $table->id();
            $table->string('skill_category'); // vocabulary, grammar, reading, listening, speaking
            $table->string('difficulty_level', 5); // A1, A2, B1, B2, C1
            $table->string('question_type'); // multiple_choice, fill_blank, reading_comprehension
            $table->text('question_text');
            $table->text('context_text')->nullable(); // reading text or dialogue
            $table->text('prompt_ar');
            $table->string('audio_url')->nullable();
            $table->json('options');
            $table->string('correct_answer');
            $table->text('explanation_ar');
            $table->integer('points')->default(10);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('placement_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default('in_progress'); // in_progress, completed
            $table->integer('total_score')->default(0);
            $table->string('estimated_level', 5)->nullable(); // A1, A2, B1, B2, C1, C2
            $table->json('skill_breakdown')->nullable();
            $table->text('summary_report_ar')->nullable();
            $table->foreignId('recommended_unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('placement_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('placement_attempt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('placement_question_id')->constrained()->cascadeOnDelete();
            $table->text('user_answer');
            $table->boolean('is_correct');
            $table->integer('points_awarded')->default(0);
            $table->integer('time_spent_seconds')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('placement_answers');
        Schema::dropIfExists('placement_attempts');
        Schema::dropIfExists('placement_questions');
    }
};
