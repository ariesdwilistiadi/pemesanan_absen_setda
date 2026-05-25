<?php

echo "Mencari penyebab eror ke SELURUH sistem (termasuk folder vendor)...\n\n";

$dir = new RecursiveDirectoryIterator(__DIR__, RecursiveDirectoryIterator::SKIP_DOTS);
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

$found = false;
foreach ($files as $file) {
    $path = $file[0];
    
    // Abaikan file script pencari itu sendiri
    if (strpos($path, 'cari_eror') !== false) {
        continue;
    }

    $content = file_get_contents($path);
    
    // Cek pemanggilan string env yang menyebabkan eror
    $patterns = [
        "app('env')", 'app("env")', 
        "app['env']", 'app["env"]', 
        "\$this->app['env']", "\$this->app[\"env\"]",
        "make('env')", 'make("env")'
    ];
    
    foreach ($patterns as $p) {
        if (strpos($content, $p) !== false) {
            echo "🚨 KETEMU! Ada pemanggilan [$p] di file:\n=> $path\n\n";
            $found = true;
            break;
        }
    }
}

if (!$found) echo "Pencarian selesai, tidak ditemukan.\n";
echo "Selesai.\n";
