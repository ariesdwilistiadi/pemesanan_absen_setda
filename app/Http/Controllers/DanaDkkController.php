<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\DanaDkk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class DanaDkkController extends Controller
{
    public function index()
    {
        $danaDkks = DanaDkk::with('anggota')->latest('tanggal_create')->get();
        $anggotas = Anggota::select(['id_anggota', 'no_anggota', 'nama'])->orderBy('nama')->get();

        return Inertia::render('DanaDkk/Index', [
            'danaDkks' => $danaDkks,
            'anggotas' => $anggotas,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_anggota' => 'required|exists:anggotas,id',
            'nominal' => 'required|integer|min:0',
            'sakit' => 'required|string',
            'keterangan' => 'required|string',
            'file_berkas' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:5120',
            'tgl_sakit' => 'required|date',
            'lama_sakit' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('file_berkas')) {
            $validated['file_berkas'] = $request->file('file_berkas')->store('dana_dkk', 'public');
        }

        $validated['tanggal_create'] = now();
        DanaDkk::create($validated);

        return redirect()->back()->with('success', 'Data Dana DKK berhasil disimpan.');
    }
}
