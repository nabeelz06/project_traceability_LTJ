<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\ExportLog;
use App\Models\LabAnalysis;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    // Get material flow data (in/out) per period
    public function getMaterialFlowData(string $period = 'week')
    {
        $dateRange = $this->getDateRange($period);

        $inFlow = Batch::whereBetween('created_at', $dateRange)
            ->selectRaw('DATE(created_at) as date, SUM(initial_weight) as total_weight')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $outFlow = ExportLog::whereBetween('exported_at', $dateRange)
            ->selectRaw('DATE(exported_at) as date, SUM(weight_kg) as total_weight')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        return [
            'in_flow' => $inFlow,
            'out_flow' => $outFlow,
        ];
    }

    // Get recovery rate trends
    public function getRecoveryRate(string $period = 'month')
    {
        $dateRange = $this->getDateRange($period);

        return LabAnalysis::whereBetween('analyzed_at', $dateRange)
            ->selectRaw('DATE(analyzed_at) as date, AVG(total_recovery) as avg_recovery')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    // Get current stock levels by product
    public function getStockLevels()
    {
        return Batch::where('stocking_status', 'stocked')
            ->join('product_codes', 'batches.product_code_id', '=', 'product_codes.id')
            ->selectRaw('product_codes.code, product_codes.description, COUNT(*) as batch_count, SUM(batches.current_weight) as total_weight')
            ->groupBy('product_codes.id', 'product_codes.code', 'product_codes.description')
            ->get();
    }

    // Get export trends
    public function getExportTrends(string $period = 'month')
    {
        $dateRange = $this->getDateRange($period);

        return ExportLog::whereBetween('exported_at', $dateRange)
            ->selectRaw('export_type, COUNT(*) as count, SUM(weight_kg) as total_weight')
            ->groupBy('export_type')
            ->get();
    }

    // Get LTJ grade analysis trends
    public function getLtjGradeTrends(string $period = 'month')
    {
        $dateRange = $this->getDateRange($period);

        return LabAnalysis::whereBetween('analyzed_at', $dateRange)
            ->selectRaw('
                DATE(analyzed_at) as date,
                AVG(nd_content) as avg_nd,
                AVG(la_content) as avg_la,
                AVG(ce_content) as avg_ce,
                AVG(y_content) as avg_y,
                AVG(pr_content) as avg_pr
            ')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
    }

    // Get warehouse stock composition (for pie chart)
    public function getWarehouseStockComposition()
    {
        return Batch::where('process_stage', 'warehouse')
            ->where('stocking_status', 'stocked')
            ->join('product_codes', 'batches.product_code_id', '=', 'product_codes.id')
            ->selectRaw('product_codes.material, SUM(batches.current_weight) as total_weight')
            ->groupBy('product_codes.material')
            ->get()
            ->map(function($item) {
                $labels = [
                    'MON' => 'Monasit',
                    'ZIRCON' => 'Zircon',
                    'ILMENITE' => 'Ilmenite',
                ];
                return [
                    'material' => $labels[$item->material] ?? $item->material,
                    'weight' => $item->total_weight,
                ];
            });
    }

    // Helper: Get date range based on period
    private function getDateRange(string $period)
    {
        switch ($period) {
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            case 'year':
                return [Carbon::now()->startOfYear(), Carbon::now()->endOfYear()];
            default:
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
        }
    }
}