<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

Schema::table('instansi_profiles', function (Blueprint $table) {
    if (!Schema::hasColumn('instansi_profiles', 'qris_image')) {
        $table->string('qris_image')->nullable()->after('logo');
        echo "Column qris_image added.\n";
    } else {
        echo "Column qris_image already exists.\n";
    }
});
