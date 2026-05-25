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
        Schema::table('transaksi_headers', function (Blueprint $table) {
            $table->enum('metode_pembayaran', ['cash', 'qris'])->default('cash')->after('status')->comment('Metode pembayaran');
            $table->decimal('jumlah_bayar', 15, 2)->default(0.00)->after('metode_pembayaran')->comment('Jumlah uang yang dibayarkan');
            $table->decimal('kembalian', 15, 2)->default(0.00)->after('jumlah_bayar')->comment('Uang kembalian');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_headers', function (Blueprint $table) {
            $table->dropColumn(['metode_pembayaran', 'jumlah_bayar', 'kembalian']);
        });
    }
};
