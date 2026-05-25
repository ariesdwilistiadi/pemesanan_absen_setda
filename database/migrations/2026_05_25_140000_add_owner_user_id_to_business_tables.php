<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['anggotas', 'produks', 'dana_dkk', 'pinjaman', 'pinjaman_bayar', 'transaksi_headers', 'absen_rapats'] as $table) {
            Schema::table($table, function (Blueprint $blueprint) use ($table) {
                if (! Schema::hasColumn($table, 'owner_user_id')) {
                    $blueprint->unsignedBigInteger('owner_user_id')->nullable()->index();
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['anggotas', 'produks', 'dana_dkk', 'pinjaman', 'pinjaman_bayar', 'transaksi_headers', 'absen_rapats'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (Schema::hasColumn($tableName, 'owner_user_id')) {
                    $table->dropColumn('owner_user_id');
                }
            });
        }
    }
};
