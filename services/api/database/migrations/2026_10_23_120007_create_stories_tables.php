<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stories', function (Blueprint $table) {
            $table->id();
            $table->string('cefr_level', 5)->default('A1');
            $table->string('title_es');
            $table->string('title_ar');
            $table->text('summary_ar');
            $table->string('topic');
            $table->integer('estimated_minutes')->default(5);
            $table->string('difficulty')->default('easy'); // easy, medium, hard
            $table->string('audio_url')->nullable();
            $table->boolean('is_published')->default(true);
            $table->integer('xp_reward')->default(25);
            $table->timestamps();
        });

        Schema::create('story_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->integer('order_index')->default(1);
            $table->string('speaker_name')->nullable();
            $table->text('spanish_text');
            $table->text('arabic_translation');
            $table->text('explanation_ar')->nullable();
            $table->string('audio_url')->nullable();
            $table->json('vocabulary_highlights')->nullable();
            $table->timestamps();
        });

        Schema::create('story_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->integer('after_segment_index')->default(1);
            $table->text('question_es');
            $table->text('question_ar');
            $table->json('options');
            $table->string('correct_answer');
            $table->text('explanation_ar');
            $table->timestamps();
        });

        Schema::create('story_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('story_id')->constrained()->cascadeOnDelete();
            $table->integer('current_segment_index')->default(0);
            $table->boolean('is_completed')->default(false);
            $table->integer('score_percent')->default(0);
            $table->boolean('is_reward_claimed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'story_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('story_progress');
        Schema::dropIfExists('story_questions');
        Schema::dropIfExists('story_segments');
        Schema::dropIfExists('stories');
    }
};
