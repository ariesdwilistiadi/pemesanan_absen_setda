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
        // Buat table ruangans
        Schema::create('ruangans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_ruangan')->unique();
            $table->text('keterangan')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tambah field id_ruangan ke transaksi_headers
        if (!Schema::hasColumn('transaksi_headers', 'id_ruangan')) {
            Schema::table('transaksi_headers', function (Blueprint $table) {
                $table->unsignedBigInteger('id_ruangan')->nullable()->after('nomor_meja');
                $table->foreign('id_ruangan')->references('id')->on('ruangans')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_headers', function (Blueprint $table) {
            $table->dropForeignKeyIfExists('transaksi_headers_id_ruangan_foreign');
            $table->dropColumn('id_ruangan');
        });

        Schema::dropIfExists('ruangans');
    }
};
