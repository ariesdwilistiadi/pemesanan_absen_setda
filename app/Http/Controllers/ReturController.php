<?php

namespace App\Http\Controllers;

use App\Models\ReturHeader;
use App\Models\ReturDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class ReturController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturHeader::with('details.produk')->latest();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('pihak_terkait', 'like', "%{$search}%");
        }

        $returs = $query->paginate(10)->withQueryString();

        return Inertia::render('Retur/Index', [
            'returs' => $returs,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        $produks = Produk::orderBy('nama_barang', 'asc')->get();
        
        $date = now()->format('Ymd');
        $lastRetur = ReturHeader::whereDate('created_at', today())->count();
        $nomorTransaksi = 'RET-' . $date . '-' . str_pad($lastRetur + 1, 4, '0', STR_PAD_LEFT);

        return Inertia::render('Retur/Create', [
            'produks' => $produks,
            'nomorTransaksi' => $nomorTransaksi
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_transaksi' => 'required|unique:retur_headers',
            'tanggal' => 'required|date',
            'jenis_retur' => 'required|in:Masuk,Keluar',
            'pihak_terkait' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:produks,id',
            'items.*.kuantitas' => 'required|integer|min:1',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $header = ReturHeader::create([
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tanggal' => $validated['tanggal'],
                'jenis_retur' => $validated['jenis_retur'],
                'pihak_terkait' => $validated['pihak_terkait'],
                'keterangan' => $validated['keterangan'],
            ]);

            foreach ($validated['items'] as $item) {
                $produk = Produk::lockForUpdate()->find($item['id']);
                
                if ($produk) {
                    ReturDetail::create([
                        'retur_header_id' => $header->id,
                        'produk_id' => $produk->id,
                        'kuantitas' => $item['kuantitas'],
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);

                    // Adjust Stock
                    if ($validated['jenis_retur'] === 'Masuk') {
                        // Retur dari Pelanggan -> Barang kembali ke toko -> Stok bertambah
                        $produk->stok += $item['kuantitas'];
                    } else {
                        // Retur Keluar (ke Supplier) -> Barang keluar toko -> Stok berkurang
                        if ($produk->stok < $item['kuantitas']) {
                            throw new \Exception("Stok tidak mencukupi untuk diretur (Produk: {$produk->nama_barang})");
                        }
                        $produk->stok -= $item['kuantitas'];
                    }
                    $produk->save();
                }
            }

            DB::commit();

            return redirect()->route('retur.index')->with('success', 'Data Retur berhasil disimpan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan retur: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $retur = ReturHeader::with('details.produk')->findOrFail($id);

        return Inertia::render('Retur/Show', [
            'retur' => $retur
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $retur = ReturHeader::findOrFail($id);
            
            // Revert stock changes
            foreach ($retur->details as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    if ($retur->jenis_retur === 'Masuk') {
                        // Revert: Barang yang tadinya masuk, sekarang dikurangi
                        $produk->stok -= $detail->kuantitas;
                    } else {
                        // Revert: Barang yang tadinya keluar, sekarang ditambah
                        $produk->stok += $detail->kuantitas;
                    }
                    $produk->save();
                }
            }

            $retur->delete();
            DB::commit();
            
            return redirect()->back()->with('success', 'Data Retur berhasil dihapus dan stok dikembalikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus retur: ' . $e->getMessage()]);
        }
    }
}
