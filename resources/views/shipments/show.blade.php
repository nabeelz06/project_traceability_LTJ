@extends('layouts.app')

@section('title', 'Detail Pengiriman')

@section('content')
<div class="container py-4" style="max-width: 1000px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-truck" style="color: var(--primary);"></i>
            Detail Pengiriman #{{ $shipment->id }}
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('shipments.index') }}" class="btn" style="background: var(--secondary); color: white;">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-truck-front-fill" style="font-size: 4rem; color: var(--primary);"></i>
                    <h5 class="mt-3">Status Pengiriman</h5>
                    
                    @if($shipment->status == 'scheduled')
                    <span class="badge badge-warning" style="font-size: 1.1rem; padding: 0.5rem 1.5rem;">Dijadwalkan</span>
                    @elseif($shipment->status == 'in_transit')
                    <span class="badge badge-info" style="font-size: 1.1rem; padding: 0.5rem 1.5rem;">Dalam Perjalanan</span>
                    @elseif($shipment->status == 'delivered')
                    <span class="badge badge-success" style="font-size: 1.1rem; padding: 0.5rem 1.5rem;">Terkirim</span>
                    @else
                    <span class="badge badge-danger" style="font-size: 1.1rem; padding: 0.5rem 1.5rem;">Dibatalkan</span>
                    @endif

                    <hr class="my-3">

                    <div class="text-start">
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-calendar-check me-2"></i>
                            <strong>Dijadwalkan:</strong><br>{{ $shipment->scheduled_at->format('d M Y, H:i') }}
                        </small>
                        
                        @if($shipment->shipped_at)
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-truck me-2"></i>
                            <strong>Mulai Dikirim:</strong><br>{{ $shipment->shipped_at->format('d M Y, H:i') }}
                        </small>
                        @endif

                        @if($shipment->delivered_at)
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-check-circle me-2"></i>
                            <strong>Diterima:</strong><br>{{ $shipment->delivered_at->format('d M Y, H:i') }}
                        </small>
                        @endif
                    </div>

                    @if($shipment->status == 'scheduled')
                    <hr class="my-3">
                    <form action="{{ route('shipments.destroy', $shipment) }}" method="POST" 
                          onsubmit="return confirm('Yakin ingin membatalkan pengiriman ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-x-circle me-1"></i>Batalkan Pengiriman
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Batch Info -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-box-seam me-2"></i>Informasi Batch
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">BATCH CODE</label>
                            <p class="mb-0">
                                <a href="{{ route('batches.show', $shipment->batch) }}" style="font-size: 1.1rem; font-weight: 600; color: var(--primary);">
                                    {{ $shipment->batch->batch_code }}
                                </a>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">PRODUK</label>
                            <p class="mb-0">{{ $shipment->batch->product_code }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">BERAT</label>
                            <p class="mb-0">{{ $shipment->batch->formatted_weight }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">KONTAINER</label>
                            <p class="mb-0">{{ $shipment->batch->container_code }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Shipment Details -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Detail Pengiriman
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">TUJUAN</label>
                            <p class="mb-0">{{ $shipment->destinationPartner->name ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">OPERATOR</label>
                            <p class="mb-0">{{ $shipment->assignedOperator->name ?? 'Belum ditentukan' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">KENDARAAN</label>
                            <p class="mb-0">{{ $shipment->vehicle_info ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">JADWAL</label>
                            <p class="mb-0">{{ $shipment->scheduled_at->format('d M Y, H:i') }}</p>
                        </div>
                        @if($shipment->notes)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold text-muted small">CATATAN</label>
                            <p class="mb-0">{{ $shipment->notes }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection