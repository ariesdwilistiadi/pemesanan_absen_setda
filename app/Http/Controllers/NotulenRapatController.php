<?php

namespace App\Http\Controllers;

use App\Models\NotulenRapat;
use App\Models\AbsenRapat;
use App\Models\User;
use App\Support\RecordOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class NotulenRapatController extends Controller
{
    /**
     * Menampilkan daftar notulen rapat
     */
    public function index(Request $request)
    {
        $query = NotulenRapat::with(['rapat', 'ketua', 'sekretaris', 'pencacat']);

        if ($request->has('search') && $request->search != '') {
            $query->whereHas('rapat', function ($q) use ($request) {
                $q->where('nama_kegiatan', 'like', '%' . $request->search . '%');
            });
        }

        $notulens = $query->latest()->paginate(10);

        return Inertia::render('NotulenRapat/Index', [
            'notulens' => $notulens,
            'filters' => $request->only(['search']),
        ]);
    }

    /**
     * Menampilkan form tambah/edit notulen
     */
    public function create(Request $request)
    {
        // Ambil data rapat
        $rapats = AbsenRapat::latest()->get();

        // Ambil semua user untuk dipilih sebagai ketua, sekretaris, pencacat
        $users = User::orderBy('name', 'asc')->get();

        // Jika ada rapats_id dari query, load那只 rapat
        $selectedRapat = null;
        if ($request->has('rapat_id')) {
            $selectedRapat = AbsenRapat::with('ruangan')->find($request->rapat_id);
        }

        return Inertia::render('NotulenRapat/Create', [
            'rapats' => $rapats,
            'users' => $users,
            'selectedRapat' => $selectedRapat,
        ]);
    }

    /**
     * Menyimpan notulen baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'absen_rapat_id' => 'required|exists:absen_rapats,id',
            'ketua_id'       => 'nullable|exists:users,id',
            'sekretaris_id'  => 'nullable|exists:users,id',
            'pencatat_id'    => 'nullable|exists:users,id',
            'ketua_manual'   => 'nullable|string|max:255',
            'sekretaris_manual' => 'nullable|string|max:255',
            'pencatat_manual' => 'nullable|string|max:255',
            'pembukaan'      => 'nullable|string',
            'pembahasan'     => 'nullable|string',
            'peraturan'      => 'nullable|string',
            'penutup'        => 'nullable|string',
            'peserta_mode'   => 'required|in:terlampir,manual',
        ]);

        NotulenRapat::create($validated);

        return redirect()->route('notulen.index')->with('success', 'Notulen rapat berhasil disimpan.');
    }

    /**
     * Menampilkan detail notulen
     */
    public function show($id)
    {
        $notulen = NotulenRapat::with(['rapat.ruangan', 'ketua', 'sekretaris', 'pencacat'])->findOrFail($id);

        return Inertia::render('NotulenRapat/Show', [
            'notulen' => $notulen,
        ]);
    }

    /**
     * Menampilkan form edit notulen
     */
    public function edit($id)
    {
        $notulen = NotulenRapat::findOrFail($id);
        $rapats = AbsenRapat::latest()->get();
        $users = User::orderBy('name', 'asc')->get();

        return Inertia::render('NotulenRapat/Edit', [
            'notulen' => $notulen,
            'rapats' => $rapats,
            'users' => $users,
        ]);
    }

    /**
     * Mengupdate notulen
     */
    public function update(Request $request, $id)
    {
        $notulen = NotulenRapat::findOrFail($id);

        $validated = $request->validate([
            'absen_rapat_id' => 'required|exists:absen_rapats,id',
            'ketua_id'       => 'nullable|exists:users,id',
            'sekretaris_id'  => 'nullable|exists:users,id',
            'pencatat_id'    => 'nullable|exists:users,id',
            'ketua_manual'   => 'nullable|string|max:255',
            'sekretaris_manual' => 'nullable|string|max:255',
            'pencatat_manual' => 'nullable|string|max:255',
            'pembukaan'      => 'nullable|string',
            'pembahasan'     => 'nullable|string',
            'peraturan'      => 'nullable|string',
            'penutup'        => 'nullable|string',
            'peserta_mode'   => 'required|in:terlampir,manual',
        ]);

        $notulen->update($validated);

        return redirect()->route('notulen.index')->with('success', 'Notulen rapat berhasil diupdate.');
    }

    /**
     * Menghapus notulen
     */
    public function destroy(Request $request, $id)
    {
        $notulen = NotulenRapat::findOrFail($id);

        // Check ownership if needed
        // RecordOwnership::abortUnlessOwned($notulen, $request->user());

        $notulen->delete();

        return redirect()->route('notulen.index')->with('success', 'Notulen rapat berhasil dihapus.');
    }

    /**
     * Print notulen rapat (PDF) - standalone route
     */
    public function print($id)
    {
        $notulen = NotulenRapat::with([
            'rapat.ruangan',
            'ketua',
            'sekretaris',
            'pencacat',
        ])->findOrFail($id);

        // Ambil data daftar hadir dari tabel daftar_hadirs
        $daftarHadir = \App\Models\DaftarHadir::with('dinas')
            ->where('absen_rapat_id', $notulen->absen_rapat_id)
            ->orderBy('created_at', 'asc')
            ->get();

        $instansi = \App\Models\InstansiProfile::firstOrCreate(['id' => 1]);

        $pdf = \PDF::loadView('print.notulen', [
            'notulen' => $notulen,
            'instansi' => $instansi,
            'daftarHadir' => $daftarHadir
        ]);

        // Set paper A4 portrait
        $pdf->setPaper('a4', 'portrait');

        $filename = 'Notulen_' . preg_replace('/[^a-zA-Z0-9]/', '_', $notulen->rapat->nama_kegiatan ?? 'Rapat') . '_' . date('Ymd') . '.pdf';

        // Save to temp file
        $tempPath = storage_path('app/temp_' . time() . '.pdf');
        file_put_contents($tempPath, $pdf->output());

        // Return as download
        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/pdf',
        ])->deleteFileAfterSend(true);
    }
}
