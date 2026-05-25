<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbsenRapat extends Model
{
    use HasFactory;

    // Jika nama tabel di database BUKAN 'absen_rapats' (plural bahasa inggris otomatis dari Laravel), 
    // kamu wajib mendefinisikan nama tabelnya di sini. Uncomment baris di bawah dan isi nama tabelmu:
    // protected $table = 'nama_tabel_kamu';

    // Kolom yang diizinkan untuk diisi secara massal
    protected $fillable = [
        'nama_kegiatan',
        'tanggal',
        'pukul',
    ];
}