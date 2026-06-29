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
        Schema::create('notulen_rapats', function (Blueprint $table) {
            $table->id(); // Membuat 'id' bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT
            $table->unsignedBigInteger('absen_rapat_id');
            $table->unsignedBigInteger('ketua_id')->nullable();
            $table->unsignedBigInteger('sekretaris_id')->nullable();
            $table->unsignedBigInteger('pencatat_id')->nullable();
            
            $table->text('pembukaan')->nullable();
            $table->text('pembahasan')->nullable();
            $table->text('peraturan')->nullable();
            $table->text('penutup')->nullable();
            
            $table->enum('peserta_mode', ['terlampir', 'manual'])->default('terlampir');
            
            $table->timestamps(); // Otomatis membuat 'created_at' dan 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notulen_rapats');
    }
};