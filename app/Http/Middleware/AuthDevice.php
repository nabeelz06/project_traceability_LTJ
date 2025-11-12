<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Device;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk autentikasi device (RFID Scanner/Writer)
 * Validasi X-Device-ID header dan device status
 */
class AuthDevice
{
    /**
     * Handle an incoming request untuk device authentication
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Get device ID dari header
        $deviceId = $request->header('X-Device-ID');

        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID tidak ditemukan. Sertakan header X-Device-ID',
                'error_code' => 'DEVICE_ID_MISSING'
            ], 401);
        }

        // Cari device di database
        $device = Device::where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak terdaftar atau tidak aktif',
                'error_code' => 'DEVICE_NOT_FOUND'
            ], 401);
        }

        // Update last seen
        $device->update(['last_seen_at' => now()]);

        // Attach device ke request untuk digunakan di controller
        $request->merge(['device' => $device]);

        return $next($request);
    }
}