@extends('layouts.app')

@section('title', 'Buat Batch Turunan')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-diagram-3" style="color: var(--primary);"></i>
        Buat Batch Turunan dari {{ $batch->batch_code }}
    </h1>

    <!-- Info Parent Batch -->
    <div class="card mb-4" style="border-left: 4px solid var(--primary);">
        <div class="card-body">
            <h6 class="text-muted mb-3">Informasi Batch Induk</h6>
            <div class="row">
                <div class="col-md-4">
                    <small class="text-muted">Kode Batch</small>
                    <p class="mb-2"><strong>{{ $batch->batch_code }}</strong></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Produk</small>
                    <p class="mb-2"><strong>{{ $batch->product_code }}</strong></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Berat Awal</small>
                    <p class="mb-2"><strong>{{ number_format($batch->initial_weight, 2) }} {{ $batch->weight_unit }}</strong></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Berat Tersisa</small>
                    <p class="mb-0"><strong style="color: var(--success);">{{ number_format($batch->remaining_weight, 2) }} {{ $batch->weight_unit }}</strong></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Total Child</small>
                    <p class="mb-0"><strong>{{ $batch->total_children }} batch</strong></p>
                </div>
                <div class="col-md-4">
                    <small class="text-muted">Progress</small>
                    <p class="mb-0"><strong>{{ number_format($batch->processed_percentage, 1) }}%</strong></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Buat Child -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-plus-circle me-2"></i>Data Batch Turunan Baru
        </div>
        <div class="card-body">
            <form action="{{ route('mitra.batches.store-child', $batch) }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Kode Produk Turunan *</label>
                        <select name="product_code" class="form-select" required>
                            <option value="">-- Pilih Produk --</option>
                            @foreach($productCodes as $product)
                            <option value="{{ $product->code }}" {{ old('product_code') == $product->code ? 'selected' : '' }}>
                                {{ $product->code }} - {{ $product->description }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Produk hasil pemrosesan (Nd, Pr, Ce, dll)</small>
                        @error('product_code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Kontainer Baru *</label>
                        <input type="text" name="container_code" class="form-control" 
                               value="{{ old('container_code') }}" 
                               placeholder="Contoh: K-M-ND-001" required>
                        @error('container_code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Berat (Estimasi) *</label>
                        <input type="number" name="initial_weight" class="form-control" 
                               value="{{ old('initial_weight') }}" 
                               step="0.01" min="0.01" 
                               max="{{ $batch->remaining_weight }}" required>
                        <small class="text-muted">Maksimal: {{ number_format($batch->remaining_weight, 2) }} {{ $batch->weight_unit }}</small>
                        @error('initial_weight')
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
                        <label class="form-label">Upload Sertifikat Lab (Opsional)</label>
                        <input type="file" name="lab_certificate" class="form-control" accept=".pdf,.jpg,.png">
                        <small class="text-muted">Format: PDF, JPG, PNG (Maks 5MB)</small>
                        @error('lab_certificate')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Catatan Proses</label>
                        <textarea name="notes" class="form-control" rows="4" 
                                  placeholder="Catatan mengenai proses pemisahan/pengolahan...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Catatan:</strong> Setelah batch turunan dibuat, Anda perlu menulis RFID tag baru untuk batch ini.
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('batches.show', $batch) }}" class="btn" style="background: var(--secondary); color: white;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i>Buat Batch Turunan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection