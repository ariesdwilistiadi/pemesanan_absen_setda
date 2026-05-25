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
        Schema::create('produks', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique()->comment('Kode unik SKU barang');
            $table->string('nama_barang');
            $table->string('kategori')->nullable();
            $table->decimal('harga_beli', 15, 2)->default(0.00);
            $table->decimal('harga_jual', 15, 2)->default(0.00);
            $table->integer('stok')->default(0);
            $table->string('satuan')->default('Pcs')->comment('Misal: Pcs, Kg, Box');
            $table->text('deskripsi')->nullable();
            $table->string('gambar')->nullable()->comment('Lokasi path gambar produk');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produks');
    }
};
