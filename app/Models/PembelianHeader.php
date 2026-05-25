<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianHeader extends Model
{
    use HasFactory;

    protected $table = 'pembelian_headers';

    protected $fillable = [
        'nomor_transaksi',
        'tanggal_pembelian',
        'nama_supplier',
        'total_harga',
        'keterangan',
    ];

    public function details()
    {
        return $this->hasMany(PembelianDetail::class, 'pembelian_header_id');
    }
}
