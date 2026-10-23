<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Add progression columns to user_profiles if not present
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('max_unlocked_level', 5)->default('A1')->after('estimated_cefr_level');
            $table->json('completed_levels')->nullable()->after('max_unlocked_level');
        });

        // 2. Add skill_category and content_type to units for structured modular layout
        Schema::table('units', function (Blueprint $table) {
            $table->string('skill_category')->default('core')->after('icon'); // vocabulary, grammar, verbs, listening, reading, phrases, exam
        });

        // 3. Level Exam Questions Table
        Schema::create('level_exam_questions', function (Blueprint $table) {
            $table->id();
            $table->string('cefr_level', 5); // A1, A2, B1, B2, C1, C2
            $table->string('skill_category'); // vocabulary, grammar, verbs, listening, reading, practical
            $table->text('question_text');
            $table->text('prompt_ar');
            $table->string('audio_url')->nullable();
            $table->json('options');
            $table->string('correct_answer');
            $table->text('explanation_ar')->nullable();
            $table->integer('points')->default(10);
            $table->integer('order_index')->default(1);
            $table->timestamps();
        });

        // 4. Level Exam Attempts Table
        Schema::create('level_exam_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('cefr_level', 5);
            $table->integer('score_percent')->default(0);
            $table->boolean('passed')->default(false);
            $table->json('skill_breakdown')->nullable();
            $table->text('feedback_ar')->nullable();
            $table->integer('xp_awarded')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'cefr_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('level_exam_attempts');
        Schema::dropIfExists('level_exam_questions');

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('skill_category');
        });

        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['max_unlocked_level', 'completed_levels']);
        });
    }
};
