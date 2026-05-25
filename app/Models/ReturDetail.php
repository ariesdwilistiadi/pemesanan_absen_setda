<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturDetail extends Model
{
    use HasFactory;

    protected $table = 'retur_details';

    protected $fillable = [
        'retur_header_id',
        'produk_id',
        'kuantitas',
        'keterangan',
    ];

    public function header()
    {
        return $this->belongsTo(ReturHeader::class, 'retur_header_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}