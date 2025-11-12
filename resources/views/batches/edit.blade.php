@extends('layouts.app')

@section('title', 'Edit Batch - ' . $batch->batch_code)

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-pencil-square" style="color: var(--primary);"></i>
        Edit Batch: {{ $batch->batch_code }}
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('batches.update', $batch) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Kontainer *</label>
                        <input type="text" name="container_code" class="form-control" 
                               value="{{ old('container_code', $batch->container_code) }}" required>
                        @error('container_code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Lokasi Saat Ini *</label>
                        <input type="text" name="current_location" class="form-control" 
                               value="{{ old('current_location', $batch->current_location) }}" required>
                        @error('current_location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Berat Saat Ini *</label>
                        <input type="number" name="current_weight" class="form-control" 
                               value="{{ old('current_weight', $batch->current_weight) }}" 
                               step="0.01" min="0" required>
                        @error('current_weight')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Satuan *</label>
                        <select name="weight_unit" class="form-select" required>
                            <option value="kg" {{ old('weight_unit', $batch->weight_unit) == 'kg' ? 'selected' : '' }}>Kilogram (kg)</option>
                            <option value="ton" {{ old('weight_unit', $batch->weight_unit) == 'ton' ? 'selected' : '' }}>Ton</option>
                        </select>
                        @error('weight_unit')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="notes" class="form-control" rows="4" 
                                  placeholder="Catatan perubahan...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('batches.show', $batch) }}" class="btn" style="background: var(--secondary); color: white;">
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