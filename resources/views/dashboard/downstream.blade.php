@extends('layouts.app')

@section('title', 'Dashboard Downstream')

@section('content')
<div class="container-fluid py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-building" style="color: var(--primary);"></i>
        Dashboard Downstream - {{ auth()->user()->partner->name }}
    </h1>

    <!-- KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-4 col-md-6">
            <div class="card" style="border-left: 4px solid var(--warning);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pengiriman Masuk</h6>
                    <h2 class="mb-0" style="color: var(--warning);">{{ $stats['incoming_shipments'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card" style="border-left: 4px solid var(--success);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Diterima</h6>
                    <h2 class="mb-0" style="color: var(--success);">{{ $stats['received_batches'] }}</h2>
                </div>
            </div>
        </div>

        <div class="col-lg-4 col-md-6">
            <div class="card" style="border-left: 4px solid var(--primary);">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Berat</h6>
                    <h2 class="mb-0" style="color: var(--primary);">{{ number_format($stats['total_weight'], 2) }} kg</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Batch Perlu Check-In -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-inbox me-2"></i>Perlu Check-In Final ({{ $needCheckin->count() }})
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($needCheckin as $batch)
                    <div class="mb-3 p-3" style="background: #fff3cd; border-radius: 8px; border-left: 4px solid var(--warning);">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div style="flex: 1;">
                                <strong style="font-size: 1.1rem;">{{ $batch->batch_code }}</strong>
                                <br><span class="text-muted">{{ $batch->product_code }} • {{ $batch->formatted_weight }}</span>
                                @if($batch->parentBatch)
                                <br><small class="text-muted">Parent: {{ $batch->parentBatch->batch_code }}</small>
                                @endif
                            </div>
                            <form action="{{ route('downstream.batches.checkin-final', $batch) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-success">
                                    <i class="bi bi-check-circle me-1"></i>Terima
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

        <!-- Riwayat Penerimaan -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Penerimaan
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($receivedBatches as $batch)
                    <div class="mb-3 p-3" style="background: #d4edda; border-radius: 8px; border-left: 4px solid var(--success);">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <strong style="font-size: 1.1rem;">{{ $batch->batch_code }}</strong>
                                <span class="badge badge-success badge-sm ms-2">Delivered</span>
                                <br><span class="text-muted">{{ $batch->product_code }} • {{ $batch->formatted_weight }}</span>
                                <br><small class="text-muted">{{ $batch->updated_at->format('d M Y, H:i') }}</small>
                            </div>
                            <a href="{{ route('batches.show', $batch) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-eye"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">Belum ada batch yang diterima</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection