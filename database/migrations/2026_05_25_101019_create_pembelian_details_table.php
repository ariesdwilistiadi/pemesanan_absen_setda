<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembelian_details', function (Blueprint $table) {
            $table->id();
            
            // Foreign key ke tabel header
            $table->unsignedBigInteger('pembelian_header_id');
            
            $table->unsignedBigInteger('barang_id'); // Relasi ke tabel master barang
            $table->integer('kuantitas');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('subtotal', 15, 2); // kuantitas * harga_satuan
            $table->timestamps();

            // Constraint relasi
            $table->foreign('pembelian_header_id')
                  ->references('id')
                  ->on('pembelian_headers')
                  ->onDelete('cascade'); // Jika header dihapus, detail otomatis terhapus
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembelian_details');
    }
};