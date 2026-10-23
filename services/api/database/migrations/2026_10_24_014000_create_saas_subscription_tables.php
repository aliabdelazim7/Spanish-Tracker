<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique(); // free, pro_monthly, pro_annual, vip_lifetime
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->decimal('price_egp', 8, 2)->default(0);
            $table->decimal('price_usd', 8, 2)->default(0);
            $table->string('billing_cycle')->default('monthly'); // free, monthly, annual, lifetime
            $table->json('features_ar')->nullable();
            $table->json('features_en')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('plan_id')->constrained('plans')->cascadeOnDelete();
            $table->string('status')->default('active'); // active, trialing, past_due, canceled, expired
            $table->string('billing_cycle')->default('monthly'); // free, monthly, annual, lifetime
            $table->string('currency')->default('EGP');
            $table->decimal('amount_paid', 8, 2)->default(0);
            $table->string('payment_method')->default('free_tier'); // vodafone_cash, paymob_card, stripe_card, instapay, free_tier
            $table->string('payment_reference')->nullable();
            $table->timestamp('current_period_starts_at')->nullable();
            $table->timestamp('current_period_ends_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subscription_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 8, 2);
            $table->string('currency')->default('EGP');
            $table->string('gateway')->default('paymob'); // paymob, stripe, vodafone_cash, instapay, manual
            $table->string('status')->default('completed'); // pending, completed, failed, refunded
            $table->string('transaction_reference')->unique()->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
    }
};
