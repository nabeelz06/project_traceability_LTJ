<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\ProductCode;
use App\Models\Partner;
use App\Models\RfidWrite;
use App\Services\TraceabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BatchController extends Controller
{
    protected $traceabilityService;

    public function __construct(TraceabilityService $traceabilityService)
    {
        $this->traceabilityService = $traceabilityService;
    }
    
    /**
     * Tampilkan daftar batch dengan filter dan pagination
     */
    public function index(Request $request)
    {
        $query = Batch::with(['productCode', 'creator', 'currentPartner', 'parentBatch']);

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan product code
        if ($request->filled('product_code')) {
            $query->where('product_code', $request->product_code);
        }

        // Filter berdasarkan partner pemilik
        if ($request->filled('partner_id')) {
            $query->where('current_owner_partner_id', $request->partner_id);
        }

        // Filter berdasarkan tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search batch code, lot, container
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(20);
        $productcodes = ProductCode::orderBy('code')->get();
        $partners = Partner::approved()->orderBy('name')->get();

        return view('batches.index', compact('batches', 'productcodes', 'partners'));
    }

    /**
     * Tampilkan form buat batch induk baru
     */
    public function create()
    {
        // Hanya admin dan super admin yang bisa buat batch induk
        if (!Auth::user()->canCreateParentBatch()) {
            abort(403, 'Anda tidak memiliki akses untuk membuat batch induk.');
        }

        $productCodes = ProductCode::where('stage', 'RAW')->orderBy('code')->get();
        $partners = Partner::approved()->orderBy('name')->get();

        return view('batches.create', compact('productCodes', 'partners'));
    }

    /**
     * Simpan batch induk baru
     */
    public function store(Request $request)
    {
        if (!Auth::user()->canCreateParentBatch()) {
            abort(403);
        }

        $validated = $request->validate([
            'product_code' => 'required|exists:product_codes,code',
            'container_code' => 'required|string|unique:batches,container_code',
            'initial_weight' => 'required|numeric|min:0.1',
            'weight_unit' => 'required|in:kg,ton',
            'current_location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            // Buat batch baru (batch_code auto-generated di Model)
            $batch = Batch::create([
                'product_code' => $validated['product_code'],
                'container_code' => $validated['container_code'],
                'initial_weight' => $validated['initial_weight'],
                'current_weight' => $validated['initial_weight'],
                'weight_unit' => $validated['weight_unit'],
                'current_location' => $validated['current_location'],
                'status' => 'created',
                'created_by_user_id' => Auth::id(),
                'is_ready' => false,
            ]);

            // Log pembuatan batch
            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'created',
                'new_status' => 'created',
                'actor_user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? 'Batch induk dibuat',
            ]);

            DB::commit();
            return redirect()->route('batches.show', $batch)
                ->with('success', 'Batch ' . $batch->batch_code . ' berhasil dibuat.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal membuat batch: ' . $e->getMessage());
        }
    }

    /**
     * Tampilkan detail batch dengan traceability tree
     */
    public function show(Batch $batch)
    {
        $batch->load(['productCode', 'creator', 'currentPartner', 'parentBatch', 'childBatches', 'logs.actor', 'documents', 'rfidWrites']);
        
        // Build traceability tree
        $tree = $this->traceabilityService->buildFullTree($batch);
        
        return view('batches.show', compact('batch', 'tree'));
    }

    /**
     * Tampilkan form edit batch
     */
    public function edit(Batch $batch)
    {
        if (!$batch->canBeEdited()) {
            return back()->with('error', 'Batch tidak dapat diedit pada status: ' . $batch->getStatusLabel());
        }

        $productCodes = ProductCode::orderBy('code')->get();
        $partners = Partner::approved()->orderBy('name')->get();

        return view('batches.edit', compact('batch', 'productCodes', 'partners'));
    }

    /**
     * Update data batch
     */
    public function update(Request $request, Batch $batch)
    {
        if (!$batch->canBeEdited()) {
            return back()->with('error', 'Batch tidak dapat diedit.');
        }

        $validated = $request->validate([
            'container_code' => 'required|string|unique:batches,container_code,' . $batch->id,
            'current_weight' => 'required|numeric|min:0',
            'weight_unit' => 'required|in:kg,ton',
            'current_location' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $batch->update($validated);

            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'updated',
                'actor_user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? 'Data batch diperbarui',
            ]);

            DB::commit();
            return redirect()->route('batches.show', $batch)
                ->with('success', 'Batch berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()
                ->with('error', 'Gagal memperbarui batch: ' . $e->getMessage());
        }
    }

    /**
     * Mark batch sebagai ready to ship
     */
    public function markReady(Batch $batch)
    {
        if (!in_array($batch->status, ['created', 'received'])) {
            return back()->with('error', 'Batch tidak dapat ditandai siap kirim.');
        }

        DB::beginTransaction();
        try {
            $oldStatus = $batch->status;
            
            $batch->update([
                'status' => 'ready_to_ship',
                'is_ready' => true,
            ]);

            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'status_updated',
                'previous_status' => $oldStatus,
                'new_status' => 'ready_to_ship',
                'actor_user_id' => Auth::id(),
                'notes' => 'Batch ditandai siap untuk dikirim',
            ]);

            DB::commit();
            return back()->with('success', 'Batch berhasil ditandai siap kirim.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Write RFID tag untuk batch
     */
    public function writeRfid(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'device_id' => 'required|string',
            'tag_uid' => 'required|string|unique:batches,rfid_tag_uid',
        ]);

        DB::beginTransaction();
        try {
            // Generate payload untuk RFID
            $payload = [
                'batch_code' => $batch->batch_code,
                'lot_number' => $batch->lot_number,
                'product_code' => $batch->product_code,
                'container_code' => $batch->container_code,
                'issued_at' => now()->toIso8601String(),
            ];

            $signature = hash_hmac('sha256', json_encode($payload), config('app.key'));

            // Simpan record RFID write
            RfidWrite::create([
                'batch_id' => $batch->id,
                'tag_uid' => $validated['tag_uid'],
                'device_id' => $validated['device_id'],
                'user_id' => Auth::id(),
                'payload' => json_encode($payload),
                'signature' => $signature,
                'is_success' => true,
            ]);

            // Update batch dengan RFID tag
            $batch->update(['rfid_tag_uid' => $validated['tag_uid']]);

            // Log aktivitas
            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'rfid_written',
                'actor_user_id' => Auth::id(),
                'notes' => 'RFID tag ditulis: ' . $validated['tag_uid'],
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'payload' => $payload,
                'signature' => $signature,
                'message' => 'RFID tag berhasil ditulis',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Verifikasi RFID tag
     */
    public function verifyRfid(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'tag_uid' => 'required|string',
            'read_payload' => 'required|string',
        ]);

        $rfidWrite = RfidWrite::where('batch_id', $batch->id)
            ->where('tag_uid', $validated['tag_uid'])
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$rfidWrite) {
            return response()->json([
                'success' => false,
                'message' => 'Tag tidak ditemukan untuk batch ini',
            ], 404);
        }

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
     * Koreksi data batch (Super Admin only)
     */
    public function correct(Request $request, Batch $batch)
    {
        if (!Auth::user()->canCorrectBatch()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:created,ready_to_ship,shipped,received,processed,delivered,quarantine',
            'correction_reason' => 'required|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $oldStatus = $batch->status;
            
            $batch->update(['status' => $validated['status']]);

            BatchLog::create([
                'batch_id' => $batch->id,
                'action' => 'corrected',
                'previous_status' => $oldStatus,
                'new_status' => $validated['status'],
                'actor_user_id' => Auth::id(),
                'notes' => 'Koreksi manual: ' . $validated['correction_reason'],
            ]);

            DB::commit();
            return back()->with('success', 'Batch berhasil dikoreksi.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    /**
     * Force delete batch (Super Admin only, hanya jika tidak punya child)
     */
    public function forceDelete(Batch $batch)
    {
        if (!Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        if (!$batch->canBeDeleted()) {
            return back()->with('error', 'Batch tidak dapat dihapus karena sudah memiliki child batch atau sudah diproses.');
        }

        DB::beginTransaction();
        try {
            $batchCode = $batch->batch_code;
            
            // Hapus semua relasi
            $batch->logs()->delete();
            $batch->documents()->delete();
            $batch->rfidWrites()->delete();
            
            // Hapus batch
            $batch->delete();

            DB::commit();
            return redirect()->route('batches.index')
                ->with('success', 'Batch ' . $batchCode . ' berhasil dihapus.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus batch: ' . $e->getMessage());
        }
    }
}