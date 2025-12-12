@extends('layouts.app')

@section('title', 'Buat Batch Mineral Ikutan')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="bi bi-plus-circle" style="color: var(--timah-blue);"></i>
            Buat Batch Mineral Ikutan Baru
        </h1>
        <p class="text-gray-600">Feed Material dari Washing Plant</p>
    </div>

    <div class="card">
        <div class="card-header" style="background: var(--timah-blue); color: white;">
            <h5 class="mb-0">Form Input Batch</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('wet-process.store') }}" method="POST">
                @csrf

                <!-- Product Code -->
                <div class="form-group">
                    <label class="form-label">Product Code <span class="text-danger">*</span></label>
                    <select name="product_code_id" class="form-select" required>
                        <option value="">-- Pilih Product Code --</option>
                        @foreach($productCodes as $code)
                        <option value="{{ $code->id }}" {{ old('product_code_id') == $code->id ? 'selected' : '' }}>
                            {{ $code->code }} - {{ $code->description }}
                        </option>
                        @endforeach
                    </select>
                    <small class="text-muted">Mineral Ikutan hasil pemisahan dari Washing Plant</small>
                </div>

                <!-- Berat (Weight) -->
                <div class="form-group">
                    <label class="form-label">Berat per Batch (kg) <span class="text-danger">*</span></label>
                    <input type="number" name="weight" class="form-control" min="900" max="1100" step="0.01" 
                           value="{{ old('weight') }}" required>
                    <small class="text-muted">Rentang: 900 - 1100 kg (0.9 - 1.1 ton)</small>
                </div>

                <!-- Origin Location -->
                <div class="form-group">
                    <label class="form-label">Lokasi Asal <span class="text-danger">*</span></label>
                    <input type="text" name="origin_location" class="form-control" 
                           value="{{ old('origin_location', 'PT Timah Washing Plant') }}" required>
                    <small class="text-muted">Lokasi washing plant asal material</small>
                </div>

                <!-- Container Code -->
                <div class="form-group">
                    <label class="form-label">Kode Kontainer (Optional)</label>
                    <input type="text" name="container_code" class="form-control" 
                           value="{{ old('container_code') }}" placeholder="K-TMH-XXX">
                    <small class="text-muted">Auto-generate jika dikosongkan</small>
                </div>

                <!-- Keterangan -->
                <div class="form-group">
                    <label class="form-label">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3">{{ old('keterangan') }}</textarea>
                    <small class="text-muted">Catatan tambahan mengenai batch ini</small>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Simpan Batch
                    </button>
                    <a href="{{ route('wet-process.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection