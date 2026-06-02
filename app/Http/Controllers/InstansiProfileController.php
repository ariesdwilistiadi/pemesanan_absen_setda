<?php

namespace App\Http\Controllers;

use App\Models\InstansiProfile;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class InstansiProfileController extends Controller
{
    public function edit()
    {
        $instansi = InstansiProfile::firstOrCreate(['id' => 1]);
        
        return Inertia::render('Instansi/Edit', [
            'instansi' => $instansi
        ]);
    }

  public function update(Request $request)
    {
        // Mengambil instance atau membuat baru
        $instansi = InstansiProfile::firstOrCreate(['id' => 1]);

        // Validasi input
        $validated = $request->validate([
            'pemerintah' => 'required|string|max:255',
            'nama_instansi' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'kontak' => 'required|string|max:255',
            'nama_kepala' => 'required|string|max:255',
            'nip_kepala' => 'nullable|string|max:255',
            'jabatan_kepala' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', 
        ]);

        // Menangani File Logo
        if ($request->hasFile('logo')) {
            // Hapus file lama jika ada
            if ($instansi->logo && Storage::disk('public')->exists($instansi->logo)) {
                Storage::disk('public')->delete($instansi->logo);
            }

            // store() akan otomatis membuat folder 'instansi' di dalam 'storage/app/public' 
            // jika folder tersebut belum ada.
            $path = $request->file('logo')->store('instansi', 'public');
            
            // Update data array dengan path file yang baru
            $validated['logo'] = $path;
        }

        // Simpan perubahan ke database
        $instansi->update($validated);

        return redirect()->back()->with('success', 'Profil Instansi berhasil diperbarui.');
    }
}
