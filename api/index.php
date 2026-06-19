<?php

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

// 4. Jalankan aplikasi berdasarkan versi Laravel
if (method_exists($app, 'handleRequest')) {
    // Untuk Laravel 11
    $app->handleRequest(Illuminate\Http\Request::capture());
} else {
    // Untuk Laravel 10 ke bawah
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    $response = $kernel->handle(
        $request = Illuminate\Http\Request::capture()
    );
    $response->send();
    $kernel->terminate($request, $response);
}