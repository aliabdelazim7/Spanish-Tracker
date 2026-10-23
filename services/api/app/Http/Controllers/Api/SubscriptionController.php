<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    /**
     * Get all active subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = $this->subscriptionService->getAvailablePlans();

        return response()->json([
            'plans' => $plans,
        ]);
    }

    /**
     * Get the authenticated user's active subscription.
     */
    public function mySubscription(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $this->subscriptionService->getUserSubscription($user);

        return response()->json($subscription);
    }

    /**
     * Checkout or activate a subscription plan.
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_slug' => 'required|string|exists:plans,slug',
            'payment_method' => 'nullable|string|in:vodafone_cash,paymob_card,stripe_card,instapay,free_tier,demo_instant',
            'currency' => 'nullable|string|in:EGP,USD,SAR',
        ]);

        $user = $request->user();
        $paymentMethod = $validated['payment_method'] ?? 'vodafone_cash';
        $currency = $validated['currency'] ?? 'EGP';

        $result = $this->subscriptionService->checkout($user, $validated['plan_slug'], $paymentMethod, $currency);

        return response()->json($result);
    }

    /**
     * Cancel an active subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->subscriptionService->cancelSubscription($user);

        return response()->json($result);
    }
}
