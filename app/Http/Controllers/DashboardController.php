<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\Partner;
use App\Models\User;
use App\Models\ExportLog; 
use App\Models\LabAnalysis; 
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard utama - routing otomatis berdasarkan role
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Route ke dashboard sesuai role
        if ($user->isSuperAdmin() || $user->isAdmin()) {
            return $this->adminDashboard();
        } elseif ($user->isOperator()) {
            return redirect()->route('scan.index');
        } elseif ($user->isMitraMiddlestream()) {
            return $this->mitraDashboard();
        } elseif ($user->isMitraDownstream()) {
            return $this->downstreamDashboard();
            
        // PERBAIKAN DI SINI: Cek Regulator Dulu!
        } elseif ($user->role === 'g_bim' || $user->role === 'g_esdm') {
            // Redirect ke RegulatorController yang sudah ada logika lengkapnya
            return redirect()->route('regulator.dashboard'); 
            
        } elseif ($user->isGovernment()) { 
            // Fallback untuk government lain (misal Auditor murni jika ada)
            return $this->auditDashboard();
        }

        return view('dashboard');
    }

    /**
     * Dashboard untuk Super Admin & Admin PT Timah
     */
    private function adminDashboard()
    {
        // KPI Stats
        $stats = [
            'total_batches' => Batch::count(),
            'active_batches' => Batch::whereIn('status', ['ready', 'in_transit', 'checked_in', 'processing'])->count(),
            'batches_in_transit' => Batch::where('status', 'in_transit')->count(),
            'batches_delivered' => Batch::where('status', 'delivered')->count(),
        ];

        // Alerts
        $alerts = [];
        $pendingPartners = Partner::where('status', 'pending')->count();
        if (Auth::user()->isSuperAdmin() && $pendingPartners > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Ada {$pendingPartners} mitra baru menunggu persetujuan.",
                'link' => route('admin.partners.index', ['status' => 'pending'])
            ];
        }

        // Aktivitas Terbaru dengan data lengkap (20 recent activities) + 5 unsur LTJ
        $recentActivities = BatchLog::with(['batch.productCode', 'actor'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get()
            ->map(function($log) {
                $batch = $log->batch;
                return [
                    'batch_code' => $batch->batch_code ?? '-',
                    'timestamp' => $log->created_at,
                    'aktivitas' => $log->getActionLabel(),
                    'user' => $log->actor->name ?? 'System',
                    'tonase' => $batch->tonase ?? 0,
                    'konsentrat' => $batch->konsentrat_persen ?? 0,
                    'keterangan' => $batch->keterangan ?? $log->notes ?? '-',
                    'massa_ltj' => $batch->massa_ltj_kg ?? 0,
                    
                    // 5 Unsur LTJ
                    'nd_content' => $batch->nd_content ?? null,
                    'y_content' => $batch->y_content ?? null,
                    'ce_content' => $batch->ce_content ?? null,
                    'la_content' => $batch->la_content ?? null,
                    'pr_content' => $batch->pr_content ?? null,
                    
                    'batch_id' => $batch->id ?? null,
                ];
            });

        // Status per Batch
        $statusPerBatch = Batch::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get()
            ->map(function($item) {
                return [
                    'status' => $item->status,
                    'label' => $this->getStatusLabel($item->status),
                    'total' => $item->total,
                    'color' => $this->getStatusColor($item->status),
                ];
            });

        // Volume Chart (7 hari terakhir)
        $volumeChart = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $count = Batch::whereDate('created_at', $date->toDateString())->count();
            $volumeChart->push([
                'date' => $date->format('d M'),
                'total' => $count,
            ]);
        }

        // Partner Performance (Top 5)
        $partnerStats = Partner::withCount(['batches' => function($query) {
                $query->where('status', 'delivered');
            }])
            ->where('status', 'approved')
            ->orderBy('batches_count', 'desc')
            ->take(5)
            ->get();

        // Recent Batches
        $recentBatches = Batch::with('productCode')
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        return view('dashboard.admin', compact(
            'stats',
            'alerts',
            'recentActivities',
            'statusPerBatch',
            'volumeChart',
            'partnerStats',
            'recentBatches'
        ));
    }

    /* Dashboard untuk Regulator (BIM/ESDM) */
    public function regulatorDashboard()
    {
        $stats = [
            'total_batches' => Batch::count(),
            'active_batches' => Batch::whereIn('status', ['in_transit', 'processing', 'received'])->count(),
            'completed_batches' => Batch::where('status', 'completed')->count(),
            
            // Warehouse stock untuk pie chart
            'warehouse_stock' => [
                'zircon' => $this->getWarehouseStockByMaterial('ZIRCON'),
                'ilmenite' => $this->getWarehouseStockByMaterial('ILMENITE'),
                'monasit' => $this->getWarehouseStockByMaterial('MON'),
            ],
            
            // Export summary
            'total_exports' => ExportLog::count(),
            'export_weight' => ExportLog::sum('weight_kg'),
            
            // Lab analysis summary
            'pending_analysis' => Batch::where('process_stage', 'lab')
                ->doesntHave('labAnalysis')
                ->count(),
            'completed_analysis' => LabAnalysis::count(),
            'avg_recovery' => LabAnalysis::avg('total_recovery') ?? 0,
        ];

        // Stock composition untuk pie chart (3 warna)
        $stockComposition = collect([
            ['material' => 'Zircon', 'weight' => $stats['warehouse_stock']['zircon'], 'color' => '#e74c3c'],
            ['material' => 'Ilmenite', 'weight' => $stats['warehouse_stock']['ilmenite'], 'color' => '#9b59b6'],
            ['material' => 'Monasit', 'weight' => $stats['warehouse_stock']['monasit'], 'color' => '#27ae60'],
        ])->filter(fn($item) => $item['weight'] > 0);

        // Recent batches
        $recentBatches = Batch::with('productCode')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Recent exports
        $recentExports = ExportLog::with('batch.productCode')
            ->orderBy('exported_at', 'desc')
            ->take(10)
            ->get();

        // Recent lab analysis
        $recentAnalysis = LabAnalysis::with('batch')
            ->orderBy('analyzed_at', 'desc')
            ->take(10)
            ->get();

        return view('regulator.dashboard', compact('stats', 'stockComposition', 'recentBatches', 'recentExports', 'recentAnalysis'));
    }

    /* Helper: Get warehouse stock by material */
    private function getWarehouseStockByMaterial(string $material)
    {
        return Batch::where('process_stage', 'warehouse')
            ->where('status', 'received')
            ->whereNull('export_status')
            ->whereHas('productCode', function($q) use ($material) {
                $q->where('material', $material);
            })
            ->sum('current_weight');
    }

    /**
     * Get status label in Indonesian
     */
    private function getStatusLabel($status)
    {
        $labels = [
            'pending' => 'Menunggu',
            'ready' => 'Siap',
            'in_transit' => 'Dalam Pengiriman',
            'checked_in' => 'Check-In',
            'processing' => 'Diproses',
            'delivered' => 'Terkirim',
            'cancelled' => 'Dibatalkan',
            'quarantine' => 'Karantina',
        ];

        return $labels[$status] ?? ucfirst($status);
    }

    /**
     * Get status color
     */
    private function getStatusColor($status)
    {
        $colors = [
            'pending' => '#ffc107',
            'ready' => '#0dcaf0',
            'in_transit' => '#6eb5c0',
            'checked_in' => '#5a7a95',
            'processing' => '#95a5b5',
            'delivered' => '#7ba888',
            'cancelled' => '#dc3545',
            'quarantine' => '#fd7e14',
        ];

        return $colors[$status] ?? '#6c757d';
    }

    /**
     * Dashboard untuk Mitra Middlestream
     */
    public function mitraDashboard()
    {
        $user = Auth::user();
        $partnerId = $user->partner_id;

        $stats = [
            'incoming_batches' => Batch::where('status', 'in_transit')->count(),
            'received_batches' => Batch::where('current_partner_id', $partnerId)
                ->where('status', 'checked_in')
                ->count(),
            'processed_batches' => Batch::where('current_partner_id', $partnerId)
                ->where('status', 'processing')
                ->count(),
            'child_batches' => Batch::where('created_by', $user->id)
                ->whereNotNull('parent_batch_id')
                ->count(),
        ];

        $needCheckin = Batch::where('status', 'in_transit')
            ->with(['productCode', 'creator'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $readyToProcess = Batch::where('current_partner_id', $partnerId)
            ->where('status', 'checked_in')
            ->with(['productCode'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        $recentActivities = BatchLog::whereHas('batch', function($q) use ($partnerId) {
                $q->where('current_partner_id', $partnerId);
            })
            ->orWhere('actor_user_id', $user->id)
            ->with(['batch', 'actor'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.mitra', compact(
            'stats',
            'needCheckin',
            'readyToProcess',
            'recentActivities'
        ));
    }

    /**
     * Dashboard untuk Mitra Downstream
     */
    public function downstreamDashboard()
    {
        $user = Auth::user();
        $partnerId = $user->partner_id;

        $stats = [
            'incoming_shipments' => Batch::where('status', 'in_transit')->count(),
            'received_batches' => Batch::where('current_partner_id', $partnerId)
                ->where('status', 'delivered')
                ->count(),
            'total_weight' => Batch::where('current_partner_id', $partnerId)
                ->where('status', 'delivered')
                ->sum('current_weight'),
        ];

        $needCheckin = Batch::where('status', 'in_transit')
            ->with(['productCode', 'parentBatch'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $receivedBatches = Batch::where('current_partner_id', $partnerId)
            ->where('status', 'delivered')
            ->with(['productCode', 'parentBatch'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        return view('dashboard.downstream', compact(
            'stats',
            'needCheckin',
            'receivedBatches'
        ));
    }

    /**
     * Dashboard untuk Auditor (Government)
     */
    public function auditDashboard()
    {
        $stats = [
            'total_batches' => Batch::count(),
            'active_batches' => Batch::whereIn('status', ['ready', 'in_transit', 'checked_in', 'processing'])->count(),
            'total_partners' => Partner::where('status', 'approved')->count(),
            'total_logs' => BatchLog::count(),
        ];

        $anomalies = [];
        
        $quarantinedBatches = Batch::where('status', 'quarantine')->count();
        if ($quarantinedBatches > 0) {
            $anomalies[] = [
                'type' => 'danger',
                'message' => "{$quarantinedBatches} batch dalam status karantina perlu investigasi",
                'link' => route('batches.index', ['status' => 'quarantine'])
            ];
        }

        $oldBatches = Batch::where('status', '!=', 'delivered')
            ->where('created_at', '<=', now()->subDays(60))
            ->count();

        if ($oldBatches > 0) {
            $anomalies[] = [
                'type' => 'info',
                'message' => "{$oldBatches} batch berumur lebih dari 60 hari belum delivered",
                'link' => route('batches.index')
            ];
        }

        $dailyActivity = BatchLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $recentLogs = BatchLog::with(['batch', 'actor'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('dashboard.audit', compact(
            'stats',
            'anomalies',
            'dailyActivity',
            'recentLogs'
        ));
    }

    /**
     * Dashboard untuk Wet Process Operator
     */
    public function wetProcessDashboard()
    {
        $user = Auth::user();

        $stats = [
            'today_batches' => Batch::where('process_stage', 'wet_process')
                ->whereDate('created_at', today())
                ->count(),
            'week_batches' => Batch::where('process_stage', 'wet_process')
                ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
            'pending_dispatch' => Batch::where('process_stage', 'wet_process')
                ->whereNull('current_checkpoint')
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

    /**
     * Dashboard untuk Dry Process Operator
     */
    public function dryProcessDashboard()
    {
        $user = Auth::user();

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
            'total_stock_weight' => Batch::where('process_stage', 'dry_process')
                ->where('stocking_status', 'stocked')
                ->sum('current_weight'),
        ];

        $pendingReceive = Batch::where('current_checkpoint', 'CP1')
            ->where('status', 'in_transit')
            ->with('productCode')
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        $stockBatches = Batch::where('process_stage', 'dry_process')
            ->where('stocking_status', 'stocked')
            ->with('productCode')
            ->orderBy('stocked_at', 'desc')
            ->take(10)
            ->get();

        return view('dry-process.dashboard', compact('stats', 'pendingReceive', 'stockBatches'));
    }

    /**
     * Dashboard untuk Warehouse Operator
     */
    public function warehouseDashboard()
    {
        $user = Auth::user();

        $stats = [
            'pending_receive' => Batch::where('current_checkpoint', 'CP3')
                ->where('status', 'in_transit')
                ->count(),
            'zircon_stock' => Batch::where('process_stage', 'warehouse')
                ->where('status', 'received')
                ->whereHas('productCode', fn($q) => $q->where('material', 'ZIRCON'))
                ->sum('current_weight'),
            'ilmenite_stock' => Batch::where('process_stage', 'warehouse')
                ->where('status', 'received')
                ->whereHas('productCode', fn($q) => $q->where('material', 'ILMENITE'))
                ->sum('current_weight'),
            'monasit_stock' => Batch::where('process_stage', 'warehouse')
                ->where('status', 'received')
                ->whereHas('productCode', fn($q) => $q->where('material', 'MON'))
                ->sum('current_weight'),
        ];

        $stockComposition = collect([
            ['material' => 'Zircon', 'weight' => $stats['zircon_stock']],
            ['material' => 'Ilmenite', 'weight' => $stats['ilmenite_stock']],
            ['material' => 'Monasit', 'weight' => $stats['monasit_stock']],
        ])->filter(fn($item) => $item['weight'] > 0);

        $pendingReceive = Batch::where('current_checkpoint', 'CP3')
            ->where('status', 'in_transit')
            ->with('productCode')
            ->orderBy('updated_at', 'desc')
            ->get();

        $recentExports = ExportLog::with('batch.productCode', 'operator')
            ->orderBy('exported_at', 'desc')
            ->take(5)
            ->get();

        return view('warehouse.dashboard', compact('stats', 'stockComposition', 'pendingReceive', 'recentExports'));
    }

    /**
     * Dashboard untuk Lab Operator
     */
    public function labDashboard()
    {
        $user = Auth::user();

        $stats = [
            'pending_receive' => Batch::where('status', 'in_transit')
                ->whereHas('productCode', fn($q) => $q->where('material', 'MON'))
                ->where('is_split', true)
                ->count(),
            'pending_analysis' => Batch::where('process_stage', 'lab')
                ->doesntHave('labAnalysis')
                ->count(),
            'completed_analysis' => LabAnalysis::count(),
            'avg_recovery' => LabAnalysis::avg('total_recovery') ?? 0,
        ];

        $pendingReceive = Batch::where('status', 'in_transit')
            ->whereHas('productCode', fn($q) => $q->where('material', 'MON'))
            ->where('is_split', true)
            ->with('productCode', 'splitParent')
            ->orderBy('updated_at', 'desc')
            ->get();

        $pendingAnalysis = Batch::where('process_stage', 'lab')
            ->doesntHave('labAnalysis')
            ->with('productCode')
            ->orderBy('created_at', 'desc')
            ->get();

        $recentAnalysis = LabAnalysis::with('batch', 'analyst')
            ->orderBy('analyzed_at', 'desc')
            ->take(10)
            ->get();

        return view('lab.dashboard', compact('stats', 'pendingReceive', 'pendingAnalysis', 'recentAnalysis'));
    }
}