@extends('layouts.app')

@section('title', 'Analisis Kandungan LTJ')

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
        background: linear-gradient(135deg, rgba(52,152,219,0.05) 0%, rgba(52,152,219,0.02) 100%);
        border-left: 4px solid #3498db;
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

    .form-control {
        border: 1px solid rgba(62,92,116,0.2);
        border-radius: 8px;
        padding: 0.75rem;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(62,92,116,0.1);
    }

    .element-input {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .element-label {
        min-width: 150px;
        font-weight: 600;
        color: var(--primary);
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

    .preview-box {
        background: linear-gradient(135deg, rgba(46,204,113,0.05) 0%, rgba(46,204,113,0.02) 100%);
        border: 2px solid #2ecc71;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1.5rem;
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-4">
        <div class="d-flex align-items-center gap-3 mb-2">
            <a href="{{ route('lab.dashboard') }}" class="btn btn-sm btn-secondary">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 style="font-weight: 700; color: var(--primary); margin: 0;">
                <i class="bi bi-microscope me-2"></i>Analisis Kandungan LTJ
            </h2>
        </div>
        <p style="color: rgba(62,92,116,0.7); margin: 0;">Input hasil analisis laboratorium untuk batch monasit</p>
    </div>

    <!-- Batch Info -->
    <div class="info-box">
        <div class="row">
            <div class="col-md-3">
                <small style="color: rgba(52,152,219,0.8); font-weight: 600;">BATCH CODE</small>
                <h5 style="color: #3498db; margin-top: 0.25rem;">{{ $batch->batch_code }}</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(52,152,219,0.8); font-weight: 600;">BERAT SAMPLE</small>
                <h5 style="color: #3498db; margin-top: 0.25rem;">{{ number_format($batch->current_weight, 2) }} kg</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(52,152,219,0.8); font-weight: 600;">MATERIAL</small>
                <h5 style="color: #3498db; margin-top: 0.25rem;">
                    <span class="badge" style="background: #3498db; color: white; padding: 0.5rem 1rem;">
                        {{ $batch->productCode->material }}
                    </span>
                </h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(52,152,219,0.8); font-weight: 600;">STATUS</small>
                <h5 style="color: #3498db; margin-top: 0.25rem;">Ready for Analysis</h5>
            </div>
        </div>
    </div>

    <!-- Analysis Form -->
    <div class="form-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.5rem;">
            <i class="bi bi-file-earmark-text me-2"></i>Form Analisis Kandungan LTJ
        </h5>

        <!-- ✅ FIXED: Changed route name from lab.store-analysis to lab.batch.analyze -->
        <form action="{{ route('lab.batch.analyze', $batch) }}" method="POST" id="analysisForm">
            @csrf

            <!-- Neodymium (Nd) -->
            <div class="element-input">
                <div class="element-label">
                    <i class="bi bi-circle-fill me-2" style="color: #e74c3c;"></i>Neodymium (Nd)
                </div>
                <div class="flex-grow-1">
                    <div class="input-group">
                        <input type="number" name="nd_content" class="form-control element-value" 
                               step="0.01" min="0" max="100" required 
                               placeholder="0.00" id="nd_content" value="{{ old('nd_content') }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('nd_content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Lanthanum (La) -->
            <div class="element-input">
                <div class="element-label">
                    <i class="bi bi-circle-fill me-2" style="color: #3498db;"></i>Lanthanum (La)
                </div>
                <div class="flex-grow-1">
                    <div class="input-group">
                        <input type="number" name="la_content" class="form-control element-value" 
                               step="0.01" min="0" max="100" required 
                               placeholder="0.00" id="la_content" value="{{ old('la_content') }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('la_content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Cerium (Ce) -->
            <div class="element-input">
                <div class="element-label">
                    <i class="bi bi-circle-fill me-2" style="color: #2ecc71;"></i>Cerium (Ce)
                </div>
                <div class="flex-grow-1">
                    <div class="input-group">
                        <input type="number" name="ce_content" class="form-control element-value" 
                               step="0.01" min="0" max="100" required 
                               placeholder="0.00" id="ce_content" value="{{ old('ce_content') }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('ce_content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Yttrium (Y) -->
            <div class="element-input">
                <div class="element-label">
                    <i class="bi bi-circle-fill me-2" style="color: #f39c12;"></i>Yttrium (Y)
                </div>
                <div class="flex-grow-1">
                    <div class="input-group">
                        <input type="number" name="y_content" class="form-control element-value" 
                               step="0.01" min="0" max="100" required 
                               placeholder="0.00" id="y_content" value="{{ old('y_content') }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('y_content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Praseodymium (Pr) -->
            <div class="element-input">
                <div class="element-label">
                    <i class="bi bi-circle-fill me-2" style="color: #9b59b6;"></i>Praseodymium (Pr)
                </div>
                <div class="flex-grow-1">
                    <div class="input-group">
                        <input type="number" name="pr_content" class="form-control element-value" 
                               step="0.01" min="0" max="100" required 
                               placeholder="0.00" id="pr_content" value="{{ old('pr_content') }}">
                        <span class="input-group-text">%</span>
                    </div>
                    @error('pr_content')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <!-- Preview Box -->
            <div class="preview-box" id="previewBox" style="display: none;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <strong style="color: #2ecc71; font-size: 1.1rem;">Total Recovery:</strong>
                        <span id="totalRecovery" style="font-size: 1.5rem; font-weight: 700; color: #2ecc71;">0.00%</span>
                    </div>
                    <div id="recoveryStatus"></div>
                </div>
                <small style="color: rgba(62,92,116,0.7); display: block; margin-top: 0.5rem;">
                    <i class="bi bi-info-circle me-1"></i>
                    Total kandungan LTJ yang berhasil di-recover dari sample
                </small>
            </div>

            <!-- Notes -->
            <div class="mb-4 mt-3">
                <label class="form-label">Catatan Analisis (Opsional)</label>
                <textarea name="notes" class="form-control" rows="3" 
                          placeholder="Catatan tambahan tentang hasil analisis, kondisi sample, dll">{{ old('notes') }}</textarea>
            </div>

            <!-- Warning -->
            <div class="alert alert-warning" style="border-left: 4px solid #ffc107;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Perhatian:</strong> Pastikan hasil analisis sudah akurat sebelum submit. Data yang tersimpan akan dicatat dalam sistem traceability.
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="bi bi-check-circle me-2"></i>Simpan Hasil Analisis
                </button>
                <a href="{{ route('lab.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
const elementInputs = document.querySelectorAll('.element-value');
const previewBox = document.getElementById('previewBox');
const totalRecoverySpan = document.getElementById('totalRecovery');
const recoveryStatus = document.getElementById('recoveryStatus');
const submitBtn = document.getElementById('submitBtn');

function updatePreview() {
    let total = 0;
    let hasValue = false;
    
    elementInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        total += value;
        if (value > 0) hasValue = true;
    });
    
    if (hasValue) {
        previewBox.style.display = 'block';
        totalRecoverySpan.textContent = total.toFixed(2) + '%';
        
        if (total > 100) {
            previewBox.style.background = 'linear-gradient(135deg, rgba(231,76,60,0.1) 0%, rgba(231,76,60,0.05) 100%)';
            previewBox.style.borderColor = '#e74c3c';
            totalRecoverySpan.style.color = '#e74c3c';
            recoveryStatus.innerHTML = '<span style="color: #e74c3c; font-weight: 600;">❌ MELEBIHI 100%</span>';
            submitBtn.disabled = true;
        } else {
            previewBox.style.background = 'linear-gradient(135deg, rgba(46,204,113,0.05) 0%, rgba(46,204,113,0.02) 100%)';
            previewBox.style.borderColor = '#2ecc71';
            totalRecoverySpan.style.color = '#2ecc71';
            recoveryStatus.innerHTML = '<span style="color: #2ecc71; font-weight: 600;">✅ VALID</span>';
            submitBtn.disabled = false;
        }
    } else {
        previewBox.style.display = 'none';
        submitBtn.disabled = true;
    }
}

elementInputs.forEach(input => {
    input.addEventListener('input', updatePreview);
});

document.getElementById('analysisForm').addEventListener('submit', function(e) {
    let total = 0;
    elementInputs.forEach(input => {
        total += parseFloat(input.value) || 0;
    });
    
    if (total > 100) {
        e.preventDefault();
        alert('Total recovery tidak boleh melebihi 100%! Saat ini: ' + total.toFixed(2) + '%');
        return false;
    }
    
    return confirm('Yakin ingin menyimpan hasil analisis ini?');
});
</script>
@endpush
@endsection