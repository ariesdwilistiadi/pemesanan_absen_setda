<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function statefulHeaders(): array
    {
        return [
            'Origin' => config('app.url'),
        ];
    }

    protected function loginChallengePayload(): array
    {
        $this->get('/login');

        return [
            'challenge_answer' => session('login_challenge.answer'),
            'website' => '',
        ];
    }
}
