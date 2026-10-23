<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_tutor_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('action_type'); // explain, correct, practice, speaking_feedback
            $table->text('prompt_summary');
            $table->text('response_summary');
            $table->string('model_used')->default('mock-provider');
            $table->integer('tokens_used')->default(0);
            $table->string('status')->default('success');
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_tutor_logs');
    }
};
