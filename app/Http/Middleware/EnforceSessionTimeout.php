<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $timeoutMinutes = (int) config('security.session_idle_timeout', config('session.lifetime', 120));
        $lastActivity = (int) $request->session()->get('last_activity', now()->timestamp);

        if ($timeoutMinutes > 0 && now()->timestamp - $lastActivity > ($timeoutMinutes * 60)) {
            return $this->expireSession($request);
        }

        $request->session()->put('last_activity', now()->timestamp);

        return $next($request);
    }

    private function expireSession(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return new JsonResponse([
                'message' => 'Sesi berakhir karena tidak ada aktivitas.',
            ], 401, [
                'Clear-Site-Data' => '"cache", "cookies", "storage"',
            ]);
        }

        return redirect()
            ->route('login')
            ->with('status', 'Sesi berakhir karena tidak ada aktivitas. Silakan login kembali.')
            ->withHeaders([
                'Clear-Site-Data' => '"cache", "cookies", "storage"',
            ]);
    }
}
