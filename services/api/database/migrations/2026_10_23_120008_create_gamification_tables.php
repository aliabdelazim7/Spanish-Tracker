<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('xp_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('amount');
            $table->string('source_type'); // lesson, review, story, placement, daily_goal
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('idempotency_key')->unique();
            $table->string('description');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('title_ar');
            $table->string('description_ar');
            $table->string('icon');
            $table->string('badge_category'); // streak, learning, vocabulary, stories
            $table->integer('xp_reward')->default(50);
            $table->integer('threshold')->default(1);
            $table->timestamps();
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('unlocked_at')->useCurrent();
            $table->timestamps();

            $table->unique(['user_id', 'achievement_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('xp_transactions');
    }
};
