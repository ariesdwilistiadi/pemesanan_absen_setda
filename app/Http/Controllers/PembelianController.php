<?php

namespace App\Http\Controllers;

use App\Models\PembelianHeader;
use App\Models\PembelianDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PembelianController extends Controller
{
    public function index(Request $request)
    {
        $query = PembelianHeader::with('details')->latest();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('nama_supplier', 'like', "%{$search}%");
        }

        $pembelians = $query->paginate(10)->withQueryString();

        return Inertia::render('Pembelian/Index', [
            'pembelians' => $pembelians,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        $produks = Produk::all();
        
        // Generate a new transaction number (e.g. PO-YYYYMMDD-XXXX)
        $date = now()->format('Ymd');
        $lastPembelian = PembelianHeader::whereDate('created_at', today())->count();
        $nomorTransaksi = 'PO-' . $date . '-' . str_pad($lastPembelian + 1, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Pembelian/Create', [
            'produks' => $produks,
            'nomorTransaksi' => $nomorTransaksi
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_transaksi' => 'required|unique:pembelian_headers',
            'tanggal_pembelian' => 'required|date',
            'nama_supplier' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:produks,id',
            'cart.*.jumlah' => 'required|integer|min:1',
            'cart.*.harga_beli' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $totalHarga = 0;
            foreach ($validated['cart'] as $item) {
                $totalHarga += $item['jumlah'] * $item['harga_beli'];
            }

            $header = PembelianHeader::create([
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tanggal_pembelian' => $validated['tanggal_pembelian'],
                'nama_supplier' => $validated['nama_supplier'],
                'total_harga' => $totalHarga,
                'keterangan' => $validated['keterangan'],
            ]);

            foreach ($validated['cart'] as $item) {
                PembelianDetail::create([
                    'pembelian_header_id' => $header->id,
                    'barang_id' => $item['id'],
                    'kuantitas' => $item['jumlah'],
                    'harga_satuan' => $item['harga_beli'],
                    'subtotal' => $item['jumlah'] * $item['harga_beli']
                ]);

                // Update stok dan harga beli produk (optional: bisa update harga rata-rata atau harga beli terakhir)
                $produk = Produk::find($item['id']);
                if ($produk) {
                    $produk->stok += $item['jumlah'];
                    $produk->harga_beli = $item['harga_beli']; // Update ke harga beli terakhir
                    $produk->save();
                }
            }

            DB::commit();

            return redirect()->route('pembelian.index')->with('success', 'Transaksi pembelian berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan transaksi: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $pembelian = PembelianHeader::with('details.produk')->findOrFail($id);

        return Inertia::render('Pembelian/Show', [
            'pembelian' => $pembelian
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $pembelian = PembelianHeader::findOrFail($id);
            
            // Kurangi stok barang
            foreach ($pembelian->details as $detail) {
                $produk = Produk::find($detail->barang_id);
                if ($produk) {
                    $produk->stok -= $detail->kuantitas;
                    $produk->save();
                }
            }

            $pembelian->delete(); // details should cascade
            DB::commit();
            
            return redirect()->back()->with('success', 'Transaksi pembelian berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus transaksi: ' . $e->getMessage()]);
        }
    }
}
