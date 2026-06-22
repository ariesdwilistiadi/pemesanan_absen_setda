<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Agama;
use App\Support\RecordOwnership;
use Illuminate\Http\Request;
use Inertia\Inertia;
  
class AnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filterStatus = $request->query('status'); // 'active', 'inactive', or null (all)

        $anggotasQuery = Anggota::with('agama')->latest();

        // Filter by status if provided
        if ($filterStatus === 'active') {
            $anggotasQuery->where('status', 1);
        } elseif ($filterStatus === 'inactive') {
            $anggotasQuery->where('status', 0);
        }

        RecordOwnership::scopeOwned($anggotasQuery, $request->user());
        $anggotas = $anggotasQuery->get();
        $agamas = Agama::all();

        return Inertia::render('Anggota/Index', [
            'anggotas' => $anggotas,
            'agamas' => $agamas,
            'filterStatus' => $filterStatus,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Not used, modal based
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'no_identitas' => 'required|string|max:25',
            'tempat_lahir' => 'required|string|max:50',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'no_telp' => 'required|string|max:17',
            'agama_id' => 'required|exists:agamas,id',
            'pekerjaan' => 'required|string|max:255',
            'tgl_masuk' => 'required|date',
            'simpanan_pokok' => 'required|integer|min:0',
            'simpanan_wajib' => 'required|integer|min:0',
        ]);

        $validated['status'] = 1; // Default status active
        $validated['owner_user_id'] = $request->user()->id;

        Anggota::create($validated);

        return redirect()->back()->with('success', 'Anggota berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Anggota $anggota)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Anggota $anggota)
    {
        // Not used, modal based
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Anggota $anggota)
    {
        RecordOwnership::abortUnlessOwned($anggota, $request->user());
        $validated = $request->validate([
            'nama' => 'required|string|max:100',
            'no_identitas' => 'required|string|max:25',
            'tempat_lahir' => 'required|string|max:50',
            'tgl_lahir' => 'required|date',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|in:laki-laki,perempuan',
            'no_telp' => 'required|string|max:17',
            'agama_id' => 'required|exists:agamas,id',
            'pekerjaan' => 'required|string|max:255',
            'tgl_masuk' => 'required|date',
            'simpanan_pokok' => 'required|integer|min:0',
            'simpanan_wajib' => 'required|integer|min:0',
            'status' => 'required|integer|in:0,1',
        ]);

        $anggota->update($validated);

        return redirect()->back()->with('success', 'Anggota berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Anggota $anggota)
    {
        RecordOwnership::abortUnlessOwned($anggota, request()->user());
        $anggota->delete();

        return redirect()->back()->with('success', 'Anggota berhasil dihapus.');
    }
}
