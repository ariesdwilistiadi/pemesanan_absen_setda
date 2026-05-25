<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturHeader extends Model
{
    use HasFactory;

    protected $table = 'retur_headers';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal',
        'jenis_retur',
        'pihak_terkait',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(ReturDetail::class, 'retur_header_id');
    }
}