<?php

use App\Http\Controllers\AbsenRapatController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\DanaDkkController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProdukEksternalController;
use App\Http\Controllers\PeminjamanBayarController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RuanganController;
use App\Http\Controllers\UserController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rute Akses Publik
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->away('http://localhost:8000/login');
});

Route::controller(KasirController::class)->group(function () {
   // Route::get('/kasir', 'index')->middleware('permission:view_kasir')->name('kasir.index');
   Route::get('/kasir', 'index')->name('kasir.index');
   Route::get('/kasir/cari-peserta', 'cariPeserta')->name('kasir.cari-peserta');
    Route::post('/kasir', 'store')->middleware(['throttle:10,1'])->name('kasir.store');
    Route::delete('/kasir/{id}', 'destroy')->middleware(['permission:delete_kasir', 'throttle:10,1'])->name('kasir.destroy');
    Route::get('/kasir/{id}/cetak', 'print')->name('kasir.print');

    Route::get('/kasir/pesanan', 'pesanan')->middleware('permission:view_kasir')->name('kasir.pesanan');
    Route::patch('/kasir/pesanan/{id}/status', 'updateStatus')->middleware('permission:edit_kasir')->name('kasir.update-status');
    Route::get('/kasir/laporan', 'laporan')->middleware('permission:view_kasir')->name('kasir.laporan');
    Route::get('/kasir/laporan-keuntungan', 'laporanKeuntungan')->middleware('permission:view_kasir')->name('kasir.laporan-keuntungan');

    // Real-time endpoints
    Route::get('/kasir/sse-pesanan', 'ssePesanan')->middleware('permission:view_kasir')->name('kasir.sse-pesanan');
    Route::get('/kasir/cek-pesanan', 'cekPesananBaru')->middleware('permission:view_kasir')->name('kasir.cek-pesanan');
});

/*
|--------------------------------------------------------------------------
| Produk Eksternal API
|--------------------------------------------------------------------------
*/
Route::get('/api/produk-eksternal', [ProdukEksternalController::class, 'index'])->name('produk-eksternal.index');
Route::post('/api/produk-eksternal/refresh', [ProdukEksternalController::class, 'refreshToken'])->name('produk-eksternal.refresh');
Route::get('/api/produk-eksternal/test', [ProdukEksternalController::class, 'testConnection'])->name('produk-eksternal.test');
Route::get('/api/produk-eksternal/penjualan', [ProdukEksternalController::class, 'penjualan'])->name('produk-eksternal.penjualan');

/*
|--------------------------------------------------------------------------
| Penjualan External Page
|--------------------------------------------------------------------------
*/
Route::get('/penjualan-external', [PenjualanController::class, 'external'])->middleware('permission:view_kasir')->name('penjualan.external');

Route::get('/rapat-public', [AbsenRapatController::class, 'publicList'])->name('rapat.public.list');
Route::get('/rapat-public/{id}/absen', [AbsenRapatController::class, 'publicShow'])->name('rapat.public.show');
Route::post('/rapat-public/{id}/absen', [AbsenRapatController::class, 'publicStore'])->name('rapat.public.store');
Route::get('/api/pegawai/{nip}', [AbsenRapatController::class, 'getPegawaiSimpeg'])->name('api.pegawai');

/*
|--------------------------------------------------------------------------
| Rute Dasbor & Manajemen Profil
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'trusted.origin', 'session.timeout'])->group(function () {
  Route::redirect('/dashboard', '/rapat')->name('dashboard');
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::get('/rapat', [AbsenRapatController::class, 'index'])
        ->middleware('permission:view_rapat')
        ->name('rapat.index');
    Route::post('/rapat', [AbsenRapatController::class, 'store'])
        ->middleware(['permission:create_rapat', 'throttle:10,1'])
        ->name('rapat.store');
    Route::get('/rapat/{id}', [AbsenRapatController::class, 'show'])
        ->middleware('permission:view_rapat')
        ->name('rapat.show');
    Route::put('/rapat/{id}', [AbsenRapatController::class, 'update'])
        ->middleware(['permission:edit_rapat', 'throttle:10,1'])
        ->name('rapat.update');
    Route::post('/rapat/{id}/hadir', [AbsenRapatController::class, 'storeKehadiran'])
        ->middleware(['permission:create_rapat', 'throttle:10,1'])
        ->name('rapat.hadir.store');
    Route::get('/rapat/{id}/cetak', [AbsenRapatController::class, 'print'])
        ->middleware('permission:view_rapat')
        ->name('rapat.print');

    /*
    |--------------------------------------------------------------------------
    | Manajemen Ruangan
    |--------------------------------------------------------------------------
    */
    Route::controller(RuanganController::class)->group(function () {
        Route::get('/ruangans', 'index')->middleware('permission:view_ruangans')->name('ruangans.index');
        Route::post('/ruangans', 'store')->middleware(['permission:create_ruangans', 'throttle:10,1'])->name('ruangans.store');
        Route::put('/ruangans/{id}', 'update')->middleware(['permission:edit_ruangans', 'throttle:10,1'])->name('ruangans.update');
        Route::delete('/ruangans/{id}', 'destroy')->middleware('permission:delete_ruangans')->name('ruangans.destroy');
        Route::post('/ruangans/{id}/toggle', 'toggleActive')->middleware(['permission:edit_ruangans', 'throttle:10,1'])->name('ruangans.toggle');
    });

    /*
    |--------------------------------------------------------------------------
    | Manajemen Menu
    |--------------------------------------------------------------------------
    */
    Route::controller(MenuController::class)->group(function () {
        Route::get('/menus', 'index')->middleware('permission:view_menus')->name('menus.index');
        Route::post('/menus', 'store')->middleware(['permission:create_menus', 'throttle:10,1'])->name('menus.store');
        Route::put('/menus/reorder', 'reorder')->middleware(['permission:reorder_menus', 'throttle:10,1'])->name('menus.reorder');
        Route::put('/menus/{menu}', 'update')->middleware(['permission:edit_menus', 'throttle:10,1'])->name('menus.update');
        Route::delete('/menus/{menu}', 'destroy')->middleware('permission:delete_menus')->name('menus.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Manajemen Pengguna & Hak Akses
    |--------------------------------------------------------------------------
    */
    Route::controller(UserController::class)->group(function () {
        Route::get('/users', 'index')->middleware('permission:view_users')->name('users.index');
        Route::post('/users', 'store')->middleware(['permission:create_users', 'throttle:10,1'])->name('users.store');
        Route::put('/users/{user}', 'update')->middleware(['permission:edit_users', 'throttle:10,1'])->name('users.update');
        Route::delete('/users/{user}', 'destroy')->middleware(['permission:delete_users', 'throttle:10,1'])->name('users.destroy');
    });

    Route::controller(PermissionController::class)->group(function () {
        Route::get('/permissions', 'index')->middleware('permission:manage_user_permissions')->name('permissions.index');
        Route::put('/permissions/users/{user}', 'updateUserPermissions')
            ->middleware(['permission:manage_user_permissions', 'throttle:10,1'])
            ->name('permissions.update-user');
    });

    /*
    |--------------------------------------------------------------------------
    | Manajemen Anggota
    |--------------------------------------------------------------------------
    */
    Route::controller(AnggotaController::class)->group(function () {
        Route::get('/anggotas', 'index')->middleware('permission:view_anggotas')->name('anggotas.index');
        Route::post('/anggotas', 'store')->middleware(['permission:create_anggotas', 'throttle:10,1'])->name('anggotas.store');
        Route::put('/anggotas/{anggota}', 'update')->middleware(['permission:edit_anggotas', 'throttle:10,1'])->name('anggotas.update');
        Route::delete('/anggotas/{anggota}', 'destroy')->middleware(['permission:delete_anggotas', 'throttle:10,1'])->name('anggotas.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Manajemen Produk
    |--------------------------------------------------------------------------
    */
    Route::controller(ProdukController::class)->group(function () {
        Route::get('/produks', 'index')->middleware('permission:view_produks')->name('produks.index');
        Route::post('/produks', 'store')->middleware(['permission:create_produks', 'throttle:10,1'])->name('produks.store');
        Route::post('/produks/{produk}', 'update')->middleware(['permission:edit_produks', 'throttle:10,1'])->name('produks.update');
        Route::delete('/produks/{produk}', 'destroy')->middleware(['permission:delete_produks', 'throttle:10,1'])->name('produks.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Manajemen Pembelian
    |--------------------------------------------------------------------------
    */
    Route::controller(\App\Http\Controllers\PembelianController::class)->group(function () {
        Route::get('/pembelian', 'index')->middleware('permission:view_pembelian')->name('pembelian.index');
        Route::get('/pembelian/create', 'create')->middleware('permission:create_pembelian')->name('pembelian.create');
        Route::post('/pembelian', 'store')->middleware(['permission:create_pembelian', 'throttle:10,1'])->name('pembelian.store');
        Route::get('/pembelian/{id}', 'show')->middleware('permission:view_pembelian')->name('pembelian.show');
        Route::delete('/pembelian/{id}', 'destroy')->middleware(['permission:delete_pembelian', 'throttle:10,1'])->name('pembelian.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Stok Opname
    |--------------------------------------------------------------------------
    */
    Route::controller(\App\Http\Controllers\StokOpnameController::class)->group(function () {
        Route::get('/stok-opname', 'index')->middleware('permission:view_stok_opname')->name('stok-opname.index');
        Route::get('/stok-opname/create', 'create')->middleware('permission:create_stok_opname')->name('stok-opname.create');
        Route::post('/stok-opname', 'store')->middleware(['permission:create_stok_opname', 'throttle:10,1'])->name('stok-opname.store');
        Route::get('/stok-opname/{id}', 'show')->middleware('permission:view_stok_opname')->name('stok-opname.show');
        Route::delete('/stok-opname/{id}', 'destroy')->middleware(['permission:delete_stok_opname', 'throttle:10,1'])->name('stok-opname.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Retur Barang
    |--------------------------------------------------------------------------
    */
    Route::controller(\App\Http\Controllers\ReturController::class)->group(function () {
        Route::get('/retur', 'index')->middleware('permission:view_retur')->name('retur.index');
        Route::get('/retur/create', 'create')->middleware('permission:create_retur')->name('retur.create');
        Route::post('/retur', 'store')->middleware(['permission:create_retur', 'throttle:10,1'])->name('retur.store');
        Route::get('/retur/{id}', 'show')->middleware('permission:view_retur')->name('retur.show');
        Route::delete('/retur/{id}', 'destroy')->middleware(['permission:delete_retur', 'throttle:10,1'])->name('retur.destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Profil Instansi
    |--------------------------------------------------------------------------
    */
    Route::controller(\App\Http\Controllers\InstansiProfileController::class)->group(function () {
        Route::get('/instansi', 'edit')->middleware('permission:manage_instansi')->name('instansi.edit');
        Route::post('/instansi', 'update')->middleware(['permission:manage_instansi', 'throttle:10,1'])->name('instansi.update');
    });
    Route::controller(DanaDkkController::class)->group(function () {
        Route::get('/dana-dkks', 'index')->middleware('permission:view_dana_dkks')->name('dana-dkks.index');
        Route::post('/dana-dkks', 'store')->middleware(['permission:create_dana_dkks', 'throttle:10,1'])->name('dana-dkks.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Transaksi Pinjaman & Angsuran
    |--------------------------------------------------------------------------
    */
    Route::controller(PinjamanController::class)->group(function () {
        Route::get('/pinjaman', 'index')->middleware('permission:view_pinjaman')->name('pinjaman.index');
        Route::post('/pinjaman', 'store')->middleware(['permission:create_pinjaman', 'throttle:10,1'])->name('pinjaman.store');
    });

    Route::controller(PinjamanBayarController::class)->group(function () {
        Route::get('/pinjaman-bayar', 'index')->middleware('permission:view_pinjaman_bayar')->name('pinjaman-bayar.index');
        Route::post('/pinjaman-bayar', 'store')->middleware(['permission:create_pinjaman_bayar', 'throttle:10,1'])->name('pinjaman-bayar.store');
        Route::get('/pinjaman-bayar/{id}/cetak', [PinjamanBayarController::class, 'print'])
            ->middleware('permission:view_pinjaman_bayar')
            ->name('pinjaman-bayar.print');
    });
});

require __DIR__.'/auth.php';
