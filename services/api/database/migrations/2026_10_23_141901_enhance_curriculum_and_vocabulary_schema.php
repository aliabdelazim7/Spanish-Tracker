<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vocabulary_items', function (Blueprint $table) {
            $table->string('english_meaning')->nullable()->after('arabic_meaning');
            $table->text('example_sentence_en')->nullable()->after('example_sentence_ar');
            $table->string('plural_form')->nullable()->after('gender');
            $table->text('notes')->nullable()->after('topic');
            $table->json('conjugation_data')->nullable()->after('notes');
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->string('skill_category')->default('grammar')->after('grammar_notes_ar'); // vocabulary, grammar, verbs, practical, reading, listening, writing, speaking, review
            $table->text('grammar_notes_en')->nullable()->after('grammar_notes_ar');
            $table->text('objective_en')->nullable()->after('objective_ar');
        });

        Schema::table('units', function (Blueprint $table) {
            $table->string('title_en')->nullable()->after('title_ar');
            $table->text('description_en')->nullable()->after('description_ar');
        });

        Schema::table('lesson_exercises', function (Blueprint $table) {
            $table->text('prompt_en')->nullable()->after('prompt_ar');
            $table->text('explanation_en')->nullable()->after('explanation_ar');
        });
    }

    public function down(): void
    {
        Schema::table('lesson_exercises', function (Blueprint $table) {
            $table->dropColumn(['prompt_en', 'explanation_en']);
        });

        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn(['title_en', 'description_en']);
        });

        Schema::table('lessons', function (Blueprint $table) {
            $table->dropColumn(['skill_category', 'grammar_notes_en', 'objective_en']);
        });

        Schema::table('vocabulary_items', function (Blueprint $table) {
            $table->dropColumn(['english_meaning', 'example_sentence_en', 'plural_form', 'notes', 'conjugation_data']);
        });
    }
};
