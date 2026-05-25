<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pinjaman', function (Blueprint $table) {
            $table->increments('id_pinjaman');
            $table->foreignId('id_anggota')->constrained('anggotas')->cascadeOnDelete();
            $table->integer('jumlah_pinjaman')->nullable();
            $table->integer('jasa');
            $table->integer('jumlah_angsuran');
            $table->date('jangka_waktu');
            $table->string('nama');
            $table->date('tgl_pinjaman');
            $table->dateTime('tanggal_create');
            $table->string('username', 25);
            $table->integer('id_jenis_pinjaman');
            $table->integer('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjaman');
    }
};
