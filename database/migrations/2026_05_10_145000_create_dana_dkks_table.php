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
        Schema::create('dana_dkk', function (Blueprint $table) {
            $table->increments('id_dana_dkk');
            $table->foreignId('id_anggota')->constrained('anggotas')->cascadeOnDelete();
            $table->integer('nominal');
            $table->text('sakit');
            $table->text('keterangan');
            $table->text('file_berkas');
            $table->date('tgl_sakit');
            $table->dateTime('tanggal_create');
            $table->integer('lama_sakit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dana_dkk');
    }
};
