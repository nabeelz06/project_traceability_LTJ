<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ExportLog;
use App\Models\LabAnalysis;
use App\Models\BatchLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RegulatorController extends Controller
{
    /* Dashboard Regulator (BIM/ESDM) */
    public function dashboard()
    {
        // KPI Cards - Overview
        $stats = [
            'total_batches' => Batch::count(),
            'active_batches' => Batch::whereIn('status', ['in_transit', 'processing', 'received'])->count(),
            'completed_batches' => Batch::where('status', 'completed')->count(),
            'avg_recovery' => LabAnalysis::avg('total_recovery') ?? 0,
            'warehouse_stock' => [
                'zircon' => $this->getStockByMaterial('ZIRCON'),
                'ilmenite' => $this->getStockByMaterial('ILMENITE'),
                'monasit' => $this->getStockByMaterial('MON'),
            ],
            'export_weight' => ExportLog::sum('weight_kg') ?? 0,
        ];

        // DEBUG: Log warehouse stock data
        Log::info('Warehouse Stock Data:', $stats['warehouse_stock']);

        // Stock Composition untuk Pie Chart 1 (Warehouse Konsentrat)
        $stockComposition = collect([
            ['material' => 'Zircon', 'weight' => (float)$stats['warehouse_stock']['zircon'], 'color' => '#e74c3c'],
            ['material' => 'Ilmenite', 'weight' => (float)$stats['warehouse_stock']['ilmenite'], 'color' => '#9b59b6'],
            ['material' => 'Monasit', 'weight' => (float)$stats['warehouse_stock']['monasit'], 'color' => '#27ae60'],
        ]);

        // DEBUG: Log stock composition
        Log::info('Stock Composition:', $stockComposition->toArray());

        // LTJ Composition untuk Pie Chart 2 (Lab Analysis)
        $ltjComposition = $this->getLTJComposition();

        // DEBUG: Log LTJ composition
        Log::info('LTJ Composition:', $ltjComposition->toArray());

        // Produksi 7 hari terakhir
        $productionTrend = $this->getProductionTrend(7);

        // Head-to-head: Bulan ini vs Bulan lalu
        $monthComparison = $this->getMonthComparison();

        // Recent batches
        $recentBatches = Batch::with('productCode')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recent exports
        $recentExports = ExportLog::with('batch.productCode', 'operator')
            ->orderBy('exported_at', 'desc')
            ->take(10)
            ->get();

        // Recent lab analysis
        $recentAnalysis = LabAnalysis::with('batch', 'analyst')
            ->orderBy('analyzed_at', 'desc')
            ->take(10)
            ->get();

        // Recent activity logs
        $recentLogs = BatchLog::with('batch.productCode', 'actor')
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        return view('regulator.dashboard', compact(
            'stats',
            'stockComposition',
            'ltjComposition',
            'productionTrend',
            'monthComparison',
            'recentBatches',
            'recentExports',
            'recentAnalysis',
            'recentLogs'
        ));
    }

    /* Helper: Get stock by material */
    private function getStockByMaterial(string $material)
    {
        $stock = Batch::where('process_stage', 'warehouse')
            ->where('status', 'received')
            ->whereNull('export_status')
            ->whereHas('productCode', function($q) use ($material) {
                $q->where('material', $material);
            })
            ->sum('current_weight') ?? 0;

        // DEBUG: Log per material
        Log::info("Stock for {$material}: {$stock}");

        return $stock;
    }

    /* Helper: Get LTJ composition dari semua lab analysis */
    private function getLTJComposition()
    {
        $totalAnalysis = LabAnalysis::count();
        
        return collect([
            ['element' => 'Neodymium (Nd)', 'percentage' => (float)(LabAnalysis::avg('nd_content') ?? 0), 'color' => '#e74c3c'],
            ['element' => 'Lanthanum (La)', 'percentage' => (float)(LabAnalysis::avg('la_content') ?? 0), 'color' => '#3498db'],
            ['element' => 'Cerium (Ce)', 'percentage' => (float)(LabAnalysis::avg('ce_content') ?? 0), 'color' => '#f39c12'],
            ['element' => 'Yttrium (Y)', 'percentage' => (float)(LabAnalysis::avg('y_content') ?? 0), 'color' => '#2ecc71'],
            ['element' => 'Praseodymium (Pr)', 'percentage' => (float)(LabAnalysis::avg('pr_content') ?? 0), 'color' => '#9b59b6'],
        ]);
    }

    /* Helper: Get production trend (7 days) */
    private function getProductionTrend(int $days = 7)
    {
        $trend = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $count = Batch::whereDate('created_at', $date->toDateString())->count();
            $weight = Batch::whereDate('created_at', $date->toDateString())->sum('initial_weight') ?? 0;
            
            $trend[] = [
                'date' => $date->format('d M'),
                'count' => $count,
                'weight' => $weight,
            ];
        }
        return collect($trend);
    }

    /* Helper: Month comparison (This month vs Last month) */
    private function getMonthComparison()
    {
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();

        return [
            'this_month' => [
                'batches' => Batch::whereBetween('created_at', [$thisMonth, Carbon::now()])->count(),
                'weight' => Batch::whereBetween('created_at', [$thisMonth, Carbon::now()])->sum('initial_weight') ?? 0,
                'exports' => ExportLog::whereBetween('exported_at', [$thisMonth, Carbon::now()])->sum('weight_kg') ?? 0,
            ],
            'last_month' => [
                'batches' => Batch::whereBetween('created_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])->count(),
                'weight' => Batch::whereBetween('created_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])->sum('initial_weight') ?? 0,
                'exports' => ExportLog::whereBetween('exported_at', [$lastMonth, $lastMonth->copy()->endOfMonth()])->sum('weight_kg') ?? 0,
            ],
        ];
    }

    /* Show batch detail (for activity log links) */
    public function showBatch($id)
    {
        $batch = Batch::with(['productCode', 'parentBatch', 'splitChildren', 'labAnalyses', 'exportLogs', 'checkpoints'])
            ->findOrFail($id);

        return view('regulator.batch-detail', compact('batch'));
    }
}