<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Support\RecordOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProdukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $produksQuery = Produk::latest();
        RecordOwnership::scopeOwned($produksQuery, $request->user());
        $produks = $produksQuery->get();

        return Inertia::render('Produk/Index', [
            'produks' => $produks,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:255|unique:produks',
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('produk', 'public');
            $validated['gambar'] = $path;
        }

        $validated['owner_user_id'] = $request->user()->id;
        $validated['is_active'] = $validated['is_active'] ?? true;
        Produk::create($validated);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produk $produk)
    {
        RecordOwnership::abortUnlessOwned($produk, $request->user());
        $validated = $request->validate([
            'kode_barang' => 'required|string|max:255|unique:produks,kode_barang,' . $produk->id,
            'nama_barang' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'satuan' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
                Storage::disk('public')->delete($produk->gambar);
            }
            $path = $request->file('gambar')->store('produk', 'public');
            $validated['gambar'] = $path;
        } else {
            // Keep the old picture if new picture not provided
            unset($validated['gambar']);
        }

        $produk->update($validated);

        return redirect()->back()->with('success', 'Produk berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produk $produk)
    {
        RecordOwnership::abortUnlessOwned($produk, request()->user());
        if ($produk->gambar && Storage::disk('public')->exists($produk->gambar)) {
            Storage::disk('public')->delete($produk->gambar);
        }

        $produk->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus.');
    }

    /**
     * Toggle the active status of a product.
     */
    public function toggleActive(Request $request, Produk $produk)
    {
        RecordOwnership::abortUnlessOwned($produk, $request->user());
        $produk->is_active = !$produk->is_active;
        $produk->save();

        $status = $produk->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Produk berhasil {$status}.");
    }
}
