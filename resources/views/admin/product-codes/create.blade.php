@extends('layouts.app')

@section('title', 'Tambah Product Code')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-plus-circle" style="color: var(--primary);"></i>
        Tambah Product Code Baru
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.product-codes.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produk *</label>
                        <input type="text" name="code" class="form-control" 
                               value="{{ old('code') }}" 
                               placeholder="Contoh: Nd, Pr, Ce, LTJ-001" required>
                        <small class="text-muted">Kode unik untuk identifikasi produk</small>
                        @error('code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Stage *</label>
                        <select name="stage" class="form-select" required>
                            <option value="">-- Pilih Stage --</option>
                            <option value="RAW" {{ old('stage') == 'RAW' ? 'selected' : '' }}>
                                Bahan Mentah (RAW)
                            </option>
                            <option value="MID" {{ old('stage') == 'MID' ? 'selected' : '' }}>
                                Hasil Pengolahan (MID)
                            </option>
                            <option value="FINAL" {{ old('stage') == 'FINAL' ? 'selected' : '' }}>
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
                               value="{{ old('description') }}" 
                               placeholder="Contoh: Neodymium Oxide" required>
                        @error('description')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Spesifikasi (Opsional)</label>
                        <textarea name="specifications" class="form-control" rows="4" 
                                  placeholder="Detail spesifikasi produk, komposisi kimia, standar kualitas, dll...">{{ old('specifications') }}</textarea>
                        @error('specifications')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Catatan:</strong> Product code akan digunakan untuk klasifikasi batch dalam sistem traceability.
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('admin.product-codes.index') }}" class="btn" style="background: var(--secondary); color: white;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Product Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection