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
        Schema::table('instansi_profiles', function (Blueprint $table) {
            $table->string('pemerintah', 255)->nullable()->after('logo');
            $table->string('nama_instansi', 255)->nullable()->after('pemerintah');
            $table->text('alamat')->nullable()->after('nama_instansi');
            $table->string('kontak', 255)->nullable()->after('alamat');
            $table->string('nama_kepala', 255)->nullable()->after('kontak');
            $table->string('jabatan_kepala', 255)->nullable()->after('nama_kepala');
            $table->string('nip_kepala', 50)->nullable()->after('jabatan_kepala');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instansi_profiles', function (Blueprint $table) {
            $table->dropColumn(['pemerintah', 'nama_instansi', 'alamat', 'kontak', 'nama_kepala', 'jabatan_kepala', 'nip_kepala']);
        });
    }
};
