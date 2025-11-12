@extends('layouts.app')

@section('title', 'Registrasi Device')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-cpu" style="color: var(--primary);"></i>
        Registrasi Device Baru
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.devices.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Device ID *</label>
                        <input type="text" name="device_id" class="form-control" 
                               value="{{ old('device_id') }}" 
                               placeholder="Contoh: RFID-001" required>
                        <small class="text-muted">ID unik device (serial number)</small>
                        @error('device_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Device *</label>
                        <input type="text" name="device_name" class="form-control" 
                               value="{{ old('device_name') }}" 
                               placeholder="Contoh: RFID Reader Gudang A" required>
                        @error('device_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Device *</label>
                        <select name="type" class="form-select" required>
                            <option value="">-- Pilih Tipe --</option>
                            <option value="rfid_reader" {{ old('type') == 'rfid_reader' ? 'selected' : '' }}>RFID Reader</option>
                            <option value="rfid_writer" {{ old('type') == 'rfid_writer' ? 'selected' : '' }}>RFID Writer</option>
                            <option value="scanner" {{ old('type') == 'scanner' ? 'selected' : '' }}>Scanner</option>
                            <option value="handheld" {{ old('type') == 'handheld' ? 'selected' : '' }}>Handheld Device</option>
                        </select>
                        @error('type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="location" class="form-control" 
                               value="{{ old('location') }}" 
                               placeholder="Contoh: Gudang A - Pintu Masuk">
                        @error('location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="description" class="form-control" rows="3" 
                                  placeholder="Informasi tambahan tentang device...">{{ old('description') }}</textarea>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Catatan:</strong> Device akan langsung aktif setelah registrasi.
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('admin.devices.index') }}" class="btn" style="background: var(--secondary); color: white;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Registrasi Device
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection