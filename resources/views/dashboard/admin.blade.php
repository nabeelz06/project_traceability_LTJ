@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-speedometer2" style="color: var(--primary);"></i>
        Dashboard {{ auth()->user()->isSuperAdmin() ? 'Super Admin' : 'Admin PT Timah' }}
    </h1>

    <!-- Alerts -->
    @if(count($alerts) > 0)
    <div class="row mb-4">
        @foreach($alerts as $alert)
        <div class="col-12">
            <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show">
                <i class="bi bi-exclamation-triangle me-2"></i>
                {{ $alert['message'] }}
                @if(isset($alert['link']))
                    <a href="{{ $alert['link'] }}" class="alert-link ms-2">Lihat Detail →</a>
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
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h6 class="text-muted mb-2">Total Batch</h6>
                            <h2 class="mb-0" style="color: var(--primary);">{{ $stats['total_batches'] }}</h2>
                        </div>
                        <i class="bi bi-box-seam" style="font-size: 2.5rem; color: var(--primary); opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--info);">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h6 class="text-muted mb-2">Batch Aktif</h6>
                            <h2 class="mb-0" style="color: var(--info);">{{ $stats['active_batches'] }}</h2>
                        </div>
                        <i class="bi bi-activity" style="font-size: 2.5rem; color: var(--info); opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--warning);">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h6 class="text-muted mb-2">Dalam Pengiriman</h6>
                            <h2 class="mb-0" style="color: var(--warning);">{{ $stats['batches_in_transit'] }}</h2>
                        </div>
                        <i class="bi bi-truck" style="font-size: 2.5rem; color: var(--warning); opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--success);">
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <h6 class="text-muted mb-2">Terkirim</h6>
                            <h2 class="mb-0" style="color: var(--success);">{{ $stats['batches_delivered'] }}</h2>
                        </div>
                        <i class="bi bi-check-circle" style="font-size: 2.5rem; color: var(--success); opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Volume Chart -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-graph-up me-2"></i>Volume Batch (7 Hari Terakhir)
                </div>
                <div class="card-body">
                    <canvas id="volumeChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Performa Mitra -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-building me-2"></i>Top 5 Mitra
                </div>
                <div class="card-body">
                    @forelse($partnerStats as $partner)
                    <div class="mb-3 p-2" style="background: #f8f9fa; border-radius: 6px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <strong>{{ $partner->name }}</strong>
                                <br><small class="text-muted">{{ $partner->getTypeLabel() }}</small>
                            </div>
                            <span class="badge badge-primary">{{ $partner->batches_count }} batch</span>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada data</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Batch Terbaru -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Batch Terbaru
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentBatches as $batch)
                    <div class="mb-2 p-2" style="background: #f8f9fa; border-radius: 6px;">
                        <div style="display: flex; justify-content: between; align-items: center;">
                            <div style="flex: 1;">
                                <a href="{{ route('batches.show', $batch) }}" style="font-weight: 600; color: var(--primary); text-decoration: none;">
                                    {{ $batch->batch_code }}
                                </a>
                                <span class="badge {{ $batch->getStatusBadgeClass() }} badge-sm ms-1">{{ $batch->getStatusLabel() }}</span>
                                <br><small class="text-muted">{{ $batch->product_code }} • {{ $batch->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada batch</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-activity me-2"></i>Aktivitas Terbaru
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentActivities as $log)
                    <div class="mb-2 p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 3px solid var(--primary);">
                        <strong style="color: var(--primary); font-size: 0.9rem;">{{ $log->getActionLabel() }}</strong>
                        <br><small class="text-muted">
                            Batch: <a href="{{ route('batches.show', $log->batch) }}">{{ $log->batch->batch_code }}</a> • 
                            {{ $log->actor->name ?? 'System' }} • 
                            {{ $log->created_at->diffForHumans() }}
                        </small>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Volume Chart
const ctx = document.getElementById('volumeChart').getContext('2d');
const volumeChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: {!! json_encode($volumeChart->pluck('date')) !!},
        datasets: [{
            label: 'Jumlah Batch',
            data: {!! json_encode($volumeChart->pluck('total')) !!},
            backgroundColor: 'rgba(11, 37, 69, 0.8)',
            borderColor: 'rgba(11, 37, 69, 1)',
            borderWidth: 1
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