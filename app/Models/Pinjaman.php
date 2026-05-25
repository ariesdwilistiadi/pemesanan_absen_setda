<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Anggota;
use App\Models\PinjamanBayar;

class Pinjaman extends Model
{
    // Konfigurasi Tabel Legacy (Sesuaikan dengan Database Anda)
    protected $table = 'pinjaman';
    protected $primaryKey = 'id_pinjaman';
    
    // Nonaktifkan timestamps jika tabel Anda tidak memiliki kolom created_at/updated_at
    public $timestamps = false;
    public $incrementing = true;

    protected $fillable = [
        'id_anggota', 
        'jumlah_pinjaman', 
        'jasa', 
        'jumlah_angsuran', 
        'jangka_waktu', 
        'nama', 
        'tgl_pinjaman', 
        'tanggal_create', 
        'username', 
        'id_jenis_pinjaman', 
        'kategori'
    ];

    /**
     * Menambahkan atribut virtual ke dalam JSON Response (Inertia)
     * Atribut ini tidak ada di tabel, tapi dihitung otomatis oleh Laravel.
     */
    protected $appends = [
        'total_dibayar', 
        'sisa_pinjaman',
        'status_lunas'
    ];

    // --- RELASI DATA ---

    /**
     * Relasi ke Data Anggota
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }

    /**
     * Relasi ke Riwayat Pembayaran (Tabel pinjaman_bayar)
     */
    public function angsuran()
    {
        return $this->hasMany(PinjamanBayar::class, 'id_pinjaman', 'id_pinjaman')
                    ->orderBy('tanggal_bayar', 'desc');
    }

    // --- ACCESSOR (PERHITUNGAN OTOMATIS) ---

    /**
     * Menghitung Total yang sudah dibayar oleh anggota
     */
    public function getTotalDibayarAttribute()
    {
        // Mengambil sum kolom 'bayar' dari relasi angsuran
        return $this->angsuran->sum('bayar');
    }

    /**
     * Menghitung Sisa Pinjaman
     * Rumus: (Pokok + Jasa) - Total Bayar
     */
    public function getSisaPinjamanAttribute()
    {
        // Total Hutang = Pinjaman Pokok + Bunga/Jasa
        $totalTanggungan = $this->jumlah_pinjaman + $this->jasa;
        
        $sisa = $totalTanggungan - $this->total_dibayar;

        // Pastikan hasil tidak minus jika ada kelebihan bayar
        return $sisa < 0 ? 0 : $sisa;
    }

    /**
     * Label Status Lunas Otomatis
     */
    public function getStatusLunasAttribute()
    {
        return $this->sisa_pinjaman <= 0 ? 'LUNAS' : 'BELUM LUNAS';
    }
}