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
        Schema::create('pinjaman_bayar', function (Blueprint $table) {
            $table->increments('id_pinjaman_bayar');
            $table->foreignId('id_anggota')->constrained('anggotas')->cascadeOnDelete();
            $table->unsignedInteger('id_pinjaman');
            $table->foreign('id_pinjaman')->references('id_pinjaman')->on('pinjaman')->cascadeOnDelete();
            $table->integer('bayar');
            $table->integer('bunga');
            $table->integer('denda');
            $table->date('tanggal_bayar');
            $table->string('username', 25);
            $table->dateTime('tanggal_create');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pinjaman_bayar');
    }
};
