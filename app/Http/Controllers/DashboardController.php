<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\Partner;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Fungsi private untuk mengambil data dashboard global.
     * Ini mencegah duplikasi kode.
     */
    private function getGlobalDashboardData()
    {
        $user = Auth::user();

        // Data untuk KPI Cards
        $stats = [
            'totalBatchesActive' => Batch::whereIn('status', ['active', 'shipped', 'received'])->count(),
            'batchesInTransit' => Batch::where('status', 'shipped')->count(),
            'batchesProcessed' => Batch::where('status', 'processed')->count(), 
            'batchesDelivered' => Batch::where('status', 'delivered')->count(),
        ];
        
        // Data untuk Notifikasi Penting
        $alerts = [];
        $pendingPartners = Partner::where('status', 'pending')->count();
        if ($user->isSuperAdmin() && $pendingPartners > 0) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "Ada $pendingPartners mitra baru yang menunggu persetujuan.",
                'link' => route('admin.partners.index')
            ];
        }

        // Data untuk Volume Chart (7 hari terakhir)
        $volumeChart = Batch::select(
                DB::raw('DATE(created_at) as date'), 
                DB::raw('count(*) as total')
            )
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        // Data Performa Mitra
        $partnerPerformance = Partner::withCount('batches')
            ->where('status', 'approved')
            ->orderBy('batches_count', 'desc')
            ->take(5)
            ->get();

        // Data Aktivitas Terbaru
        $recentActivities = BatchLog::with(['batch', 'actor']) // Memuat relasi batch dan actor
            ->latest()
            ->take(10)
            ->get();

        // Gabungkan semua data
        return array_merge($stats, [
            'alerts' => $alerts,
            'volumeChart' => $volumeChart,
            'partnerPerformance' => $partnerPerformance,
            'recentActivities' => $recentActivities,
        ]);
    }

    /**
     * Dashboard utama, hanya untuk Admin/SuperAdmin
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Cek role. Jika bukan Admin, alihkan ke dashboard spesifik mereka.
        // INI ADALAH SUMBER LOOP SEBELUMNYA.
        switch ($user->role) {
            case 'operator':
                return redirect()->route('scan.index');
            case 'mitra_middlestream':
                return redirect()->route('mitra.dashboard');
            case 'mitra_downstream':
                return redirect()->route('downstream.dashboard');
            case 'auditor':
                return redirect()->route('audit.dashboard');
        }

        // Jika lolos (Admin/SuperAdmin), tampilkan dashboard global.
        $data = $this->getGlobalDashboardData();
        return view('dashboard.index', $data);
    }

    /**
     * Dashboard untuk Mitra Middlestream
     * PERBAIKAN: Memanggil data dan view secara langsung.
     */
    public function mitraDashboard()
    {
        $data = $this->getGlobalDashboardData();
        // Anda bisa membuat view 'dashboard.mitra' jika perlu
        return view('dashboard.index', $data);
    }

    /**
     * Dashboard untuk Mitra Downstream
     * PERBAIKAN: Memanggil data dan view secara langsung.
     */
    public function downstreamDashboard()
    {
        $data = $this->getGlobalDashboardData();
        // Anda bisa membuat view 'dashboard.downstream' jika perlu
        return view('dashboard.index', $data);
    }

    /**
     * Dashboard untuk Auditor
     * PERBAIKAN: Memanggil data dan view secara langsung.
     */
    public function auditorDashboard()
    {
        $data = $this->getGlobalDashboardData();
        // Anda bisa membuat view 'dashboard.auditor' jika perlu
        return view('dashboard.index', $data);
    }
}