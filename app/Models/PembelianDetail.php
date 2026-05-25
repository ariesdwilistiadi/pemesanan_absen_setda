<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianDetail extends Model
{
    use HasFactory;

    protected $table = 'pembelian_details';

    protected $fillable = [
        'pembelian_header_id',
        'barang_id',
        'kuantitas',
        'harga_satuan',
        'subtotal',
    ];

    public function header()
    {
        return $this->belongsTo(PembelianHeader::class, 'pembelian_header_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'barang_id');
    }
}
