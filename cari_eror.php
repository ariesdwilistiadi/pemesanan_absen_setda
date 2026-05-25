<?php

$dir = new RecursiveDirectoryIterator(__DIR__);
$ite = new RecursiveIteratorIterator($dir);
$files = new RegexIterator($ite, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

echo "Mencari penyebab eror 'Target class [env] does not exist'...\n\n";

$found = false;
foreach ($files as $file) {
    $path = $file[0];
    
    // Lewati folder vendor dan storage
    if (strpos($path, DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR) !== false || 
        strpos($path, DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR) !== false ||
        strpos($path, DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache') !== false) {
        continue;
    }

    $content = file_get_contents($path);
    
    // Mencari pemanggilan app('env')
    if (preg_match('/app\(\s*[\'"]env[\'"]\s*\)|app\[\s*[\'"]env[\'"]\s*\]|this->app\[\s*[\'"]env[\'"]\s*\]/', $content, $matches)) {
        echo "🚨 KETEMU! Ada pemanggilan " . $matches[0] . " di file:\n=> " . $path . "\n\n";
        $found = true;
    }
    
    // Mencari salah ketik parameter fungsi (misal: public function index(env $env))
    if (preg_match('/function\s+[a-zA-Z0-9_]+\s*\([^)]*\benv\s+\$[a-zA-Z0-9_]+/', $content, $matches)) {
        echo "🚨 KETEMU! Ada kesalahan penulisan 'env' sebagai parameter di file:\n=> " . $path . "\n\n";
        $found = true;
    }
}

if (!$found) echo "Pencarian otomatis selesai, tapi tidak ditemukan. Cobalah cari tulisan app('env') menggunakan fitur Global Search (Ctrl + Shift + F) di VS Code.\n";
