<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // 1. Pastikan user terautentikasi
        if (!$user) {
            if ($request->expectsJson() || $request->header('X-Inertia')) {
                abort(401, 'Unauthenticated access.');
            }
            return redirect()->guest(route('login'));
        }

        // --- KEAMANAN TINGKAT TINGGI: Cegah Brute Force & Scanning ---
        // Buat kunci rate limit berdasarkan IP, User ID, dan Permission yang dituju
        $rateLimitKey = 'permission_check:' . $permission . ':' . $request->ip() . ':' . $user->id;

        // Blokir jika terlalu banyak percobaan tidak sah (Contoh: 5 kali per menit)
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            Log::critical('SECURITY THREAT: Brute-force permission bypass attempt blocked.', [
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'target_permission' => $permission,
            ]);
            abort(429, 'Terlalu banyak permintaan tidak sah. Akses ditangguhkan sementara demi keamanan.');
        }

        // 2. Strict Permission Check
        if (!$user->can($permission)) {
            // Catat kegagalan ke Rate Limiter (blokir 60 detik jika > 5 kali gagal)
            RateLimiter::hit($rateLimitKey, 60);

            // 3. Security Audit Logging Level Forensik
            Log::alert('SECURITY ALERT: Unauthorized access attempt', [
                'event_type' => 'unauthorized_access',
                'user_id' => $user->id,
                'email' => $user->email,
                'attempted_permission' => $permission,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'target_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'session_id' => session()->getId(),
                // Hindari melog nilai mentah untuk mencegah kebocoran password/PIN, log key-nya saja
                'payload_keys' => array_keys($request->except(['password', 'password_confirmation', 'pin', 'token'])), 
            ]);

            // 4. Samarkan pesan error (Jangan bocorkan nama permission kepada penyerang)
            abort(403, 'Access Denied. You do not have the required authorization to perform this action.');
        }

        // Bersihkan hit count jika berhasil masuk (mencegah blokir tidak sengaja)
        RateLimiter::clear($rateLimitKey);

        return $next($request);
    }
}
