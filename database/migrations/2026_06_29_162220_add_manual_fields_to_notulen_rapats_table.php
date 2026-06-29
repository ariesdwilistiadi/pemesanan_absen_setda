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
        Schema::table('notulen_rapats', function (Blueprint $table) {
            $table->string('ketua_manual', 255)->nullable()->after('pencatat_id');
            $table->string('sekretaris_manual', 255)->nullable()->after('ketua_manual');
            $table->string('pencacat_manual', 255)->nullable()->after('sekretaris_manual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notulen_rapats', function (Blueprint $table) {
            $table->dropColumn(['ketua_manual', 'sekretaris_manual', 'pencacat_manual']);
        });
    }
};
