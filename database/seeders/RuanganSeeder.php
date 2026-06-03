<?php

namespace Database\Seeders;

use App\Models\Ruangan;
use Illuminate\Database\Seeder;

class RuanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ruangans = [
            ['nama_ruangan' => 'Ruang Rapat 1', 'keterangan' => 'Ruang rapat utama', 'is_active' => true],
            ['nama_ruangan' => 'Ruang Rapat 2', 'keterangan' => 'Ruang rapat kedua', 'is_active' => true],
            ['nama_ruangan' => 'Ruang Rapat 3', 'keterangan' => 'Ruang rapat ketiga', 'is_active' => true],
            ['nama_ruangan' => 'Hall Utama', 'keterangan' => 'Hall utama untuk acara besar', 'is_active' => true],
            ['nama_ruangan' => 'Ruang VIP', 'keterangan' => 'Ruang VIP eksklusif', 'is_active' => true],
            ['nama_ruangan' => 'Teras Outdoor', 'keterangan' => 'Area outdoor', 'is_active' => true],
        ];

        foreach ($ruangans as $ruangan) {
            Ruangan::firstOrCreate(
                ['nama_ruangan' => $ruangan['nama_ruangan']],
                $ruangan
            );
        }
    }
}
