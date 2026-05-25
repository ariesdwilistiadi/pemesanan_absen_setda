<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            return $response;
        }

        $viteDevUrl = app()->environment('local') ? " http://127.0.0.1:5173 http://localhost:5173" : '';
        $viteDevWs = app()->environment('local') ? " ws://127.0.0.1:5173 ws://localhost:5173" : '';

        $cspImages = app()->environment('local') ? ' https://laravel.com' : '';

        $csp = "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline'{$viteDevUrl}; " .
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net{$viteDevUrl}; " .
            "font-src 'self' https://fonts.bunny.net; " .
            "img-src 'self' data:{$cspImages}; " .
            "connect-src 'self'{$viteDevUrl}{$viteDevWs}; " .
            "frame-src 'none'; " .
            "media-src 'none'; " .
            "manifest-src 'self'; " .
            "script-src-attr 'none'; " .
            "style-src-attr 'unsafe-inline'; " .
            "frame-ancestors 'none'; " .
            "base-uri 'self'; " .
            "form-action 'self'; " .
            "object-src 'none';";

        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'accelerometer=(), camera=(), geolocation=(), gyroscope=(), magnetometer=(), microphone=(), payment=(), usb=()');
        $response->headers->set('X-XSS-Protection', '0');
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
        $response->headers->set('Origin-Agent-Cluster', '?1');
        $response->headers->set('X-Permitted-Cross-Domain-Policies', 'none');
        $response->headers->set('Content-Security-Policy', $csp);

        if (app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
            $response->headers->set('Content-Security-Policy', $csp.' upgrade-insecure-requests;');
        }

        return $response;
    }
}
