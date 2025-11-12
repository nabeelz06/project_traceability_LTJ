<?php

namespace App\Http\Controllers;

use App\Models\Batch;
use App\Models\BatchLog;
use App\Models\SystemLog;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Dashboard laporan
     */
    public function index()
    {
        if (!Auth::user()->canExportReports()) {
            abort(403);
        }

        $stats = [
            'total_batches' => Batch::count(),
            'active_batches' => Batch::active()->count(),
            'total_partners' => Partner::approved()->count(),
            'total_logs' => BatchLog::count(),
        ];

        return view('reports.index', compact('stats'));
    }

    /**
     * Laporan operasional
     */
    public function operational(Request $request)
    {
        $query = Batch::with(['productCode', 'creator', 'currentPartner']);

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $batches = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('reports.operational', compact('batches'));
    }

    /**
     * System logs (Super Admin & Government only)
     */
    public function systemLogs(Request $request)
    {
        if (!Auth::user()->isSuperAdmin() && !Auth::user()->isGovernment()) {
            abort(403);
        }

        $logs = SystemLog::with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return view('reports.system-logs', compact('logs'));
    }

    /**
     * Batch logs (untuk audit)
     */
    public function batchLogs(Request $request)
    {
        if (!Auth::user()->canViewAuditLogs()) {
            abort(403);
        }

        $query = BatchLog::with(['batch', 'actor']);

        // Filter berdasarkan batch
        if ($request->filled('batch_id')) {
            $query->where('batch_id', $request->batch_id);
        }

        // Filter berdasarkan action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);

        return view('reports.batch-logs', compact('logs'));
    }

    /**
     * Generate report dengan format tertentu
     */
    public function generate(Request $request)
    {
        $validated = $request->validate([
            'report_type' => 'required|in:batches,logs,partners,full',
            'format' => 'required|in:pdf,excel,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date',
        ]);

        // TODO: Implementasi export ke PDF/Excel/CSV
        // Untuk saat ini return JSON
        
        return response()->json([
            'success' => true,
            'message' => 'Report generation feature coming soon',
            'requested' => $validated,
        ]);
    }

    /**
     * Export report
     */
    public function export(Request $request)
    {
        if (!Auth::user()->canExportReports()) {
            abort(403);
        }

        // TODO: Implementasi export
        return back()->with('info', 'Export feature coming soon');
    }

    /**
     * Laporan untuk mitra
     */
    public function mitraReports(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isMitra()) {
            abort(403);
        }

        $batches = Batch::where('current_owner_partner_id', $user->partner_id)
            ->with(['productCode', 'creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('reports.mitra', compact('batches'));
    }
}