<?php

namespace App\Models;

use App\Models\Anggota;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DanaDkk extends Model
{
    protected $table = 'dana_dkk';
    protected $primaryKey = 'id_dana_dkk';
    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'id_anggota',
        'nominal',
        'sakit',
        'keterangan',
        'file_berkas',
        'tgl_sakit',
        'tanggal_create',
        'lama_sakit',
    ];

    protected $appends = [
        'file_url',
    ];

    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota');
    }

    public function getFileUrlAttribute()
    {
        return $this->file_berkas ? Storage::disk('public')->url($this->file_berkas) : null;
    }
}
