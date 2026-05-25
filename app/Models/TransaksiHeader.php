<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiHeader extends Model
{
    protected $fillable = [
        'no_transaksi',
        'id_absen_rapats',
        'nip',
        'nama',
        'nomor_meja',
        'tanggal_transaksi',
        'total_item',
        'total_harga',
        'keterangan',
        'owner_user_id',
        'status',
        'metode_pembayaran',
        'jumlah_bayar',
        'kembalian'
    ];

    public function details()
    {
        return $this->hasMany(TransaksiDetail::class, 'transaksi_header_id');
    }

    public function absen()
    {
        return $this->belongsTo(AbsenRapat::class, 'id_absen_rapats');
    }
}
