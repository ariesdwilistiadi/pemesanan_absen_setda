<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiTokenAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_receive_access_and_refresh_tokens(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
            'device_name' => 'web-app',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'token_type',
                'access_token',
                'access_token_expires_at',
                'refresh_token',
                'refresh_token_expires_at',
                'user' => ['id', 'name', 'email'],
            ]);

        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    public function test_refresh_token_rotates_the_token_pair(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
            'device_name' => 'mobile-app',
        ])->assertOk();

        $oldRefreshToken = $loginResponse->json('refresh_token');
        $oldRefreshTokenId = PersonalAccessToken::findToken($oldRefreshToken)?->id;

        $refreshResponse = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $oldRefreshToken,
        ]);

        $refreshResponse->assertOk()
            ->assertJsonStructure([
                'access_token',
                'refresh_token',
            ]);

        $this->assertNull(PersonalAccessToken::find($oldRefreshTokenId));
        $this->assertDatabaseCount('personal_access_tokens', 2);
    }

    public function test_access_token_can_access_me_endpoint(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
        ])->assertOk();

        $accessToken = $loginResponse->json('access_token');

        $response = $this->withHeader('Authorization', 'Bearer '.$accessToken)
            ->getJson('/api/auth/me');

        $response->assertOk()
            ->assertJsonPath('user.email', $user->email);
    }

    public function test_logout_revokes_access_and_refresh_tokens_for_current_session(): void
    {
        $user = User::factory()->create([
            'password' => 'secret123',
        ]);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'secret123',
            'device_name' => 'tablet-app',
        ])->assertOk();

        $accessToken = $loginResponse->json('access_token');
        $refreshToken = $loginResponse->json('refresh_token');

        $this->withHeader('Authorization', 'Bearer '.$accessToken)
            ->postJson('/api/auth/logout')
            ->assertOk();

        $this->assertNull(PersonalAccessToken::findToken($accessToken));
        $this->assertNull(PersonalAccessToken::findToken($refreshToken));
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }
}
