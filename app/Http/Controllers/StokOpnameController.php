<?php

namespace App\Http\Controllers;

use App\Models\StokOpnameHeader;
use App\Models\StokOpnameDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class StokOpnameController extends Controller
{
    public function index(Request $request)
    {
        $query = StokOpnameHeader::with('details')->latest();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nomor_transaksi', 'like', "%{$search}%")
                  ->orWhere('penanggung_jawab', 'like', "%{$search}%");
        }

        $stokOpnames = $query->paginate(10)->withQueryString();

        return Inertia::render('StokOpname/Index', [
            'stokOpnames' => $stokOpnames,
            'filters' => $request->only('search')
        ]);
    }

    public function create()
    {
        $produks = Produk::orderBy('nama_barang', 'asc')->get();
        
        // Generate a new transaction number (e.g. SO-YYYYMMDD-XXXX)
        $date = now()->format('Ymd');
        $lastOpname = StokOpnameHeader::whereDate('created_at', today())->count();
        $nomorTransaksi = 'SO-' . $date . '-' . str_pad($lastOpname + 1, 4, '0', STR_PAD_LEFT);

        return Inertia::render('StokOpname/Create', [
            'produks' => $produks,
            'nomorTransaksi' => $nomorTransaksi
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_transaksi' => 'required|unique:stok_opname_headers',
            'tanggal' => 'required|date',
            'penanggung_jawab' => 'required|string|max:255',
            'keterangan' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:produks,id',
            'items.*.stok_fisik' => 'required|integer|min:0',
            'items.*.keterangan' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $header = StokOpnameHeader::create([
                'nomor_transaksi' => $validated['nomor_transaksi'],
                'tanggal' => $validated['tanggal'],
                'penanggung_jawab' => $validated['penanggung_jawab'],
                'keterangan' => $validated['keterangan'],
            ]);

            foreach ($validated['items'] as $item) {
                $produk = Produk::lockForUpdate()->find($item['id']);
                
                if ($produk) {
                    $stokSistem = $produk->stok;
                    $stokFisik = $item['stok_fisik'];
                    $selisih = $stokFisik - $stokSistem;

                    StokOpnameDetail::create([
                        'stok_opname_header_id' => $header->id,
                        'produk_id' => $produk->id,
                        'stok_sistem' => $stokSistem,
                        'stok_fisik' => $stokFisik,
                        'selisih' => $selisih,
                        'keterangan' => $item['keterangan'] ?? null,
                    ]);

                    // Update actual stock
                    $produk->stok = $stokFisik;
                    $produk->save();
                }
            }

            DB::commit();

            return redirect()->route('stok-opname.index')->with('success', 'Data Stok Opname berhasil disimpan dan stok produk telah diperbarui!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menyimpan data Stok Opname: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $stokOpname = StokOpnameHeader::with('details.produk')->findOrFail($id);

        return Inertia::render('StokOpname/Show', [
            'stokOpname' => $stokOpname
        ]);
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $opname = StokOpnameHeader::findOrFail($id);
            
            // Revert stock changes
            foreach ($opname->details as $detail) {
                $produk = Produk::find($detail->produk_id);
                if ($produk) {
                    // Reverse the selisih (stok fisik - stok sistem).
                    // Example: Sistem 10, Fisik 8, Selisih -2. New Sistem is 8.
                    // To revert, subtract selisih from current stock: 8 - (-2) = 10.
                    $produk->stok -= $detail->selisih;
                    $produk->save();
                }
            }

            $opname->delete(); // Details should be cascaded
            DB::commit();
            
            return redirect()->back()->with('success', 'Data Stok Opname berhasil dihapus dan stok sistem dikembalikan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus data Stok Opname: ' . $e->getMessage()]);
        }
    }
}
