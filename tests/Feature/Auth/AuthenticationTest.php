<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertHeader('Pragma', 'no-cache');
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create();
        $challengePayload = $this->loginChallengePayload();

        $response = $this->withHeaders($this->statefulHeaders())
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
            ] + $challengePayload);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();
        $challengePayload = $this->loginChallengePayload();

        $this->withHeaders($this->statefulHeaders())
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ] + $challengePayload);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders($this->statefulHeaders())
            ->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }

    public function test_login_is_locked_after_too_many_failed_attempts(): void
    {
        $user = User::factory()->create();

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $challengePayload = $this->loginChallengePayload();
            $this->from('/login')
                ->withHeaders($this->statefulHeaders())
                ->post('/login', [
                    'email' => $user->email,
                    'password' => 'wrong-password',
                ] + $challengePayload);
        }

        $challengePayload = $this->loginChallengePayload();
        $response = $this->from('/login')
            ->withHeaders($this->statefulHeaders())
            ->post('/login', [
                'email' => $user->email,
                'password' => 'wrong-password',
            ] + $challengePayload);

        $response->assertSessionHasErrors('email');
    }

    public function test_login_rejects_invalid_human_challenge(): void
    {
        $user = User::factory()->create();

        $this->get('/login');

        $response = $this->from('/login')
            ->withHeaders($this->statefulHeaders())
            ->post('/login', [
                'email' => $user->email,
                'password' => 'password',
                'challenge_answer' => -999,
                'website' => '',
            ]);

        $response->assertSessionHasErrors('challenge_answer');
        $this->assertGuest();
    }
}
