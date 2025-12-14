<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ProductCode;
use App\Services\CheckpointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DryProcessController extends Controller
{
    protected $checkpointService;

    public function __construct(CheckpointService $checkpointService)
    {
        $this->checkpointService = $checkpointService;
    }

    /* Dashboard dry process */
    public function dashboard()
    {
        // Calculate stats for KPI cards
        $stats = [
            // Pending receive from wet process (status: dispatched, stage: wet_process)
            'pending_receive' => Batch::where('process_stage', 'wet_process')
                ->where('status', 'dispatched')
                ->count(),
            
            // In stockpile (status: stocked, stage: dry_process)
            'in_stock' => Batch::where('process_stage', 'dry_process')
                ->where('status', 'stocked')
                ->count(),
            
            // Currently processing (status: processing, stage: dry_process)
            'in_processing' => Batch::where('process_stage', 'dry_process')
                ->where('status', 'processing')
                ->count(),
            
            // Processed and ready to dispatch (child batches, status: ready)
            'processed_ready' => Batch::where('process_stage', 'dry_process')
                ->where('status', 'ready')
                ->whereNotNull('parent_batch_id')
                ->count(),
        ];

        // Batches pending receive from wet process
        $pendingReceive = Batch::where('process_stage', 'wet_process')
            ->where('status', 'dispatched')
            ->with(['productCode', 'creator'])
            ->latest()
            ->get();

        // Batches already received, now in stock/stockpile
        $stockBatches = Batch::where('process_stage', 'dry_process')
            ->where('status', 'stocked')
            ->with(['productCode'])
            ->latest()
            ->get();

        // Batches currently being processed
        $processingBatches = Batch::where('process_stage', 'dry_process')
            ->where('status', 'processing')
            ->with(['productCode'])
            ->latest()
            ->get();

        // Child batches (hasil proses) ready to dispatch to warehouse
        $processedBatches = Batch::where('process_stage', 'dry_process')
            ->where('status', 'ready')
            ->whereNotNull('parent_batch_id')
            ->with(['productCode', 'parentBatch'])
            ->latest()
            ->get();

        return view('dry-process.dashboard', compact(
            'stats',
            'pendingReceive',
            'stockBatches',
            'processingBatches',
            'processedBatches'
        ));
    }

    /* Receive batch dari wet process (CP2) */
    public function receive(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'action' => 'required|in:stock,process',
            'location' => 'required_if:action,stock|nullable|string|max:255',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch dari wet process
            if ($batch->process_stage !== 'wet_process') {
                throw new \Exception('Batch bukan dari Wet Process');
            }

            if ($batch->status !== 'dispatched') {
                throw new \Exception('Batch belum di-dispatch dari Wet Process');
            }

            // Record checkpoint CP2
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP2',
                Auth::id(),
                $validated['notes'] ?? "Diterima di Dry Process"
            );

            // Update batch based on action
            if ($validated['action'] === 'stock') {
                // Stock ke gudang sementara - REMOVED stocked_at column
                $batch->update([
                    'status' => 'stocked',
                    'current_location' => 'Dry Process - Stockpile',
                    'process_stage' => 'dry_process',
                    'stockpile_location' => $validated['location'],
                    // ✅ REMOVED: 'stocked_at' => now(),
                ]);

                $message = "Batch {$batch->batch_code} berhasil diterima dan di-stock di: {$validated['location']}";
            } else {
                // Langsung proses
                $batch->update([
                    'status' => 'processing',
                    'current_location' => 'Dry Process - Processing',
                    'process_stage' => 'dry_process',
                ]);

                $message = "Batch {$batch->batch_code} berhasil diterima dan siap diproses!";
            }

            // Log activity
            $batch->logs()->create([
                'action' => 'DRY_RECEIVE',
                'actor_user_id' => Auth::id(),
                'notes' => "Received {$batch->current_weight} kg. Action: " . strtoupper($validated['action']),
            ]);

            DB::commit();

            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal receive batch: ' . $e->getMessage());
        }
    }

    /* Retrieve batch dari stockpile untuk diproses */
    public function retrieve(Request $request, Batch $batch)
    {
        try {
            DB::beginTransaction();

            // Validate batch in stockpile
            if ($batch->status !== 'stocked') {
                throw new \Exception('Batch tidak ada di stockpile');
            }

            // Update to processing
            $batch->update([
                'status' => 'processing',
                'current_location' => 'Dry Process - Processing',
            ]);

            // Log activity
            $batch->logs()->create([
                'action' => 'RETRIEVE_FROM_STOCK',
                'actor_user_id' => Auth::id(),
                'notes' => $request->input('notes') ?? 'Retrieved from stockpile for processing',
            ]);

            DB::commit();

            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil diambil dari stockpile untuk diproses");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal retrieve batch: ' . $e->getMessage()]);
        }
    }

    /* Form untuk input kandungan 3 konsentrat */
    public function processForm(Batch $batch)
    {
        // Validate batch ready untuk diproses
        if ($batch->process_stage !== 'dry_process' || $batch->status !== 'processing') {
            return redirect()
                ->route('dry-process.dashboard')
                ->with('error', 'Batch tidak ready untuk diproses');
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
        ], [
            'zircon_percentage.required' => 'Persentase Zircon wajib diisi',
            'ilmenite_percentage.required' => 'Persentase Ilmenite wajib diisi',
            'monasit_percentage.required' => 'Persentase Monasit wajib diisi',
            '*.max' => 'Persentase tidak boleh melebihi 100%',
            '*.min' => 'Persentase tidak boleh negatif',
        ]);

        try {
            DB::beginTransaction();

            // Validate total percentage
            $totalPercentage = $validated['zircon_percentage'] + 
                             $validated['ilmenite_percentage'] + 
                             $validated['monasit_percentage'];
            
            if ($totalPercentage > 100) {
                throw new \Exception("Total kandungan tidak boleh melebihi 100%. Saat ini: {$totalPercentage}%");
            }

            if ($totalPercentage == 0) {
                throw new \Exception("Setidaknya harus ada satu konsentrat yang diinput");
            }

            // Calculate weights
            $totalWeight = $batch->current_weight;
            $zirconWeight = ($totalWeight * $validated['zircon_percentage']) / 100;
            $ilmeniteWeight = ($totalWeight * $validated['ilmenite_percentage']) / 100;
            $monasitWeight = ($totalWeight * $validated['monasit_percentage']) / 100;

            // Get product codes by CODE (not by material+spec)
            $zirconProductCode = ProductCode::firstOrCreate(
                ['code' => 'DRY-ZIRCON-CON'],
                [
                    'stage' => 'Midstream',
                    'description' => 'Zircon Concentrate from Dry Process',
                    'material' => 'ZIRCON',
                    'spec' => 'ZrO2>65%',
                    'category' => 'Konsentrat',
                    'specifications' => 'Zircon concentrate dengan kandungan ZrO2 > 65%',
                ]
            );

            $ilmeniteProductCode = ProductCode::firstOrCreate(
                ['code' => 'DRY-ILMENITE-CON'],
                [
                    'stage' => 'Midstream',
                    'description' => 'Ilmenite Concentrate from Dry Process',
                    'material' => 'ILMENITE',
                    'spec' => 'TiO2>50%',
                    'category' => 'Konsentrat',
                    'specifications' => 'Ilmenite concentrate dengan kandungan TiO2 > 50%',
                ]
            );

            $monasitProductCode = ProductCode::firstOrCreate(
                ['code' => 'DRY-MON-CON'],
                [
                    'stage' => 'Midstream',
                    'description' => 'Monasit Concentrate from Dry Process',
                    'material' => 'MON',
                    'spec' => 'REO>55%',
                    'category' => 'Konsentrat',
                    'specifications' => 'Monasit concentrate dengan kandungan REO > 55%',
                ]
            );

            // Ensure materials are set (for old records without material)
            $zirconProductCode->update(['material' => 'ZIRCON', 'spec' => 'ZrO2>65%']);
            $ilmeniteProductCode->update(['material' => 'ILMENITE', 'spec' => 'TiO2>50%']);
            $monasitProductCode->update(['material' => 'MON', 'spec' => 'REO>55%']);

            // Create child batches
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
                if ($child['weight'] > 0) {
                    $childBatchCode = $batch->batch_code . '-' . $child['suffix'];
                    $childLotNumber = Batch::generateLotNumber($batch->batch_code, $child['lot_suffix']);
                    
                    $childBatch = Batch::create([
                        'batch_code' => $childBatchCode,
                        'lot_number' => $childLotNumber,
                        'product_code_id' => $child['product_code']->id,
                        'parent_batch_id' => $batch->id,
                        'initial_weight' => $child['weight'],
                        'current_weight' => $child['weight'],
                        'weight_unit' => 'kg',
                        'status' => 'ready',
                        'origin_location' => $batch->origin_location,
                        'current_location' => 'Dry Process - Ready for Warehouse',
                        'process_stage' => 'dry_process',
                        'konsentrat_persen' => $child['percentage'],
                        'created_by' => Auth::id(),
                    ]);

                    $createdBatches[] = $childBatch;
                }
            }

            // Update parent batch - REMOVED dry_process_completed_at
            $totalChildWeight = array_sum(array_map(fn($b) => $b->current_weight, $createdBatches));
            $batch->update([
                'status' => 'processed',
                'current_weight' => $totalWeight - $totalChildWeight,
                // ✅ REMOVED: 'dry_process_completed_at' => now(),
            ]);

            // Record checkpoint CP3 on parent
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP3',
                Auth::id(),
                $validated['notes'] ?? "Dry separation complete. Created " . count($createdBatches) . " concentrate batches"
            );

            // Log activity
            $batch->logs()->create([
                'action' => 'DRY_PROCESS_COMPLETE',
                'actor_user_id' => Auth::id(),
                'notes' => "Processed into " . count($createdBatches) . " concentrates: " .
                          "Zircon {$validated['zircon_percentage']}%, " .
                          "Ilmenite {$validated['ilmenite_percentage']}%, " .
                          "Monasit {$validated['monasit_percentage']}%",
            ]);

            DB::commit();

            $wastePercentage = 100 - $totalPercentage;
            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil diproses! Menghasilkan " . count($createdBatches) . " batch konsentrat. Waste: {$wastePercentage}%");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal process batch: ' . $e->getMessage()])->withInput();
        }
    }

    /* Dispatch batch ke warehouse */
    public function dispatchToWarehouse(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch is child batch (hasil proses)
            if (!$batch->parent_batch_id) {
                throw new \Exception('Hanya batch hasil proses yang bisa di-dispatch');
            }

            if ($batch->status !== 'ready') {
                throw new \Exception('Batch tidak ready untuk dispatch');
            }

            // Update batch
            $batch->update([
                'status' => 'dispatched',
                'current_location' => 'In Transit to Warehouse',
            ]);

            // Log activity
            $batch->logs()->create([
                'action' => 'DISPATCH_TO_WAREHOUSE',
                'actor_user_id' => Auth::id(),
                'notes' => $validated['notes'] ?? "Dispatched to warehouse: {$batch->current_weight} kg",
            ]);

            DB::commit();

            return redirect()
                ->route('dry-process.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil di-dispatch ke warehouse!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal dispatch batch: ' . $e->getMessage());
        }
    }
}