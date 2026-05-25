<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kasir = \App\Models\Menu::where('name', 'Kasir')->first();
if ($kasir) {
    \App\Models\Menu::firstOrCreate(
        ['name' => 'Laporan Keuntungan', 'parent_id' => $kasir->id],
        ['route' => 'kasir.laporan-keuntungan', 'order' => 4]
    );
    echo "Menu Laporan Keuntungan Updated\n";
} else {
    echo "Menu Kasir not found\n";
}
