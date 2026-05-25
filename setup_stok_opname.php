<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Menu;

if (!Schema::hasTable('stok_opname_headers')) {
    Schema::create('stok_opname_headers', function (Blueprint $table) {
        $table->id();
        $table->string('nomor_transaksi')->unique();
        $table->date('tanggal');
        $table->string('penanggung_jawab');
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}

if (!Schema::hasTable('stok_opname_details')) {
    Schema::create('stok_opname_details', function (Blueprint $table) {
        $table->id();
        $table->foreignId('stok_opname_header_id')->constrained('stok_opname_headers')->onDelete('cascade');
        $table->foreignId('produk_id')->constrained('produks')->onDelete('cascade');
        $table->integer('stok_sistem');
        $table->integer('stok_fisik');
        $table->integer('selisih');
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}

$permissions = ['view_stok_opname', 'create_stok_opname', 'edit_stok_opname', 'delete_stok_opname'];
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
        ['name' => 'Stok Opname', 'parent_id' => $masterData->id],
        ['route' => 'stok-opname.index', 'order' => 9, 'permission_name' => 'view_stok_opname']
    );
} else {
    Menu::firstOrCreate(
        ['name' => 'Stok Opname'],
        ['route' => 'stok-opname.index', 'order' => 11, 'permission_name' => 'view_stok_opname']
    );
}

echo "Stok Opname Setup Completed\n";