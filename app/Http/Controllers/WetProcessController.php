<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ProductCode;
use App\Services\CheckpointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WetProcessController extends Controller
{
    protected $checkpointService;

    public function __construct(CheckpointService $checkpointService)
    {
        $this->checkpointService = $checkpointService;
    }

    /* Dashboard Wet Process */
    public function dashboard()
    {
        $stats = [
            'today_batches' => Batch::where('process_stage', 'wet_process')
                ->whereDate('created_at', today())
                ->count(),
            'week_batches' => Batch::where('process_stage', 'wet_process')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'pending_dispatch' => Batch::where('process_stage', 'wet_process')
                ->where('status', 'created')
                ->count(),
            'total_weight_today' => Batch::where('process_stage', 'wet_process')
                ->whereDate('created_at', today())
                ->sum('initial_weight'),
        ];

        $recentBatches = Batch::where('process_stage', 'wet_process')
            ->with('productCode')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('wet-process.dashboard', compact('stats', 'recentBatches'));
    }

    /* Form create batch (Mineral Ikutan) */
    public function create()
    {
        // Get product code untuk Mineral Ikutan
        $productCodes = ProductCode::where('material', 'IKUTAN')
            ->orWhere('code', 'like', 'MIN-%')
            ->get();

        return view('wet-process.create-batch', compact('productCodes'));
    }

    /* Store batch baru */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_code_id' => 'required|exists:product_codes,id',
            'weight' => 'required|numeric|min:900|max:1100',
            'origin_location' => 'required|string|max:255',
            'container_code' => 'nullable|string|max:50',
            'keterangan' => 'nullable|string',
        ], [
            'weight.min' => 'Berat minimal 900 kg (0.9 ton)',
            'weight.max' => 'Berat maksimal 1100 kg (1.1 ton)',
        ]);

        try {
            DB::beginTransaction();

            // Create batch
            $batch = Batch::create([
                'product_code_id' => $validated['product_code_id'],
                'initial_weight' => $validated['weight'],
                'current_weight' => $validated['weight'],
                'weight_unit' => 'kg',
                'tonase' => $validated['weight'] / 1000,
                'origin_location' => $validated['origin_location'],
                'current_location' => $validated['origin_location'],
                'container_code' => $validated['container_code'],
                'keterangan' => $validated['keterangan'],
                'status' => 'created',
                'process_stage' => 'wet_process',
                'created_by' => Auth::id(),
            ]);

            // Log activity
            $batch->logs()->create([
                'action' => 'BATCH_CREATED',
                'actor_user_id' => Auth::id(),
                'notes' => "Batch created at Wet Process: {$batch->initial_weight} kg",
            ]);

            DB::commit();

            return redirect()
                ->route('wet-process.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil dibuat! Berat: {$batch->initial_weight} kg");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal membuat batch: ' . $e->getMessage()])->withInput();
        }
    }

    /* Dispatch batch ke Dry Process (CP1) */
    public function dispatch(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'gps_latitude' => 'nullable|numeric',
            'gps_longitude' => 'nullable|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch ready
            if ($batch->status !== 'created') {
                throw new \Exception('Batch tidak ready untuk dispatch');
            }

            // Prepare GPS data
            $gps = null;
            if (!empty($validated['gps_latitude']) && !empty($validated['gps_longitude'])) {
                $gps = [
                    'latitude' => $validated['gps_latitude'],
                    'longitude' => $validated['gps_longitude'],
                ];
            }

            // Record CP1
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP1',
                Auth::id(),
                $validated['notes'] ?? "Dispatch ke Dry Process",
                $gps
            );

            // Update batch
            $batch->update([
                'status' => 'dispatched',
                'current_location' => 'In Transit to Dry Process',
            ]);

            // Log activity
            $batch->logs()->create([
                'action' => 'DISPATCH_TO_DRY',
                'actor_user_id' => Auth::id(),
                'notes' => "Dispatched to Dry Process: {$batch->current_weight} kg",
            ]);

            DB::commit();

            return redirect()
                ->route('wet-process.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil di-dispatch ke Dry Process (CP1)");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal dispatch batch: ' . $e->getMessage()]);
        }
    }

    /* List batches pending dispatch */
    public function pendingDispatch()
    {
        $batches = Batch::where('process_stage', 'wet_process')
            ->where('status', 'created')
            ->with('productCode')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('wet-process.pending-dispatch', compact('batches'));
    }
}