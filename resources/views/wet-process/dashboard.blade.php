@extends('layouts.app')

@section('title', 'Dashboard Wet Process')

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

    /* KPI Cards */
    .kpi-container {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        flex: 1;
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
        transition: all 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(62,92,116,0.18);
    }

    .kpi-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .kpi-label {
        font-size: 0.9rem;
        color: rgba(62,92,116,0.7);
        font-weight: 500;
    }

    .action-card {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .table-container {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .table thead th {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        font-weight: 600;
        padding: 1rem 0.75rem;
        border: none;
        text-align: center;
    }

    .table thead th:first-child { border-radius: 10px 0 0 0; }
    .table thead th:last-child { border-radius: 0 10px 0 0; }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(62,92,116,0.08);
        text-align: center;
    }

    .table tbody tr:hover {
        background: rgba(62,92,116,0.03);
    }

    @media (max-width: 992px) {
        .kpi-container { flex-wrap: wrap; }
        .kpi-card { flex: 1 1 calc(50% - 0.5rem); }
    }

    @media (max-width: 576px) {
        .kpi-card { flex: 1 1 100%; }
    }
</style>

<div class="container-fluid py-4">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0;">
            <i class="bi bi-droplet-half me-2"></i>Wet Process - Washing Plant
        </h2>
        <span style="color: rgba(62,92,116,0.6);">{{ now()->format('l, d F Y') }}</span>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #3e5c74 0%, #2d4454 100%);">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="kpi-value">{{ $stats['today_batches'] }}</div>
            <div class="kpi-label">Batch Hari Ini</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #0dcaf0 0%, #6eb5c0 100%);">
                <i class="bi bi-calendar-week"></i>
            </div>
            <div class="kpi-value">{{ $stats['week_batches'] }}</div>
            <div class="kpi-label">Batch Minggu Ini</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);">
                <i class="bi bi-hourglass-split"></i>
            </div>
            <div class="kpi-value">{{ $stats['pending_dispatch'] }}</div>
            <div class="kpi-label">Pending Dispatch</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #28a745 0%, #4caf50 100%);">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['total_weight_today'], 0) }}</div>
            <div class="kpi-label">Tonase Hari Ini (kg)</div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="action-card">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-lightning-charge me-2"></i>Quick Actions
        </h5>
        <div class="d-flex gap-3">
            <a href="{{ route('wet-process.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Buat Batch Baru
            </a>
            <a href="{{ route('wet-process.pending-dispatch') }}" class="btn btn-outline-warning">
                <i class="bi bi-send me-2"></i>Lihat Pending Dispatch
            </a>
        </div>
    </div>

    <!-- Recent Batches Table -->
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-clock-history me-2"></i>Batch Terbaru
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Product</th>
                    <th>Berat (kg)</th>
                    <th>Lokasi Asal</th>
                    <th>Status</th>
                    <th>Dibuat</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentBatches as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>{{ $batch->productCode->description ?? '-' }}</td>
                    <td>{{ number_format($batch->initial_weight, 0) }}</td>
                    <td>{{ $batch->origin_location }}</td>
                    <td>
                        @if($batch->current_checkpoint)
                            <span class="badge badge-success">{{ $batch->current_checkpoint }}</span>
                        @else
                            <span class="badge badge-warning">Pending Dispatch</span>
                        @endif
                    </td>
                    <td>{{ $batch->created_at->format('d M Y H:i') }}</td>
                    <td>
                        @if(!$batch->current_checkpoint)
                        <form action="{{ route('wet-process.dispatch', $batch) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Dispatch batch ini ke Dry Process?')">
                                <i class="bi bi-send"></i> Dispatch
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: rgba(62,92,116,0.5);">
                        <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                        Belum ada batch
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection