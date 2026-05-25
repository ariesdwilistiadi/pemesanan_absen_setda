<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dinas', function (Blueprint $table) {
            $table->id();
            $table->string('nama_dinas'); // Kolom untuk menyimpan nama instansi/dinas
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dinas');
    }
};