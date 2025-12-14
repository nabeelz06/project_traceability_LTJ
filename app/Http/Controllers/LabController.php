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
        // Stats
        $stats = [
            // Batches split dari warehouse, menunggu receive di lab
            'pending_receive' => Batch::where('is_split', true)
                ->where('process_stage', 'warehouse')
                ->where('status', 'ready')
                ->whereDoesntHave('labAnalysis')
                ->count(),
            
            // Batches sudah diterima di lab, belum dianalisis
            'pending_analysis' => Batch::where('process_stage', 'lab')
                ->where('status', 'received')
                ->doesntHave('labAnalysis')
                ->count(),
            
            // Total analisis completed
            'completed_analysis' => LabAnalysis::count(),
            
            // Average recovery dari semua analisis
            'avg_recovery' => round(LabAnalysis::avg('total_recovery') ?? 0, 2),
        ];

        // Pending receive - Batches dari warehouse split (MON samples)
        $pendingReceive = Batch::where('is_split', true)
            ->where('process_stage', 'warehouse')
            ->where('status', 'ready')
            ->whereHas('productCode', function($q) {
                $q->where('material', 'MON');
            })
            ->with(['productCode', 'parent'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pending analysis - Sudah received, belum dianalisis
        $pendingAnalysis = Batch::where('process_stage', 'lab')
            ->where('status', 'received')
            ->doesntHave('labAnalysis')
            ->with(['productCode', 'parent'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // Recent analysis - 10 analisis terakhir
        $recentAnalysis = LabAnalysis::with(['batch.productCode', 'analyst'])
            ->orderBy('analyzed_at', 'desc')
            ->take(10)
            ->get();

        return view('lab.dashboard', compact('stats', 'pendingReceive', 'pendingAnalysis', 'recentAnalysis'));
    }

    /* Receive batch dari Warehouse split (CP5) */
    public function receive(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch is split sample dari warehouse
            if (!$batch->is_split) {
                throw new \Exception('Batch bukan sample split dari warehouse');
            }

            if ($batch->process_stage !== 'warehouse') {
                throw new \Exception('Batch bukan dari warehouse');
            }

            if ($batch->status !== 'ready') {
                throw new \Exception('Batch tidak ready untuk receive');
            }

            // Validate material MON
            if ($batch->productCode->material !== 'MON') {
                throw new \Exception('Hanya batch Monasit yang bisa diterima di Lab. Material: ' . $batch->productCode->material);
            }

            // Record checkpoint CP5
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP5',
                Auth::id(),
                $validated['notes'] ?? "Sample diterima di Lab untuk analisis LTJ"
            );

            // Update batch status
            $batch->update([
                'status' => 'received',
                'current_location' => 'Lab/Project Plan',
                'process_stage' => 'lab',
            ]);

            // Log activity
            $batch->logs()->create([
                'action' => 'LAB_RECEIVE',
                'actor_user_id' => Auth::id(),
                'notes' => "Sample {$batch->current_weight} kg diterima untuk analisis LTJ",
            ]);

            DB::commit();

            return redirect()
                ->route('lab.dashboard')
                ->with('success', "Sample {$batch->batch_code} ({$batch->current_weight} kg) berhasil diterima di Lab!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal receive batch: ' . $e->getMessage());
        }
    }

    /* Form input analisis LTJ */
    public function analysisForm(Batch $batch)
    {
        // Validate batch ready untuk analisis
        if ($batch->process_stage !== 'lab' || $batch->status !== 'received') {
            return redirect()
                ->route('lab.dashboard')
                ->with('error', 'Batch tidak ready untuk analisis');
        }

        // Check jika sudah ada analisis
        if ($batch->labAnalysis) {
            return redirect()
                ->route('lab.view-analysis', $batch)
                ->with('info', 'Batch ini sudah dianalisis. Menampilkan hasil analisis.');
        }

        return view('lab.analysis', compact('batch'));
    }

    /* Store hasil analisis LTJ (CP6) */
    public function storeAnalysis(Request $request, Batch $batch)
    {
        $validated = $request->validate([
            'nd_content' => 'required|numeric|min:0|max:100',
            'la_content' => 'required|numeric|min:0|max:100',
            'ce_content' => 'required|numeric|min:0|max:100',
            'y_content' => 'required|numeric|min:0|max:100',
            'pr_content' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ], [
            'nd_content.required' => 'Kandungan Neodymium (Nd) wajib diisi',
            'la_content.required' => 'Kandungan Lanthanum (La) wajib diisi',
            'ce_content.required' => 'Kandungan Cerium (Ce) wajib diisi',
            'y_content.required' => 'Kandungan Yttrium (Y) wajib diisi',
            'pr_content.required' => 'Kandungan Praseodymium (Pr) wajib diisi',
            '*.max' => 'Kandungan tidak boleh melebihi 100%',
            '*.min' => 'Kandungan tidak boleh negatif',
        ]);

        try {
            DB::beginTransaction();

            // Validate batch belum ada analisis
            if ($batch->labAnalysis) {
                throw new \Exception('Batch ini sudah dianalisis sebelumnya');
            }

            // Validate batch status
            if ($batch->process_stage !== 'lab' || $batch->status !== 'received') {
                throw new \Exception('Batch tidak dalam status yang tepat untuk analisis');
            }

            // Calculate total recovery
            $totalRecovery = $validated['nd_content'] + $validated['la_content'] + 
                           $validated['ce_content'] + $validated['y_content'] + 
                           $validated['pr_content'];

            // Validate total tidak melebihi 100%
            if ($totalRecovery > 100) {
                throw new \Exception("Total recovery tidak boleh melebihi 100%. Saat ini: {$totalRecovery}%");
            }

            // Create lab analysis record
            $analysis = LabAnalysis::create([
                'batch_id' => $batch->id,
                'nd_content' => $validated['nd_content'],
                'la_content' => $validated['la_content'],
                'ce_content' => $validated['ce_content'],
                'y_content' => $validated['y_content'],
                'pr_content' => $validated['pr_content'],
                'total_recovery' => round($totalRecovery, 2),
                'analyst_user_id' => Auth::id(),
                'analyzed_at' => now(),
                'notes' => $validated['notes'],
            ]);

            // Update batch dengan kandungan LTJ
            $batch->update([
                'nd_content' => $validated['nd_content'],
                'la_content' => $validated['la_content'],
                'ce_content' => $validated['ce_content'],
                'y_content' => $validated['y_content'],
                'pr_content' => $validated['pr_content'],
                'status' => 'analyzed',
                'current_location' => 'Lab - Analysis Complete',
            ]);

            // Record checkpoint CP6
            $this->checkpointService->recordCheckpoint(
                $batch,
                'CP6',
                Auth::id(),
                "Analisis LTJ selesai. Total Recovery: {$totalRecovery}% (Nd: {$validated['nd_content']}%, La: {$validated['la_content']}%, Ce: {$validated['ce_content']}%, Y: {$validated['y_content']}%, Pr: {$validated['pr_content']}%)"
            );

            // Log activity
            $batch->logs()->create([
                'action' => 'LAB_ANALYSIS_COMPLETE',
                'actor_user_id' => Auth::id(),
                'notes' => "Analisis LTJ selesai. Total recovery: {$totalRecovery}%. " . 
                          "Komposisi: Nd {$validated['nd_content']}%, La {$validated['la_content']}%, " .
                          "Ce {$validated['ce_content']}%, Y {$validated['y_content']}%, Pr {$validated['pr_content']}%",
            ]);

            DB::commit();

            return redirect()
                ->route('lab.view-analysis', $batch)
                ->with('success', "Analisis batch {$batch->batch_code} berhasil disimpan! Total Recovery: {$totalRecovery}%");

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
                ->with('error', 'Batch ini belum dianalisis');
        }

        // Prepare composition data untuk pie chart
        $composition = collect([
            [
                'element' => 'Neodymium (Nd)', 
                'symbol' => 'Nd',
                'percentage' => $analysis->nd_content,
                'color' => '#e74c3c', // Red
            ],
            [
                'element' => 'Lanthanum (La)', 
                'symbol' => 'La',
                'percentage' => $analysis->la_content,
                'color' => '#3498db', // Blue
            ],
            [
                'element' => 'Cerium (Ce)', 
                'symbol' => 'Ce',
                'percentage' => $analysis->ce_content,
                'color' => '#2ecc71', // Green
            ],
            [
                'element' => 'Yttrium (Y)', 
                'symbol' => 'Y',
                'percentage' => $analysis->y_content,
                'color' => '#f39c12', // Orange
            ],
            [
                'element' => 'Praseodymium (Pr)', 
                'symbol' => 'Pr',
                'percentage' => $analysis->pr_content,
                'color' => '#9b59b6', // Purple
            ],
        ])->filter(function($item) {
            return $item['percentage'] > 0; // Only show elements with content
        });

        return view('lab.view-analysis', compact('batch', 'analysis', 'composition'));
    }

    /* List all analyses - untuk reporting */
    public function listAnalyses()
    {
        $analyses = LabAnalysis::with(['batch.productCode', 'analyst'])
            ->orderBy('analyzed_at', 'desc')
            ->paginate(20);

        // Calculate statistics
        $stats = [
            'total_analyses' => LabAnalysis::count(),
            'avg_nd' => round(LabAnalysis::avg('nd_content') ?? 0, 2),
            'avg_la' => round(LabAnalysis::avg('la_content') ?? 0, 2),
            'avg_ce' => round(LabAnalysis::avg('ce_content') ?? 0, 2),
            'avg_y' => round(LabAnalysis::avg('y_content') ?? 0, 2),
            'avg_pr' => round(LabAnalysis::avg('pr_content') ?? 0, 2),
            'avg_total' => round(LabAnalysis::avg('total_recovery') ?? 0, 2),
        ];

        return view('lab.list-analyses', compact('analyses', 'stats'));
    }
}