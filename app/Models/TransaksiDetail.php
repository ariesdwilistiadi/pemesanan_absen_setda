<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiDetail extends Model
{
    protected $fillable = [
        'transaksi_header_id',
        'produk_id',
        'nama_produk_external',
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

    /**
     * Get nama produk (dari produk lokal atau dari field external)
     */
    public function getNamaBarangAttribute()
    {
        if ($this->nama_produk_external) {
            return $this->nama_produk_external;
        }
        return $this->produk ? $this->produk->nama_barang : 'Produk Dihapus';
    }
}
