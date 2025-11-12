@extends('layouts.app')

@section('title', 'Detail Device')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-cpu" style="color: var(--primary);"></i>
            Device: {{ $device->device_name }}
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.devices.index') }}" class="btn" style="background: var(--secondary); color: white;">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('admin.devices.edit', $device) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <i class="bi bi-cpu-fill" style="font-size: 4rem; color: var(--primary);"></i>
                    <h5 class="mt-3">{{ $device->device_name }}</h5>
                    <p class="text-muted" style="font-family: monospace;">{{ $device->device_id }}</p>
                    
                    @if($device->is_active)
                    <span class="badge badge-success" style="font-size: 1rem; padding: 0.5rem 1rem;">Aktif</span>
                    @else
                    <span class="badge badge-danger" style="font-size: 1rem; padding: 0.5rem 1rem;">Tidak Aktif</span>
                    @endif

                    <hr class="my-3">

                    @if($device->is_active)
                    <form action="{{ route('admin.devices.revoke', $device) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100 mb-2" 
                                onclick="return confirm('Yakin ingin menonaktifkan device ini?')">
                            <i class="bi bi-shield-x me-1"></i>Revoke Access
                        </button>
                    </form>
                    @endif

                    <form action="{{ route('admin.devices.destroy', $device) }}" method="POST" 
                          onsubmit="return confirm('Yakin ingin menghapus device ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i>Hapus Device
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Informasi Device
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">DEVICE ID</label>
                            <p class="mb-0" style="font-family: monospace; font-size: 1.1rem;">{{ $device->device_id }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">NAMA</label>
                            <p class="mb-0" style="font-size: 1.1rem;">{{ $device->device_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">TIPE</label>
                            <p class="mb-0"><span class="badge badge-info">{{ $device->getTypeLabel() }}</span></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">LOKASI</label>
                            <p class="mb-0">{{ $device->location ?? '-' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">TERDAFTAR</label>
                            <p class="mb-0">{{ $device->created_at->format('d M Y, H:i') }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">LAST SEEN</label>
                            <p class="mb-0">{{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah aktif' }}</p>
                        </div>
                        @if($device->description)
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold text-muted small">DESKRIPSI</label>
                            <p class="mb-0">{{ $device->description }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection