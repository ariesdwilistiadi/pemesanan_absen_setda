<?php

namespace App\Http\Controllers;

use App\Models\AbsenRapat;
use App\Models\Produk;
use App\Models\Ruangan;
use App\Models\DaftarHadir;
use App\Models\TransaksiHeader;
use App\Models\TransaksiDetail;
use App\Support\RecordOwnership;
use App\Events\PesananBaruCreated;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

use App\Models\InstansiProfile;

class KasirController extends Controller
{
	
   public function index(Request $request)
    {
        $produks = Produk::all();
        $ruangans = Ruangan::where('is_active', true)->orderBy('nama_ruangan')->get();
        $transaksisQuery = TransaksiHeader::with('details.produk')->latest();

        // --- PERBAIKAN DI SINI ---
        // Cek apakah ada user yang sedang login
        if ($request->user()) {
            RecordOwnership::scopeOwned($transaksisQuery, $request->user());
        }
        // -------------------------

        $transaksis = $transaksisQuery->get();
        $instansi = InstansiProfile::first();

        return Inertia::render('Kasir/Index', [
            'produks' => $produks,
            'ruangans' => $ruangans,
            'transaksis' => $transaksis,
            'instansi' => $instansi,
            'initialPesertaId' => $request->query('id_peserta')
        ]);
    }

    public function cariPeserta(Request $request)
    {
        $nip = $request->query('nip');
        $id = $request->query('id');
        
        if ($id) {
            $peserta = DaftarHadir::find($id);
        } elseif ($nip) {
            $peserta = DaftarHadir::where('nip', $nip)->latest()->first();
        } else {
            return response()->json(['message' => 'NIP atau ID tidak boleh kosong'], 400);
        }

        if ($peserta) {
            return response()->json([
                'success' => true,
                'data' => $peserta
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Peserta tidak ditemukan pada absen rapat.'
        ], 404);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_absen_rapats' => ['nullable', Rule::exists(DaftarHadir::class, 'id')],
            'nip' => 'nullable|string',
            'nama' => 'required|string',
            'id_ruangan' => ['nullable', Rule::exists(Ruangan::class, 'id')],
            'nomor_meja' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'metode_pembayaran' => 'required|in:cash,qris',
            'jumlah_bayar' => 'required|numeric|min:0',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required',
            'cart.*.jumlah' => 'required|integer|min:1',
            'cart.*.harga_jual' => 'required|numeric|min:0', // min:0 untuk produk gratis
            'cart.*.sumber' => 'nullable|string'
        ]);

        $totalItem = 0;
        $totalHarga = 0;

        foreach ($validated['cart'] as $item) {
            $totalItem += $item['jumlah'];
            $totalHarga += ($item['jumlah'] * $item['harga_jual']);
        }

        // Validate enough payment for cash
        if ($validated['metode_pembayaran'] === 'cash' && $validated['jumlah_bayar'] < $totalHarga) {
            return back()->withErrors(['jumlah_bayar' => 'Jumlah bayar kurang dari total harga.']);
        }

        // Jika total = 0, tidak perlu print struk (produk gratis semua)
        $printId = null;

        $kembalian = $validated['metode_pembayaran'] === 'cash' ? ($validated['jumlah_bayar'] - $totalHarga) : 0;
        if ($kembalian < 0) $kembalian = 0;

        $noTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $header = TransaksiHeader::create([
            'no_transaksi' => $noTransaksi,
            'id_absen_rapats' => $validated['id_absen_rapats'],
            'nip' => $validated['nip'] ?? null,
            'nama' => $validated['nama'],
            'id_ruangan' => $validated['id_ruangan'] ?? null,
            'nomor_meja' => $validated['nomor_meja'] ?? null,
            'tanggal_transaksi' => now(),
            'total_item' => $totalItem,
            'total_harga' => $totalHarga,
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => 'pending',
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'jumlah_bayar' => $validated['jumlah_bayar'],
            'kembalian' => $kembalian
        ] + [
            'owner_user_id' => $request->user()?->id,
        ]);

        foreach ($validated['cart'] as $item) {
            // Untuk produk external, produk_id bisa null
            $produkId = ($item['sumber'] ?? 'lokal') === 'external' ? null : $item['id'];

            TransaksiDetail::create([
                'transaksi_header_id' => $header->id,
                'produk_id' => $produkId,
                'nama_produk_external' => ($item['sumber'] ?? 'lokal') === 'external' ? $item['nama_barang'] : null,
                'jumlah' => $item['jumlah'],
                'harga_satuan' => $item['harga_jual'],
                'subtotal' => $item['jumlah'] * $item['harga_jual']
            ]);

            // Update stok produk hanya untuk produk lokal
            if (($item['sumber'] ?? 'lokal') === 'lokal') {
                $produk = Produk::find($item['id']);
                if ($produk) {
                    $produk->stok -= $item['jumlah'];
                    $produk->save();
                }
            }
        }

        // Load relasi details untuk broadcast
        $header->load('details.produk');

        // Broadcast event untuk real-time
        broadcast(new PesananBaruCreated($header))->toOthers();

        // Set print_id hanya jika ada item dengan harga > 0
        if ($totalHarga > 0) {
            $printId = $header->id;
        }

        return redirect()->back()
            ->with('success', 'Transaksi berhasil disimpan dengan No: ' . $noTransaksi)
            ->with('print_id', $printId);
    }

    public function print($id)
    {
        $transaksi = TransaksiHeader::with('details.produk')->findOrFail($id);
        
        if (request()->user()) {
            RecordOwnership::abortUnlessOwned($transaksi, request()->user());
        }
        
        return view('print.kasir', compact('transaksi'));
    }

    public function destroy($id)
    {
        $transaksi = TransaksiHeader::findOrFail($id);
        
        if (request()->user()) {
            RecordOwnership::abortUnlessOwned($transaksi, request()->user());
        }
        
        // Kembalikan stok
        foreach ($transaksi->details as $detail) {
            $produk = Produk::find($detail->produk_id);
            if ($produk) {
                $produk->stok += $detail->jumlah;
                $produk->save();
            }
        }

        $transaksi->delete();

        return redirect()->back()->with('success', 'Transaksi berhasil dihapus');
    }

    public function pesanan(Request $request)
    {
        // Get all transactions sorted by latest first
        $transaksisQuery = TransaksiHeader::with('details.produk')->latest();

        if ($request->user()) {
            RecordOwnership::scopeOwned($transaksisQuery, $request->user());
        }
        $transaksis = $transaksisQuery->get();

        return Inertia::render('Kasir/Pesanan', [
            'transaksis' => $transaksis
        ]);
    }

    /**
     * SSE endpoint untuk real-time pesanan
     */
    public function ssePesanan()
    {
        $lastId = request()->query('last_id', 0);

        $response = new \Illuminate\Http\Response(function () use ($lastId) {
            $transaksisQuery = TransaksiHeader::with('details.produk')
                ->where('id', '>', $lastId)
                ->whereIn('status', ['pending', 'diproses']);

            if (auth()->check()) {
                RecordOwnership::scopeOwned($transaksisQuery, auth()->user());
            }

            $transaksis = $transaksisQuery->latest()->get();

            echo "data: " . json_encode([
                'type' => 'initial',
                'transaksis' => $transaksis,
                'count' => $transaksis->count()
            ]) . "\n\n";
            ob_flush();
            flush();
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('X-Accel-Buffering', 'no');

        return $response;
    }

    /**
     * Polling endpoint untuk cek pesanan baru (fallback)
     */
    public function cekPesananBaru(Request $request)
    {
        $lastId = $request->query('last_id', 0);

        $transaksisQuery = TransaksiHeader::with('details.produk')
            ->where('id', '>', $lastId)
            ->whereIn('status', ['pending', 'diproses']);

        if ($request->user()) {
            RecordOwnership::scopeOwned($transaksisQuery, $request->user());
        }

        $transaksis = $transaksisQuery->latest()->get();

        return response()->json([
            'success' => true,
            'transaksis' => $transaksis,
            'count' => $transaksis->count()
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai,batal'
        ]);

        $transaksi = TransaksiHeader::findOrFail($id);
        
        if ($request->user()) {
            RecordOwnership::abortUnlessOwned($transaksi, $request->user());
        }
        $transaksi->status = $request->status;
        
        // If status becomes batal, return stock
        if ($request->status === 'batal') {
            foreach ($transaksi->details as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    $produk->stok += $detail->jumlah;
                    $produk->save();
                }
            }
        }

        $transaksi->save();

        return redirect()->back()->with('success', 'Status pesanan berhasil diperbarui');
    }

    public function laporan(Request $request)
    {
        $query = TransaksiHeader::with('details.produk')->where('status', 'selesai')->latest();
        
        if ($request->user()) {
            RecordOwnership::scopeOwned($query, $request->user());
        }

        // Simple filtering by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal_transaksi', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } else {
            // Default to today
            $query->whereDate('tanggal_transaksi', today());
        }

        $transaksis = $query->get();

        $totalPendapatan = $transaksis->sum('total_harga');

        return Inertia::render('Kasir/Laporan', [
            'transaksis' => $transaksis,
            'totalPendapatan' => $totalPendapatan,
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }

    public function laporanKeuntungan(Request $request)
    {
        $query = TransaksiHeader::with('details.produk')->where('status', 'selesai')->latest();
        
        if ($request->user()) {
            RecordOwnership::scopeOwned($query, $request->user());
        }

        // Simple filtering by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('tanggal_transaksi', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } else {
            // Default to today
            $query->whereDate('tanggal_transaksi', today());
        }

        $transaksis = $query->get();

        $totalPendapatan = 0;
        $totalModal = 0;

        $transaksis->transform(function ($trx) use (&$totalPendapatan, &$totalModal) {
            $modal = 0;
            $pendapatan = $trx->total_harga;
            
            foreach ($trx->details as $detail) {
                // If product is deleted, we might not know the exact modal, assume 0 or handle it
                $hargaBeli = $detail->produk ? $detail->produk->harga_beli : 0;
                $modal += $hargaBeli * $detail->jumlah;
            }

            $trx->modal = $modal;
            $trx->keuntungan = $pendapatan - $modal;
            
            $totalPendapatan += $pendapatan;
            $totalModal += $modal;

            return $trx;
        });

        $totalKeuntungan = $totalPendapatan - $totalModal;

        return Inertia::render('Kasir/LaporanKeuntungan', [
            'transaksis' => $transaksis,
            'totalPendapatan' => $totalPendapatan,
            'totalModal' => $totalModal,
            'totalKeuntungan' => $totalKeuntungan,
            'filters' => $request->only(['start_date', 'end_date'])
        ]);
    }
}
 
