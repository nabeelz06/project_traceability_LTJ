@extends('layouts.app')

@section('title', 'Laporan Operasional')

@section('content')
<div class="container-fluid py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-graph-up" style="color: var(--primary);"></i>
        Laporan Operasional
    </h1>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('reports.operational') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Tanggal Mulai</label>
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Tanggal Akhir</label>
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">&nbsp;</label>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-filter me-1"></i>Filter Data
                        </button>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">&nbsp;</label>
                        <button type="button" class="btn btn-outline-success w-100" onclick="alert('Export feature coming soon')">
                            <i class="bi bi-download me-1"></i>Export
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-table me-2"></i>Data Batch ({{ $batches->total() }})
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Batch Code</th>
                            <th>Produk</th>
                            <th>Berat</th>
                            <th>Status</th>
                            <th>Pemilik</th>
                            <th>Dibuat</th>
                            <th>Dibuat Oleh</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>
                                <a href="{{ route('batches.show', $batch) }}" style="font-weight: 600; color: var(--primary);">
                                    {{ $batch->batch_code }}
                                </a>
                            </td>
                            <td>{{ $batch->product_code }}</td>
                            <td>{{ $batch->formatted_weight }}</td>
                            <td><span class="badge {{ $batch->getStatusBadgeClass() }}">{{ $batch->getStatusLabel() }}</span></td>
                            <td>{{ $batch->currentPartner->name ?? 'PT Timah' }}</td>
                            <td>{{ $batch->created_at->format('d M Y') }}</td>
                            <td>{{ $batch->creator->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $batches->links() }}
    </div>
</div>
@endsection