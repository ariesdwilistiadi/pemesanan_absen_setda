<?php
require __DIR__.'/vendor/autoload.php';

try {
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    echo "Aplikasi berhasil dimuat!\n";
} catch (\Throwable $e) {
    echo "🚨 ERROR DITEMUKAN!\n";
    echo "Pesan: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " pada baris " . $e->getLine() . "\n\n";
    echo "=== JEJAK EROR (STACK TRACE) ===\n";
    foreach ($e->getTrace() as $i => $t) {
        $file = $t['file'] ?? 'unknown';
        $line = $t['line'] ?? '?';
        $func = $t['function'] ?? '?';
        echo "#$i $file:$line -> $func()\n";
    }
}
