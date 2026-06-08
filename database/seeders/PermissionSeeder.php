<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // User Management Permissions
            ['name' => 'view_users', 'guard_name' => 'web'],
            ['name' => 'create_users', 'guard_name' => 'web'],
            ['name' => 'edit_users', 'guard_name' => 'web'],
            ['name' => 'delete_users', 'guard_name' => 'web'],
            ['name' => 'manage_user_permissions', 'guard_name' => 'web'],

            // Menu Management Permissions
            ['name' => 'view_menus', 'guard_name' => 'web'],
            ['name' => 'create_menus', 'guard_name' => 'web'],
            ['name' => 'edit_menus', 'guard_name' => 'web'],
            ['name' => 'delete_menus', 'guard_name' => 'web'],
            ['name' => 'reorder_menus', 'guard_name' => 'web'],

            // Anggota Management Permissions
            ['name' => 'view_anggotas', 'guard_name' => 'web'],
            ['name' => 'create_anggotas', 'guard_name' => 'web'],
            ['name' => 'edit_anggotas', 'guard_name' => 'web'],
            ['name' => 'delete_anggotas', 'guard_name' => 'web'],

            // Produk Management Permissions
            ['name' => 'view_produks', 'guard_name' => 'web'],
            ['name' => 'create_produks', 'guard_name' => 'web'],
            ['name' => 'edit_produks', 'guard_name' => 'web'],
            ['name' => 'delete_produks', 'guard_name' => 'web'],
            
            // Kasir / Transaksi
            ['name' => 'view_kasir', 'guard_name' => 'web'],
            ['name' => 'create_kasir', 'guard_name' => 'web'],
            ['name' => 'edit_kasir', 'guard_name' => 'web'],
            ['name' => 'delete_kasir', 'guard_name' => 'web'],

            // Rapat Management
            ['name' => 'view_rapat', 'guard_name' => 'web'],
            ['name' => 'create_rapat', 'guard_name' => 'web'],
            ['name' => 'edit_rapat', 'guard_name' => 'web'],

            // Transaksi & Keuangan Permissions
            ['name' => 'view_dana_dkks', 'guard_name' => 'web'],
            ['name' => 'create_dana_dkks', 'guard_name' => 'web'],
            ['name' => 'view_pinjaman', 'guard_name' => 'web'],
            ['name' => 'create_pinjaman', 'guard_name' => 'web'],
            ['name' => 'view_pinjaman_bayar', 'guard_name' => 'web'],
            ['name' => 'create_pinjaman_bayar', 'guard_name' => 'web'],

            // Ruangan Management Permissions
            ['name' => 'view_ruangans', 'guard_name' => 'web'],
            ['name' => 'create_ruangans', 'guard_name' => 'web'],
            ['name' => 'edit_ruangans', 'guard_name' => 'web'],
            ['name' => 'delete_ruangans', 'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            try {
                Permission::updateOrCreate(
                    [
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ],
                    $permission
                );
            } catch (\Exception $e) {
                // Skip if duplicate, continue with others
                if (!str_contains($e->getMessage(), 'Duplicate')) {
                    throw $e;
                }
            }
        }
    }
}
