<?php

define('LARAVEL_START', microtime(true));

// --- Arahkan Vercel ke folder instalasi Laravel yang benar ---
// Vercel menjalankan ini dari /api, jadi kita harus 'naik' satu level
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

// --- Tangani Request ---
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$response->send();
$kernel->terminate($request, $response);