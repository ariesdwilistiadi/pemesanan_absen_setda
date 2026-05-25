<?php

namespace Database\Seeders;

use App\Models\Agama;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Reset cache Spatie Permission
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Eksekusi pembuat permission & menu terlebih dahulu
        $this->call([
            PermissionSeeder::class,
            MenuSeeder::class,
        ]);

        // 3. Eksekusi Master Data Agama
        $agamas = ['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];
        foreach ($agamas as $agama) {
            Agama::firstOrCreate(['nama' => $agama]);
        }

        // 4. Buat Akun Test
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );

        // Sinkronisasi dengan SEMUA permission yang ada di seeder baru
        $testUser->syncPermissions([
            'view_users', 'create_users', 'edit_users', 'delete_users', 'manage_user_permissions',
            'view_menus', 'create_menus', 'edit_menus', 'delete_menus', 'reorder_menus',
            'view_anggotas', 'create_anggotas', 'edit_anggotas', 'delete_anggotas',
            'view_dana_dkks', 'create_dana_dkks', 
            'view_pinjaman', 'create_pinjaman', 
            'view_pinjaman_bayar', 'create_pinjaman_bayar'
        ]);

        // 5. Buat Akun Utama / Super Admin
        $admin = User::firstOrCreate(
            ['email' => 'dwilistiadi.aries51@gmail.com'],
            [
                'name' => 'Dwi Listiadi Aries',
                'password' => Hash::make('password'),
            ]
        );

        // Super Admin otomatis mendapatkan seluruh hak akses
        $admin->syncPermissions(Permission::all());
    }
}