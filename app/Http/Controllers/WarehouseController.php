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
        // Calculate stats for KPI cards
        $stats = [
            'pending_receive' => Batch::where('process_stage', 'dry_process')
                ->where('status', 'dispatched')
                ->count(),
            
            'zircon_stock' => Batch::where('process_stage', 'warehouse')
                ->where('status', 'received')
                ->whereNull('export_status')
                ->whereHas('productCode', function($q) {
                    $q->where('material', 'ZIRCON');
                })
                ->sum('current_weight'),
            
            'ilmenite_stock' => Batch::where('process_stage', 'warehouse')
                ->where('status', 'received')
                ->whereNull('export_status')
                ->whereHas('productCode', function($q) {
                    $q->where('material', 'ILMENITE');
                })
                ->sum('current_weight'),
            
            'monasit_stock' => Batch::where('process_stage', 'warehouse')
                ->where('status', 'received')
                ->whereNull('export_status')
                ->whereHas('productCode', function($q) {
                    $q->where('material', 'MON');
                })
                ->sum('current_weight'),
        ];

        // Pending receive dari dry process
        $pendingReceive = Batch::where('process_stage', 'dry_process')
            ->where('status', 'dispatched')
            ->with(['productCode', 'parent'])
            ->latest()
            ->get();

        // Current stocked batches di warehouse
        $stockedBatches = Batch::where('process_stage', 'warehouse')
            ->where('status', 'received')
            ->whereNull('export_status')
            ->with(['productCode'])
            ->latest()
            ->get();

        // Recent exports
        $recentExports = ExportLog::with(['batch.productCode'])
            ->orderBy('exported_at', 'desc')
            ->limit(20)
            ->get();

        // Stock composition untuk pie chart
        $stockComposition = collect([
            [
                'material' => 'Zircon',
                'weight' => $stats['zircon_stock'],
            ],
            [
                'material' => 'Ilmenite',
                'weight' => $stats['ilmenite_stock'],
            ],
            [
                'material' => 'Monasit',
                'weight' => $stats['monasit_stock'],
            ],
        ])->filter(function($item) {
            return $item['weight'] > 0;
        });

        return view('warehouse.dashboard', compact(
            'stats',
            'pendingReceive',
            'stockedBatches',
            'recentExports',
            'stockComposition'
        ));
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
        // Validate material
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

            // Validate batch
            if ($batch->process_stage !== 'warehouse' || $batch->status !== 'received') {
                throw new \Exception('Batch tidak ready untuk export');
            }

            // Validate material
            $material = $batch->productCode->material;
            $exportableMaterials = ['ZIRCON', 'ILMENITE'];
            
            if (!in_array($material, $exportableMaterials)) {
                throw new \Exception("Material {$material} tidak boleh diekspor!");
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
                'current_weight' => 0,
            ]);

            // Record checkpoint
            $checkpointCode = $material === 'ZIRCON' ? 'CP5.1' : 'CP5.2';
            $this->checkpointService->recordCheckpoint(
                $batch,
                $checkpointCode,
                Auth::id(),
                "Exported to {$validated['destination']}"
            );

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil diekspor!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal export: ' . $e->getMessage())->withInput();
        }
    }

    /* Show split form (Monasit only) */
    public function splitForm(Batch $batch)
    {
        // Validate material
        if ($batch->productCode->material !== 'MON') {
            return redirect()
                ->route('warehouse.dashboard')
                ->with('error', 'Hanya Monasit yang bisa di-split untuk Lab');
        }

        // Get MON product codes
        $productCodes = ProductCode::where('material', 'MON')->get();
        
        return view('warehouse.split-lab', compact('batch', 'productCodes'));
    }

    /* Split Monasit untuk Lab (POST) */
    public function splitForLab(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'split_count' => 'required|integer|min:1|max:20',
            'weight_per_batch' => 'required|numeric|min:50|max:50',
            'lab_product_code_id' => 'required|exists:product_codes,id',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate material
            if ($batch->productCode->material !== 'MON') {
                throw new \Exception("Hanya Monasit yang bisa di-split");
            }

            // Validate product code
            $sampleProductCode = ProductCode::findOrFail($validated['lab_product_code_id']);
            if ($sampleProductCode->material !== 'MON') {
                throw new \Exception('Product code sample harus Monasit');
            }

            // Calculate total weight
            $totalWeightNeeded = $validated['split_count'] * $validated['weight_per_batch'];
            
            if ($totalWeightNeeded > $batch->current_weight) {
                throw new \Exception("Total berat ({$totalWeightNeeded} kg) melebihi tersedia ({$batch->current_weight} kg)");
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

            // Log
            $batch->logs()->create([
                'action' => 'SPLIT_FOR_LAB',
                'actor_user_id' => Auth::id(),
                'notes' => "Split {$validated['split_count']} batch @ 50kg (Total: {$totalWeightNeeded} kg)",
            ]);

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Berhasil split {$validated['split_count']} sample @ 50kg untuk Lab!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal split: ' . $e->getMessage())->withInput();
        }
    }

    /* Dispatch sample ke Lab */
    public function dispatchToLab(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate
            if (!$batch->is_split) {
                throw new \Exception("Batch ini belum di-split");
            }

            if ($batch->status !== 'ready') {
                throw new \Exception("Batch tidak ready");
            }

            // Update batch
            $batch->update([
                'status' => 'in_transit',
                'current_location' => 'In Transit to Lab',
                'process_stage' => 'lab',
            ]);

            // Log
            $batch->logs()->create([
                'action' => 'DISPATCH_TO_LAB',
                'actor_user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? "Dispatch ke Lab",
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