<?php

namespace App\Http\Controllers;

use App\Models\AbsensiPeserta;
use Illuminate\Http\Request;

class AbsensiPesertaController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'absen_rapat_id' => 'required|exists:absen_rapats,id',
            'tipe_peserta'   => 'required|in:internal,eksternal',
            'nip'            => 'nullable|string',
            'nama'           => 'required|string',
            'id_dinas'       => 'nullable|integer',
            'nama_external'  => 'nullable|string',
            'telp'           => 'required|string',
            'email'          => 'required|email',
            'signature'      => 'required|string', // Base64 tanda tangan
        ]);

        AbsensiPeserta::create($validated);
        return redirect()->back()->with('success', 'Kehadiran berhasil disimpan.');
    }

    // Fungsi untuk melihat daftar hadir di rapat tertentu
    public function show($id)
    {
        $rapat = \App\Models\AbsenRapat::findOrFail($id);
        $pesertas = AbsensiPeserta::where('absen_rapat_id', $id)->get();

        return Inertia::render('AbsenRapat/Show', [
            'rapat' => $rapat,
            'pesertas' => $pesertas
        ]);
    }
}