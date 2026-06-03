<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('absensi_pesertas', function (Blueprint $table) {
            // Menambahkan kolom jabatan setelah kolom nama (atau sesuaikan posisinya)
            $table->string('jabatan')->nullable()->after('nama'); 
        });
    }

    public function down()
    {
        Schema::table('absensi_pesertas', function (Blueprint $table) {
            // Membatalkan (drop) kolom jika di-rollback
            $table->dropColumn('jabatan');
        });
    }
}; 