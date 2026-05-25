<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Menu;

if (!Schema::hasTable('retur_headers')) {
    Schema::create('retur_headers', function (Blueprint $table) {
        $table->id();
        $table->string('nomor_transaksi')->unique();
        $table->date('tanggal');
        $table->enum('jenis_retur', ['Masuk', 'Keluar'])->comment('Masuk = dari pelanggan (stok bertambah), Keluar = ke supplier (stok berkurang)');
        $table->string('pihak_terkait')->nullable()->comment('Nama Pelanggan / Supplier');
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}

if (!Schema::hasTable('retur_details')) {
    Schema::create('retur_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('retur_header_id')->constrained('retur_headers')->onDelete('cascade');
        $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
        $table->integer('kuantitas');
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}

$permissions = ['view_retur', 'create_retur', 'edit_retur', 'delete_retur'];
foreach ($permissions as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

$role = Role::first();
if ($role) {
    $role->givePermissionTo($permissions);
}

$masterData = Menu::where('name', 'Master Data')->first();
if ($masterData) {
    Menu::firstOrCreate(
        ['name' => 'Retur Barang', 'parent_id' => $masterData->id],
        ['route' => 'retur.index', 'order' => 10, 'permission_name' => 'view_retur']
    );
} else {
    Menu::firstOrCreate(
        ['name' => 'Retur Barang'],
        ['route' => 'retur.index', 'order' => 12, 'permission_name' => 'view_retur']
    );
}

echo "Retur Barang Setup Completed\n";