<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('endpoint');
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('content_encoding')->default('aes128gcm');
            $table->string('device_name')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamps();

            $table->index('user_id');
        });

        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('daily_reminder_enabled')->default(true);
            $table->boolean('review_due_enabled')->default(true);
            $table->boolean('streak_saver_enabled')->default(true);
            $table->boolean('new_content_enabled')->default(true);
            $table->boolean('assessment_results_enabled')->default(true);
            $table->string('preferred_reminder_time', 5)->default('20:00');
            $table->string('timezone', 50)->default('Africa/Cairo');
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->string('type');
            $table->string('title');
            $table->text('body');
            $table->string('status', 20); // sent, failed, expired
            $table->text('error_message')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();

            $table->index(['user_id', 'type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_preferences');
        Schema::dropIfExists('push_subscriptions');
    }
};
