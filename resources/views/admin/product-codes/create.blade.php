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

                <!-- Stage Selection -->
                <div class="mb-3">
                    <label class="form-label">Stage / Tahapan *</label>
                    <select name="stage" id="stage" class="form-select" required>
                        <option value="">-- Pilih Tahapan --</option>
                        <option value="Upstream" {{ old('stage') == 'Upstream' ? 'selected' : '' }}>
                            Upstream (TIM) - Material Mentah
                        </option>
                        <option value="Midstream" {{ old('stage') == 'Midstream' ? 'selected' : '' }}>
                            Midstream (MID) - Hasil Pengolahan
                        </option>
                        <option value="Downstream" {{ old('stage') == 'Downstream' ? 'selected' : '' }}>
                            Downstream (FINAL) - Produk Akhir
                        </option>
                    </select>
                    <small class="text-muted">Pilih tahapan supply chain untuk produk ini</small>
                    @error('stage')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="row">
                    <!-- Material -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Material / Bahan *</label>
                        <input type="text"
                               name="material"
                               id="material"
                               class="form-control"
                               value="{{ old('material') }}"
                               placeholder="Contoh: MON, ND, CE, Y, PR, LA"
                               maxlength="10"
                               required>
                        <small class="text-muted">Kode material (MON, ND, CE, Y, PR, LA)</small>
                        @error('material')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Specification -->
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Spesifikasi *</label>
                        <input type="text"
                               name="spec"
                               id="spec"
                               class="form-control"
                               value="{{ old('spec') }}"
                               placeholder="Contoh: RAW, CON, OXI99"
                               maxlength="20"
                               required>
                        <small class="text-muted">RAW, CON, OXI99, OXI999, MET</small>
                        @error('spec')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Preview Code -->
                <div class="mb-3 p-3" style="background: #E3F2FD; border-left: 4px solid #2196F3; border-radius: 4px;">
                    <small style="color: #1565C0; font-weight: 600;">
                        <i class="bi bi-info-circle me-1"></i>Preview Kode Produk
                    </small>
                    <div style="font-size: 1.25rem; font-weight: 700; color: #0D47A1; margin-top: 0.25rem;" id="codePreview">
                        Format: [STAGE]-[MATERIAL]-[SPEC]
                    </div>
                    <small style="color: #1976D2;">Contoh: TIM-MON-RAW, MID-ND-OXI99</small>
                </div>

                <!-- Description -->
                <div class="mb-3">
                    <label class="form-label">Deskripsi *</label>
                    <input type="text"
                           name="description"
                           class="form-control"
                           value="{{ old('description') }}"
                           placeholder="Contoh: Monasit Mentah dari Mineral Ikutan Timah"
                           maxlength="255"
                           required>
                    @error('description')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Category -->
                <div class="mb-3">
                    <label class="form-label">Kategori <span class="text-muted">(Opsional)</span></label>
                    <input type="text"
                           name="category"
                           class="form-control"
                           value="{{ old('category') }}"
                           placeholder="Contoh: Raw Material, Concentrated, Purified Oxide"
                           maxlength="100">
                    <small class="text-muted">Kategori produk untuk pengelompokan</small>
                </div>

                <!-- Specifications (Detail) -->
                <div class="mb-3">
                    <label class="form-label">Spesifikasi Detail <span class="text-muted">(Opsional)</span></label>
                    <textarea name="specifications"
                              class="form-control"
                              rows="4"
                              placeholder="Masukkan spesifikasi teknis produk, komposisi kimia, atau detail lainnya..."
                              maxlength="1000">{{ old('specifications') }}</textarea>
                    <small class="text-muted">Detail teknis seperti kemurnian, komposisi, atau standar kualitas</small>
                    @error('specifications')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Info Box -->
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Catatan:</strong> Product code akan digunakan untuk klasifikasi batch dalam sistem traceability.
                    Pastikan kode yang dibuat sesuai dengan standar perusahaan.
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('admin.product-codes.index') }}" class="btn" style="background: var(--secondary); color: white;">
                        <i class="bi bi-x-circle me-1"></i>Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Product Code
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- JavaScript untuk Preview Code -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stageInput = document.getElementById('stage');
    const materialInput = document.getElementById('material');
    const specInput = document.getElementById('spec');
    const codePreview = document.getElementById('codePreview');

    function updatePreview() {
        const stage = stageInput.value;
        const material = materialInput.value.toUpperCase().trim();
        const spec = specInput.value.toUpperCase().trim();

        // Map stage ke prefix
        const stagePrefix = {
            'Upstream': 'TIM',
            'Midstream': 'MID',
            'Downstream': 'FINAL'
        }[stage] || '[STAGE]';

        // Generate preview
        const preview = `${stagePrefix}-${material || '[MATERIAL]'}-${spec || '[SPEC]'}`;
        codePreview.textContent = preview;
    }

    // Event listeners
    stageInput.addEventListener('change', updatePreview);
    materialInput.addEventListener('input', updatePreview);
    specInput.addEventListener('input', updatePreview);

    // Initial preview
    updatePreview();
});
</script>
@endsection
