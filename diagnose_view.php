<?php
require __DIR__ . '/vendor/autoload.php';

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['HTTP_HOST'] = 'localhost';

$app = require __DIR__ . '/bootstrap/app.php';

try {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo 'kernel loaded\n';
    echo 'bootstrapped before=' . (int) $app->hasBeenBootstrapped() . '\n';
    $kernel->bootstrap();
    echo 'bootstrapped after=' . (int) $app->hasBeenBootstrapped() . '\n';
    echo 'has view after bootstrap=' . (int) $app->has('view') . '\n';
    $response = $kernel->handle(Illuminate\Http\Request::capture());
    echo 'response status=' . $response->getStatusCode() . '\n';
} catch (Throwable $e) {
    echo get_class($e) . ': ' . $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
