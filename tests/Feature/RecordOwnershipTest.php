<?php

namespace Tests\Feature;

use App\Models\Agama;
use App\Models\Anggota;
use App\Models\Pinjaman;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RecordOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_only_sees_owned_produk_records(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Permission::findOrCreate('view_produks', 'web');
        $user->givePermissionTo('view_produks');

        Produk::create([
            'kode_barang' => 'PRD-001',
            'nama_barang' => 'Produk Saya',
            'kategori' => 'ATK',
            'harga_beli' => 1000,
            'harga_jual' => 1500,
            'stok' => 5,
            'satuan' => 'pcs',
            'owner_user_id' => $user->id,
        ]);

        Produk::create([
            'kode_barang' => 'PRD-002',
            'nama_barang' => 'Produk User Lain',
            'kategori' => 'ATK',
            'harga_beli' => 2000,
            'harga_jual' => 2500,
            'stok' => 5,
            'satuan' => 'pcs',
            'owner_user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->withHeaders($this->statefulHeaders())
            ->get(route('produks.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Produk/Index')
                ->where('produks.0.nama_barang', 'Produk Saya')
                ->missing('produks.1')
            );
    }

    public function test_user_cannot_update_produk_owned_by_other_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Permission::findOrCreate('edit_produks', 'web');
        $user->givePermissionTo('edit_produks');

        $produk = Produk::create([
            'kode_barang' => 'PRD-003',
            'nama_barang' => 'Produk Terkunci',
            'kategori' => 'ATK',
            'harga_beli' => 1000,
            'harga_jual' => 1500,
            'stok' => 5,
            'satuan' => 'pcs',
            'owner_user_id' => $otherUser->id,
        ]);

        $this->actingAs($user)
            ->withHeaders($this->statefulHeaders())
            ->post(route('produks.update', $produk), [
                'kode_barang' => 'PRD-003',
                'nama_barang' => 'Produk Terkunci',
                'kategori' => 'ATK',
                'harga_beli' => 1000,
                'harga_jual' => 1500,
                'stok' => 5,
                'satuan' => 'pcs',
            ])
            ->assertForbidden();
    }

    public function test_legacy_pinjaman_record_is_visible_when_username_matches_current_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Petugas A',
        ]);

        Permission::findOrCreate('view_pinjaman', 'web');
        $user->givePermissionTo('view_pinjaman');

        $agama = Agama::create(['nama' => 'Islam']);
        $anggota = Anggota::create([
            'no_anggota' => 'A-001',
            'nama' => 'Anggota Satu',
            'no_identitas' => '123456',
            'tempat_lahir' => 'Jakarta',
            'tgl_lahir' => '1990-01-01',
            'alamat' => 'Jl. Test',
            'jenis_kelamin' => 'laki-laki',
            'no_telp' => '08123456789',
            'agama_id' => $agama->id,
            'pekerjaan' => 'Tester',
            'tgl_masuk' => '2025-01-01',
            'simpanan_pokok' => 100000,
            'simpanan_wajib' => 50000,
            'status' => 1,
        ]);

        Pinjaman::create([
            'id_anggota' => $anggota->id,
            'jumlah_pinjaman' => 1000000,
            'jasa' => 100000,
            'jumlah_angsuran' => 10,
            'jangka_waktu' => '2026-12-31',
            'nama' => 'Pinjaman Lama',
            'tgl_pinjaman' => '2026-05-01',
            'tanggal_create' => now(),
            'username' => 'Petugas A',
            'id_jenis_pinjaman' => 1,
            'kategori' => 1,
        ]);

        Pinjaman::create([
            'id_anggota' => $anggota->id,
            'jumlah_pinjaman' => 2000000,
            'jasa' => 200000,
            'jumlah_angsuran' => 10,
            'jangka_waktu' => '2026-12-31',
            'nama' => 'Pinjaman Orang Lain',
            'tgl_pinjaman' => '2026-05-02',
            'tanggal_create' => now(),
            'username' => 'Petugas B',
            'id_jenis_pinjaman' => 1,
            'kategori' => 1,
        ]);

        $this->actingAs($user)
            ->withHeaders($this->statefulHeaders())
            ->get(route('pinjaman.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Pinjaman/Index')
                ->where('pinjaman.0.nama', 'Pinjaman Lama')
                ->missing('pinjaman.1')
            );
    }
}
