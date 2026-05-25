<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_normal_browser_can_open_login_page(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/136.0 Safari/537.36',
        ])->get('/login');

        $response->assertOk();
    }

    public function test_scanner_user_agent_is_blocked_immediately(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'sqlmap/1.8',
        ])->get('/login');

        $response->assertForbidden();
    }

    public function test_sensitive_probe_path_is_blocked_immediately(): void
    {
        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0',
        ])->get('/.env');

        $response->assertForbidden();
    }

    public function test_blocked_ip_stays_blocked_for_following_request(): void
    {
        $this->withHeaders([
            'User-Agent' => 'sqlmap/1.8',
        ])->get('/login')->assertForbidden();

        $response = $this->withHeaders([
            'User-Agent' => 'Mozilla/5.0',
        ])->get('/');

        $response->assertForbidden();
    }
}
