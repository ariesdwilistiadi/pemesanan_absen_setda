<?php

namespace App\Models;

use App\Models\DanaDkk;
use App\Models\Pinjaman;
use App\Models\PinjamanBayar;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table = 'anggotas';

    // 1. TAMBAHKAN INI: Memberitahu Laravel bahwa kuncinya bukan 'id'
  //  protected $primaryKey = 'id';

    // 2. TAMBAHKAN INI: Karena tabel Anda tidak punya kolom created_at/updated_at
    public $timestamps = false;

    // 3. Pastikan auto-increment aktif
    public $incrementing = true;

    protected $fillable = [
        'id_anggota', // Masukkan ke fillable untuk keamanan
        'no_anggota',
        'nama',
        'no_identitas',
        'tempat_lahir',
        'tgl_lahir',
        'alamat',
        'jenis_kelamin',
        'no_telp',
        'agama_id',
        'pekerjaan',
        'simpanan_pokok',
        'simpanan_wajib',
        'pangkat',
        'nrp_nip',
        'kesatuan',
        'kategori',
        'status',
        'tgl_masuk',
        'owner_user_id',
    ];

    /**
     * Relasi HasMany (Satu Anggota punya banyak Dana DKK)
     */
    public function danaDkk()
    {
        return $this->hasMany(DanaDkk::class, 'id_anggota', 'id');
    }

    /**
     * Relasi HasMany (Satu Anggota punya banyak Pinjaman)
     */
    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class, 'id_anggota', 'id');
    }

    /**
     * Relasi HasMany (Satu Anggota punya banyak riwayat Angsuran)
     */
    public function angsuran()
    {
        return $this->hasMany(PinjamanBayar::class, 'id_anggota', 'id');
    }
	
	public function agama()
    {
        // Parameter: (ModelTarget, foreign_key_di_tabel_anggota, owner_key_di_tabel_agama)
        return $this->belongsTo(Agama::class, 'agama_id', 'id');
    }
}
