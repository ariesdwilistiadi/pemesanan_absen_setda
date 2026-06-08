<?php

namespace App\Http\Controllers;

use App\Models\Ruangan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RuanganController extends Controller
{
    /**
     * Display a listing of ruangan.
     */
    public function index(Request $request)
    {
        $query = Ruangan::query();

        // Filter aktif/nonaktif
        if ($request->has('filter') && $request->filter === 'nonaktif') {
            $query->where('is_active', false);
        } elseif ($request->filter === 'aktif') {
            $query->where('is_active', true);
        } else {
            // Default: tampilkan semua
        }

        $ruangans = $query->orderBy('nama_ruangan', 'asc')->get();

        return Inertia::render('Ruangan/Index', [
            'ruangans' => $ruangans,
            'filter' => $request->filter ?? 'semua',
        ]);
    }

    /**
     * Store a newly created ruangan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_ruangan' => 'required|string|max:255|unique:ruangans,nama_ruangan',
            'keterangan'   => 'nullable|string|max:500',
            'is_active'    => 'boolean',
        ]);

        // Default is_active = true jika tidak diset
        if (!isset($validated['is_active'])) {
            $validated['is_active'] = true;
        }

        $ruangan = Ruangan::create($validated);

        return redirect()->back()->with('success', 'Ruangan "' . $ruangan->nama_ruangan . '" berhasil ditambahkan.');
    }

    /**
     * Update the specified ruangan.
     */
    public function update(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);

        $validated = $request->validate([
            'nama_ruangan' => 'required|string|max:255|unique:ruangans,nama_ruangan,' . $id,
            'keterangan'   => 'nullable|string|max:500',
            'is_active'    => 'boolean',
        ]);

        $ruangan->update($validated);

        return redirect()->back()->with('success', 'Ruangan "' . $ruangan->nama_ruangan . '" berhasil diperbarui.');
    }

    /**
     * Toggle active status of ruangan.
     */
    public function toggleActive(Request $request, $id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $ruangan->is_active = !$ruangan->is_active;
        $ruangan->save();

        $status = $ruangan->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', 'Ruangan "' . $ruangan->nama_ruangan . '" berhasil ' . $status . '.');
    }

    /**
     * Remove the specified ruangan.
     */
    public function destroy($id)
    {
        $ruangan = Ruangan::findOrFail($id);
        $nama = $ruangan->nama_ruangan;

        // Cek apakah ruangan digunakan di rapat manapun
        if ($ruangan->absenRapats()->count() > 0) {
            return redirect()->back()->with('error', 'Ruangan "' . $nama . '" tidak dapat dihapus karena masih digunakan di rapat.');
        }

        $ruangan->delete();

        return redirect()->back()->with('success', 'Ruangan "' . $nama . '" berhasil dihapus.');
    }
}