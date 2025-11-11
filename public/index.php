<?php

use Illuminate\Contracts\Http\Kernel; 
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Register autoloader
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__.'/../bootstrap/app.php';

// Handle request
$kernel = $app->make(Kernel::class);

$response = $kernel->handle(
    $request = Request::capture()
)->send();

$kernel->terminate($request, $response);