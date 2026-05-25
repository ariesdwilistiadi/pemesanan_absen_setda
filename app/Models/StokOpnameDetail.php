<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokOpnameDetail extends Model
{
    use HasFactory;

    protected $table = 'stok_opname_details';

    protected $fillable = [
        'stok_opname_header_id',
        'produk_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan',
    ];

    public function header()
    {
        return $this->belongsTo(StokOpnameHeader::class, 'stok_opname_header_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}