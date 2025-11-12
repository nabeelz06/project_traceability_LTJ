@extends('layouts.app')

@section('title', 'Edit Product Code')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-pencil-square" style="color: var(--primary);"></i>
        Edit Product Code: {{ $productCode->code }}
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.product-codes.update', $productCode) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produk</label>
                        <input type="text" class="form-control" value="{{ $productCode->code }}" disabled>
                        <small class="text-muted">Kode tidak dapat diubah</small>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stage *</label>
                        <select name="stage" class="form-select" required>
                            <option value="RAW" {{ old('stage', $productCode->stage) == 'RAW' ? 'selected' : '' }}>
                                Bahan Mentah (RAW)
                            </option>
                            <option value="MID" {{ old('stage', $productCode->stage) == 'MID' ? 'selected' : '' }}>
                                Hasil Pengolahan (MID)
                            </option>
                            <option value="FINAL" {{ old('stage', $productCode->stage) == 'FINAL' ? 'selected' : '' }}>
                                Produk Akhir (FINAL)
                            </option>
                        </select>
                        @error('stage')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Deskripsi *</label>
                        <input type="text" name="description" class="form-control" 
                               value="{{ old('description', $productCode->description) }}" required>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Spesifikasi</label>
                        <textarea name="specifications" class="form-control" rows="4">{{ old('specifications', $productCode->specifications) }}</textarea>
                        @error('specifications')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('admin.product-codes.show', $productCode) }}" class="btn" style="background: var(--secondary); color: white;">
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