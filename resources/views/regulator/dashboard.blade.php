@extends('layouts.app')

@section('title', 'Dashboard Regulator')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --primary: #3e5c74;
        --primary-dark: #2d4454;
        --primary-light: #e8eef3;
        --gold: #c5a572;
    }

    body {
        font-family: 'Poppins', system-ui, sans-serif;
        background: linear-gradient(135deg, var(--primary-light) 0%, #f5f8fa 100%);
    }

    /* Header Section */
    .dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .dashboard-title {
        font-weight: 700;
        color: var(--primary);
        margin: 0;
        font-size: 1.75rem;
    }

    .download-btn {
        background: linear-gradient(135deg, var(--gold) 0%, #a58960 100%);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        text-decoration: none;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        box-shadow: 0 4px 12px rgba(197,165,114,0.3);
        transition: all 0.3s ease;
    }

    .download-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(197,165,114,0.4);
        color: white;
    }

    /* KPI Cards - 2 BARIS x 4 KOLOM */
    .kpi-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr); /* 4 kolom */
        grid-template-rows: repeat(2, 1fr);    /* 2 baris */
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
        transition: all 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(62,92,116,0.18);
    }

    .kpi-icon {
        width: 45px;
        height: 45px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.3rem;
        color: white;
        margin-bottom: 0.75rem;
    }

    .kpi-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .kpi-label {
        font-size: 0.85rem;
        color: rgba(62,92,116,0.7);
        font-weight: 500;
    }

    /* Responsive - Tablet */
    @media (max-width: 1200px) {
        .kpi-container {
            grid-template-columns: repeat(2, 1fr); /* 2 kolom */
            grid-template-rows: repeat(4, 1fr);    /* 4 baris */
        }
    }

    /* Responsive - Mobile */
    @media (max-width: 768px) {
        .kpi-container {
            grid-template-columns: 1fr;           /* 1 kolom */
            grid-template-rows: repeat(8, auto);  /* 8 baris */
        }
        
        .kpi-value {
            font-size: 1.5rem;
        }
        
        .kpi-icon {
            width: 40px;
            height: 40px;
            font-size: 1.1rem;
        }
    }

    /* Charts Grid */
    .charts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .chart-card {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .chart-title {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .chart-container {
        position: relative;
        height: 300px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .empty-chart {
        text-align: center;
        padding: 3rem;
        color: rgba(62,92,116,0.5);
    }

    .empty-chart i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

    /* Comparison Cards */
    .comparison-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .comparison-card {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .comparison-header {
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .comparison-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
        padding-bottom: 0.75rem;
        border-bottom: 1px solid rgba(62,92,116,0.08);
    }

    .comparison-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .comparison-label {
        font-size: 0.9rem;
        color: rgba(62,92,116,0.7);
    }

    .comparison-value {
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--primary);
    }

    .trend-badge {
        display: inline-block;
        padding: 0.25rem 0.6rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }

    .trend-up {
        background: #d4edda;
        color: #155724;
    }

    .trend-down {
        background: #f8d7da;
        color: #721c24;
    }

    /* Table Container */
    .table-container {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .table-title {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 1rem;
        font-size: 1.1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        font-weight: 600;
        padding: 0.9rem 0.75rem;
        border: none;
        text-align: center;
        font-size: 0.85rem;
    }

    .table thead th:first-child { border-radius: 10px 0 0 0; }
    .table thead th:last-child { border-radius: 0 10px 0 0; }

    .table tbody td {
        padding: 0.85rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(62,92,116,0.08);
        text-align: center;
        font-size: 0.9rem;
    }

    .table tbody tr:hover {
        background: rgba(62,92,116,0.03);
    }

    /* Activity Log Styles */
    .activity-log {
        max-height: 500px;
        overflow-y: auto;
    }

    .log-item {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        padding: 1rem;
        margin-bottom: 0.75rem;
        background: rgba(62,92,116,0.03);
        border-radius: 10px;
        border-left: 4px solid var(--primary);
        transition: all 0.2s ease;
    }

    .log-item:hover {
        background: rgba(62,92,116,0.06);
        transform: translateX(5px);
    }

    .log-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.1rem;
    }

    .log-content {
        flex: 1;
    }

    .log-title {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.25rem;
        font-size: 0.95rem;
    }

    .log-detail {
        font-size: 0.85rem;
        color: rgba(62,92,116,0.7);
    }

    .log-detail a {
        color: var(--primary);
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .log-detail a:hover {
        color: var(--gold);
        text-decoration: underline;
    }

    .log-time {
        font-size: 0.75rem;
        color: rgba(62,92,116,0.5);
        margin-top: 0.25rem;
    }

    /* Badge Styles */
    .badge {
        padding: 0.35rem 0.7rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .badge-primary { background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%); color: white; }
    .badge-success { background: linear-gradient(135deg, #198754 0%, #4caf50 100%); color: white; }
    .badge-warning { background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%); color: #000; }
    .badge-danger { background: linear-gradient(135deg, #dc3545 0%, #ff6b7a 100%); color: white; }
    .badge-secondary { background: linear-gradient(135deg, #6c757d 0%, #858796 100%); color: white; }

    /* Responsive */
    @media (max-width: 768px) {
        .charts-grid {
            grid-template-columns: 1fr;
        }
        
        .comparison-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-header {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
        }
    }
</style>

<div class="container-fluid py-4">
    <!-- Header with Download Button -->
    <div class="dashboard-header">
        <h2 class="dashboard-title">
            <i class="bi bi-shield-check me-2"></i>Dashboard Regulator {{ auth()->user()->role === 'g_bim' ? 'BIM' : 'ESDM' }}
        </h2>
        <a href="{{ route('regulator.report.download') }}" class="download-btn">
            <i class="bi bi-file-earmark-pdf"></i>
            Download Laporan PDF
        </a>
    </div>

    <!-- KPI Cards - Main Overview -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #3e5c74 0%, #2d4454 100%);">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="kpi-value">{{ $stats['total_batches'] }}</div>
            <div class="kpi-label">Total Batches</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);">
                <i class="bi bi-gear-fill"></i>
            </div>
            <div class="kpi-value">{{ $stats['active_batches'] }}</div>
            <div class="kpi-label">Active Batches</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #28a745 0%, #4caf50 100%);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="kpi-value">{{ $stats['completed_batches'] }}</div>
            <div class="kpi-label">Completed</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #6eb5c0 100%);">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['avg_recovery'], 1) }}%</div>
            <div class="kpi-label">Avg Recovery</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <i class="bi bi-gem"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['warehouse_stock']['zircon'], 0) }}</div>
            <div class="kpi-label">Zircon (kg)</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['warehouse_stock']['ilmenite'], 0) }}</div>
            <div class="kpi-label">Ilmenite (kg)</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <i class="bi bi-archive"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['warehouse_stock']['monasit'], 0) }}</div>
            <div class="kpi-label">Monasit (kg)</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--gold) 0%, #a58960 100%);">
                <i class="bi bi-box-arrow-right"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['export_weight'], 0) }}</div>
            <div class="kpi-label">Total Export (kg)</div>
        </div>
    </div>

    <!-- Dual Pie Charts -->
    <div class="charts-grid">
        <!-- Chart 1: Warehouse Konsentrat -->
        <div class="chart-card">
            <h5 class="chart-title">
                <i class="bi bi-pie-chart"></i>
                Komposisi Stok Warehouse
            </h5>
            <div class="chart-container">
                @php
                    $hasStockData = $stockComposition->sum('weight') > 0;
                @endphp
                
                @if($hasStockData)
                    <canvas id="warehouseChart"></canvas>
                @else
                    <div class="empty-chart">
                        <i class="bi bi-inbox"></i>
                        <p style="margin: 0;">Belum ada stok warehouse</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Chart 2: LTJ Composition -->
        <div class="chart-card">
            <h5 class="chart-title">
                <i class="bi bi-graph-up"></i>
                Komposisi LTJ (Lab Analysis)
            </h5>
            <div class="chart-container">
                @php
                    $hasLTJData = $ltjComposition->sum('percentage') > 0;
                @endphp
                
                @if($hasLTJData)
                    <canvas id="ltjChart"></canvas>
                @else
                    <div class="empty-chart">
                        <i class="bi bi-inbox"></i>
                        <p style="margin: 0;">Belum ada data analisis LTJ</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Head-to-Head: This Month vs Last Month -->
    <div class="comparison-grid">
        <div class="comparison-card">
            <h6 class="comparison-header">
                <i class="bi bi-calendar-month me-2"></i>Bulan Ini vs Bulan Lalu
            </h6>
            
            <div class="comparison-row">
                <span class="comparison-label">Jumlah Batch</span>
                <div>
                    <span class="comparison-value">{{ $monthComparison['this_month']['batches'] }}</span>
                    @php
                        $diff = $monthComparison['this_month']['batches'] - $monthComparison['last_month']['batches'];
                        $percentage = $monthComparison['last_month']['batches'] > 0 
                            ? round(($diff / $monthComparison['last_month']['batches']) * 100, 1) 
                            : 0;
                    @endphp
                    @if($monthComparison['last_month']['batches'] > 0)
                    <span class="trend-badge {{ $diff >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="bi bi-{{ $diff >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($percentage) }}%
                    </span>
                    @endif
                </div>
            </div>

            <div class="comparison-row">
                <span class="comparison-label">Berat Produksi (kg)</span>
                <div>
                    <span class="comparison-value">{{ number_format($monthComparison['this_month']['weight'], 0) }}</span>
                    @php
                        $diff = $monthComparison['this_month']['weight'] - $monthComparison['last_month']['weight'];
                        $percentage = $monthComparison['last_month']['weight'] > 0 
                            ? round(($diff / $monthComparison['last_month']['weight']) * 100, 1) 
                            : 0;
                    @endphp
                    @if($monthComparison['last_month']['weight'] > 0)
                    <span class="trend-badge {{ $diff >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="bi bi-{{ $diff >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($percentage) }}%
                    </span>
                    @endif
                </div>
            </div>

            <div class="comparison-row">
                <span class="comparison-label">Total Export (kg)</span>
                <div>
                    <span class="comparison-value">{{ number_format($monthComparison['this_month']['exports'], 0) }}</span>
                    @php
                        $diff = $monthComparison['this_month']['exports'] - $monthComparison['last_month']['exports'];
                        $percentage = $monthComparison['last_month']['exports'] > 0 
                            ? round(($diff / $monthComparison['last_month']['exports']) * 100, 1) 
                            : 0;
                    @endphp
                    @if($monthComparison['last_month']['exports'] > 0)
                    <span class="trend-badge {{ $diff >= 0 ? 'trend-up' : 'trend-down' }}">
                        <i class="bi bi-{{ $diff >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                        {{ abs($percentage) }}%
                    </span>
                    @endif
                </div>
            </div>
        </div>

        <div class="comparison-card">
            <h6 class="comparison-header">
                <i class="bi bi-graph-up-arrow me-2"></i>Bulan Lalu (Referensi)
            </h6>
            
            <div class="comparison-row">
                <span class="comparison-label">Jumlah Batch</span>
                <span class="comparison-value">{{ $monthComparison['last_month']['batches'] }}</span>
            </div>

            <div class="comparison-row">
                <span class="comparison-label">Berat Produksi (kg)</span>
                <span class="comparison-value">{{ number_format($monthComparison['last_month']['weight'], 0) }}</span>
            </div>

            <div class="comparison-row">
                <span class="comparison-label">Total Export (kg)</span>
                <span class="comparison-value">{{ number_format($monthComparison['last_month']['exports'], 0) }}</span>
            </div>
        </div>
    </div>

    <!-- Production Trend (7 Days) -->
    <div class="table-container">
        <h5 class="table-title">
            <i class="bi bi-bar-chart-line"></i>
            Tren Produksi 7 Hari Terakhir
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jumlah Batch</th>
                    <th>Total Berat (kg)</th>
                    <th>Rata-rata per Batch (kg)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($productionTrend as $day)
                <tr>
                    <td><strong>{{ $day['date'] }}</strong></td>
                    <td>{{ $day['count'] }}</td>
                    <td>{{ number_format($day['weight'], 2) }}</td>
                    <td>{{ $day['count'] > 0 ? number_format($day['weight'] / $day['count'], 2) : '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Activity Logs (Recent 15) with Clickable Batch Links -->
    <div class="table-container">
        <h5 class="table-title">
            <i class="bi bi-activity"></i>
            Log Aktivitas Terbaru
        </h5>
        <div class="activity-log">
            @forelse($recentLogs as $log)
                <div class="log-item">
                    <div class="log-icon" style="background: 
                        @if($log->action == 'created') linear-gradient(135deg, #28a745 0%, #4caf50 100%)
                        @elseif($log->action == 'checkpoint_recorded') linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%)
                        @elseif($log->action == 'exported') linear-gradient(135deg, #ffc107 0%, #ffb300 100%)
                        @else linear-gradient(135deg, #6c757d 0%, #858796 100%)
                        @endif;">
                        <i class="bi bi-
                            @if($log->action == 'created') plus-circle
                            @elseif($log->action == 'checkpoint_recorded') check-circle
                            @elseif($log->action == 'exported') box-arrow-right
                            @else activity
                            @endif" style="color: white;"></i>
                    </div>
                    <div class="log-content">
                        <div class="log-title">{{ $log->getActionLabel() }}</div>
                        <div class="log-detail">
                            Batch: <a href="{{ route('regulator.batch.show', $log->batch->id) }}" target="_blank">
                                {{ $log->batch->batch_code }}
                            </a>
                            @if($log->batch->productCode)
                                <span class="badge" style="background: 
                                    {{ $log->batch->productCode->material == 'ZIRCON' ? '#e74c3c' : 
                                       ($log->batch->productCode->material == 'ILMENITE' ? '#9b59b6' : '#27ae60') }}; 
                                    color: white; margin-left: 0.5rem;">
                                    {{ $log->batch->productCode->material }}
                                </span>
                            @endif
                            @if($log->actor)
                                • Oleh: {{ $log->actor->name }}
                            @endif
                        </div>
                        <div class="log-time">
                            <i class="bi bi-clock"></i> {{ $log->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @empty
                <div class="empty-chart">
                    <i class="bi bi-inbox"></i>
                    <p style="margin: 0;">Belum ada aktivitas</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Recent Exports -->
    @if($recentExports->count() > 0)
    <div class="table-container">
        <h5 class="table-title">
            <i class="bi bi-box-arrow-right"></i>
            Export Terbaru
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Material</th>
                    <th>Berat (kg)</th>
                    <th>Tipe</th>
                    <th>Destination</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentExports->take(10) as $export)
                <tr>
                    <td>
                        <a href="{{ route('regulator.batch.show', $export->batch->id) }}" target="_blank" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $export->batch->batch_code }}
                        </a>
                    </td>
                    <td>
                        <span class="badge" style="background: 
                            {{ $export->batch->productCode->material == 'ZIRCON' ? '#e74c3c' : 
                               ($export->batch->productCode->material == 'ILMENITE' ? '#9b59b6' : '#27ae60') }}; 
                            color: white;">
                            {{ $export->batch->productCode->material ?? '-' }}
                        </span>
                    </td>
                    <td>{{ number_format($export->weight_kg, 0) }}</td>
                    <td>
                        <span class="badge {{ $export->export_type == 'export' ? 'badge-primary' : 'badge-success' }}">
                            {{ strtoupper($export->export_type) }}
                        </span>
                    </td>
                    <td>{{ $export->destination }}</td>
                    <td>{{ $export->exported_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent Lab Analysis -->
    @if($recentAnalysis->count() > 0)
    <div class="table-container">
        <h5 class="table-title">
            <i class="bi bi-microscope"></i>
            Analisis Lab Terbaru
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Nd (%)</th>
                    <th>La (%)</th>
                    <th>Ce (%)</th>
                    <th>Y (%)</th>
                    <th>Pr (%)</th>
                    <th>Recovery (%)</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentAnalysis->take(10) as $analysis)
                <tr>
                    <td>
                        <a href="{{ route('regulator.batch.show', $analysis->batch->id) }}" target="_blank" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                            {{ $analysis->batch->batch_code }}
                        </a>
                    </td>
                    <td>{{ number_format($analysis->nd_content, 2) }}</td>
                    <td>{{ number_format($analysis->la_content, 2) }}</td>
                    <td>{{ number_format($analysis->ce_content, 2) }}</td>
                    <td>{{ number_format($analysis->y_content, 2) }}</td>
                    <td>{{ number_format($analysis->pr_content, 2) }}</td>
                    <td><strong style="color: var(--gold);">{{ number_format($analysis->total_recovery, 2) }}</strong></td>
                    <td>{{ $analysis->analyzed_at->format('d M Y') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Chart 1: Warehouse Konsentrat
    @php
        $hasStockData = $stockComposition->sum('weight') > 0;
    @endphp
    
    @if($hasStockData)
    const warehouseCtx = document.getElementById('warehouseChart');
    if (warehouseCtx) {
        new Chart(warehouseCtx, {
            type: 'pie',
            data: {
                labels: @json($stockComposition->pluck('material')),
                datasets: [{
                    data: @json($stockComposition->pluck('weight')),
                    backgroundColor: @json($stockComposition->pluck('color')),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 13, family: 'Poppins', weight: '600' },
                            padding: 15,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        const total = data.datasets[0].data.reduce((a, b) => a + b, 0);
                                        const percentage = ((value / total) * 100).toFixed(1);
                                        return {
                                            text: `${label}: ${value.toFixed(0)} kg (${percentage}%)`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(62, 92, 116, 0.95)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 14, family: 'Poppins', weight: '700' },
                        bodyFont: { size: 13, family: 'Poppins' },
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${context.label}: ${value.toFixed(0)} kg (${percentage}%)`;
                            }
                        }
                    }
                },
                animation: { 
                    duration: 1200, 
                    easing: 'easeInOutQuart' 
                }
            }
        });
    }
    @endif

    // Chart 2: LTJ Composition
    @php
        $hasLTJData = $ltjComposition->sum('percentage') > 0;
    @endphp
    
    @if($hasLTJData)
    const ltjCtx = document.getElementById('ltjChart');
    if (ltjCtx) {
        new Chart(ltjCtx, {
            type: 'pie',
            data: {
                labels: @json($ltjComposition->pluck('element')),
                datasets: [{
                    data: @json($ltjComposition->pluck('percentage')),
                    backgroundColor: @json($ltjComposition->pluck('color')),
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 15
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            font: { size: 12, family: 'Poppins', weight: '600' },
                            padding: 12,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            generateLabels: function(chart) {
                                const data = chart.data;
                                if (data.labels.length && data.datasets.length) {
                                    return data.labels.map((label, i) => {
                                        const value = data.datasets[0].data[i];
                                        return {
                                            text: `${label}: ${value.toFixed(2)}%`,
                                            fillStyle: data.datasets[0].backgroundColor[i],
                                            hidden: false,
                                            index: i
                                        };
                                    });
                                }
                                return [];
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(62, 92, 116, 0.95)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 14, family: 'Poppins', weight: '700' },
                        bodyFont: { size: 13, family: 'Poppins' },
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed.toFixed(2)}%`;
                            }
                        }
                    }
                },
                animation: { 
                    duration: 1200, 
                    easing: 'easeInOutQuart' 
                }
            }
        });
    }
    @endif
});
</script>
@endsection