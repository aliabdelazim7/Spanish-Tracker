<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\PlanSeeder::class);
    }

    public function test_public_plans_api_returns_active_plans()
    {
        $response = $this->getJson('/api/v1/subscription/plans');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'plans' => [
                '*' => [
                    'id',
                    'slug',
                    'name_ar',
                    'name_en',
                    'price_egp',
                    'price_usd',
                    'billing_cycle',
                    'features_ar',
                    'features_en',
                ]
            ]
        ]);

        $this->assertCount(3, $response->json('plans'));
    }

    public function test_user_can_view_current_subscription_status()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/subscription/my-subscription');

        $response->assertStatus(200);
        $this->assertFalse($response->json('is_pro'));
        $this->assertEquals('free', $response->json('status'));
    }

    public function test_user_can_checkout_and_upgrade_to_pro_monthly()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/subscription/checkout', [
            'plan_slug' => 'pro_monthly',
            'payment_method' => 'vodafone_cash',
            'currency' => 'EGP',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertTrue($response->json('subscription.is_pro'));
        $this->assertEquals('pro_monthly', $response->json('subscription.plan.slug'));
        $this->assertEquals(149.00, $response->json('subscription.amount_paid'));

        // Assert DB records
        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status' => 'active',
            'billing_cycle' => 'monthly',
            'payment_method' => 'vodafone_cash',
        ]);

        $this->assertDatabaseHas('payment_transactions', [
            'user_id' => $user->id,
            'amount' => 149.00,
            'currency' => 'EGP',
            'status' => 'completed',
        ]);

        // User model helper verification
        $this->assertTrue($user->fresh()->isPro());
        $this->assertEquals('pro_monthly', $user->fresh()->subscriptionTier());
    }

    public function test_user_can_checkout_annual_plan_with_usd()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/subscription/checkout', [
            'plan_slug' => 'pro_annual',
            'payment_method' => 'stripe_card',
            'currency' => 'USD',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json('subscription.is_pro'));
        $this->assertEquals(69.99, $response->json('subscription.amount_paid'));
        $this->assertEquals('USD', $response->json('subscription.currency'));
    }

    public function test_user_can_cancel_active_subscription()
    {
        $user = User::factory()->create();

        // 1. Subscribe first
        $this->actingAs($user)->postJson('/api/v1/subscription/checkout', [
            'plan_slug' => 'pro_monthly',
            'payment_method' => 'vodafone_cash',
        ]);

        // 2. Cancel
        $response = $this->actingAs($user)->postJson('/api/v1/subscription/cancel');

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseHas('subscriptions', [
            'user_id' => $user->id,
            'status' => 'canceled',
        ]);
    }
}
