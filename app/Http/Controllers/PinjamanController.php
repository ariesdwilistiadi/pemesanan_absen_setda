<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class PinjamanController extends Controller
{
    /**
     * Menampilkan halaman utama dan daftar transaksi pinjaman
     */
    public function index()
    {
        /**
         * PERBAIKAN UTAMA:
         * Memuat relasi 'anggota' DAN 'angsuran' (riwayat pembayaran).
         * Dengan memuat 'angsuran', data history akan langsung terbawa ke frontend
         * sehingga saat baris diklik, data sudah siap ditampilkan tanpa reload.
         */
        $pinjaman = Pinjaman::with(['anggota', 'angsuran'])
            ->latest('tanggal_create')
            ->get();
        
        // Memuat daftar anggota untuk dropdown form
        $anggotas = Anggota::select(['id_anggota', 'no_anggota', 'nama'])
            ->orderBy('nama')
            ->get();

        return Inertia::render('Pinjaman/Index', [
            'pinjaman' => $pinjaman,
            'anggotas' => $anggotas,
        ]);
    }

    /**
     * Menyimpan data pinjaman baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_anggota'        => 'required|exists:anggota,id_anggota',
            'jumlah_pinjaman'   => 'nullable|integer|min:0',
            'jasa'              => 'required|integer|min:0',
            'jumlah_angsuran'   => 'required|integer|min:0',
            'jangka_waktu'      => 'required|date',
            'nama'              => 'nullable|string|max:255', 
            'tgl_pinjaman'      => 'required|date',
            'id_jenis_pinjaman' => 'required|integer|min:0',
            'kategori'          => 'required|integer|min:0',
        ]);

        $validated['tanggal_create'] = now();

        // Pencegahan kolom NOT NULL yang diisi null
        $validated['nama'] = $validated['nama'] ?? '';

        // Ambil petugas dan batasi 25 karakter sesuai database
        $petugas = Auth::user()->username ?? Auth::user()->name ?? 'admin';
        $validated['username'] = Str::limit($petugas, 25, '');

        Pinjaman::create($validated);

        return redirect()->back()->with('success', 'Data pinjaman berhasil disimpan.');
    }
}