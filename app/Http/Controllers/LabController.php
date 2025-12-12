<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\LabAnalysis;
use App\Services\CheckpointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
{
    protected $checkpointService;

    public function __construct(CheckpointService $checkpointService)
    {
        $this->checkpointService = $checkpointService;
    }

    /* Dashboard Lab */
    public function dashboard()
    {
        // Stats dengan query yang lebih fleksibel
        $stats = [
            'pending_receive' => Batch::where('is_split', true)
                ->where(function($q) {
                    $q->where('status', 'in_transit')
                    ->orWhere('process_stage', 'lab');
                })
                ->where('status', '!=', 'received')
                ->whereDoesntHave('labAnalysis')
                ->count(),
            'pending_analysis' => Batch::where('process_stage', 'lab')
                ->where('status', 'received')
                ->doesntHave('labAnalysis')
                ->count(),
            'completed_analysis' => LabAnalysis::count(),
            'avg_recovery' => LabAnalysis::avg('total_recovery') ?? 0,
        ];

        // Pending receive - IMPROVED QUERY
        $pendingReceive = Batch::where('is_split', true)
            ->where(function($q) {
                $q->where(function($subq) {
                    // Status in_transit DAN process_stage = lab
                    $subq->where('status', 'in_transit')
                        ->where('process_stage', 'lab');
                })
                ->orWhere(function($subq) {
                    // ATAU: status in_transit DAN current_location contains 'Lab'
                    $subq->where('status', 'in_transit')
                        ->where('current_location', 'like', '%Lab%');
                });
            })
            ->with('productCode', 'splitParent')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Pending analysis (sudah received, belum dianalisis)
        $pendingAnalysis = Batch::where('process_stage', 'lab')
            ->where('status', 'received')
            ->doesntHave('labAnalysis')
            ->with('productCode')
            ->orderBy('updated_at', 'desc')
            ->get();

        // Recent analysis
        $recentAnalysis = LabAnalysis::with('batch', 'analyst')
            ->orderBy('analyzed_at', 'desc')
            ->take(10)
            ->get();

        return view('lab.dashboard', compact('stats', 'pendingReceive', 'pendingAnalysis', 'recentAnalysis'));
    }

    /* Receive batch dari Warehouse (CP5) */
    public function receive(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            // Record CP5
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP5',
                Auth::id(),
                $validated['notes'] ?? "Diterima di Lab"
            );

            $batch->update([
                'status' => 'received',
                'current_location' => 'Lab/Project Plan',
                'process_stage' => 'lab',
            ]);

            return redirect()
                ->route('lab.dashboard')
                ->with('success', "Batch {$batch->batch_code} berhasil diterima (CP5)");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Gagal receive batch: ' . $e->getMessage()]);
        }
    }

    /* Form input analisis LTJ */
    public function analysisForm(Batch $batch)
    {
        return view('lab.analysis', compact('batch'));
    }

    /* Store hasil analisis */
    public function storeAnalysis(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'nd_content' => 'required|numeric|min:0|max:100',
            'la_content' => 'required|numeric|min:0|max:100',
            'ce_content' => 'required|numeric|min:0|max:100',
            'y_content' => 'required|numeric|min:0|max:100',
            'pr_content' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Calculate total recovery
            $totalRecovery = $validated['nd_content'] + $validated['la_content'] + 
                           $validated['ce_content'] + $validated['y_content'] + $validated['pr_content'];

            // Create lab analysis
            LabAnalysis::create([
                'batch_id' => $batch->id,
                'nd_content' => $validated['nd_content'],
                'la_content' => $validated['la_content'],
                'ce_content' => $validated['ce_content'],
                'y_content' => $validated['y_content'],
                'pr_content' => $validated['pr_content'],
                'total_recovery' => $totalRecovery,
                'analyst_user_id' => Auth::id(),
                'analyzed_at' => now(),
                'notes' => $validated['notes'],
            ]);

            // Update batch dengan LTJ content
            $batch->update([
                'nd_content' => $validated['nd_content'],
                'la_content' => $validated['la_content'],
                'ce_content' => $validated['ce_content'],
                'y_content' => $validated['y_content'],
                'pr_content' => $validated['pr_content'],
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()
                ->route('lab.dashboard')
                ->with('success', "Analisis batch {$batch->batch_code} berhasil disimpan. Total Recovery: {$totalRecovery}%");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Gagal menyimpan analisis: ' . $e->getMessage()])->withInput();
        }
    }

    /* View hasil analisis dengan pie chart */
    public function viewAnalysis(Batch $batch)
    {
        $analysis = $batch->labAnalysis;

        if (!$analysis) {
            return redirect()
                ->route('lab.dashboard')
                ->withErrors(['error' => 'Batch ini belum dianalisis']);
        }

        // Prepare composition data untuk pie chart
        $composition = [
            ['element' => 'Nd', 'percentage' => $analysis->nd_content],
            ['element' => 'La', 'percentage' => $analysis->la_content],
            ['element' => 'Ce', 'percentage' => $analysis->ce_content],
            ['element' => 'Y', 'percentage' => $analysis->y_content],
            ['element' => 'Pr', 'percentage' => $analysis->pr_content],
        ];

        return view('lab.view-analysis', compact('batch', 'analysis', 'composition'));
    }
}