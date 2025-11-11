<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\ProductCode;
use App\Models\Partner;
use App\Models\RfidWrite;
use App\Models\Document;
use App\Services\TraceabilityService; // <-- Pastikan ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }
    
    public function index(Request $request)
    {
        $query = Batch::with(['productCode', 'creator', 'currentPartner', 'parentBatch']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_code')) {
            $query->where('product_code', $request->product_code);
        }

        if ($request->filled('partner_id')) {
            $query->where('current_owner_partner_id', $request->partner_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('batch_code', 'like', "%$search%")
                  ->orWhere('lot_number', 'like', "%$search%")
                  ->orWhere('container_code', 'like', "%$search%");
            });
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20);

        $productcodes = ProductCode::orderBy('code')->get();
        $partners = Partner::approved()->orderBy('name')->get();

        return view('batches.index', compact('batches', 'productcodes', 'partners'));
    }

    public function create()
    {
        $productcodes = ProductCode::where('stage', 'TIM')->orderBy('code')->get();
        return view('batches.create', compact('productcodes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code' => 'required|string|exists:product_codes,code',
            'initial_weight' => 'required|numeric|min:0.1',
            'weight_unit' => 'required|string',
            'container_code' => 'nullable|string|max:255|unique:batches,container_code',
            'current_location' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        DB::beginTransaction();
        try {
            $validated['batch_code'] = Batch::generateBatchNumber(); // Asumsi method ini ada di Model
            $validated['lot_number'] = Batch::generateLotNumber($validated['batch_code']); // Asumsi method ini ada di Model

            if (empty($validated['container_code'])) {
                $validated['container_code'] = 'K-TMH-' . str_pad(Batch::count() + 1, 4, '0', STR_PAD_LEFT);
            }

            $batchData = array_merge($validated, [
                'created_by_user_id' => Auth::id(),
                'current_weight' => $validated['initial_weight'],
                'status' => 'created',
            ]);

            $batch = Batch::create($batchData);

            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'created',
                'new_status' => 'created',
                'actor_user_id' => Auth::id(),
                'notes' => 'Batch induk dibuat oleh ' . Auth::user()->name,
            ]);

            DB::commit();

            return redirect()->route('batches.show', $batch)
                ->with('success', "Batch {$batch->batch_code} berhasil dibuat. Silakan tulis ke RFID tag.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal membuat batch: ' . $e->getMessage());
        }
    }

    public function show(Batch $batch)
    {
        $user = Auth::user();

        if ($user->role === 'mitra_middlestream' || $user->role === 'mitra_downstream') {
             if ($batch->current_owner_partner_id !== $user->partner_id) {
                // abort(403, 'Anda tidak memiliki akses ke batch ini.');
             }
        }

        $batch->load([
            'productCode',
            'parentBatch.productCode',
            'childBatches.productCode',
            'creator',
            'currentPartner',
            'logs.actor',
            'documents',
        ]);

        $logs = $batch->logs()->orderBy('created_at', 'desc')->get();
        $traceTree = $this->buildTraceTree($batch);

        return view('batches.show', compact('batch', 'traceTree', 'logs'));
    }

    public function update(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'current_weight' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        $oldWeight = $batch->current_weight;
        $batch->update($validated);

        BatchLog::create([
            'batch_id' => $batch->id,
            'action' => 'updated',
            'actor_user_id' => Auth::id(),
            'notes' => "Metadata diupdate. Berat: $oldWeight -> {$batch->current_weight}",
        ]);

        return back()->with('success', 'Batch berhasil diupdate.');
    }

    public function markReady(Batch $batch)
    {
        if (!in_array($batch->status, ['created'])) {
            return back()->with('error', 'Batch tidak dapat diubah statusnya.');
        }

        $oldStatus = $batch->status;
        $batch->update(['status' => 'ready_to_ship']);

        BatchLog::create([
            'batch_id' => $batch->id,
            'action' => 'status_updated',
            'previous_status' => $oldStatus,
            'new_status' => 'ready_to_ship',
            'actor_user_id' => Auth::id(),
            'notes' => 'Batch ditandai siap kirim',
        ]);

        return back()->with('success', 'Batch ditandai siap untuk dikirim.');
    }

    public function writeRfid(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'tag_uid' => 'required|string|unique:batches,rfid_tag_uid',
        ]);

        $payload = [
            'batch_code' => $batch->batch_code,
            'lot_number' => $batch->lot_number,
            'product_code' => $batch->productCode->code,
            'container_code' => $batch->container_code,
            'issued_at' => now()->toIso8601String(),
        ];

        $signature = hash_hmac('sha256', json_encode($payload), config('app.key'));

        RfidWrite::create([
            'batch_id' => $batch->id,
            'tag_uid' => $validated['tag_uid'],
            'device_id' => $validated['device_id'], // <-- Ini perbaikannya
            'user_id' => Auth::id(),
            'is_success' => true, 
        ]);

        $batch->update(['rfid_tag_uid' => $validated['tag_uid']]);

        BatchLog::create([
            'batch_id' => $batch->id,
            'action' => 'rfid_written',
            'new_status' => $batch->status,
            'actor_user_id' => Auth::id(),
            'device_id' => 1, // Ganti dengan ID device yang benar
            'notes' => 'RFID tag ditulis dengan UID: ' . $validated['tag_uid'],
        ]);

        return response()->json([
            'success' => true,
            'payload' => $payload,
            'signature' => $signature,
            'message' => 'RFID tag berhasil ditulis',
        ]);
    }

    public function verifyRfid(Request $request, Batch $batch)
    {
        // Implementasi logika verifikasi
    }

    public function correct(Request $request, Batch $batch)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:created,ready_to_ship,shipped,received,processed,delivered,quarantine',
            'correction_reason' => 'required|string|max:1000',
        ]);

        $oldStatus = $batch->status;
        $batch->update(['status' => $validated['status']]);

        BatchLog::create([
            'batch_id' => $batch->id,
            'action' => 'corrected',
            'previous_status' => $oldStatus,
            'new_status' => $validated['status'],
            'actor_user_id' => Auth::id(),
            'notes' => 'Koreksi manual oleh Super Admin: ' . $validated['correction_reason'],
        ]);

        return back()->with('success', 'Batch berhasil dikoreksi.');
    }
    
    public function downstreamBatches()
    {
        $user = auth()->user();
        
        $batches = Batch::where('current_owner_partner_id', $user->partner_id)
            ->with(['productCode', 'parentBatch'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('downstream.batches.index', compact('batches'));
    }

    public function auditorBatches(Request $request)
    {
        // Cukup panggil index utama karena auditor melihat semua
        return $this->index($request);
    }
    
    public function auditorBatchDetail(Batch $batch)
    {
        // Cukup panggil show utama karena auditor melihat semua
        return $this->show($batch);
    }

    private function buildTraceTree(Batch $batch, $depth = 0)
    {
        $tree = [
            'batch' => $batch,
            'depth' => $depth,
            'children' => [],
        ];

        if ($batch->childBatches->count() > 0) {
            foreach ($batch->childBatches as $child) {
                $tree['children'][] = $this->buildTraceTree($child, $depth + 1);
            }
        }

        return $tree;
    }
}