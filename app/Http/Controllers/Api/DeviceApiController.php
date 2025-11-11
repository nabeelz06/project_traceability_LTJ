namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeviceApiController extends Controller
{
    /**
     * Authenticate device
     */
    public function authenticate(Request $request)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'device_secret' => 'nullable|string',
        ]);

        $device = Device::where('device_id', $validated['device_id'])
            ->where('is_active', true)
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Device tidak terdaftar atau tidak aktif',
            ], 401);
        }

        // Update last seen
        $device->update(['last_seen_at' => now()]);

        // Generate token untuk device (menggunakan Sanctum)
        // Untuk device, kita bisa buat dummy user atau gunakan device_id sebagai token
        $token = base64_encode($device->device_id . ':' . now()->timestamp);

        return response()->json([
            'success' => true,
            'message' => 'Device authenticated',
            'data' => [
                'device_id' => $device->device_id,
                'device_name' => $device->device_name,
                'type' => $device->type,
                'token' => $token,
                'expires_at' => now()->addHours(24)->toIso8601String(),
            ]
        ]);
    }

    /**
     * Device heartbeat
     */
    public function heartbeat(Request $request)
    {
        $device = $request->device;
        
        $device->update(['last_seen_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Heartbeat received',
            'server_time' => now()->toIso8601String(),
        ]);
    }
}