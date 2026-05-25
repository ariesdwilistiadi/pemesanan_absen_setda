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
        Schema::create('transaksi_headers', function (Blueprint $table) {
            $table->id();
            $table->string('no_transaksi')->unique()->comment('Nomor unik transaksi, misal: TRX-20231001-001');
            $table->unsignedBigInteger('id_absen_rapats')->index()->comment('Relasi ke tabel absen_rapats');
            $table->string('nip')->nullable()->comment('NIP pegawai jika internal');
            $table->string('nama')->comment('Nama peserta');
            $table->string('nomor_meja', 50)->nullable()->comment('Nomor atau nama meja peserta');
            $table->dateTime('tanggal_transaksi');
            $table->integer('total_item')->default(0)->comment('Total jumlah barang yang diambil');
            $table->decimal('total_harga', 15, 2)->default(0.00)->comment('Grand total harga');
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_headers');
    }
};
