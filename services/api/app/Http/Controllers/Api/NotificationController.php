<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    protected WebPushService $webPushService;

    public function __construct(WebPushService $webPushService)
    {
        $this->webPushService = $webPushService;
    }

    /**
     * Retrieve the server's public VAPID key.
     */
    public function getVapidKey(): JsonResponse
    {
        return response()->json([
            'public_key' => $this->webPushService->getPublicKey(),
            'is_configured' => $this->webPushService->isConfigured(),
        ]);
    }

    /**
     * Get the authenticated user's notification preferences.
     */
    public function getPreferences(Request $request): JsonResponse
    {
        $user = $request->user();
        $preferences = NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'daily_reminder_enabled' => true,
                'review_due_enabled' => true,
                'streak_saver_enabled' => true,
                'new_content_enabled' => true,
                'assessment_results_enabled' => true,
                'preferred_reminder_time' => '20:00',
                'timezone' => $user->profile?->timezone ?? 'Africa/Cairo',
            ]
        );

        $activeSubscriptionsCount = $user->pushSubscriptions()->count();

        return response()->json([
            'preferences' => $preferences,
            'active_devices_count' => $activeSubscriptionsCount,
        ]);
    }

    /**
     * Update notification preferences for the authenticated user.
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'daily_reminder_enabled' => 'nullable|boolean',
            'review_due_enabled' => 'nullable|boolean',
            'streak_saver_enabled' => 'nullable|boolean',
            'new_content_enabled' => 'nullable|boolean',
            'assessment_results_enabled' => 'nullable|boolean',
            'preferred_reminder_time' => ['nullable', 'string', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/'],
            'timezone' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $preferences = NotificationPreference::firstOrCreate(['user_id' => $user->id]);
        $preferences->update($validated);

        return response()->json([
            'message' => 'Notification preferences updated successfully.',
            'preferences' => $preferences->fresh(),
        ]);
    }

    /**
     * Register or update a browser push subscription for the authenticated user.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string|url',
            'keys.p256dh' => 'required|string',
            'keys.auth' => 'required|string',
            'device_name' => 'nullable|string|max:100',
        ]);

        $user = $request->user();

        $subscription = PushSubscription::updateOrCreate(
            [
                'user_id' => $user->id,
                'endpoint' => $request->endpoint,
            ],
            [
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('content_encoding', 'aes128gcm'),
                'device_name' => $request->input('device_name', $request->header('User-Agent')),
                'last_used_at' => now(),
            ]
        );

        return response()->json([
            'message' => 'Push subscription registered successfully.',
            'subscription_id' => $subscription->id,
        ], 201);
    }

    /**
     * Unsubscribe a specific device endpoint.
     */
    public function unsubscribe(Request $request): JsonResponse
    {
        $request->validate([
            'endpoint' => 'required|string',
        ]);

        $user = $request->user();
        $deleted = PushSubscription::where('user_id', $user->id)
            ->where('endpoint', $request->endpoint)
            ->delete();

        return response()->json([
            'message' => $deleted ? 'Subscription removed successfully.' : 'Subscription not found.',
            'deleted' => (bool) $deleted,
        ]);
    }

    /**
     * Send an immediate test notification to the authenticated user.
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        $user = $request->user();
        $result = $this->webPushService->sendNotificationToUser(
            $user,
            'مرحباً بك في إشعارات Ruta Español! 🇪🇸✨',
            'تم تفعيل إشعارات التذكير بنجاح. ستصلك تذكيراتك اليومية وفق جدولك المفضل.',
            [
                'url' => '/dashboard',
                'type' => 'test',
            ]
        );

        return response()->json([
            'message' => 'Test notification dispatch processed.',
            'result' => $result,
        ]);
    }
}
