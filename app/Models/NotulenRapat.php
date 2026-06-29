<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\RecordOwnership;

class NotulenRapat extends Model
{
    use HasFactory;

    protected $table = 'notulen_rapats';

    protected $fillable = [
        'absen_rapat_id',
        'ketua_id',
        'sekretaris_id',
        'pencatat_id',
        'pembukaan',
        'pembahasan',
        'peraturan',
        'penutup',
        'peserta_mode',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke tabel absen_rapats (rapat)
     */
    public function rapat()
    {
        return $this->belongsTo(AbsenRapat::class, 'absen_rapat_id');
    }

    /**
     * Relasi ke user (ketua)
     */
    public function ketua()
    {
        return $this->belongsTo(User::class, 'ketua_id');
    }

    /**
     * Relasi ke user (sekretaris)
     */
    public function sekretaris()
    {
        return $this->belongsTo(User::class, 'sekretaris_id');
    }

    /**
     * Relasi ke user (pencatat/notulis)
     */
    public function pencacat()
    {
        return $this->belongsTo(User::class, 'pencatat_id');
    }
}
