<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    protected $fillable = [
        'transaksi_header_id',
        'produk_id',
        'jumlah',
        'harga_satuan',
        'subtotal'
    ];

    public function header()
    {
        return $this->belongsTo(TransaksiHeader::class, 'transaksi_header_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
