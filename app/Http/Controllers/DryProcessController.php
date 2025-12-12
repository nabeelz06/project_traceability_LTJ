<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ProductCode;
use App\Services\CheckpointService;
use App\Services\StockingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DryProcessController extends Controller
{
    protected $checkpointService;
    protected $stockingService;

    public function __construct(CheckpointService $checkpointService, StockingService $stockingService)
    {
        $this->checkpointService = $checkpointService;
        $this->stockingService = $stockingService;
    }

    /* Dashboard Dry Process */
    public function dashboard()
    {
        $stats = [
            'pending_receive' => Batch::where('current_checkpoint', 'CP1')
                ->where('status', 'in_transit')
                ->count(),
            'in_stock' => Batch::where('process_stage', 'dry_process')
                ->where('stocking_status', 'stocked')
                ->count(),
            'in_processing' => Batch::where('process_stage', 'dry_process')
                ->where('status', 'processing')
                ->count(),
            'processed_ready' => Batch::where('process_stage', 'dry_process')
                ->where('status', 'processed')
                ->count(),
            'total_stock_weight' => Batch::where('process_stage', 'dry_process')
                ->where('stocking_status', 'stocked')
                ->sum('current_weight'),
        ];

        // Pending receive dari Wet Process
        $pendingReceive = Batch::where('current_checkpoint', 'CP1')
            ->where('status', 'in_transit')
            ->with('productCode')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Stock batches (di stockpile)
        $stockBatches = Batch::where('process_stage', 'dry_process')
            ->where('stocking_status', 'stocked')
            ->with('productCode')
            ->orderBy('stocked_at', 'desc')
            ->get();

        // Processing batches (sedang diproses)
        $processingBatches = Batch::where('process_stage', 'dry_process')
            ->where('status', 'processing')
            ->with('productCode')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Processed batches (siap dispatch ke warehouse)
        $processedBatches = Batch::where('process_stage', 'dry_process')
            ->where('status', 'processed')
            ->whereNotNull('parent_batch_id') // Hanya child batches
            ->with(['productCode', 'parentBatch'])
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('dry-process.dashboard', compact('stats', 'pendingReceive', 'stockBatches', 'processingBatches', 'processedBatches'));
    }

    /* Receive batch dari Wet Process (CP2) - dengan pilihan */
    public function receive(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'action' => 'required|in:stock,process',
            'location' => 'required_if:action,stock|nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Record CP2
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP2',
                Auth::id(),
                $validated['notes'] ?? "Diterima di Dry Process"
            );

            if ($validated['action'] === 'stock') {
                // Stock ke gudang sementara
                $this->stockingService->stockBatch(
                    $batch,
                    $validated['location'],
                    Auth::id(),
                    "Stock setelah receive dari Wet Process"
                );

                $message = "Batch {$batch->batch_code} berhasil diterima dan di-stock ke {$validated['location']}";
            } else {
                // Langsung set status processing
                $batch->update([
                    'status' => 'processing',
                    'current_location' => 'Dry Process - Processing',
                    'process_stage' => 'dry_process',
                ]);

                $message = "Batch {$batch->batch_code} berhasil diterima dan siap diproses";
            }

            DB::commit();

            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal receive batch: ' . $e->getMessage()]);
        }
    }

    /* Retrieve batch dari stockpile untuk diproses */
    public function retrieve(Request $request, Batch $batch)
    {
        try {
            // Retrieve from stockpile (notes optional)
            $this->stockingService->retrieveBatch(
                $batch,
                Auth::id(),
                $request->input('notes') // Get notes directly from request, null if not exists
            );

            // Update batch status to processing
            $batch->update([
                'status' => 'processing',
                'current_location' => 'Dry Process - Processing',
            ]);

            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil diambil dari stockpile untuk diproses");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal retrieve batch: ' . $e->getMessage()]);
        }
    }

    /* Form untuk input kandungan 3 konsentrat */
    public function processForm(Batch $batch)
    {
        // Pastikan batch dalam status processing
        if ($batch->status !== 'processing') {
            return redirect()
                ->route('dry-process.dashboard')
                ->withErrors(['error' => 'Batch ini tidak dalam status processing']);
        }

        return view('dry-process.process', compact('batch'));
    }

    /* Process batch - input kandungan 3 konsentrat */
    public function process(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'zircon_percentage' => 'required|numeric|min:0|max:100',
            'ilmenite_percentage' => 'required|numeric|min:0|max:100',
            'monasit_percentage' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate total percentage tidak melebihi 100% (tapi boleh kurang dari 100%)
            $totalPercentage = $validated['zircon_percentage'] + $validated['ilmenite_percentage'] + $validated['monasit_percentage'];
            if ($totalPercentage > 100) {
                throw new \Exception("Total kandungan tidak boleh melebihi 100%. Saat ini: {$totalPercentage}%");
            }

            // Validate at least one concentrate has value
            if ($totalPercentage == 0) {
                throw new \Exception("Setidaknya harus ada satu konsentrat yang diinput");
            }

            // Calculate weight untuk masing-masing konsentrat
            $totalWeight = $batch->current_weight;
            $zirconWeight = ($totalWeight * $validated['zircon_percentage']) / 100;
            $ilmeniteWeight = ($totalWeight * $validated['ilmenite_percentage']) / 100;
            $monasitWeight = ($totalWeight * $validated['monasit_percentage']) / 100;

            // Get or create product codes untuk masing-masing konsentrat
            $zirconProductCode = ProductCode::firstOrCreate(
                ['material' => 'ZIRCON', 'spec' => 'CON'],
                [
                    'code' => 'DRY-ZIRCON-CON',
                    'stage' => 'middlestream',
                    'description' => 'Zircon Concentrate from Dry Process',
                    'category' => 'concentrate'
                ]
            );

            $ilmeniteProductCode = ProductCode::firstOrCreate(
                ['material' => 'ILMENITE', 'spec' => 'CON'],
                [
                    'code' => 'DRY-ILMENITE-CON',
                    'stage' => 'middlestream',
                    'description' => 'Ilmenite Concentrate from Dry Process',
                    'category' => 'concentrate'
                ]
            );

            $monasitProductCode = ProductCode::firstOrCreate(
                ['material' => 'MON', 'spec' => 'CON'],
                [
                    'code' => 'DRY-MON-CON',
                    'stage' => 'middlestream',
                    'description' => 'Monasit Concentrate from Dry Process',
                    'category' => 'concentrate'
                ]
            );

            // Create child batches dengan unique lot_number
            // FIXED: Menggunakan lot_suffix unik (ZIR, ILM, MON) agar tidak bentrok dengan parent (A)
            $children = [
                [
                    'product_code' => $zirconProductCode,
                    'weight' => $zirconWeight,
                    'percentage' => $validated['zircon_percentage'],
                    'suffix' => 'ZIR',
                    'lot_suffix' => 'ZIR' 
                ],
                [
                    'product_code' => $ilmeniteProductCode,
                    'weight' => $ilmeniteWeight,
                    'percentage' => $validated['ilmenite_percentage'],
                    'suffix' => 'ILM',
                    'lot_suffix' => 'ILM' 
                ],
                [
                    'product_code' => $monasitProductCode,
                    'weight' => $monasitWeight,
                    'percentage' => $validated['monasit_percentage'],
                    'suffix' => 'MON',
                    'lot_suffix' => 'MON' 
                ],
            ];

            $createdBatches = [];
            foreach ($children as $child) {
                if ($child['weight'] > 0) { // Hanya create jika weight > 0
                    // Generate unique batch_code dan lot_number
                    $childBatchCode = $batch->batch_code . '-' . $child['suffix'];
                    $childLotNumber = Batch::generateLotNumber($batch->batch_code, $child['lot_suffix']);
                    
                    $childBatch = Batch::create([
                        'batch_code' => $childBatchCode,
                        'lot_number' => $childLotNumber, // EXPLICIT lot_number
                        'product_code_id' => $child['product_code']->id,
                        'parent_batch_id' => $batch->id,
                        'initial_weight' => $child['weight'],
                        'current_weight' => $child['weight'],
                        'weight_unit' => 'kg',
                        'status' => 'processed',
                        'origin_location' => $batch->origin_location,
                        'current_location' => 'Dry Process - Processed',
                        'process_stage' => 'dry_process',
                        'konsentrat_persen' => $child['percentage'],
                        'created_by' => Auth::id(),
                    ]);

                    $createdBatches[] = $childBatch;
                }
            }

            // Update parent batch
            $totalChildWeight = array_sum(array_map(fn($b) => $b->current_weight, $createdBatches));
            $batch->update([
                'status' => 'processed',
                'current_weight' => $totalWeight - $totalChildWeight, // Sisa/waste
            ]);

            DB::commit();

            $wastePercentage = 100 - $totalPercentage;
            $message = "Batch {$batch->batch_code} berhasil diproses. Menghasilkan " . count($createdBatches) . " batch konsentrat. Waste: {$wastePercentage}%";

            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal process batch: ' . $e->getMessage()])->withInput();
        }
    }

    /* Dispatch konsentrat ke Warehouse (CP3) */
    public function dispatchToWarehouse(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            // Pastikan batch adalah hasil proses (child batch)
            if (!$batch->parent_batch_id || $batch->status !== 'processed') {
                throw new \Exception("Hanya batch hasil proses yang bisa di-dispatch");
            }

            // Record CP3
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP3',
                Auth::id(),
                $validated['notes'] ?? "Dispatch ke Warehouse"
            );

            $batch->update([
                'status' => 'in_transit',
                'current_location' => 'In Transit to Warehouse',
            ]);

            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil di-dispatch ke Warehouse (CP3)");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal dispatch batch: ' . $e->getMessage()]);
        }
    }
}