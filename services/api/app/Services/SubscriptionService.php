<?php

namespace App\Services;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\PaymentTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class SubscriptionService
{
    /**
     * Get all active subscription plans.
     */
    public function getAvailablePlans()
    {
        return Plan::active()->get();
    }

    /**
     * Get user's current subscription details.
     */
    public function getUserSubscription(User $user): array
    {
        $subscription = $user->subscription()->with('plan')->first();

        if (!$subscription) {
            // Default free tier fallback
            $freePlan = Plan::where('slug', 'free')->first();
            return [
                'has_subscription' => false,
                'is_pro' => false,
                'status' => 'free',
                'plan' => $freePlan,
                'current_period_ends_at' => null,
                'days_remaining' => 0,
                'can_cancel' => false,
            ];
        }

        return [
            'has_subscription' => true,
            'is_pro' => $subscription->isPro(),
            'status' => $subscription->status,
            'plan' => $subscription->plan,
            'billing_cycle' => $subscription->billing_cycle,
            'currency' => $subscription->currency,
            'amount_paid' => $subscription->amount_paid,
            'payment_method' => $subscription->payment_method,
            'current_period_starts_at' => $subscription->current_period_starts_at?->toIso8601String(),
            'current_period_ends_at' => $subscription->current_period_ends_at?->toIso8601String(),
            'days_remaining' => $subscription->daysRemaining(),
            'canceled_at' => $subscription->canceled_at?->toIso8601String(),
            'can_cancel' => $subscription->isActive() && $subscription->status !== 'canceled' && $subscription->plan?->slug !== 'free',
        ];
    }

    /**
     * Process checkout/activation for a plan.
     */
    public function checkout(User $user, string $planSlug, string $paymentMethod = 'vodafone_cash', string $currency = 'EGP'): array
    {
        $plan = Plan::where('slug', $planSlug)->where('is_active', true)->firstOrFail();

        $now = Carbon::now();
        $isFree = $plan->isFree();

        $amount = $currency === 'USD' ? $plan->price_usd : $plan->price_egp;

        $endsAt = null;
        if ($plan->billing_cycle === 'monthly') {
            $endsAt = (clone $now)->addDays(30);
        } elseif ($plan->billing_cycle === 'annual') {
            $endsAt = (clone $now)->addDays(365);
        }

        $paymentRef = 'SUB_' . strtoupper(Str::random(10));

        // Create or update subscription
        $subscription = Subscription::updateOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => $plan->billing_cycle,
                'currency' => $currency,
                'amount_paid' => $amount,
                'payment_method' => $paymentMethod,
                'payment_reference' => $paymentRef,
                'current_period_starts_at' => $now,
                'current_period_ends_at' => $endsAt,
                'canceled_at' => null,
            ]
        );

        // Record payment transaction
        if ($amount > 0) {
            PaymentTransaction::create([
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'amount' => $amount,
                'currency' => $currency,
                'gateway' => $paymentMethod,
                'status' => 'completed',
                'transaction_reference' => 'TXN_' . strtoupper(Str::random(12)),
                'payload' => [
                    'plan_slug' => $plan->slug,
                    'billing_cycle' => $plan->billing_cycle,
                    'timestamp' => $now->toIso8601String(),
                ],
            ]);
        }

        return [
            'success' => true,
            'message_ar' => 'تم تفعيل باقة ' . $plan->name_ar . ' بنجاح! استمتع بكافة ميزات المنصة دون قيود.',
            'message_en' => 'Successfully subscribed to ' . $plan->name_en . '!',
            'subscription' => $this->getUserSubscription($user),
        ];
    }

    /**
     * Cancel an active recurring subscription.
     */
    public function cancelSubscription(User $user): array
    {
        $subscription = $user->subscription()->where('status', 'active')->first();

        if (!$subscription) {
            return [
                'success' => false,
                'message_ar' => 'لا يوجد اشتراك نشط للإلغاء.',
            ];
        }

        $subscription->update([
            'status' => 'canceled',
            'canceled_at' => Carbon::now(),
        ]);

        return [
            'success' => true,
            'message_ar' => 'تم إلغاء التجديد التلقائي للاشتراك. ستبقى ميزاتك متاحة حتى نهاية الفترة الحالية (' . ($subscription->current_period_ends_at ? $subscription->current_period_ends_at->format('Y-m-d') : 'اليوم') . ').',
            'subscription' => $this->getUserSubscription($user),
        ];
    }
}
