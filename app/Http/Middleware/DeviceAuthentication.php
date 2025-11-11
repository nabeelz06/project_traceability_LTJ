<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Device; // <-- WAJIB

class DeviceAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $deviceId = $request->header('X-Device-ID');
        
        if (!$deviceId) {
            return response()->json([
                'success' => false,
                'message' => 'Device ID required'
            ], 401);
        }

        $device = Device::where('device_id', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device not registered or inactive'
            ], 401);
        }

        $device->update(['last_seen_at' => now()]);
        $request->merge(['device' => $device]);

        return $next($request);
    }
}