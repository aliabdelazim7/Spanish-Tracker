<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_and_receives_token(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'أحمد علي',
            'email' => 'ahmed@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'access_token',
                'token_type',
                'user' => ['id', 'name', 'email', 'profile'],
            ]);

        $this->assertDatabaseHas('users', ['email' => 'ahmed@example.com']);
        $this->assertDatabaseHas('user_profiles', [
            'learning_reason' => 'travel',
            'max_unlocked_level' => 'A1',
            'estimated_cefr_level' => 'A1',
        ]);
    }

    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'test@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'user']);
    }

    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'unknown@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(422);
    }

    public function test_authenticated_user_can_fetch_profile(): void
    {
        $user = User::factory()->create();
        $user->profile()->create(['timezone' => 'Africa/Cairo']);

        $response = $this->actingAs($user)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_unauthenticated_request_is_rejected(): void
    {
        $response = $this->getJson('/api/v1/auth/me');
        $response->assertStatus(401);
    }

    public function test_password_reset_requires_valid_token_and_rejects_missing_or_invalid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'victim@example.com',
            'password' => bcrypt('OriginalPassword123!'),
        ]);

        // Attempt without token
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'victim@example.com',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['token']);

        // Attempt with wrong token
        $response = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'victim@example.com',
            'token' => 'invalid-token-123',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);
        $response->assertStatus(422);

        // Password should remain unchanged
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('OriginalPassword123!', $user->fresh()->password));
    }

    public function test_password_reset_succeeds_with_valid_token_and_revokes_previous_tokens(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('OldPassword123!'),
        ]);
        $user->createToken('active_session_token');

        $this->assertCount(1, $user->tokens);

        // Request reset
        $forgotResponse = $this->postJson('/api/v1/auth/forgot-password', [
            'email' => 'student@example.com',
        ]);
        $forgotResponse->assertStatus(200);
        $token = $forgotResponse->json('debug_token');
        $this->assertNotEmpty($token);

        // Submit valid reset
        $resetResponse = $this->postJson('/api/v1/auth/reset-password', [
            'email' => 'student@example.com',
            'token' => $token,
            'password' => 'BrandNewPassword123!',
            'password_confirmation' => 'BrandNewPassword123!',
        ]);
        $resetResponse->assertStatus(200);

        // Password updated
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('BrandNewPassword123!', $user->fresh()->password));

        // Old access tokens revoked
        $this->assertCount(0, $user->fresh()->tokens);

        // Token consumed from password_reset_tokens
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'student@example.com']);
    }

    public function test_regular_learner_cannot_access_admin_overview(): void
    {
        $learner = User::factory()->create(['role' => 'learner']);

        $response = $this->actingAs($learner)->getJson('/api/v1/admin/overview');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_admin_overview(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->getJson('/api/v1/admin/overview');
        $response->assertStatus(200)
            ->assertJsonStructure(['stats', 'recent_lessons']);
    }
}
