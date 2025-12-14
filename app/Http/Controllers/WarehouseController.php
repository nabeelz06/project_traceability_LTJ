<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ExportLog;
use App\Models\ProductCode;
use App\Services\CheckpointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    protected $checkpointService;

    public function __construct(CheckpointService $checkpointService)
    {
        $this->checkpointService = $checkpointService;
    }

    /* Dashboard warehouse */
    public function dashboard()
    {
        // Batches waiting to be received
        $pendingBatches = Batch::where('process_stage', 'dry_process')
            ->where('status', 'dispatched')
            ->with(['productCode', 'parent'])
            ->latest()
            ->get();

        // Batches in warehouse
        $warehouseBatches = Batch::where('process_stage', 'warehouse')
            ->where('status', 'received')
            ->whereNull('export_status')
            ->with(['productCode'])
            ->latest()
            ->get();

        // Batches exported
        $exportedBatches = Batch::where('process_stage', 'warehouse')
            ->whereNotNull('export_status')
            ->with(['productCode'])
            ->latest()
            ->limit(20)
            ->get();

        return view('warehouse.dashboard', compact('pendingBatches', 'warehouseBatches', 'exportedBatches'));
    }

    /* Receive batch dari dry process (CP4) */
    public function receive(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);
        
        try {
            DB::beginTransaction();
            
            // Validate batch
            if ($batch->process_stage !== 'dry_process') {
                throw new \Exception('Batch bukan dari Dry Process');
            }
            
            if ($batch->status !== 'dispatched') {
                throw new \Exception('Batch belum di-dispatch');
            }
            
            // Get material
            $material = $batch->productCode->material;
            
            // Validate material exists
            if (empty($material)) {
                throw new \Exception("Product code tidak memiliki material. Code: " . $batch->productCode->code);
            }
            
            // Validate material is valid for warehouse
            $validMaterials = ['ZIRCON', 'ILMENITE', 'MON'];
            if (!in_array($material, $validMaterials)) {
                throw new \Exception("Material '{$material}' tidak valid untuk warehouse. Valid: " . implode(', ', $validMaterials));
            }
            
            // Tentukan checkpoint code
            $checkpointCode = match($material) {
                'ZIRCON' => 'CP4.1',
                'ILMENITE' => 'CP4.2',
                'MON' => 'CP4.3',
                default => throw new \Exception("Material '{$material}' tidak memiliki checkpoint code")
            };
            
            // Record checkpoint
            $this->checkpointService->recordCheckpoint(
                $batch,
                $checkpointCode,
                Auth::id(),
                $validated['notes'] ?? "Diterima di Warehouse - {$material}"
            );
            
            // Update batch status
            $batch->update([
                'status' => 'received',
                'current_location' => 'Warehouse',
                'process_stage' => 'warehouse',
                'warehouse_received_at' => now(),
            ]);
            
            // Log activity
            $batch->logs()->create([
                'action' => 'WAREHOUSE_RECEIVE',
                'actor_user_id' => Auth::id(),
                'notes' => "Received {$batch->current_weight} kg of {$material}",
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Batch {$batch->batch_code} ({$material}) berhasil diterima di warehouse!");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal receive batch: ' . $e->getMessage());
        }
    }

    /* Show export form (Zircon & Ilmenite only) */
    public function exportForm(Batch $batch)
    {
        // Validate material (hanya Zircon & Ilmenite boleh export)
        $material = $batch->productCode->material;
        if (!in_array($material, ['ZIRCON', 'ILMENITE'])) {
            return redirect()
                ->route('warehouse.dashboard')
                ->with('error', 'Hanya Zircon dan Ilmenite yang bisa di-export. Monasit harus di-split untuk Lab.');
        }

        return view('warehouse.export', compact('batch'));
    }

    /* Export batch (POST - Zircon & Ilmenite only) */
    public function exportBatch(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'export_type' => 'required|in:export,domestic',
            'destination' => 'required|string|max:255',
            'manifest_number' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch in warehouse
            if ($batch->process_stage !== 'warehouse' || $batch->status !== 'received') {
                throw new \Exception('Batch tidak ready untuk export');
            }

            // Validate material can be exported (NOT MON!)
            $material = $batch->productCode->material;
            $exportableMaterials = ['ZIRCON', 'ILMENITE'];
            
            if (!in_array($material, $exportableMaterials)) {
                throw new \Exception("Material {$material} tidak boleh diekspor! Hanya ZIRCON dan ILMENITE yang boleh diekspor.");
            }

            // Create export log
            ExportLog::create([
                'batch_id' => $batch->id,
                'export_type' => $validated['export_type'],
                'destination' => $validated['destination'],
                'manifest_number' => $validated['manifest_number'],
                'weight_kg' => $batch->current_weight,
                'operator_user_id' => Auth::id(),
                'exported_at' => now(),
                'notes' => $validated['notes'],
            ]);

            // Update batch
            $batch->update([
                'export_status' => 'exported',
                'exported_at' => now(),
                'export_destination' => $validated['destination'],
                'export_manifest_number' => $validated['manifest_number'],
                'status' => 'exported',
                'current_weight' => 0, // Stock berkurang
            ]);

            // Record checkpoint CP5
            $checkpointCode = $material === 'ZIRCON' ? 'CP5.1' : 'CP5.2';
            $this->checkpointService->recordCheckpoint(
                $batch,
                $checkpointCode,
                Auth::id(),
                "Exported to {$validated['destination']} - {$validated['export_type']}"
            );

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil diekspor ke {$validated['destination']}!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal export: ' . $e->getMessage())->withInput();
        }
    }

    /* Show split form (Monasit only) */
    public function splitForm(Batch $batch)
    {
        // Validate material harus Monasit
        if ($batch->productCode->material !== 'MON') {
            return redirect()
                ->route('warehouse.dashboard')
                ->with('error', 'Hanya Monasit yang bisa di-split untuk Lab');
        }

        // Get all MON product codes
        $productCodes = ProductCode::where('material', 'MON')->get();
        
        return view('warehouse.split-lab', compact('batch', 'productCodes'));
    }

    /* Split Monasit untuk Lab (POST - multiple 50kg batches) */
    public function splitForLab(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'split_count' => 'required|integer|min:1|max:20',
            'weight_per_batch' => 'required|numeric|min:50|max:50',
            'lab_product_code_id' => 'required|exists:product_codes,id',
            'notes' => 'nullable|string',
        ], [
            'split_count.required' => 'Jumlah batch wajib diisi',
            'split_count.max' => 'Maksimal 20 batch per split',
            'weight_per_batch.required' => 'Berat per batch wajib diisi',
            'weight_per_batch.min' => 'Berat minimal 50 kg',
            'weight_per_batch.max' => 'Berat maksimal 50 kg (standar lab)',
            'lab_product_code_id.required' => 'Product code lab wajib dipilih',
        ]);

        try {
            DB::beginTransaction();

            // Validate material harus Monasit
            if ($batch->productCode->material !== 'MON') {
                throw new \Exception("Hanya Monasit yang bisa di-split untuk Lab");
            }

            // Validate product code is MON-LAB-SAMPLE
            $sampleProductCode = ProductCode::findOrFail($validated['lab_product_code_id']);
            if ($sampleProductCode->material !== 'MON') {
                throw new \Exception('Product code sample harus Monasit');
            }

            // Calculate total weight needed
            $totalWeightNeeded = $validated['split_count'] * $validated['weight_per_batch'];
            
            if ($totalWeightNeeded > $batch->current_weight) {
                throw new \Exception("Berat total ({$totalWeightNeeded} kg) melebihi berat tersedia ({$batch->current_weight} kg)");
            }

            // Create multiple child batches
            $createdBatches = [];
            for ($i = 1; $i <= $validated['split_count']; $i++) {
                $sampleBatchCode = $batch->batch_code . '-LAB' . $i;
                $sampleLotNumber = Batch::generateLotNumber($batch->batch_code, 'LAB' . $i);

                $sampleBatch = Batch::create([
                    'batch_code' => $sampleBatchCode,
                    'lot_number' => $sampleLotNumber,
                    'product_code_id' => $validated['lab_product_code_id'],
                    'parent_batch_id' => $batch->id,
                    'initial_weight' => $validated['weight_per_batch'],
                    'current_weight' => $validated['weight_per_batch'],
                    'weight_unit' => 'kg',
                    'status' => 'ready',
                    'is_split' => true,
                    'origin_location' => 'Warehouse',
                    'current_location' => 'Ready for Lab Analysis',
                    'process_stage' => 'warehouse',
                    'created_by' => Auth::id(),
                ]);

                $createdBatches[] = $sampleBatch;
            }

            // Update parent batch
            $batch->update([
                'current_weight' => $batch->current_weight - $totalWeightNeeded,
            ]);

            // Log activity
            $batch->logs()->create([
                'action' => 'SPLIT_FOR_LAB',
                'actor_user_id' => Auth::id(),
                'notes' => "Split {$validated['split_count']} batch @ 50kg untuk lab (Total: {$totalWeightNeeded} kg). " . 
                          ($validated['notes'] ?? ''),
            ]);

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Berhasil split batch {$batch->batch_code} menjadi {$validated['split_count']} sample @ 50kg untuk Lab!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal split batch: ' . $e->getMessage())->withInput();
        }
    }

    /* Dispatch Monasit sample ke Lab */
    public function dispatchToLab(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch is split batch
            if (!$batch->is_split) {
                throw new \Exception("Batch ini belum di-split untuk Lab");
            }

            if ($batch->status !== 'ready') {
                throw new \Exception("Batch tidak ready untuk dispatch");
            }

            // Update batch
            $batch->update([
                'status' => 'in_transit',
                'current_location' => 'In Transit to Lab',
                'process_stage' => 'lab', // Important!
            ]);

            // Log activity
            $batch->logs()->create([
                'action' => 'DISPATCH_TO_LAB',
                'actor_user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? "Dispatch sample ke Lab untuk analisis LTJ",
            ]);

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Sample {$batch->batch_code} berhasil di-dispatch ke Lab!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal dispatch: ' . $e->getMessage());
        }
    }
}