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

// 3. --- HACK VERCEL EXODIA: Pindahkan folder storage & cache ke /tmp ---
$storage = '/tmp/storage';
$dirs = [
    'app',
    'framework/views',
    'framework/cache',
    'framework/cache/data',
    'framework/sessions',
    'logs',
    'bootstrap/cache'
];
foreach ($dirs as $dir) {
    if (!is_dir($storage . '/' . $dir)) {
        mkdir($storage . '/' . $dir, 0777, true);
    }
}
$app->useStoragePath($storage);

// Paksa rute cache dasar ke /tmp agar bebas dari kutukan Read-Only Vercel
putenv('APP_SERVICES_CACHE=' . $storage . '/bootstrap/cache/services.php');
putenv('APP_PACKAGES_CACHE=' . $storage . '/bootstrap/cache/packages.php');
putenv('APP_CONFIG_CACHE=' . $storage . '/bootstrap/cache/config.php');
putenv('APP_ROUTES_CACHE=' . $storage . '/bootstrap/cache/routes.php');
putenv('APP_EVENTS_CACHE=' . $storage . '/bootstrap/cache/events.php');
putenv('VIEW_COMPILED_PATH=' . $storage . '/framework/views');
// ----------------------------------------------------------------------

// 4. Jalankan aplikasi
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