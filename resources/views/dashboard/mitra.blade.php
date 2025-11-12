@extends('layouts.app')

@section('title', 'Dashboard Mitra')

@section('content')
<div class="container-fluid py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-building" style="color: var(--primary);"></i>
        Dashboard Mitra - {{ auth()->user()->partner->name }}
    </h1>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--warning);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Perlu Check-In</h6>
                    <h2 class="mb-0" style="color: var(--warning);">{{ $stats['incoming_batches'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--info);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Diterima</h6>
                    <h2 class="mb-0" style="color: var(--info);">{{ $stats['received_batches'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--success);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Diproses</h6>
                    <h2 class="mb-0" style="color: var(--success);">{{ $stats['processed_batches'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card" style="border-left: 4px solid var(--primary);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Batch Turunan</h6>
                    <h2 class="mb-0" style="color: var(--primary);">{{ $stats['child_batches'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Batch Perlu Check-In -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="bi bi-inbox me-2"></i>Perlu Check-In ({{ $needCheckin->count() }})</span>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($needCheckin as $batch)
                    <div class="mb-3 p-3" style="background: #fff3cd; border-radius: 8px; border-left: 4px solid var(--warning);">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <strong style="font-size: 1.1rem;">{{ $batch->batch_code }}</strong>
                                <br><span class="text-muted">{{ $batch->product_code }} • {{ $batch->formatted_weight }}</span>
                            </div>
                            <form action="{{ route('mitra.batches.checkin', $batch) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">
                                    <i class="bi bi-box-arrow-in-down me-1"></i>Check-In
                                </button>
                            </form>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">Tidak ada batch yang perlu di-check-in</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Batch Siap Diproses -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-gear me-2"></i>Siap Diproses ({{ $readyToProcess->count() }})
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($readyToProcess as $batch)
                    <div class="mb-3 p-3" style="background: #d1ecf1; border-radius: 8px; border-left: 4px solid var(--info);">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <strong style="font-size: 1.1rem;">{{ $batch->batch_code }}</strong>
                                <br><span class="text-muted">{{ $batch->product_code }} • {{ $batch->formatted_weight }}</span>
                                @if($batch->isChild())
                                    <br><small class="text-muted">Parent: {{ $batch->parentBatch->batch_code }}</small>
                                @endif
                            </div>
                            <a href="{{ route('mitra.batches.create-child', $batch) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-diagram-3 me-1"></i>Proses
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">Tidak ada batch yang siap diproses</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru
                </div>
                <div class="card-body" style="max-height: 350px; overflow-y: auto;">
                    @forelse($recentActivities as $log)
                    <div class="mb-2 p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 3px solid var(--primary);">
                        <strong style="color: var(--primary); font-size: 0.9rem;">{{ $log->getActionLabel() }}</strong>
                        <br><small class="text-muted">
                            Batch: <a href="{{ route('batches.show', $log->batch) }}">{{ $log->batch->batch_code }}</a> • 
                            {{ $log->actor->name ?? 'System' }} • 
                            {{ $log->created_at->diffForHumans() }}
                        </small>
                        @if($log->notes)
                        <br><small class="text-muted">{{ $log->notes }}</small>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection