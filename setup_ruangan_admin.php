<?php
/**
 * Script untuk setup ruangan: run migrations, seed permissions & menu
 * Jalankan dari folder root: php setup_ruangan_admin.php
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

// 1. Run migrations
echo "📦 Menjalankan migration...\n";
$status = $kernel->handle(
    $input = new Symfony\Component\Console\Input\ArrayInput(['command' => 'migrate']),
    new Symfony\Component\Console\Output\BufferedOutput
);
echo "✅ Migration selesai.\n\n";

// 2. Seed permissions
echo "🔐 Menambahkan permissions ruangan...\n";
$permissions = [
    ['name' => 'view_ruangans', 'guard_name' => 'web'],
    ['name' => 'create_ruangans', 'guard_name' => 'web'],
    ['name' => 'edit_ruangans', 'guard_name' => 'web'],
    ['name' => 'delete_ruangans', 'guard_name' => 'web'],
];

foreach ($permissions as $permission) {
    $exists = \Spatie\Permission\Models\Permission::where('name', $permission['name'])->exists();
    if (!$exists) {
        \Spatie\Permission\Models\Permission::create($permission);
        echo "   ➕ Permission '{$permission['name']}' ditambahkan.\n";
    } else {
        echo "   ✓ Permission '{$permission['name']}' sudah ada.\n";
    }
}
echo "✅ Permissions selesai.\n\n";

// 3. Add menu
echo "📋 Menambahkan menu ruangan...\n";
$menuExists = \App\Models\Menu::where('route', 'ruangans.index')->exists();
if (!$menuExists) {
    // Cari parent_id untuk Master Data
    $masterData = \App\Models\Menu::where('name', 'Master Data')->first();
    $maxOrder = \App\Models\Menu::where('parent_id', $masterData->id)->max('order') ?? 0;

    \App\Models\Menu::create([
        'name' => 'Ruangan',
        'route' => 'ruangans.index',
        'icon' => 'office-building',
        'permission_name' => 'view_ruangans',
        'parent_id' => $masterData->id,
        'order' => $maxOrder + 1,
    ]);
    echo "   ➕ Menu 'Ruangan' ditambahkan.\n";
} else {
    echo "   ✓ Menu 'Ruangan' sudah ada.\n";
}
echo "✅ Menu selesai.\n\n";

// 4. Tampilkan ruangan yang ada
echo "📋 Daftar Ruangan:\n";
echo str_repeat("-", 60) . "\n";
$ruangans = \App\Models\Ruangan::all();
foreach ($ruangans as $r) {
    echo "   ID: {$r->id}, Nama: {$r->nama_ruangan}, Status: " . ($r->is_active ? 'Aktif' : 'Tidak Aktif') . "\n";
}
echo str_repeat("-", 60) . "\n";
echo "Total: " . count($ruangans) . " ruangan\n\n";

echo "🎉 Setup Ruangan Admin Selesai!\n";
echo "\n📌 Langkah selanjutnya:\n";
echo "   1. Berikan permission 'view_ruangans' ke user/admin melalui halaman Permissions\n";
echo "   2. Menu 'Ruangan' akan muncul di sidebar menu Master Data\n";
echo "   3. Akses halaman: http://localhost:8000/ruangans\n";