@extends('layouts.app')

@section('title', 'Split Monasit untuk Lab')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --primary: #3e5c74;
        --primary-dark: #2d4454;
        --primary-light: #e8eef3;
    }

    body {
        font-family: 'Poppins', system-ui, sans-serif;
        background: linear-gradient(135deg, var(--primary-light) 0%, #f5f8fa 100%);
    }

    .form-container {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .info-box {
        background: linear-gradient(135deg, rgba(39,174,96,0.05) 0%, rgba(39,174,96,0.02) 100%);
        border-left: 4px solid #27ae60;
        padding: 1.25rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }

    .form-label {
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control, .form-select {
        border: 1px solid rgba(62,92,116,0.2);
        border-radius: 8px;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(62,92,116,0.1);
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border: none;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(62,92,116,0.3);
    }

    .btn-secondary {
        border: 2px solid rgba(62,92,116,0.3);
        color: var(--primary);
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .btn-secondary:hover {
        background: rgba(62,92,116,0.05);
        border-color: var(--primary);
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <a href="{{ route('warehouse.dashboard') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 style="font-weight: 700; color: var(--primary); margin: 0;">
                <i class="bi bi-scissors me-2"></i>Split Monasit untuk Lab
            </h2>
        </div>
        <p style="color: rgba(62,92,116,0.7); margin: 0;">Pecah batch besar menjadi batch kecil @ 50kg untuk analisis laboratorium</p>
    </div>

    <!-- Batch Info Card -->
    <div class="info-box">
        <div class="row">
            <div class="col-md-3">
                <small style="color: rgba(39,174,96,0.8); font-weight: 600;">BATCH CODE</small>
                <h5 style="color: #27ae60; margin-top: 0.25rem;">{{ $batch->batch_code }}</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(39,174,96,0.8); font-weight: 600;">MATERIAL</small>
                <h5 style="color: #27ae60; margin-top: 0.25rem;">
                    <span class="badge" style="background: #27ae60; color: white; padding: 0.5rem 1rem; font-size: 0.9rem;">
                        MONASIT
                    </span>
                </h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(39,174,96,0.8); font-weight: 600;">BERAT TERSEDIA</small>
                <h5 style="color: #27ae60; margin-top: 0.25rem;">{{ number_format($batch->current_weight, 2) }} kg</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(39,174,96,0.8); font-weight: 600;">MAX SPLIT</small>
                <h5 style="color: #27ae60; margin-top: 0.25rem;">{{ floor($batch->current_weight / 50) }} batch</h5>
            </div>
        </div>
    </div>

    <!-- Split Form -->
    <div class="form-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.5rem;">
            <i class="bi bi-file-earmark-text me-2"></i>Form Split Batch
        </h5>

        <form action="{{ route('warehouse.split-lab', $batch) }}" method="POST" id="splitForm">
            @csrf

            <!-- Product Code Lab -->
            <div class="mb-3">
                <label class="form-label">Product Code untuk Sample Lab <span class="text-danger">*</span></label>
                <select name="lab_product_code_id" class="form-select" required>
                    <option value="">-- Pilih Product Code --</option>
                    @foreach($productCodes as $code)
                        @if($code->material == 'MON' && str_contains($code->spec, 'SAMPLE'))
                        <option value="{{ $code->id }}">{{ $code->code }} - {{ $code->description }}</option>
                        @endif
                    @endforeach
                </select>
                <small class="text-muted">Pilih product code untuk sample laboratorium</small>
            </div>

            <!-- Weight per Batch (Fixed 50kg) -->
            <div class="mb-3">
                <label class="form-label">Berat per Batch Sample <span class="text-danger">*</span></label>
                <div class="input-group">
                    <input type="number" name="weight_per_batch" class="form-control" value="50" readonly required>
                    <span class="input-group-text">kg</span>
                </div>
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Standar batch lab adalah 50 kg (tidak bisa diubah)
                </small>
            </div>

            <!-- Split Count -->
            <div class="mb-3">
                <label class="form-label">Jumlah Batch yang akan dibuat <span class="text-danger">*</span></label>
                <input type="number" name="split_count" class="form-control" 
                       min="1" max="{{ floor($batch->current_weight / 50) }}" 
                       required id="splitCount" placeholder="Masukkan jumlah batch">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Maksimal: {{ floor($batch->current_weight / 50) }} batch @ 50kg (total: {{ floor($batch->current_weight / 50) * 50 }} kg)
                </small>
            </div>

            <!-- Preview Box -->
            <div class="alert alert-info mt-3" id="splitPreview" style="display: none; border-left: 4px solid #17a2b8;">
                <div class="d-flex align-items-center">
                    <i class="bi bi-calculator me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Preview Hasil Split:</strong><br>
                        <span id="previewText">-</span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="3" 
                          placeholder="Catatan tambahan tentang split batch ini (opsional)"></textarea>
            </div>

            <!-- Warning Info -->
            <div class="alert alert-warning" style="border-left: 4px solid #ffc107;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Perhatian:</strong> Setelah split, batch parent akan berkurang beratnya dan child batches akan dibuat untuk dikirim ke Lab.
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="bi bi-scissors me-2"></i>Split Batch
                </button>
                <a href="{{ route('warehouse.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const maxSplit = {{ floor($batch->current_weight / 50) }};
const availableWeight = {{ $batch->current_weight }};
const splitCountInput = document.getElementById('splitCount');
const previewBox = document.getElementById('splitPreview');
const previewText = document.getElementById('previewText');
const submitBtn = document.getElementById('submitBtn');

splitCountInput.addEventListener('input', function() {
    const count = parseInt(this.value) || 0;
    const weightPerBatch = 50;
    const totalWeight = count * weightPerBatch;
    const remainingWeight = availableWeight - totalWeight;
    
    if (count <= 0) {
        previewBox.style.display = 'none';
        submitBtn.disabled = true;
        return;
    }
    
    previewBox.style.display = 'block';
    
    if (count > maxSplit || totalWeight > availableWeight) {
        // Error state
        previewBox.className = 'alert alert-danger mt-3';
        previewBox.style.borderLeft = '4px solid #dc3545';
        previewText.innerHTML = `
            <span style="color: #dc3545; font-weight: 600;">❌ MELEBIHI KAPASITAS!</span><br>
            Total berat: ${totalWeight} kg > Berat tersedia: ${availableWeight.toFixed(2)} kg<br>
            <small>Maksimal hanya ${maxSplit} batch @ 50kg</small>
        `;
        submitBtn.disabled = true;
    } else {
        // Success state
        previewBox.className = 'alert alert-success mt-3';
        previewBox.style.borderLeft = '4px solid #28a745';
        previewText.innerHTML = `
            <span style="color: #28a745; font-weight: 600;">✅ Akan dibuat ${count} batch @ 50kg</span><br>
            Total berat yang akan di-split: <strong>${totalWeight} kg</strong><br>
            Sisa berat parent batch: <strong>${remainingWeight.toFixed(2)} kg</strong>
        `;
        submitBtn.disabled = false;
    }
});

// Form submission validation
document.getElementById('splitForm').addEventListener('submit', function(e) {
    const count = parseInt(splitCountInput.value) || 0;
    if (count <= 0 || count > maxSplit) {
        e.preventDefault();
        alert('Jumlah split tidak valid!');
        return false;
    }
    
    return confirm(`Yakin ingin split batch ini menjadi ${count} batch @ 50kg untuk Lab?`);
});
</script>
@endpush
@endsection