<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventSensitiveResponseCaching
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (! $response instanceof Response) {
            return $response;
        }

        if (! $this->shouldDisableCaching($request)) {
            return $response;
        }

        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    private function shouldDisableCaching(Request $request): bool
    {
        if ($request->user()) {
            return true;
        }

        return $request->routeIs([
            'login',
            'register',
            'password.*',
            'verification.*',
        ]);
    }
}
