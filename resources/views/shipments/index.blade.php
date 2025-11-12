@extends('layouts.app')

@section('title', 'Manajemen Pengiriman')

@section('content')
<div class="container-fluid py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-truck" style="color: var(--primary);"></i>
            Manajemen Pengiriman
        </h1>
        <a href="{{ route('shipments.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Jadwalkan Pengiriman
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('shipments.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari batch code..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Dijadwalkan</option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>Dalam Perjalanan</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Terkirim</option>
                            <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Batch</th>
                            <th>Tujuan</th>
                            <th>Operator</th>
                            <th>Jadwal</th>
                            <th>Kendaraan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shipments as $shipment)
                        <tr>
                            <td>
                                <strong>{{ $shipment->batch->batch_code }}</strong>
                                <br><small class="text-muted">{{ $shipment->batch->product_code }}</small>
                            </td>
                            <td>{{ $shipment->destinationPartner->name ?? '-' }}</td>
                            <td>{{ $shipment->assignedOperator->name ?? 'Belum ditentukan' }}</td>
                            <td>{{ $shipment->scheduled_at->format('d M Y, H:i') }}</td>
                            <td>{{ $shipment->vehicle_info ?? '-' }}</td>
                            <td>
                                @if($shipment->status == 'scheduled')
                                <span class="badge badge-warning">Dijadwalkan</span>
                                @elseif($shipment->status == 'in_transit')
                                <span class="badge badge-info">Dalam Perjalanan</span>
                                @elseif($shipment->status == 'delivered')
                                <span class="badge badge-success">Terkirim</span>
                                @else
                                <span class="badge badge-danger">Dibatalkan</span>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="{{ route('shipments.show', $shipment) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($shipment->status == 'scheduled')
                                    <form action="{{ route('shipments.destroy', $shipment) }}" method="POST" 
                                          onsubmit="return confirm('Yakin ingin membatalkan pengiriman ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Batalkan">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data pengiriman</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $shipments->links() }}
    </div>
</div>
@endsection