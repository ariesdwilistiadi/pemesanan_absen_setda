<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Hardening Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini memusatkan semua pengaturan env terkait keamanan 
    | (rate limiting, timeout, scanner blocking) agar aman saat perintah
    | `php artisan config:cache` dijalankan di production.
    |
    */

    // Batas percobaan dan waktu blokir untuk fitur otorisasi
    'permission_denial_max_attempts' => (int) env('PERMISSION_DENIAL_MAX_ATTEMPTS', 5),
    'permission_denial_lockout_seconds' => (int) env('PERMISSION_DENIAL_LOCKOUT_SECONDS', 60),

    // Waktu tunggu sesi tidak aktif (Fallback ke session.lifetime bawaan Laravel)
    'session_idle_timeout' => (int) env('SESSION_IDLE_TIMEOUT', config('session.lifetime', 120)),

    // Proteksi dari Web Vulnerability Scanners (Bot)
    'scanner_block_seconds' => (int) env('SCANNER_BLOCK_SECONDS', 3600),
    'scanner_max_strikes' => (int) env('SCANNER_MAX_STRIKES', 1),
];