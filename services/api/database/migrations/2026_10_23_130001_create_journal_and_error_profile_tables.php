<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Journal entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->longText('content');
            $table->string('prompt_topic')->nullable();
            $table->string('cefr_level', 4)->default('A1');
            $table->string('status', 20)->default('draft'); // draft, submitted, analyzed
            $table->integer('word_count')->default(0);
            $table->date('entry_date');
            $table->timestamps();

            $table->index(['user_id', 'entry_date']);
        });

        // 2. Writing analyses
        Schema::create('writing_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('corrected_content');
            $table->text('overall_feedback_ar')->nullable();
            $table->text('overall_feedback_en')->nullable();
            $table->integer('fluency_score')->default(80);
            $table->integer('total_errors')->default(0);
            $table->integer('total_suggestions')->default(0);
            $table->timestamps();
        });

        // 3. Writing suggestions
        Schema::create('writing_suggestions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('writing_analysis_id')->constrained()->cascadeOnDelete();
            $table->string('original_text');
            $table->string('suggested_text');
            $table->string('error_category', 50); // grammar, verb_conjugation, gender_agreement, vocabulary, spelling, naturalness
            $table->string('severity', 20)->default('error'); // error, suggestion
            $table->text('explanation_ar');
            $table->text('explanation_en')->nullable();
            $table->text('example_sentence_es')->nullable();
            $table->string('related_concept_slug')->nullable();
            $table->timestamps();
        });

        // 4. Learner error concepts
        Schema::create('learner_error_concepts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('concept_slug');
            $table->string('concept_name_ar');
            $table->string('concept_name_en');
            $table->string('category', 50);
            $table->integer('total_errors_count')->default(1);
            $table->integer('successful_practices_count')->default(0);
            $table->integer('mastery_score')->default(20); // 0-100%
            $table->timestamp('last_occurred_at')->nullable();
            $table->timestamp('last_practiced_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'concept_slug']);
        });

        // 5. Learning recommendations
        Schema::create('learning_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title_ar');
            $table->string('title_en');
            $table->text('reason_ar');
            $table->text('reason_en');
            $table->string('action_type', 50); // practice_concept, review_vocabulary, take_lesson
            $table->json('action_payload')->nullable();
            $table->boolean('is_dismissed')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_recommendations');
        Schema::dropIfExists('learner_error_concepts');
        Schema::dropIfExists('writing_suggestions');
        Schema::dropIfExists('writing_analyses');
        Schema::dropIfExists('journal_entries');
    }
};
