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
        Schema::create('transaksi_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaksi_header_id')->index()->comment('Relasi ke tabel transaksi_headers');
            $table->unsignedBigInteger('produk_id')->index()->comment('Relasi ke tabel produks');
            $table->integer('jumlah')->default(1)->comment('Qty barang yang diambil');
            $table->decimal('harga_satuan', 15, 2)->default(0.00)->comment('Harga barang saat transaksi terjadi');
            $table->decimal('subtotal', 15, 2)->default(0.00)->comment('jumlah x harga_satuan');
            $table->timestamps();

            $table->foreign('transaksi_header_id', 'fk_transaksi_header')
                  ->references('id')->on('transaksi_headers')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_details');
    }
};
