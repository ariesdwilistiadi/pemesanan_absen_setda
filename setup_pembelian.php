<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Menu;

$permissions = ['view_pembelian', 'create_pembelian', 'edit_pembelian', 'delete_pembelian'];

foreach ($permissions as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

$role = Role::first();
if ($role) {
    $role->givePermissionTo($permissions);
}

// Tambah Menu Pembelian di bawah Master Data (atau sebagai menu utama)
$masterData = Menu::where('name', 'Master Data')->first();
if ($masterData) {
    Menu::firstOrCreate(
        ['name' => 'Pembelian Barang', 'parent_id' => $masterData->id],
        ['route' => 'pembelian.index', 'order' => 8, 'permission_name' => 'view_pembelian']
    );
} else {
    Menu::firstOrCreate(
        ['name' => 'Pembelian Barang'],
        ['route' => 'pembelian.index', 'order' => 10, 'permission_name' => 'view_pembelian']
    );
}

echo "Pembelian Setup Completed\n";
