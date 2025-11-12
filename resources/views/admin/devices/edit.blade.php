@extends('layouts.app')

@section('title', 'Edit Device')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-pencil-square" style="color: var(--primary);"></i>
        Edit Device: {{ $device->device_name }}
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.devices.update', $device) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Device ID</label>
                        <input type="text" class="form-control" value="{{ $device->device_id }}" disabled>
                        <small class="text-muted">Device ID tidak dapat diubah</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Device *</label>
                        <input type="text" name="device_name" class="form-control" 
                               value="{{ old('device_name', $device->device_name) }}" required>
                        @error('device_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Device *</label>
                        <select name="type" class="form-select" required>
                            <option value="rfid_reader" {{ old('type', $device->type) == 'rfid_reader' ? 'selected' : '' }}>RFID Reader</option>
                            <option value="rfid_writer" {{ old('type', $device->type) == 'rfid_writer' ? 'selected' : '' }}>RFID Writer</option>
                            <option value="scanner" {{ old('type', $device->type) == 'scanner' ? 'selected' : '' }}>Scanner</option>
                            <option value="handheld" {{ old('type', $device->type) == 'handheld' ? 'selected' : '' }}>Handheld Device</option>
                        </select>
                        @error('type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="location" class="form-control" 
                               value="{{ old('location', $device->location) }}">
                        @error('location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" {{ old('is_active', $device->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', $device->is_active) == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('is_active')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3">{{ old('description', $device->description) }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('admin.devices.show', $device) }}" class="btn" style="background: var(--secondary); color: white;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection