<?php
// File untuk setup table ruangan dan seed data
// Jalankan dari folder root: php setup_ruangan.php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'migrate']),
    new Symfony\Component\Console\Output\BufferedOutput
);

echo "✅ Migrate selesai.\n";

$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'db:seed', '--class' => 'RuanganSeeder']),
    new Symfony\Component\Console\Output\BufferedOutput
);

echo "✅ Ruangan seeder selesai. Data ruangan sudah ditambahkan ke database.\n";

// Display ruangan data
$ruangans = \App\Models\Ruangan::all();
echo "\nDaftar Ruangan:\n";
echo str_repeat("-", 60) . "\n";
foreach ($ruangans as $r) {
    echo "ID: {$r->id}, Nama: {$r->nama_ruangan}, Status: " . ($r->is_active ? 'Aktif' : 'Tidak Aktif') . "\n";
}
echo str_repeat("-", 60) . "\n";
echo "Total: " . count($ruangans) . " ruangan\n";

echo "\n✅ Setup Ruangan Selesai!\n";
