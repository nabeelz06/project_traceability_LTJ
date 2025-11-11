namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Batch;
use App\Models\RfidWrite;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ScanApiController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }

    /**
     * Read RFID tag and return batch info
     */
    public function readTag(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
        ]);

        $batch = Batch::where('rfid_tag_uid', $validated['tag_uid'])
            ->with(['productCode', 'currentPartner', 'creator'])
            ->first();

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Tag tidak terdaftar',
                'error_code' => 'TAG_NOT_FOUND'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'batch_number' => $batch->batch_number,
                'lot_number' => $batch->lot_number,
                'product_code' => $batch->productCode->code,
                'product_description' => $batch->productCode->description,
                'container_number' => $batch->container_number,
                'weight' => $batch->estimated_weight . ' ' . $batch->weight_unit,
                'status' => $batch->status,
                'status_display' => $batch->getStatusDisplayName(),
                'current_partner' => $batch->currentPartner->name ?? null,
                'created_at' => $batch->created_at->toIso8601String(),
            ]
        ]);
    }

    /**
     * Process checkout via API
     */
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
            'operator_id' => 'required|exists:users,id',
            'gps_location' => 'nullable|string',
            'photo_base64' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        $batch = Batch::where('rfid_tag_uid', $validated['tag_uid'])->first();

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Tag tidak terdaftar',
            ], 404);
        }

        // Validate status
        if (!in_array($batch->status, ['ready_to_ship', 'created'])) {
            return response()->json([
                'success' => false,
                'message' => 'Batch tidak dapat dikirim. Status: ' . $batch->getStatusDisplayName(),
            ], 400);
        }

        // Handle photo upload from base64
        $photoPath = null;
        if (!empty($validated['photo_base64'])) {
            try {
                $image = base64_decode($validated['photo_base64']);
                $filename = 'checkout_' . time() . '.jpg';
                \Storage::disk('public')->put('checkout_photos/' . $filename, $image);
                $photoPath = 'checkout_photos/' . $filename;
            } catch (\Exception $e) {
                Log::error('Failed to save photo: ' . $e->getMessage());
            }
        }

        // Process checkout
        $result = $this->traceabilityService->processCheckout($batch, [
            'device_id' => $request->device->device_id,
            'gps_location' => $validated['gps_location'],
            'photo_path' => $photoPath,
            'notes' => $validated['notes'],
        ], $validated['operator_id']);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil',
                'data' => [
                    'batch_number' => $batch->batch_number,
                    'new_status' => 'shipped',
                    'timestamp' => now()->toIso8601String(),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
    }

    /**
     * Process checkin via API
     */
    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
            'operator_id' => 'required|exists:users,id',
            'partner_id' => 'nullable|exists:partners,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $batch = Batch::where('rfid_tag_uid', $validated['tag_uid'])->first();

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Tag tidak terdaftar',
            ], 404);
        }

        // Process checkin
        $result = $this->traceabilityService->processCheckin(
            $batch,
            $validated['partner_id'] ?? null,
            [
                'device_id' => $request->device->device_id,
                'notes' => $validated['notes'],
            ],
            $validated['operator_id']
        );

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil',
                'data' => [
                    'batch_number' => $batch->batch_number,
                    'new_status' => 'received',
                    'timestamp' => now()->toIso8601String(),
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
    }

    /**
     * Generate payload for RFID write
     */
    public function generatePayload(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
        ]);

        $batch = Batch::with('productCode')->find($validated['batch_id']);

        $payload = [
            'batch_number' => $batch->batch_number,
            'lot_number' => $batch->lot_number,
            'product_code' => $batch->productCode->code,
            'container_number' => $batch->container_number,
            'issued_at' => now()->toIso8601String(),
        ];

        $signature = hash_hmac('sha256', json_encode($payload), config('app.key'));

        return response()->json([
            'success' => true,
            'payload' => $payload,
            'signature' => $signature,
        ]);
    }

    /**
     * Write RFID tag
     */
    public function writeTag(Request $request)
    {
        $validated = $request->validate([
            'batch_id' => 'required|exists:batches,id',
            'tag_uid' => 'required|string',
            'operator_id' => 'required|exists:users,id',
        ]);

        // Check if tag already used
        $existingBatch = Batch::where('rfid_tag_uid', $validated['tag_uid'])->first();
        if ($existingBatch) {
            return response()->json([
                'success' => false,
                'message' => 'Tag UID sudah terdaftar pada batch: ' . $existingBatch->batch_number,
            ], 400);
        }

        $batch = Batch::with('productCode')->find($validated['batch_id']);

        // Generate payload
        $payload = [
            'batch_number' => $batch->batch_number,
            'lot_number' => $batch->lot_number,
            'product_code' => $batch->productCode->code,
            'container_number' => $batch->container_number,
            'issued_at' => now()->toIso8601String(),
        ];

        $signature = hash_hmac('sha256', json_encode($payload), config('app.key'));

        // Save RFID write record
        RfidWrite::create([
            'batch_id' => $batch->id,
            'tag_uid' => $validated['tag_uid'],
            'payload' => json_encode($payload),
            'signature' => $signature,
            'device_id' => $request->device->device_id,
            'written_by_user_id' => $validated['operator_id'],
            'verified' => false,
        ]);

        // Update batch
        $batch->update(['rfid_tag_uid' => $validated['tag_uid']]);

        return response()->json([
            'success' => true,
            'message' => 'RFID tag berhasil ditulis',
            'data' => [
                'batch_number' => $batch->batch_number,
                'tag_uid' => $validated['tag_uid'],
                'payload' => $payload,
                'signature' => $signature,
            ]
        ]);
    }

    /**
     * Verify RFID tag
     */
    public function verifyTag(Request $request)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
            'read_payload' => 'required|string',
        ]);

        $rfidWrite = RfidWrite::where('tag_uid', $validated['tag_uid'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$rfidWrite) {
            return response()->json([
                'success' => false,
                'message' => 'Tag tidak ditemukan dalam database',
            ], 404);
        }

        // Verify payload matches
        $isValid = ($rfidWrite->payload === $validated['read_payload']);

        if ($isValid) {
            $rfidWrite->update(['verified' => true]);
        }

        return response()->json([
            'success' => true,
            'verified' => $isValid,
            'message' => $isValid ? 'Tag terverifikasi' : 'Payload tidak cocok',
        ]);
    }

    /**
     * Batch sync for offline operations
     */
    public function batchSync(Request $request)
    {
        $validated = $request->validate([
            'scans' => 'required|array',
            'scans.*.tag_uid' => 'required|string',
            'scans.*.action' => 'required|in:checkout,checkin',
            'scans.*.operator_id' => 'required|exists:users,id',
            'scans.*.timestamp' => 'required|date',
            'scans.*.gps_location' => 'nullable|string',
            'scans.*.notes' => 'nullable|string',
        ]);

        $synced = [];
        $failed = [];

        foreach ($validated['scans'] as $scan) {
            $batch = Batch::where('rfid_tag_uid', $scan['tag_uid'])->first();
            
            if (!$batch) {
                $failed[] = [
                    'tag_uid' => $scan['tag_uid'],
                    'reason' => 'Batch tidak ditemukan'
                ];
                continue;
            }

            try {
                if ($scan['action'] === 'checkout') {
                    $result = $this->traceabilityService->processCheckout($batch, $scan, $scan['operator_id']);
                } else {
                    $result = $this->traceabilityService->processCheckin($batch, null, $scan, $scan['operator_id']);
                }

                if ($result['success']) {
                    $synced[] = [
                        'tag_uid' => $scan['tag_uid'],
                        'batch_number' => $batch->batch_number,
                        'action' => $scan['action'],
                    ];
                } else {
                    $failed[] = [
                        'tag_uid' => $scan['tag_uid'],
                        'reason' => $result['message']
                    ];
                }
            } catch (\Exception $e) {
                $failed[] = [
                    'tag_uid' => $scan['tag_uid'],
                    'reason' => $e->getMessage()
                ];
            }
        }

        return response()->json([
            'success' => true,
            'synced' => $synced,
            'failed' => $failed,
            'summary' => [
                'total' => count($validated['scans']),
                'synced_count' => count($synced),
                'failed_count' => count($failed),
            ]
        ]);
    }

    /**
     * Get batch info by tag UID
     */
    public function getBatchByTag($tagUid)
    {
        $batch = Batch::where('rfid_tag_uid', $tagUid)
            ->with(['productCode', 'currentPartner', 'creator', 'parentBatch'])
            ->first();

        if (!$batch) {
            return response()->json([
                'success' => false,
                'message' => 'Batch tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $batch,
        ]);
    }

    /**
     * N8N Webhook endpoint
     */
    public function n8nWebhook(Request $request)
    {
        // Log webhook data
        Log::info('N8N Webhook received', $request->all());

        // Process webhook data based on type
        $type = $request->input('type');

        switch ($type) {
            case 'rfid_scan':
                return $this->handleRfidScan($request);
            case 'batch_update':
                return $this->handleBatchUpdate($request);
            default:
                return response()->json([
                    'success' => true,
                    'message' => 'Webhook received'
                ]);
        }
    }

    private function handleRfidScan($request)
    {
        // Handle RFID scan from N8N automation
        $tagUid = $request->input('tag_uid');
        $action = $request->input('action');
        
        // Process based on action
        // Implementation depends on your N8N workflow
        
        return response()->json([
            'success' => true,
            'message' => 'RFID scan processed'
        ]);
    }

    private function handleBatchUpdate($request)
    {
        // Handle batch update from N8N automation
        // Implementation depends on your N8N workflow
        
        return response()->json([
            'success' => true,
            'message' => 'Batch update processed'
        ]);
    }
}