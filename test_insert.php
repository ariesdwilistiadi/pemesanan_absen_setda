<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    App\Models\Anggota::create([
        'nama' => 'Test',
        'no_identitas' => '123',
        'tempat_lahir' => 'Jakarta',
        'tgl_lahir' => '2000-01-01',
        'alamat' => 'Test',
        'jenis_kelamin' => 'laki-laki',
        'no_telp' => '08123',
        'agama_id' => 1,
        'pekerjaan' => 'Test',
        'tgl_masuk' => '2024-01-01',
        'simpanan_pokok' => 0,
        'simpanan_wajib' => 0,
        'status' => 1
    ]);
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
