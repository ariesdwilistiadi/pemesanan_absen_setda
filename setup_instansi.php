<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use App\Models\Menu;

if (!Schema::hasTable('instansi_profiles')) {
    Schema::create('instansi_profiles', function (Blueprint $table) {
        $table->id();
        $table->string('pemerintah')->default('PEMERINTAH PROVINSI DAERAH');
        $table->string('nama_instansi')->default('DINAS / INSTANSI TERKAIT');
        $table->string('alamat')->default('Jalan Contoh Alamat No. 123, Kota, Provinsi - Kode Pos 12345');
        $table->string('kontak')->default('Telp: (021) 1234567 | Email: info@instansi.go.id | Web: www.instansi.go.id');
        $table->string('nama_kepala')->default('NAMA LENGKAP KEPALA');
        $table->string('nip_kepala')->default('19800101 200501 1 001');
        $table->string('jabatan_kepala')->default('Kepala Dinas / Instansi');
        $table->string('logo')->nullable();
        $table->timestamps();
    });
}

// Model file setup
$modelContent = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstansiProfile extends Model
{
    use HasFactory;

    protected \$table = 'instansi_profiles';

    protected \$fillable = [
        'pemerintah',
        'nama_instansi',
        'alamat',
        'kontak',
        'nama_kepala',
        'nip_kepala',
        'jabatan_kepala',
        'logo',
    ];
}
";
file_put_contents(__DIR__.'/app/Models/InstansiProfile.php', $modelContent);

// Seed default data
\App\Models\InstansiProfile::firstOrCreate(['id' => 1]);

$permissions = ['manage_instansi'];
foreach ($permissions as $p) {
    Permission::firstOrCreate(['name' => $p]);
}

$role = Role::first();
if ($role) {
    $role->givePermissionTo($permissions);
}

Menu::firstOrCreate(
    ['name' => 'Profil Instansi'],
    ['route' => 'instansi.edit', 'order' => 99, 'permission_name' => 'manage_instansi']
);

echo "Instansi Profile Setup Completed\n";