<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use App\Models\User;
use App\Models\UserVocabulary;
use App\Models\XpTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    protected ?WebPush $webPush = null;
    protected ?string $publicKey;
    protected ?string $privateKey;
    protected string $subject;

    public function __construct()
    {
        $this->publicKey = config('services.webpush.public_key');
        $this->privateKey = config('services.webpush.private_key');
        $this->subject = config('services.webpush.subject', 'mailto:admin@ruta-espanol.com');

        if ($this->isConfigured()) {
            try {
                $this->webPush = new WebPush([
                    'VAPID' => [
                        'subject' => $this->subject,
                        'publicKey' => $this->publicKey,
                        'privateKey' => $this->privateKey,
                    ],
                ]);
                $this->webPush->setAutomaticPadding(false); // optimize payload size
            } catch (\Throwable $e) {
                Log::warning('Failed to initialize WebPush service: ' . $e->getMessage());
                $this->webPush = null;
            }
        }
    }

    public function isConfigured(): bool
    {
        return !empty($this->publicKey) && !empty($this->privateKey);
    }

    public function getPublicKey(): ?string
    {
        return $this->publicKey;
    }

    /**
     * Send push notification to all active devices of a user.
     */
    public function sendNotificationToUser(User $user, string $title, string $body, array $options = []): array
    {
        $subscriptions = $user->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No active push subscriptions found for this user.',
                'sent_count' => 0,
            ];
        }

        $payload = json_encode([
            'title' => $title,
            'body' => $body,
            'icon' => '/pwa-192x192.png',
            'badge' => '/pwa-192x192.png',
            'dir' => 'rtl',
            'lang' => 'ar',
            'data' => [
                'url' => $options['url'] ?? '/dashboard',
                'type' => $options['type'] ?? 'general',
                'timestamp' => now()->toISOString(),
            ],
        ], JSON_UNESCAPED_UNICODE);

        $sentCount = 0;
        $failedCount = 0;
        $expiredCount = 0;

        foreach ($subscriptions as $subRecord) {
            $status = 'sent';
            $errorMessage = null;

            if ($this->webPush) {
                try {
                    $sub = Subscription::create([
                        'endpoint' => $subRecord->endpoint,
                        'publicKey' => $subRecord->public_key,
                        'authToken' => $subRecord->auth_token,
                        'contentEncoding' => $subRecord->content_encoding ?? 'aes128gcm',
                    ]);

                    $report = $this->webPush->sendOneNotification($sub, $payload);

                    if ($report->isSuccess()) {
                        $sentCount++;
                        $subRecord->update(['last_used_at' => now()]);
                    } else {
                        $failedCount++;
                        $status = 'failed';
                        $errorMessage = $report->getReason();

                        // 404 Not Found or 410 Gone indicates expired subscription
                        if ($report->isSubscriptionExpired()) {
                            $status = 'expired';
                            $expiredCount++;
                            $subRecord->delete();
                        }
                    }
                } catch (\Throwable $e) {
                    $failedCount++;
                    $status = 'failed';
                    $errorMessage = $e->getMessage();
                    Log::error('WebPush dispatch exception: ' . $e->getMessage());
                }
            } else {
                // Simulated delivery (e.g. testing or missing VAPID)
                $sentCount++;
                $status = 'sent';
                $errorMessage = 'Simulated delivery (WebPush credentials not active).';
            }

            // Record in audit log
            NotificationLog::create([
                'user_id' => $user->id,
                'subscription_id' => $status === 'expired' ? null : $subRecord->id,
                'type' => $options['type'] ?? 'general',
                'title' => $title,
                'body' => $body,
                'status' => $status,
                'error_message' => $errorMessage,
                'sent_at' => now(),
            ]);
        }

        return [
            'success' => $sentCount > 0,
            'sent_count' => $sentCount,
            'failed_count' => $failedCount,
            'expired_count' => $expiredCount,
        ];
    }

    /**
     * Send scheduled reminders across users based on timezone, preferred time, and real learning state.
     */
    public function sendScheduledReminders(): array
    {
        $users = User::with(['profile', 'notificationPreference', 'pushSubscriptions'])->get();
        $processed = 0;
        $remindersSent = 0;

        foreach ($users as $user) {
            $pref = $user->notificationPreference;
            if (!$pref) {
                continue;
            }

            // Check if user has any active subscriptions
            if ($user->pushSubscriptions->isEmpty()) {
                continue;
            }

            $userTz = $pref->timezone ?: 'Africa/Cairo';
            try {
                $userLocalTime = Carbon::now($userTz);
            } catch (\Throwable) {
                $userLocalTime = Carbon::now('UTC');
            }

            // 1. Check Daily Learning Reminder
            if ($pref->daily_reminder_enabled) {
                // Parse preferred time, e.g. "20:00"
                $targetParts = explode(':', $pref->preferred_reminder_time ?: '20:00');
                $targetHour = (int) ($targetParts[0] ?? 20);

                // Only send during the user's scheduled hour window (e.g., 20:00 - 20:59)
                if ($userLocalTime->hour === $targetHour) {
                    // Check idempotency: check if daily reminder was already sent to user today
                    $alreadySentToday = NotificationLog::where('user_id', $user->id)
                        ->where('type', 'daily_reminder')
                        ->whereDate('sent_at', $userLocalTime->toDateString())
                        ->exists();

                    if (!$alreadySentToday) {
                        // Check if user has already achieved their daily goal today
                        $todayXp = XpTransaction::where('user_id', $user->id)
                            ->whereDate('created_at', $userLocalTime->toDateString())
                            ->sum('amount');

                        $goalMinutes = $user->profile?->daily_goal_minutes ?? 15;
                        $hasCompletedGoal = $todayXp >= ($goalMinutes * 3);

                        if (!$hasCompletedGoal) {
                            $streak = $user->profile?->current_streak ?? 0;
                            $title = 'حان وقت جرعتك اليومية من الإسبانية! 🇪🇸';
                            $body = $streak > 0
                                ? "حافظ على حماسك وحافظ على سلسلتك ({$streak} أيام متتالية)! 10 دقائق فقط اليوم تصنع الفرق."
                                : 'ابدأ درس اليوم خطوة بخطوة مع تمارسين ونطق أصيل.';

                            $this->sendNotificationToUser($user, $title, $body, [
                                'url' => '/learn',
                                'type' => 'daily_reminder',
                            ]);
                            $remindersSent++;
                        }
                    }
                }
            }

            // 2. Check Vocabulary Review Due Reminder
            if ($pref->review_due_enabled) {
                $alreadySentVocabToday = NotificationLog::where('user_id', $user->id)
                    ->where('type', 'review_due')
                    ->whereDate('sent_at', $userLocalTime->toDateString())
                    ->exists();

                if (!$alreadySentVocabToday) {
                    $dueCount = UserVocabulary::where('user_id', $user->id)
                        ->where('next_review_at', '<=', now())
                        ->count();

                    if ($dueCount >= 3) {
                        $this->sendNotificationToUser(
                            $user,
                            'كلمات تنتظر المراجعة بالذاكرة المتباعدة 🧠',
                            "لديك {$dueCount} كلمات جاهزة للمراجعة السريعة لتثبيتها في الذاكرة طويلة المدى.",
                            [
                                'url' => '/review',
                                'type' => 'review_due',
                            ]
                        );
                        $remindersSent++;
                    }
                }
            }

            $processed++;
        }

        return [
            'users_checked' => $processed,
            'reminders_sent' => $remindersSent,
        ];
    }
}
