<?php

namespace App\Http\Controllers;

use App\Models\AbsenRapat;
use App\Models\DaftarHadir;
use App\Models\Dinas;
use App\Models\Ruangan;
use App\Support\RecordOwnership;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

use Inertia\Inertia;

class AbsenRapatController extends Controller
{
    public function index(Request $request)
    {
        $query = AbsenRapat::query();
        $canViewAllRapat = RecordOwnership::canAccessAllRecords($request->user());

        if ($request->has('tanggal') && $request->tanggal != '') {
            $query->whereDate('tanggal', $request->tanggal);
        } elseif (!$canViewAllRapat) {
            // Tampilkan rapat hari ini secara default untuk user biasa.
            $query->whereDate('tanggal', today());
        }

        $rapats = $query->latest('tanggal')->with('ruangan')->get();

        // Ambil semua ruangan yang aktif
        $ruangans = Ruangan::where('is_active', true)->orderBy('nama_ruangan', 'asc')->get();

        return Inertia::render('AbsenRapat/Index', [
            'rapats' => $rapats,
            'filters' => $request->only(['tanggal']),
            'canViewAll' => $canViewAllRapat,
            'ruangans' => $ruangans,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'pukul'         => 'required',
            'ruangan_id'    => 'nullable|exists:ruangans,id',
        ]);

        $validated['owner_user_id'] = $request->user()->id;
        AbsenRapat::create($validated);
        return redirect()->back()->with('success', 'Rapat berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'nama_kegiatan' => 'required|string|max:255',
            'tanggal'       => 'required|date',
            'pukul'         => 'required',
            'ruangan_id'    => 'nullable|exists:ruangans,id',
        ]);

        $rapat = AbsenRapat::findOrFail($id);
        RecordOwnership::abortUnlessOwned($rapat, $request->user());
        $rapat->update($validated);

        return redirect()->back()->with('success', 'Rapat berhasil diupdate.');
    }

   

    public function show(Request $request, $id)
    {
        $rapat = AbsenRapat::with('ruangan')->findOrFail($id);
        $kehadiran = DaftarHadir::where('absen_rapat_id', $id)->latest()->get();
<<<<<<< Updated upstream

        // Ambil semua data dinas dari tabel
=======
        
 
>>>>>>> Stashed changes
        $masterDinas = Dinas::orderBy('nama_dinas', 'asc')->get();

        return Inertia::render('AbsenRapat/Detail', [
            'rapat' => $rapat,
            'kehadiran' => $kehadiran,
            'masterDinas' => $masterDinas // Kirim variabel ini ke Vue
        ]);
    }
    
    public function print(Request $request, $id)
    {
        $rapat = AbsenRapat::with('ruangan')->findOrFail($id);
        
        $kehadiran = DaftarHadir::with('dinas')->where('absen_rapat_id', $id)->orderBy('created_at', 'asc')->get();
        
        $instansi = \App\Models\InstansiProfile::firstOrCreate(['id' => 1]);
        
        return view('print.rapat', compact('rapat', 'kehadiran', 'instansi'));
    }
    
    // Metode untuk menampilkan list rapat aktif (publik - tanpa login)
   public function publicList()
    {
        $today = now()->toDateString();

        // Hanya tampilkan semua rapat hari ini, diurutkan berdasarkan jam
        $rapats = AbsenRapat::with('ruangan')
            ->whereDate('tanggal', $today)
            ->orderBy('pukul', 'asc')
            ->get();

        return Inertia::render('AbsenRapat/PublicList', [
            'rapats' => $rapats,
        ]);
    }

    // Metode untuk menampilkan form absen publik
    public function publicShow($id)
    {
        $rapat = AbsenRapat::with('ruangan')->findOrFail($id);
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
            'jenis_kelamin' => 'required|string|max:50',
            'id_dinas'      => 'nullable',
            'jabatan'       => 'nullable|string|max:191',
            'nama_external' => 'nullable|string|max:191',
            'telp'          => 'required|string|max:191',
            'email'         => 'required|email|max:191',
            'signature'     => 'required|string',
        ]);

        // Convert empty string to null for id_dinas
        if (empty($validated['id_dinas'])) {
            $validated['id_dinas'] = null;
        }

        // Validasi tambahan: internal harus pilih dinas atau isi nama_external
        if ($validated['tipe_peserta'] === 'internal') {
            if (empty($validated['id_dinas']) && empty(trim($validated['nama_external'] ?? ''))) {
                return back()->withErrors(['nama_external' => 'Pilih Dinas atau isi Nama Unit Kerja untuk peserta internal.'])->withInput();
            }
        } else {
            // Eksternal wajib isi asal instansi
            if (empty(trim($validated['nama_external'] ?? ''))) {
                return back()->withErrors(['nama_external' => 'Asal Dinas/Instansi wajib diisi untuk peserta eksternal.'])->withInput();
            }
        }

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
<<<<<<< Updated upstream
            'jenis_kelamin' => 'required|string|max:50',
            'id_dinas'      => 'nullable',
            'jabatan'       => 'nullable|string|max:191',
=======
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            
            // id_dinas harus nullable dan berupa integer (sesuai ID dari dropdown)
            'id_dinas'      => 'nullable|string', 
            
>>>>>>> Stashed changes
            'nama_external' => 'nullable|string|max:191',
            'telp'          => 'required|string|max:191',
            'email'         => 'required|email|max:191',
            'signature'     => 'required|string',
        ]);

        // Convert empty string to null for id_dinas
        if (empty($validated['id_dinas'])) {
            $validated['id_dinas'] = null;
        }

        // Validasi tambahan untuk internal
        if ($validated['tipe_peserta'] === 'internal') {
            if (empty($validated['id_dinas']) && empty(trim($validated['nama_external'] ?? ''))) {
                return back()->withErrors(['nama_external' => 'Pilih Dinas atau isi Nama Unit Kerja untuk peserta internal.'])->withInput();
            }
        }

        $validated['absen_rapat_id'] = $id;

        $kehadiran = DaftarHadir::create($validated);

        return redirect()->route('kasir.index', ['id_peserta' => $kehadiran->id])->with('success', 'Kehadiran berhasil dicatat.');
    }

    // Endpoint API untuk mengambil Data Pegawai dari SIMPEG
    public function getPegawaiSimpeg($nip)
    {
        try {
            // 1. Dapatkan Token API
            $tokenResponse = Http::asForm()->post("https://restsimpeg.kotabogor.go.id/v3/api/auth/gettokenapps", [
                'username' => 'setda@kotabogor.go.id',
                'passkey'  => 'SetdaBogor6202!',
            ]);

            $token = $tokenResponse->json('token') ?? $tokenResponse->json('access_token');
            
            if (!$token) {
                return response()->json(['success' => false, 'message' => 'Gagal mendapatkan token dari SIMPEG.']);
            }

            // 2. Ambil Data Pegawai
            $pegawaiResponse = Http::withToken($token)->get("https://restsimpeg.kotabogor.go.id/v3/setda/getpegawai/{$nip}");

            if ($pegawaiResponse->successful() && $pegawaiResponse->json()) {
                return response()->json([
                    'success' => true,
                    'data' => $pegawaiResponse->json()
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Data pegawai tidak ditemukan.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
