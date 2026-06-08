<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ruangan extends Model
{
    protected $table = 'ruangans';

    protected $fillable = [
        'nama_ruangan',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public function transaksis()
    {
        return $this->hasMany(TransaksiHeader::class, 'id_ruangan');
    }

    public function absenRapats()
    {
        return $this->hasMany(AbsenRapat::class, 'ruangan_id');
    }
}
