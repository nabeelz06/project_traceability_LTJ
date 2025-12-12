<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\ExportLog;
use App\Models\LabAnalysis;
use App\Models\BatchLog;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class PDFReportController extends Controller
{
    /* Generate Regulator Report PDF */
    public function generateRegulatorReport()
    {
        // Data cadangan konsentrat
        $warehouseStock = [
            'zircon' => $this->getStockByMaterial('ZIRCON'),
            'ilmenite' => $this->getStockByMaterial('ILMENITE'),
            'monasit' => $this->getStockByMaterial('MON'),
        ];

        // Data cadangan LTJ (average dari lab analysis)
        $ltjStock = [
            'nd' => LabAnalysis::avg('nd_content') ?? 0,
            'la' => LabAnalysis::avg('la_content') ?? 0,
            'ce' => LabAnalysis::avg('ce_content') ?? 0,
            'y' => LabAnalysis::avg('y_content') ?? 0,
            'pr' => LabAnalysis::avg('pr_content') ?? 0,
            'total_recovery' => LabAnalysis::avg('total_recovery') ?? 0,
        ];

        // Log aktivitas 1 bulan terakhir
        $recentLogs = BatchLog::with('batch', 'actor')
            ->where('created_at', '>=', Carbon::now()->subMonth())
            ->orderBy('created_at', 'desc')
            ->get();

        // Data ekspor 1 bulan terakhir
        $exports = ExportLog::with('batch.productCode')
            ->where('exported_at', '>=', Carbon::now()->subMonth())
            ->orderBy('exported_at', 'desc')
            ->get();

        $totalExport = $exports->sum('weight_kg');

        // Analisis per kuartal (1 tahun terakhir)
        $quarterlyAnalysis = $this->getQuarterlyAnalysis();

        // Generate PDF
        $pdf = Pdf::loadView('reports.regulator-report', compact(
            'warehouseStock',
            'ltjStock',
            'recentLogs',
            'exports',
            'totalExport',
            'quarterlyAnalysis'
        ));

        $filename = 'Laporan_Regulator_' . Carbon::now()->format('Y-m-d_His') . '.pdf';
        
        return $pdf->download($filename);
    }

    /* Helper: Get stock by material */
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

    /* Helper: Get quarterly analysis (4 quarters) */
    private function getQuarterlyAnalysis()
    {
        $quarters = [];
        
        for ($i = 3; $i >= 0; $i--) {
            $quarterStart = Carbon::now()->subQuarters($i)->startOfQuarter();
            $quarterEnd = Carbon::now()->subQuarters($i)->endOfQuarter();
            
            $quarters[] = [
                'quarter' => 'Q' . $quarterStart->quarter . ' ' . $quarterStart->year,
                'production' => Batch::whereBetween('created_at', [$quarterStart, $quarterEnd])->sum('initial_weight'),
                'exports' => ExportLog::whereBetween('exported_at', [$quarterStart, $quarterEnd])->sum('weight_kg'),
                'batches_count' => Batch::whereBetween('created_at', [$quarterStart, $quarterEnd])->count(),
            ];
        }

        return collect($quarters);
    }
}