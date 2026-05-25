<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Menu;

class MenuSeeder extends Seeder
{
    public function run()
    {
        Menu::create([
            'name' => 'Dashboard',
            'route' => 'dashboard',
            'icon' => 'home',
            'order' => 1,
        ]);

        $masterData = Menu::create([
            'name' => 'Master Data',
            'route' => null,
            'permission_name' => 'view_users', // Hanya yang punya akses 'view_users' yang bisa lihat menu dropdown ini
            'icon' => 'database',
            'order' => 2,
        ]);

        Menu::create([
            'name' => 'Users',
            'route' => 'users.index',
            'icon' => 'users',
            'permission_name' => 'view_users',
            'parent_id' => $masterData->id,
            'order' => 1,
        ]);

        Menu::create([
            'name' => 'Anggota',
            'route' => 'anggotas.index',
            'icon' => 'users',
            'permission_name' => 'view_anggotas',
            'parent_id' => $masterData->id,
            'order' => 2,
        ]);

        Menu::create([
            'name' => 'Dana DKK',
            'route' => 'dana-dkks.index',
            'icon' => 'document-text',
            'permission_name' => 'view_dana_dkks',
            'parent_id' => $masterData->id,
            'order' => 3,
        ]);

        Menu::create([
            'name' => 'Pinjaman',
            'route' => 'pinjaman.index',
            'icon' => 'cash',
            'permission_name' => 'view_pinjaman',
            'parent_id' => $masterData->id,
            'order' => 4,
        ]);

        Menu::create([
            'name' => 'Pembayaran Pinjaman',
            'route' => 'pinjaman-bayar.index',
            'icon' => 'credit-card',
            'permission_name' => 'view_pinjaman_bayar',
            'parent_id' => $masterData->id,
            'order' => 5,
        ]);

        Menu::create([
            'name' => 'Permissions',
            'route' => 'permissions.index',
            'icon' => 'shield',
            'permission_name' => 'manage_user_permissions',
            'parent_id' => $masterData->id,
            'order' => 6,
        ]);

        Menu::create([
            'name' => 'Profile',
            'route' => 'profile.edit',
            'parent_id' => $masterData->id,
            'order' => 7,
        ]);
    }
}