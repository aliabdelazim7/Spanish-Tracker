<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('title_ar');
            $table->text('description_ar')->nullable();
            $table->string('target_language')->default('es-ES');
            $table->string('source_language')->default('ar-EG');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('cefr_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('code', 5); // A1, A2, B1, B2, C1, C2
            $table->string('name');
            $table->string('name_ar');
            $table->text('description_ar')->nullable();
            $table->integer('order_index')->default(1);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cefr_level_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('title_ar');
            $table->text('description_ar')->nullable();
            $table->string('icon')->nullable();
            $table->integer('order_index')->default(1);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('title_ar');
            $table->text('objective_ar');
            $table->text('grammar_notes_ar')->nullable();
            $table->integer('estimated_minutes')->default(10);
            $table->integer('xp_reward')->default(20);
            $table->integer('order_index')->default(1);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('lesson_exercises', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lesson_id')->constrained()->cascadeOnDelete();
            $table->string('exercise_type'); // multiple_choice, translation, fill_blank, word_order, matching_pairs, listening, reading, flashcard, short_answer, speaking
            $table->text('prompt');
            $table->text('prompt_ar');
            $table->text('target_phrase')->nullable();
            $table->string('audio_url')->nullable();
            $table->json('options')->nullable();
            $table->json('correct_answers');
            $table->text('explanation_ar')->nullable();
            $table->text('hint_ar')->nullable();
            $table->integer('xp_value')->default(5);
            $table->integer('order_index')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_exercises');
        Schema::dropIfExists('lessons');
        Schema::dropIfExists('units');
        Schema::dropIfExists('cefr_levels');
        Schema::dropIfExists('courses');
    }
};
