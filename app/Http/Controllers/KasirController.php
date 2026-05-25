<?php

namespace App\Http\Controllers;

use App\Models\AbsenRapat;
use App\Models\Produk;
use App\Models\DaftarHadir;
use App\Models\TransaksiHeader;
use App\Models\TransaksiDetail;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Str;

class KasirController extends Controller
{
	
    public function index(Request $request)
    {
        $produks = Produk::all();
        $transaksis = TransaksiHeader::with('details.produk')->latest()->get();

        return Inertia::render('Kasir/Index', [
            'produks' => $produks,
            'transaksis' => $transaksis,
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
            'id_absen_rapats' => 'nullable|exists:absen_rapats,id',
            'nip' => 'nullable|string',
            'nama' => 'required|string',
            'nomor_meja' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'metode_pembayaran' => 'required|in:cash,qris',
            'jumlah_bayar' => 'required|numeric|min:0',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:produks,id',
            'cart.*.jumlah' => 'required|integer|min:1',
            'cart.*.harga_jual' => 'required|numeric'
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

        $kembalian = $validated['metode_pembayaran'] === 'cash' ? ($validated['jumlah_bayar'] - $totalHarga) : 0;
        if ($kembalian < 0) $kembalian = 0;

        $noTransaksi = 'TRX-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $header = TransaksiHeader::create([
            'no_transaksi' => $noTransaksi,
            'id_absen_rapats' => $validated['id_absen_rapats'],
            'nip' => $validated['nip'] ?? null,
            'nama' => $validated['nama'],
            'nomor_meja' => $validated['nomor_meja'] ?? null,
            'tanggal_transaksi' => now(),
            'total_item' => $totalItem,
            'total_harga' => $totalHarga,
            'keterangan' => $validated['keterangan'] ?? null,
            'status' => 'pending',
            'metode_pembayaran' => $validated['metode_pembayaran'],
            'jumlah_bayar' => $validated['jumlah_bayar'],
            'kembalian' => $kembalian
        ]);

        foreach ($validated['cart'] as $item) {
            TransaksiDetail::create([
                'transaksi_header_id' => $header->id,
                'produk_id' => $item['id'],
                'jumlah' => $item['jumlah'],
                'harga_satuan' => $item['harga_jual'],
                'subtotal' => $item['jumlah'] * $item['harga_jual']
            ]);

            // Update stok produk
            $produk = Produk::find($item['id']);
            if ($produk) {
                $produk->stok -= $item['jumlah'];
                $produk->save();
            }
        }

        return redirect()->back()->with('success', 'Transaksi berhasil disimpan dengan No: ' . $noTransaksi);
    }

    public function destroy($id)
    {
        $transaksi = TransaksiHeader::findOrFail($id);
        
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

    public function pesanan()
    {
        // Get all transactions sorted by latest first
        $transaksis = TransaksiHeader::with('details.produk')->latest()->get();

        return Inertia::render('Kasir/Pesanan', [
            'transaksis' => $transaksis
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,diproses,selesai,batal'
        ]);

        $transaksi = TransaksiHeader::findOrFail($id);
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
}
