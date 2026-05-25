<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$kasir = \App\Models\Menu::where('name', 'Kasir')->first();
if ($kasir) {
    $kasir->route = null;
    $kasir->save();
    
    \App\Models\Menu::firstOrCreate(
        ['name' => 'POS Kasir', 'parent_id' => $kasir->id],
        ['route' => 'kasir.index', 'order' => 1]
    );
    \App\Models\Menu::firstOrCreate(
        ['name' => 'Pesanan Dapur', 'parent_id' => $kasir->id],
        ['route' => 'kasir.pesanan', 'order' => 2]
    );
    \App\Models\Menu::firstOrCreate(
        ['name' => 'Laporan Pendapatan', 'parent_id' => $kasir->id],
        ['route' => 'kasir.laporan', 'order' => 3]
    );
    echo "Menu Kasir Updated\n";
} else {
    echo "Menu Kasir not found\n";
}
