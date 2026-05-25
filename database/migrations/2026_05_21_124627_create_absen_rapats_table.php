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
        Schema::create('absen_rapats', function (Blueprint $table) {
            $table->id();
            $table->enum('tipe_peserta', ['internal', 'eksternal'])->default('internal'); 
            $table->string('nip')->nullable(); 
            $table->string('nama');
            $table->unsignedBigInteger('id_dinas')->nullable(); 
            $table->string('nama_external')->nullable();        
            $table->string('telp');
            $table->string('email');
            $table->text('signature'); 
            $table->date('tanggal_rapat');
            $table->time('jam_rapat');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('absen_rapats');
    }
};