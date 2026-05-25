<?php

namespace App\Http\Controllers;

use App\Models\AbsenRapat;
use App\Models\DaftarHadir;
use App\Models\Dinas;
use Illuminate\Http\Request;

use Inertia\Inertia;

class AbsenRapatController extends Controller
{
    public function index(Request $request)
    {
        // Fitur Filter Berdasarkan Tanggal
        $query = AbsenRapat::query();

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('tanggal', $request->tanggal);
        }

        $rapats = $query->latest('tanggal')->get();

        return Inertia::render('AbsenRapat/Index', [
            'rapats' => $rapats,
            'filters' => $request->only(['tanggal']) // Kirim filter aktif ke frontend
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'pukul'         => 'required', // Boleh tambahkan date_format:H:i jika format ketat
        ]);

        AbsenRapat::create($validated);
        return redirect()->back()->with('success', 'Rapat berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'pukul'         => 'required',
        ]);

        $rapat = AbsenRapat::findOrFail($id);
        $rapat->update($validated);

        return redirect()->back()->with('success', 'Rapat berhasil diupdate.');
    }

   

    public function show($id)
    {
        $rapat = AbsenRapat::findOrFail($id);
        $kehadiran = DaftarHadir::where('absen_rapat_id', $id)->latest()->get();
        
        // Ambil semua data dinas dari tabel
        $masterDinas = Dinas::orderBy('nama_dinas', 'asc')->get();

        return Inertia::render('AbsenRapat/Detail', [
            'rapat' => $rapat,
            'kehadiran' => $kehadiran,
            'masterDinas' => $masterDinas // Kirim variabel ini ke Vue
        ]);
    }
    
    // Metode untuk menampilkan form absen publik
    public function publicShow($id)
    {
        $rapat = AbsenRapat::findOrFail($id);
        $masterDinas = Dinas::orderBy('nama_dinas', 'asc')->get();

        return Inertia::render('AbsenRapat/PublicForm', [
            'rapat' => $rapat,
            'masterDinas' => $masterDinas
        ]);
    }

    // Metode untuk memproses form absen publik
    public function publicStore(Request $request, $id)
    {
        $validated = $request->validate([
            'tipe_peserta'  => 'required|in:internal,eksternal',
            'nip'           => 'nullable|string',
            'nama'          => 'required|string|max:191',
            'id_dinas'      => 'nullable|integer', 
            'nama_external' => 'nullable|string|max:191',
            'telp'          => 'required|string|max:191',
            'email'         => 'required|email|max:191',
            'signature'     => 'required|string',
        ]);

        $validated['absen_rapat_id'] = $id;

        $kehadiran = DaftarHadir::create($validated);

        return redirect()->route('kasir.index', ['id_peserta' => $kehadiran->id])->with('success', 'Kehadiran Anda berhasil dicatat.');
    }

    // Metode baru untuk menyimpan data form tanda tangan (Dari Dalam Admin)
    public function storeKehadiran(Request $request, $id)
    {
        $validated = $request->validate([
            'tipe_peserta'  => 'required|in:internal,eksternal',
            'nip'           => 'nullable|string',
            'nama'          => 'required|string|max:191',
            
            // id_dinas harus nullable dan berupa integer (sesuai ID dari dropdown)
            'id_dinas'      => 'nullable|integer', 
            
            'nama_external' => 'nullable|string|max:191',
            'telp'          => 'required|string|max:191',
            'email'         => 'required|email|max:191',
            'signature'     => 'required|string',
        ]);

        $validated['absen_rapat_id'] = $id;

        $kehadiran = DaftarHadir::create($validated);

        return redirect()->route('kasir.index', ['id_peserta' => $kehadiran->id])->with('success', 'Kehadiran berhasil dicatat.');
    }
}