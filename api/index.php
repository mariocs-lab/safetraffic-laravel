<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Cek status maintenance
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// 1. Muat autoloader Composer
require __DIR__ . '/../vendor/autoload.php';

// 2. Panggil bootstrap Laravel (membuat instance $app)
$app = require_once __DIR__ . '/../bootstrap/app.php';

// 3. --- HACK VERCEL: Pindahkan folder storage ke /tmp ---
$storage = '/tmp/storage';
$dirs = ['app', 'framework/views', 'framework/cache', 'framework/sessions', 'logs'];
foreach ($dirs as $dir) {
    if (!is_dir($storage . '/' . $dir)) {
        mkdir($storage . '/' . $dir, 0777, true);
    }
}
$app->useStoragePath($storage);
// ------------------------------------------------------

// 4. Jalankan aplikasi (Mendukung Laravel 10 & 11)
if (method_exists($app, 'handleRequest')) {
    $app->handleRequest(Request::capture());
} else {
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);
}