<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DeviceApiController;
use App\Http\Controllers\Api\ScanApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rute-rute ini untuk device eksternal (RFID Scanner/Writer)
| dan otentikasi Sanctum (jika ada mobile app).
|
*/

// Rute default Sanctum (jika diperlukan untuk mobile app)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// === Rute Otentikasi Device ===
// Rute ini tidak dilindungi middleware 'auth.device' karena ini adalah
// rute untuk mendapatkan token/sesi.
Route::post('/device/authenticate', [DeviceApiController::class, 'authenticate'])
    ->name('api.device.authenticate');


// === Rute Operasi Device (RFID) ===
// Rute-rute ini harus dilindungi oleh middleware 'auth.device'
// yang akan memverifikasi X-Device-ID dan token.
Route::middleware('auth.device')->group(function () {
    
    // Mendapatkan detail batch dari TAG UID (curl /api/scan/read)
    Route::post('/scan/read', [ScanApiController::class, 'readTag'])
        ->name('api.scan.read');
    
    // Melakukan Check-out (Operator Gudang) (curl /api/scan/checkout)
    Route::post('/scan/checkout', [ScanApiController::class, 'checkout'])
        ->name('api.scan.checkout');
    
    // Melakukan Check-in (Mitra)
    Route::post('/scan/checkin', [ScanApiController::class, 'checkin'])
        ->name('api.scan.checkin');
    
    // Verifikasi RFID (Admin)
    Route::post('/scan/verify', [ScanApiController::class, 'verifyTag'])
        ->name('api.scan.verify');
});