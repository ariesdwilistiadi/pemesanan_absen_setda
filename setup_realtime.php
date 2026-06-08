<?php
/**
 * Script untuk setup Real-time Pesanan
 * Jalankan dari folder root: php setup_realtime.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

echo "🚀 Setup Real-time Pesanan\n";
echo str_repeat("=", 50) . "\n\n";

// 1. Clear config cache
echo "📦 Clear config cache...\n";
$kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'config:clear']),
    new Symfony\Component\Console\Output\BufferedOutput
);
echo "   ✅ Config cleared\n\n";

// 2. Check broadcasting config
echo "🔔 Checking broadcasting configuration...\n";
$driver = env('BROADCAST_CONNECTION', 'log');
echo "   Current driver: {$driver}\n\n";

// 3. Info about real-time setup
echo "📋 Real-time Configuration Summary:\n";
echo str_repeat("-", 50) . "\n";
echo "   Mode: Polling (3 detik interval)\n";
echo "   Endpoints:\n";
echo "   - GET /kasir/pesanan (halaman utama)\n";
echo "   - GET /kasir/cek-pesanan (polling endpoint)\n";
echo "   Features:\n";
echo "   - Audio notification (ding-dong sound)\n";
echo "   - Vibration (mobile)\n";
echo "   - Visual badge notification\n";
echo "   - Live status indicator\n";
echo str_repeat("-", 50) . "\n\n";

// 4. Test polling endpoint
echo "🧪 Testing polling endpoint...\n";
$response = file_get_contents('http://localhost:8000/kasir/cek-pesanan?last_id=0');
if ($response) {
    $data = json_decode($response, true);
    if ($data && isset($data['success'])) {
        echo "   ✅ Polling endpoint accessible\n";
        echo "   Current orders: " . ($data['count'] ?? 0) . "\n";
    } else {
        echo "   ⚠️ Response not as expected, but endpoint exists\n";
    }
} else {
    echo "   ⚠️ Could not reach endpoint. Make sure server is running.\n";
    echo "   Start server with: php artisan serve\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 Setup Complete!\n\n";

echo "📌 Cara Penggunaan:\n";
echo "   1. Buka http://localhost:8000/kasir/pesanan\n";
echo "   2. Buka tab baru dan buka http://localhost:8000/kasir\n";
echo "   3. Buat pesanan baru di tab kasir\n";
echo "   4. Lihat pesanan muncul otomatis di tab pesanan\n";
echo "   5. Dengarkan sound notification!\n\n";

echo "📌 Catatan:\n";
echo "   - Polling interval: 3 detik\n";
echo "   - Audio notification: Ding-dong sound\n";
echo "   - Badge akan muncul di pojok kiri atas\n";
echo "   - Klik badge untuk reload data\n";