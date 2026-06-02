<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstansiProfile extends Model
{
    use HasFactory;

    protected $table = 'instansi_profiles';

    protected $fillable = [
        'pemerintah',
        'nama_instansi',
        'alamat',
        'kontak',
        'nama_kepala',
        'nip_kepala',
        'jabatan_kepala',
        'logo',
    ];
}
