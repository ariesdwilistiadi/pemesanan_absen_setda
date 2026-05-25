<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $fillable = [
        'kode_barang',
        'nama_barang',
        'kategori',
        'harga_beli',
        'harga_jual',
        'stok',
        'satuan',
        'deskripsi',
        'gambar',
        'owner_user_id',
    ];
}
