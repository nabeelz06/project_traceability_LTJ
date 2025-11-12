@extends('layouts.app')

@section('title', 'Pencarian Traceability')

@section('content')
<div class="container py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-search" style="color: var(--primary);"></i>
        Pencarian Traceability
    </h1>

    <!-- Form Pencarian -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('traceability.search') }}" method="GET">
                <div class="row">
                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control form-control-lg" 
                               value="{{ $searchTerm }}"
                               placeholder="Cari berdasarkan Kode Batch, Nomor Lot, Nomor Kontainer, atau RFID UID..."
                               autofocus>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Hasil Pencarian -->
    @if($searchTerm)
        @if($batches && $batches->count() > 0)
        <div class="card">
            <div class="card-header">
                <i class="bi bi-list-check me-2"></i>Hasil Pencarian ({{ $batches->count() }} batch ditemukan)
            </div>
            <div class="card-body" style="padding: 0;">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Kode Batch</th>
                            <th>Nomor Lot</th>
                            <th>Produk</th>
                            <th>Status</th>
                            <th>Pemilik</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($batches as $batch)
                        <tr>
                            <td>
                                <strong>{{ $batch->batch_code }}</strong>
                                @if($batch->isChild())
                                    <span class="badge badge-info badge-sm ms-1">Child</span>
                                @endif
                            </td>
                            <td>{{ $batch->lot_number }}</td>
                            <td>
                                <strong>{{ $batch->product_code }}</strong>
                                <br><small class="text-muted">{{ $batch->productCode->description ?? '' }}</small>
                            </td>
                            <td><span class="badge {{ $batch->getStatusBadgeClass() }}">{{ $batch->getStatusLabel() }}</span></td>
                            <td>{{ $batch->currentPartner->name ?? 'PT Timah' }}</td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="{{ route('batches.show', $batch) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('traceability.tree', $batch) }}" class="btn btn-sm btn-outline-success" title="Tree">
                                        <i class="bi bi-diagram-2"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: rgba(11,37,69,0.3);"></i>
                <h5 class="mt-3 text-muted">Tidak ada hasil ditemukan</h5>
                <p class="text-muted">Coba gunakan kata kunci yang berbeda</p>
            </div>
        </div>
        @endif
    @else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="bi bi-search" style="font-size: 3rem; color: rgba(11,37,69,0.3);"></i>
            <h5 class="mt-3 text-muted">Mulai Pencarian</h5>
            <p class="text-muted">Masukkan kode batch, nomor lot, atau RFID UID untuk melacak batch</p>
        </div>
    </div>
    @endif
</div>
@endsection