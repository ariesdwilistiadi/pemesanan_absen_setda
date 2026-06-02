<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DaftarHadir extends Model
{
    use HasFactory;
    protected $table = 'absensi_pesertas';

    // Sesuaikan dengan nama tabelmu di database jika berbeda
    // protected $table = 'nama_tabel_kamu';

    protected $fillable = [
        'absen_rapat_id',
        'tipe_peserta',
        'nip',
        'nama',
        'id_dinas',
        'nama_external',
        'telp',
        'email',
        'signature',
    ];

    public function dinas()
    {
        return $this->belongsTo(Dinas::class, 'id_dinas', 'id');
    }
}