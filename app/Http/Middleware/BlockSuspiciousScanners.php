<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousScanners
{
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip() ?? 'unknown';

        if ($this->isBlocked($ip)) {
            abort(403, 'Akses diblokir demi keamanan.');
        }

        if ($this->looksLikeScanner($request)) {
            $this->registerStrike($request, 'scanner_signature');
            abort(403, 'Akses diblokir demi keamanan.');
        }

        if ($this->targetsSensitivePath($request)) {
            $this->registerStrike($request, 'sensitive_path_probe');
            abort(403, 'Akses diblokir demi keamanan.');
        }

        return $next($request);
    }

    private function isBlocked(string $ip): bool
    {
        return Cache::has($this->blockKey($ip));
    }

    private function looksLikeScanner(Request $request): bool
    {
        $userAgent = strtolower((string) $request->userAgent());

        if ($userAgent === '') {
            return true;
        }

        $signatures = [
            'sqlmap',
            'nikto',
            'nmap',
            'masscan',
            'acunetix',
            'nessus',
            'dirbuster',
            'gobuster',
            'wpscan',
            'whatweb',
            'zgrab',
            'zmeu',
            'curl/',
            'python-requests',
            'httpx',
            'libwww-perl',
            'java/',
            'go-http-client',
        ];

        foreach ($signatures as $signature) {
            if (str_contains($userAgent, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function targetsSensitivePath(Request $request): bool
    {
        $path = '/'.ltrim(strtolower($request->path()), '/');

        $suspiciousPatterns = [
            '/.env',
            '/wp-admin',
            '/wp-login.php',
            '/xmlrpc.php',
            '/phpmyadmin',
            '/pma',
            '/adminer',
            '/manager/html',
            '/boaform',
            '/cgi-bin',
            '/vendor',
            '/storage/logs',
            '/storage/framework',
            '/server-status',
            '/debug/default',
            '/_ignition',
            '/actuator',
            '/.git',
            '/config.php',
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }

        return false;
    }

    private function registerStrike(Request $request, string $reason): void
    {
        $ip = $request->ip() ?? 'unknown';
        $strikeKey = $this->strikeKey($ip);
        $blockSeconds = (int) config('security.scanner_block_seconds', 3600);
        $maxStrikes = (int) config('security.scanner_max_strikes', 1);
        $strikes = (int) Cache::get($strikeKey, 0) + 1;

        Cache::put($strikeKey, $strikes, now()->addSeconds($blockSeconds));

        if ($strikes >= $maxStrikes) {
            Cache::put($this->blockKey($ip), true, now()->addSeconds($blockSeconds));
        }

        Log::warning('SECURITY SCANNER BLOCK', [
            'reason' => $reason,
            'ip_address' => $ip,
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'path' => $request->path(),
            'strikes' => $strikes,
            'blocked_until_seconds' => $blockSeconds,
        ]);
    }

    private function strikeKey(string $ip): string
    {
        return 'security:scanner:strikes:'.$ip;
    }

    private function blockKey(string $ip): string
    {
        return 'security:scanner:blocked:'.$ip;
    }
}
