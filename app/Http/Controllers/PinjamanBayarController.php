<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Pinjaman;
use App\Models\PinjamanBayar;
use App\Support\RecordOwnership;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PinjamanBayarController extends Controller
{
   public function index(Request $request)
	{
		// 1. Tangkap Parameter dari Klien (Kombinasi Tanggal atau Periode Bulanan)
		$tanggal = $request->input('date'); // Format: YYYY-MM-DD
		$bulan   = $request->input('month', now()->month);
		$tahun   = $request->input('year', now()->year);

		// 2. Kueri Pembayaran yang SUDAH MASUK (Untuk Tabel Riwayat)
		$pembayaranQuery = PinjamanBayar::with(['anggota', 'pinjaman'])->latest('tanggal_create');
        RecordOwnership::scopeOwned($pembayaranQuery, $request->user(), 'username');

		if (!empty($tanggal)) {
			// Mode Harian: Filter spesifik berdasarkan tanggal_bayar
			$pembayaranQuery->whereDate('tanggal_bayar', $tanggal);
		} else {
			// Mode Bulanan: Filter berdasarkan bulan & tahun
			$pembayaranQuery->whereMonth('tanggal_bayar', $bulan)
							->whereYear('tanggal_bayar', $tahun);
		}
		$pembayaran = $pembayaranQuery->get();

		// 3. Kueri Pinjaman Aktif untuk Dropdown "Pilih Rekening" di Modal (WAJIB ADA)
		// Eager load 'angsuran' agar perhitungan Accessor sisa_pinjaman efisien
		$pinjamanAktifQuery = Pinjaman::with(['anggota', 'angsuran']);
        RecordOwnership::scopeOwned($pinjamanAktifQuery, $request->user(), 'username');
		$pinjamanAktif = $pinjamanAktifQuery->get()
			->filter(function ($item) {
				// Hanya menyertakan pinjaman yang saldonya masih ada (belum lunas)
				return $item->sisa_pinjaman > 0;
			})
			->values();

		// 4. Kueri Tunggakan (Siapa yang belum bayar di PERIODE terpilih)
		$belumBayarQuery = Pinjaman::with(['anggota', 'angsuran'])
			->whereDoesntHave('angsuran', function ($query) use ($tanggal, $bulan, $tahun) {
				if (!empty($tanggal)) {
					// Cari yang tidak bayar di tanggal spesifik ini
					$query->whereDate('tanggal_bayar', $tanggal);
				} else {
					// Cari yang belum ada setoran sama sekali di bulan/tahun ini
					$query->whereMonth('tanggal_bayar', $bulan)
						  ->whereYear('tanggal_bayar', $tahun);
				}
			});
        RecordOwnership::scopeOwned($belumBayarQuery, $request->user(), 'username');
		$belumBayar = $belumBayarQuery->get()
			->filter(function ($item) {
				// Memastikan hanya memuat pinjaman yang memang masih punya hutang
				return $item->sisa_pinjaman > 0;
			})
			->values();

		// 5. Daftar Anggota untuk Keperluan Dropdown lainnya
		$anggotas = Anggota::select(['id_anggota', 'no_anggota', 'nama'])
			->orderBy('nama')
			->get();

		// Kirim paket data lengkap ke antarmuka Inertia
		return Inertia::render('PinjamanBayar/Index', [
			'pembayaran' => $pembayaran,
			'pinjaman'   => $pinjamanAktif, // Variabel ini yang membuat dropdown terisi
			'belumBayar' => $belumBayar,
			'filters'    => [
				'date'  => $tanggal ?? '',
				'month' => (int)$bulan,
				'year'  => (int)$tahun,
			],
			'anggotas'   => $anggotas,
		]);
	}


    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'id_pinjaman' => 'required|exists:pinjaman,id_pinjaman',
            'bayar' => 'required|integer|min:0',
            'bunga' => 'required|integer|min:0',
            'denda' => 'required|integer|min:0',
            'tanggal_bayar' => 'required|date',
            'username' => 'required|string|max:25',
        ]);

        $pinjaman = Pinjaman::findOrFail($validated['id_pinjaman']);
        RecordOwnership::abortUnlessOwned($pinjaman, $request->user(), 'username');

        $validated['tanggal_create'] = now();
        $validated['owner_user_id'] = $request->user()->id;
        PinjamanBayar::create($validated);

        return redirect()->back()->with('success', 'Pembayaran pinjaman berhasil disimpan.');
    }
	
	public function print(Request $request, $id)
	{
		// Ambil data pembayaran spesifik dengan relasi lengkap
		$data = PinjamanBayar::with(['anggota', 'pinjaman'])->findOrFail($id);
        RecordOwnership::abortUnlessOwned($data, $request->user(), 'username');

		// Kita gunakan Blade biasa (bukan Inertia) khusus untuk fungsi cetak
		return view('print.kwitansi_pinjaman', compact('data'));
	}
}
