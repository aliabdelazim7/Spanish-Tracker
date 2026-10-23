<?php

namespace Tests\Feature;

use App\Models\NotificationPreference;
use App\Models\PushSubscription;
use App\Models\User;
use App\Services\WebPushService;
use Database\Seeders\CourseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CourseSeeder::class);
    }

    public function test_unauthenticated_user_cannot_access_notifications(): void
    {
        $this->getJson('/api/v1/notifications/vapid-key')->assertStatus(401);
        $this->getJson('/api/v1/notifications/preferences')->assertStatus(401);
        $this->postJson('/api/v1/notifications/subscribe', [])->assertStatus(401);
    }

    public function test_authenticated_user_can_retrieve_vapid_public_key(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/v1/notifications/vapid-key');

        $response->assertStatus(200)
            ->assertJsonStructure(['public_key', 'is_configured'])
            ->assertJsonPath('is_configured', true);

        $this->assertNotEmpty($response->json('public_key'));
    }

    public function test_user_can_retrieve_and_update_notification_preferences(): void
    {
        $user = User::factory()->create();

        // 1. Fetch preferences (auto-creates default)
        $getResp = $this->actingAs($user)->getJson('/api/v1/notifications/preferences');
        $getResp->assertStatus(200)
            ->assertJsonPath('preferences.daily_reminder_enabled', true)
            ->assertJsonPath('preferences.preferred_reminder_time', '20:00');

        // 2. Update preferences
        $updateResp = $this->actingAs($user)->putJson('/api/v1/notifications/preferences', [
            'daily_reminder_enabled' => false,
            'review_due_enabled' => true,
            'preferred_reminder_time' => '18:30',
            'timezone' => 'Europe/Madrid',
        ]);

        $updateResp->assertStatus(200)
            ->assertJsonPath('preferences.daily_reminder_enabled', false)
            ->assertJsonPath('preferences.preferred_reminder_time', '18:30')
            ->assertJsonPath('preferences.timezone', 'Europe/Madrid');

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $user->id,
            'daily_reminder_enabled' => false,
            'preferred_reminder_time' => '18:30',
        ]);
    }

    public function test_user_can_subscribe_and_unsubscribe_push_device(): void
    {
        $user = User::factory()->create();

        $endpoint = 'https://fcm.googleapis.com/fcm/send/test-device-endpoint-123';
        $payload = [
            'endpoint' => $endpoint,
            'keys' => [
                'p256dh' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QT9AcFY3Xn2RTo02G_thFqRbfjT10TGjeLK_ribRReJF2624',
                'auth' => 'tBHItJI5svbpez7KI4CCXg',
            ],
            'device_name' => 'Chrome on Android Pixel',
        ];

        // 1. Subscribe
        $subResp = $this->actingAs($user)->postJson('/api/v1/notifications/subscribe', $payload);
        $subResp->assertStatus(201)
            ->assertJsonStructure(['message', 'subscription_id']);

        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => $endpoint,
            'device_name' => 'Chrome on Android Pixel',
        ]);

        // 2. Unsubscribe
        $unsubResp = $this->actingAs($user)->postJson('/api/v1/notifications/unsubscribe', [
            'endpoint' => $endpoint,
        ]);
        $unsubResp->assertStatus(200)
            ->assertJsonPath('deleted', true);

        $this->assertDatabaseMissing('push_subscriptions', [
            'user_id' => $user->id,
            'endpoint' => $endpoint,
        ]);
    }

    public function test_user_cannot_delete_another_users_subscription(): void
    {
        $victim = User::factory()->create();
        $attacker = User::factory()->create();

        $endpoint = 'https://fcm.googleapis.com/fcm/send/victim-endpoint-999';
        PushSubscription::create([
            'user_id' => $victim->id,
            'endpoint' => $endpoint,
            'public_key' => 'key123',
            'auth_token' => 'auth123',
        ]);

        // Attacker attempts to delete victim's endpoint
        $resp = $this->actingAs($attacker)->postJson('/api/v1/notifications/unsubscribe', [
            'endpoint' => $endpoint,
        ]);

        $resp->assertStatus(200)
            ->assertJsonPath('deleted', false);

        // Victim's subscription is still safe in DB
        $this->assertDatabaseHas('push_subscriptions', [
            'user_id' => $victim->id,
            'endpoint' => $endpoint,
        ]);
    }

    public function test_test_notification_dispatch_and_logging(): void
    {
        $user = User::factory()->create();

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://updates.push.services.mozilla.com/wpush/v2/test',
            'public_key' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QT9AcFY3Xn2RTo02G_thFqRbfjT10TGjeLK_ribRReJF2624',
            'auth_token' => 'tBHItJI5svbpez7KI4CCXg',
        ]);

        $resp = $this->actingAs($user)->postJson('/api/v1/notifications/test');

        $resp->assertStatus(200)
            ->assertJsonStructure(['message', 'result']);

        $this->assertDatabaseHas('notification_logs', [
            'user_id' => $user->id,
            'type' => 'test',
        ]);
    }

    public function test_scheduler_command_runs_idempotently(): void
    {
        $user = User::factory()->create();
        $user->profile()->create([
            'current_streak' => 3,
            'daily_goal_minutes' => 15,
        ]);

        // Preference set for current hour
        $currentHour = now()->format('H');
        NotificationPreference::create([
            'user_id' => $user->id,
            'daily_reminder_enabled' => true,
            'review_due_enabled' => true,
            'preferred_reminder_time' => $currentHour . ':00',
            'timezone' => 'UTC',
        ]);

        PushSubscription::create([
            'user_id' => $user->id,
            'endpoint' => 'https://fcm.googleapis.com/fcm/send/test-scheduled',
            'public_key' => 'BNcRdreALRFXTkOOUHK1EtK2wtaz5Ry4YfYCA_0QT9AcFY3Xn2RTo02G_thFqRbfjT10TGjeLK_ribRReJF2624',
            'auth_token' => 'tBHItJI5svbpez7KI4CCXg',
        ]);

        // Run command
        $this->artisan('learning:send-reminders')
            ->assertExitCode(0);

        $initialLogsCount = \App\Models\NotificationLog::where('user_id', $user->id)
            ->where('type', 'daily_reminder')
            ->count();
        $this->assertGreaterThanOrEqual(1, $initialLogsCount);

        // Re-run command immediately - duplicate should NOT be sent
        $this->artisan('learning:send-reminders')
            ->assertExitCode(0);

        $secondLogsCount = \App\Models\NotificationLog::where('user_id', $user->id)
            ->where('type', 'daily_reminder')
            ->count();
        $this->assertEquals($initialLogsCount, $secondLogsCount);
    }
}
