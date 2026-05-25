<?php

namespace App\Support;

use Illuminate\Contracts\Session\Session;

class LoginChallenge
{
    private const SESSION_KEY = 'login_challenge';

    public static function issue(Session $session, bool $force = false): array
    {
        if (! $force && $session->has(self::SESSION_KEY)) {
            return $session->get(self::SESSION_KEY);
        }

        $left = random_int(2, 9);
        $right = random_int(1, 9);
        $operator = random_int(0, 1) === 1 ? '+' : '-';

        if ($operator === '-' && $left < $right) {
            [$left, $right] = [$right, $left];
        }

        $answer = $operator === '+' ? $left + $right : $left - $right;

        $challenge = [
            'question' => "{$left} {$operator} {$right}",
            'answer' => $answer,
            'issued_at' => now()->timestamp,
        ];

        $session->put(self::SESSION_KEY, $challenge);

        return $challenge;
    }

    public static function question(Session $session): string
    {
        return self::issue($session)['question'];
    }

    public static function answer(Session $session): ?int
    {
        return self::issue($session)['answer'] ?? null;
    }

    public static function isMature(Session $session): bool
    {
        $issuedAt = self::issue($session)['issued_at'] ?? now()->timestamp;
        $minimumSeconds = (int) env('LOGIN_MIN_FORM_FILL_SECONDS', app()->environment('testing') ? 0 : 2);

        return now()->timestamp - (int) $issuedAt >= $minimumSeconds;
    }

    public static function rotate(Session $session): array
    {
        return self::issue($session, true);
    }

    public static function clear(Session $session): void
    {
        $session->forget(self::SESSION_KEY);
    }
}
