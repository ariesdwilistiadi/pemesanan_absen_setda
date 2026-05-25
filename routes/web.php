<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\DanaDkkController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\PinjamanBayarController;
use App\Http\Controllers\AbsenRapatController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\KasirController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rute Akses Publik
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});
 Route::controller(KasirController::class)->group(function () {
        Route::get('/kasir', 'index')->middleware('permission:view_kasir')->name('kasir.index');
        Route::get('/kasir/cari-peserta', 'cariPeserta')->middleware('permission:view_kasir')->name('kasir.cari-peserta');
        Route::post('/kasir', 'store')->middleware(['permission:create_kasir', 'throttle:10,1'])->name('kasir.store');
        Route::delete('/kasir/{id}', 'destroy')->middleware(['permission:delete_kasir', 'throttle:10,1'])->name('kasir.destroy');
        
        // Daftar Pesanan
        Route::get('/kasir/pesanan', 'pesanan')->middleware('permission:view_kasir')->name('kasir.pesanan');
        Route::patch('/kasir/pesanan/{id}/status', 'updateStatus')->middleware('permission:edit_kasir')->name('kasir.update-status');
   
   });
// Rute Publik untuk Absensi (Scan QR Code)
Route::get('/rapat-public/{id}/absen', [AbsenRapatController::class, 'publicShow'])->name('rapat.public.show');
Route::post('/rapat-public/{id}/absen', [AbsenRapatController::class, 'publicStore'])->name('rapat.public.store');

/*
|--------------------------------------------------------------------------
| Rute Dasbor & Manajemen Profil
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });



	Route::resource('rapat', AbsenRapatController::class)->only([
        'index', 'store', 'show', 'update'
    ]);
   // Route::post('/rapat/{id}/hadir', [AbsenRapatController::class, 'storeKehadiran'])->name('absen.hadir.store');
    Route::post('/rapat/{id}/hadir', [AbsenRapatController::class, 'storeKehadiran'])->name('rapat.hadir.store');
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
    | Manajemen Dana DKK
    |--------------------------------------------------------------------------
    */
    Route::controller(DanaDkkController::class)->group(function () {
        Route::get('/dana-dkks', 'index')->middleware('permission:view_dana_dkks')->name('dana-dkks.index');
        Route::post('/dana-dkks', 'store')->middleware(['permission:create_ কান্না_dkks', 'throttle:10,1'])->name('dana-dkks.store');
    });

    /*
    |--------------------------------------------------------------------------
    | Transaksi Kasir
    |--------------------------------------------------------------------------
    */
   

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
        // Tambahkan di dalam grup middleware auth di web.php
        Route::get('/pinjaman-bayar/{id}/cetak', [PinjamanBayarController::class, 'print'])->name('pinjaman-bayar.print');
        });

});

require __DIR__.'/auth.php';