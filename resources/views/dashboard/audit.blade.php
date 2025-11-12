@extends('layouts.app')

@section('title', 'Dashboard Audit')

@section('content')
<div class="container-fluid py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-shield-check" style="color: var(--primary);"></i>
        Dashboard Audit - {{ auth()->user()->getRoleLabel() }}
    </h1>

    <!-- Anomali Alerts -->
    @if(count($anomalies) > 0)
    <div class="row mb-4">
        @foreach($anomalies as $anomaly)
        <div class="col-12">
            <div class="alert alert-{{ $anomaly['type'] }} alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Perhatian:</strong> {{ $anomaly['message'] }}
                @if(isset($anomaly['link']))
                    <a href="{{ $anomaly['link'] }}" class="alert-link ms-2">Investigasi →</a>
                @endif
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--primary);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Batch</h6>
                    <h2 class="mb-0" style="color: var(--primary);">{{ $stats['total_batches'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--info);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Batch Aktif</h6>
                    <h2 class="mb-0" style="color: var(--info);">{{ $stats['active_batches'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--success);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Mitra</h6>
                    <h2 class="mb-0" style="color: var(--success);">{{ $stats['total_partners'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--warning);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Log Aktivitas</h6>
                    <h2 class="mb-0" style="color: var(--warning);">{{ $stats['total_logs'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Activity Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-graph-up me-2"></i>Aktivitas Sistem (30 Hari Terakhir)
                </div>
                <div class="card-body">
                    <canvas id="activityChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-link-45deg me-2"></i>Akses Cepat
                </div>
                <div class="card-body">
                    <a href="{{ route('audit.logs.batch') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-journal-check me-2"></i>Log Audit Batch
                    </a>
                    <a href="{{ route('audit.logs.system') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-shield-lock me-2"></i>Log Sistem
                    </a>
                    <a href="{{ route('traceability.search') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-search me-2"></i>Pencarian Traceability
                    </a>
                    <a href="{{ route('audit.reports.index') }}" class="btn btn-outline-primary w-100">
                        <i class="bi bi-file-earmark-bar-graph me-2"></i>Laporan
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Logs -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Log Aktivitas Terbaru
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @forelse($recentLogs as $log)
                    <div class="mb-2 p-3" style="background: #f8f9fa; border-radius: 6px; border-left: 3px solid {{ $log->action == 'corrected' ? 'var(--danger)' : 'var(--primary)' }};">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong style="color: var(--primary); font-size: 0.95rem;">{{ $log->getActionLabel() }}</strong>
                                @if($log->action == 'corrected')
                                    <span class="badge badge-warning badge-sm ms-2">Koreksi Manual</span>
                                @endif
                                <br><small class="text-muted">
                                    Batch: <a href="{{ route('batches.show', $log->batch) }}">{{ $log->batch->batch_code }}</a> • 
                                    Oleh: {{ $log->actor->name ?? 'System' }} ({{ $log->actor->getRoleLabel() }}) • 
                                    {{ $log->created_at->format('d M Y, H:i') }}
                                </small>
                                @if($log->notes)
                                <br><small class="text-muted">Catatan: {{ $log->notes }}</small>
                                @endif
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada log aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('activityChart').getContext('2d');
const activityChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyActivity->pluck('date')) !!},
        datasets: [{
            label: 'Aktivitas Harian',
            data: {!! json_encode($dailyActivity->pluck('total')) !!},
            borderColor: 'rgba(11, 37, 69, 1)',
            backgroundColor: 'rgba(11, 37, 69, 0.1)',
            tension: 0.3,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});
</script>
@endsection