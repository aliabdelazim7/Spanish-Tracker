<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vocabulary_items', function (Blueprint $table) {
            $table->id();
            $table->string('spanish_word');
            $table->string('phonetic')->nullable();
            $table->string('part_of_speech'); // noun, verb, adjective, adverb, expression
            $table->string('gender')->nullable(); // masculine, feminine
            $table->boolean('is_irregular')->default(false);
            $table->string('arabic_meaning');
            $table->text('example_sentence_es');
            $table->text('example_sentence_ar');
            $table->string('audio_url')->nullable();
            $table->string('cefr_level', 5)->default('A1');
            $table->string('topic');
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('user_vocabulary', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vocabulary_item_id')->constrained()->cascadeOnDelete();
            $table->string('mastery_level')->default('learning'); // learning, reviewing, mastered
            $table->integer('repetitions')->default(0);
            $table->integer('interval_days')->default(1);
            $table->decimal('ease_factor', 4, 2)->default(2.50); // SM-2
            $table->timestamp('next_review_at')->useCurrent();
            $table->timestamp('last_reviewed_at')->nullable();
            $table->integer('successful_reviews')->default(0);
            $table->integer('failed_reviews')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'vocabulary_item_id']);
            $table->index(['user_id', 'next_review_at']);
        });

        Schema::create('vocabulary_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_vocabulary_id')->constrained('user_vocabulary')->cascadeOnDelete();
            $table->integer('quality_rating'); // 0 to 5
            $table->integer('repetition_number');
            $table->integer('interval_applied');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vocabulary_reviews');
        Schema::dropIfExists('user_vocabulary');
        Schema::dropIfExists('vocabulary_items');
    }
};
