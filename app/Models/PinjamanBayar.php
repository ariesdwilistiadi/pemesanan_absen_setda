<?php

namespace App\Models;

use App\Models\Anggota;
use App\Models\Pinjaman;
use Illuminate\Database\Eloquent\Model;

class PinjamanBayar extends Model
{
    protected $table = 'pinjaman_bayar';
    protected $primaryKey = 'id_pinjaman_bayar';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_anggota',
        'id_pinjaman',
        'bayar',
        'bunga',
        'denda',
        'tanggal_bayar',
        'owner_user_id',
        'username',
        'tanggal_create',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id');
    }

    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'id_pinjaman');
    }
}
