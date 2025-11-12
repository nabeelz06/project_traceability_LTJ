<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\Partner;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Dashboard utama - routing otomatis berdasarkan role
     * User akses /dashboard → otomatis diarahkan ke dashboard sesuai rolenya
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
        } elseif ($user->isGovernment()) {
            return $this->auditDashboard();
        }

        // Fallback jika role tidak dikenali
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
            'active_batches' => Batch::active()->count(),
            'batches_in_transit' => Batch::where('status', 'shipped')->count(),
            'batches_delivered' => Batch::where('status', 'delivered')->count(),
        ];

        // Notifikasi pending approvals
        $alerts = [];
        $pendingPartners = Partner::where('status', 'pending')->count();
        if (Auth::user()->isSuperAdmin() && $pendingPartners > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Ada {$pendingPartners} mitra baru menunggu persetujuan.",
                'link' => route('admin.partners.index', ['status' => 'pending'])
            ];
        }

        // Chart volume batch (7 hari terakhir)
        $volumeChart = Batch::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Batch terbaru
        $recentBatches = Batch::with(['productCode', 'creator', 'currentPartner'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Aktivitas terbaru
        $recentActivities = BatchLog::with(['batch', 'actor'])
            ->orderBy('created_at', 'desc')
            ->take(15)
            ->get();

        // Statistik mitra
        $partnerStats = Partner::withCount('batches')
            ->where('status', 'approved')
            ->orderBy('batches_count', 'desc')
            ->take(5)
            ->get();

        return view('dashboard.admin', compact(
            'stats', 
            'alerts', 
            'volumeChart', 
            'recentBatches', 
            'recentActivities',
            'partnerStats'
        ));
    }

    /**
     * Dashboard untuk Mitra Middlestream
     */
    public function mitraDashboard()
    {
        $user = Auth::user();
        $partnerId = $user->partner_id;

        // KPI Stats
        $stats = [
            'incoming_batches' => Batch::where('status', 'shipped')
                ->whereHas('logs', function($q) use ($partnerId) {
                    // Batch yang sedang dalam pengiriman menuju mitra ini
                })
                ->count(),
            'received_batches' => Batch::where('current_owner_partner_id', $partnerId)
                ->where('status', 'received')
                ->count(),
            'processed_batches' => Batch::where('current_owner_partner_id', $partnerId)
                ->where('status', 'processed')
                ->count(),
            'child_batches' => Batch::where('created_by_user_id', $user->id)
                ->whereNotNull('parent_batch_id')
                ->count(),
        ];

        // Batch yang perlu di-checkin (shipped status)
        $needCheckin = Batch::where('status', 'shipped')
            ->with(['productCode', 'creator'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // Batch yang bisa diproses (sudah diterima, belum diproses)
        $readyToProcess = Batch::where('current_owner_partner_id', $partnerId)
            ->where('status', 'received')
            ->whereNull('parent_batch_id') // Hanya parent batch
            ->with(['productCode'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Aktivitas terbaru mitra ini
        $recentActivities = BatchLog::whereHas('batch', function($q) use ($partnerId) {
                $q->where('current_owner_partner_id', $partnerId);
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

        // KPI Stats
        $stats = [
            'incoming_shipments' => Batch::where('status', 'shipped')
                ->count(),
            'received_batches' => Batch::where('current_owner_partner_id', $partnerId)
                ->where('status', 'delivered')
                ->count(),
            'total_weight' => Batch::where('current_owner_partner_id', $partnerId)
                ->where('status', 'delivered')
                ->sum('current_weight'),
        ];

        // Batch yang perlu di-checkin
        $needCheckin = Batch::where('status', 'shipped')
            ->with(['productCode', 'parentBatch'])
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        // Riwayat penerimaan
        $receivedBatches = Batch::where('current_owner_partner_id', $partnerId)
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
     * Dashboard untuk Government (G:BIM & G:ESDM)
     */
    public function auditDashboard()
    {
        // KPI Stats
        $stats = [
            'total_batches' => Batch::count(),
            'active_batches' => Batch::active()->count(),
            'total_partners' => Partner::approved()->count(),
            'total_logs' => BatchLog::count(),
        ];

        // Deteksi anomali
        $anomalies = [];
        
        // Batch dengan status quarantine
        $quarantinedBatches = Batch::where('status', 'quarantine')->count();
        if ($quarantinedBatches > 0) {
            $anomalies[] = [
                'type' => 'danger',
                'message' => "{$quarantinedBatches} batch dalam status karantina perlu investigasi",
                'link' => route('batches.index', ['status' => 'quarantine'])
            ];
        }

        // Aktivitas mencurigakan - terlalu banyak koreksi manual
        $suspiciousCorrections = BatchLog::where('action', 'corrected')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        if ($suspiciousCorrections > 5) {
            $anomalies[] = [
                'type' => 'warning',
                'message' => "{$suspiciousCorrections} koreksi manual terdeteksi dalam 7 hari terakhir",
                'link' => route('audit.logs.batch', ['action' => 'corrected'])
            ];
        }

        // Batch dengan umur terlalu lama (> 60 hari masih aktif)
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

        // Statistik aktivitas per hari (30 hari terakhir)
        $dailyActivity = BatchLog::select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Log terbaru (semua aktivitas)
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
}