<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpnameHeader extends Model
{
    use HasFactory;

    protected $table = 'stok_opname_headers';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'penanggung_jawab',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(StokOpnameDetail::class, 'stok_opname_header_id');
    }
}