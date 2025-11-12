@extends('layouts.app')

@section('title', 'Manajemen Device')

@section('content')
<div class="container-fluid py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-cpu" style="color: var(--primary);"></i>
            Manajemen Device
        </h1>
        <a href="{{ route('admin.devices.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Registrasi Device
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.devices.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari device ID atau nama..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="rfid_reader" {{ request('type') == 'rfid_reader' ? 'selected' : '' }}>RFID Reader</option>
                            <option value="rfid_writer" {{ request('type') == 'rfid_writer' ? 'selected' : '' }}>RFID Writer</option>
                            <option value="scanner" {{ request('type') == 'scanner' ? 'selected' : '' }}>Scanner</option>
                            <option value="handheld" {{ request('type') == 'handheld' ? 'selected' : '' }}>Handheld</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-2">
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
                            <th>Device ID</th>
                            <th>Nama Device</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th>Status</th>
                            <th>Last Seen</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($devices as $device)
                        <tr>
                            <td><strong style="font-family: monospace;">{{ $device->device_id }}</strong></td>
                            <td>{{ $device->device_name }}</td>
                            <td><span class="badge badge-info">{{ $device->getTypeLabel() }}</span></td>
                            <td>{{ $device->location ?? '-' }}</td>
                            <td>
                                @if($device->is_active)
                                <span class="badge badge-success">Aktif</span>
                                @else
                                <span class="badge badge-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '-' }}</td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="{{ route('admin.devices.show', $device) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.devices.edit', $device) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($device->is_active)
                                    <form action="{{ route('admin.devices.revoke', $device) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Revoke">
                                            <i class="bi bi-shield-x"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data device</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $devices->links() }}
    </div>
</div>
@endsection