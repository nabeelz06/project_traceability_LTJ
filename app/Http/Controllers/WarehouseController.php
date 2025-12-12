<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ExportLog;
use App\Models\ProductCode;
use App\Services\CheckpointService;
use App\Services\BatchSplitService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseController extends Controller
{
    protected $checkpointService;
    protected $batchSplitService;

    public function __construct(CheckpointService $checkpointService, BatchSplitService $batchSplitService)
    {
        $this->checkpointService = $checkpointService;
        $this->batchSplitService = $batchSplitService;
    }

    /* Dashboard Warehouse */
    public function dashboard()
    {
        $stats = [
            'pending_receive' => Batch::where('current_checkpoint', 'CP3')
                ->where('status', 'in_transit')
                ->count(),
            'zircon_stock' => $this->getStockByMaterial('ZIRCON'),
            'ilmenite_stock' => $this->getStockByMaterial('ILMENITE'),
            'monasit_stock' => $this->getStockByMaterial('MON'),
        ];

        // Stock composition untuk pie chart
        $stockComposition = collect([
            ['material' => 'Zircon', 'weight' => $stats['zircon_stock']],
            ['material' => 'Ilmenite', 'weight' => $stats['ilmenite_stock']],
            ['material' => 'Monasit', 'weight' => $stats['monasit_stock']],
        ])->filter(fn($item) => $item['weight'] > 0);

        // Pending receive batches (dari Dry Process)
        $pendingReceive = Batch::where('current_checkpoint', 'CP3')
            ->where('status', 'in_transit')
            ->with('productCode', 'parentBatch')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Batches in stock (sudah di-receive, belum di-export/split)
        $stockedBatches = Batch::where('process_stage', 'warehouse')
            ->where('status', 'received')
            ->whereNull('export_status')
            ->with('productCode')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Recent exports
        $recentExports = ExportLog::with('batch.productCode', 'operator')
            ->orderBy('exported_at', 'desc')
            ->take(10)
            ->get();

        return view('warehouse.dashboard', compact('stats', 'stockComposition', 'pendingReceive', 'stockedBatches', 'recentExports'));
    }

    /* Receive batch (CP4.1, CP4.2, CP4.3) dan update stock */
    public function receive(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Tentukan checkpoint code berdasarkan material
            $material = $batch->productCode->material;
            $checkpointCode = match($material) {
                'ZIRCON' => 'CP4.1',
                'ILMENITE' => 'CP4.2',
                'MON' => 'CP4.3',
                default => throw new \Exception("Material tidak valid untuk warehouse")
            };

            // Record checkpoint
            $this->checkpointService->recordCheckpoint(
                $batch,
                $checkpointCode,
                Auth::id(),
                $validated['notes'] ?? "Diterima di Warehouse"
            );

            // Update batch status
            $batch->update([
                'status' => 'received',
                'current_location' => 'Warehouse',
                'process_stage' => 'warehouse',
            ]);

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil diterima dan ditambahkan ke stock warehouse ({$checkpointCode})");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal receive batch: ' . $e->getMessage()]);
        }
    }

    /* Show export form */
    public function exportForm(Batch $batch)
    {
        // Validate material (hanya Zircon & Ilmenite boleh export)
        $material = $batch->productCode->material;
        if (!in_array($material, ['ZIRCON', 'ILMENITE'])) {
            return redirect()
                ->route('warehouse.dashboard')
                ->withErrors(['error' => 'Hanya Zircon dan Ilmenite yang bisa di-export']);
        }

        return view('warehouse.export', compact('batch'));
    }

    /* Export batch (POST route - Zircon & Ilmenite) */
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

            // Validate material (hanya Zircon & Ilmenite boleh export)
            $material = $batch->productCode->material;
            if (!in_array($material, ['ZIRCON', 'ILMENITE'])) {
                throw new \Exception("Hanya Zircon dan Ilmenite yang bisa di-export");
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

            // Update batch - stock berkurang
            $batch->update([
                'export_status' => 'exported',
                'exported_at' => now(),
                'export_destination' => $validated['destination'],
                'export_manifest_number' => $validated['manifest_number'],
                'status' => 'exported',
                'current_weight' => 0, // Stock berkurang
            ]);

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil di-export ke {$validated['destination']}. Stock warehouse updated.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal export batch: ' . $e->getMessage()])->withInput();
        }
    }

    /* Show split form */
    public function splitForm(Batch $batch)
    {
        // Validate material harus Monasit
        if ($batch->productCode->material !== 'MON') {
            return redirect()
                ->route('warehouse.dashboard')
                ->withErrors(['error' => 'Hanya Monasit yang bisa di-split untuk Lab']);
        }

        $productCodes = ProductCode::where('material', 'MON')->get();
        return view('warehouse.split-lab', compact('batch', 'productCodes'));
    }

    /* Split Monasit untuk Lab (POST route - 1 ton → multiple 50kg) */
    public function splitForLab(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'split_count' => 'required|integer|min:1|max:20',
            'weight_per_batch' => 'required|numeric|min:50|max:50',
            'lab_product_code_id' => 'required|exists:product_codes,id',
            'notes' => 'nullable|string',
        ]);

        try {
            // Validate material harus Monasit
            if ($batch->productCode->material !== 'MON') {
                throw new \Exception("Hanya Monasit yang bisa di-split untuk Lab");
            }

            // Split batch
            $childBatches = $this->batchSplitService->splitBatch(
                $batch,
                $validated['lab_product_code_id'],
                $validated['weight_per_batch'],
                Auth::id(),
                $validated['split_count']
            );

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil di-split menjadi {$validated['split_count']} batch @ 50kg untuk Lab. Stock warehouse updated.");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal split batch: ' . $e->getMessage()])->withInput();
        }
    }

    /* Dispatch Monasit ke Lab */
    public function dispatchToLab(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch is split batch
            if (!$batch->is_split) {
                throw new \Exception("Batch ini belum di-split. Silakan split terlebih dahulu.");
            }

            // Record checkpoint (CP_LAB atau bisa custom)
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP_LAB',
                Auth::id(),
                $validated['notes'] ?? "Dispatch ke Lab/Project Plan untuk analisis LTJ"
            );

            // CRITICAL: Update status, process_stage, dan location
            $batch->update([
                'status' => 'in_transit',
                'current_location' => 'In Transit to Lab',
                'process_stage' => 'lab', // TAMBAHAN INI!
            ]);

            DB::commit();

            return redirect()
                ->route('warehouse.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil di-dispatch ke Lab/Project Plan (CP_LAB)");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal dispatch batch: ' . $e->getMessage()]);
        }
    }

    /* Helper: Get stock by material (hanya yang status received dan belum di-export) */
    private function getStockByMaterial(string $material)
    {
        return Batch::where('process_stage', 'warehouse')
            ->where('status', 'received')
            ->whereNull('export_status')
            ->whereHas('productCode', function($q) use ($material) {
                $q->where('material', $material);
            })
            ->sum('current_weight');
    }
}