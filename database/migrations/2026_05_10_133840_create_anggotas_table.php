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
        Schema::create('anggotas', function (Blueprint $table) {
            $table->id();
            $table->string('no_anggota')->unique();
            $table->string('nama', 100);
            $table->string('no_identitas', 25);
            $table->string('tempat_lahir', 50);
            $table->date('tgl_lahir');
            $table->text('alamat');
            $table->enum('jenis_kelamin', ['laki-laki', 'perempuan']);
            $table->string('no_telp', 17);
            $table->foreignId('agama_id')->constrained('agamas')->onDelete('cascade');
            $table->string('pekerjaan', 255);
            $table->date('tgl_masuk');
            $table->integer('simpanan_pokok');
            $table->integer('simpanan_wajib');
            $table->integer('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggotas');
    }
};
