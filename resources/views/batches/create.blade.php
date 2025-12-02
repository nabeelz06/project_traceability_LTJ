@extends('layouts.app')

@section('title', 'Buat Batch Induk Baru - Traceability LTJ')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --primary: #3e5c74;
        --primary-dark: #2d4454;
        --primary-light: #e8eef3;
        --gold: #c5a572;
    }

    body {
        font-family: 'Poppins', system-ui, sans-serif;
        background: linear-gradient(135deg, var(--primary-light) 0%, #f5f8fa 100%);
        min-height: 100vh;
        color: #2d4454;
    }

    /* Header */
    .page-header {
        margin-bottom: 1.5rem;
        animation: fadeInDown 0.6s ease;
    }

    .page-header h2 {
        font-family: 'Poppins', sans-serif;
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
        letter-spacing: -0.5px;
    }

    /* Card */
    .card {
        border-radius: 14px;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 10px 28px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
        animation: fadeInUp 0.7s ease;
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border-radius: 14px 14px 0 0 !important;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        border: none;
    }

    .card-body {
        padding: 2rem;
    }

    /* Form Sections */
    .form-section {
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid rgba(62,92,116,0.1);
    }

    .form-section:last-of-type {
        border-bottom: none;
    }

    .form-section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .form-section-title i {
        color: var(--gold);
    }

    /* Form Elements */
    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: var(--primary);
        font-size: 0.9rem;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(62,92,116,0.2);
        border-radius: 10px;
        font-size: 1rem;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
        background: rgba(255,255,255,0.9);
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(62,92,116,0.1);
        background: white;
    }

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    /* Grid Layouts */
    .form-grid-2 {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1rem;
    }

    .form-grid-5 {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 1rem;
    }

    /* Buttons */
    .btn {
        padding: 0.75rem 1.75rem;
        border-radius: 10px;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(62,92,116,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(62,92,116,0.4);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #858796 0%, #9fa1b0 100%);
        color: white;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(133,135,150,0.3);
    }

    /* Messages */
    .error-message {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: block;
    }

    .help-text {
        color: rgba(62,92,116,0.7);
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: block;
    }

    .required-mark {
        color: #dc3545;
        margin-left: 2px;
    }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid rgba(62,92,116,0.1);
    }

    /* Preview Box */
    .preview-box {
        background: linear-gradient(135deg, rgba(62,92,116,0.05) 0%, rgba(197,165,114,0.05) 100%);
        border: 2px dashed var(--primary);
        border-radius: 10px;
        padding: 1.25rem;
        margin-top: 1rem;
    }

    .preview-box h6 {
        color: var(--primary);
        font-weight: 600;
        margin-bottom: 0.75rem;
    }

    .preview-item {
        display: flex;
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(62,92,116,0.1);
    }

    .preview-item:last-child {
        border-bottom: none;
    }

    .preview-label {
        color: rgba(62,92,116,0.8);
        font-size: 0.9rem;
    }

    .preview-value {
        color: var(--primary);
        font-weight: 600;
        font-size: 0.9rem;
    }

    /* File Upload Custom Styling */
    .file-upload-wrapper {
        position: relative;
    }

    .file-upload-wrapper input[type="file"] {
        cursor: pointer;
    }

    .file-upload-wrapper input[type="file"]::-webkit-file-upload-button {
        background: var(--primary);
        color: white;
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-weight: 600;
        transition: all 0.3s ease;
    }

    .file-upload-wrapper input[type="file"]::-webkit-file-upload-button:hover {
        background: var(--primary-dark);
    }

    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .form-grid-2,
        .form-grid-5 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container py-4">
    <!-- Header -->
    <div class="page-header">
        <h2><i class="bi bi-box-seam me-2"></i>Buat Batch Induk Baru</h2>
    </div>
    
    <div class="card">
        <div class="card-header">
            <i class="bi bi-plus-circle me-2"></i>Form Pembuatan Batch LTJ
        </div>
        <div class="card-body">
            <form action="{{ route('batches.store') }}" method="POST" id="batchForm" enctype="multipart/form-data">
                @csrf
                
                <!-- SECTION 1: Data Produk -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-box"></i>
                        <span>Data Produk</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="product_code_id">
                            Kode Produk LTJ <span class="required-mark">*</span>
                        </label>
                        <select id="product_code_id" name="product_code_id" class="form-select" required>
                            <option value="">-- Pilih Kode Produk --</option>
                            @foreach($productCodes as $code)
                            <option value="{{ $code->id }}" {{ old('product_code_id') == $code->id ? 'selected' : '' }}>
                                {{ $code->code }} - {{ $code->description }}
                            </option>
                            @endforeach
                        </select>
                        @error('product_code_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SECTION 2: Data Berat & Konsentrat -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-speedometer2"></i>
                        <span>Data Berat & Konsentrat</span>
                    </div>
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="initial_weight">
                                Tonase/Berat <span class="required-mark">*</span>
                            </label>
                            <input type="number" id="initial_weight" name="initial_weight" class="form-control" 
                                   step="0.001" min="0.001" value="{{ old('initial_weight') }}" 
                                   required placeholder="Masukkan berat">
                            @error('initial_weight')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="weight_unit">
                                Satuan <span class="required-mark">*</span>
                            </label>
                            <select id="weight_unit" name="weight_unit" class="form-select" required>
                                <option value="kg" {{ old('weight_unit') == 'kg' ? 'selected' : '' }}>kg</option>
                                <option value="ton" {{ old('weight_unit', 'ton') == 'ton' ? 'selected' : '' }}>ton</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="konsentrat_persen">
                            Konsentrat LTJ (%) <span class="required-mark">*</span>
                        </label>
                        <input type="number" id="konsentrat_persen" name="konsentrat_persen" class="form-control" 
                               step="0.01" min="0" max="100" value="{{ old('konsentrat_persen') }}" 
                               required placeholder="Contoh: 68.20">
                        <span class="help-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Persentase kandungan konsentrat LTJ dalam material (0-100%)
                        </span>
                        @error('konsentrat_persen')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SECTION 3: Kandungan 5 Unsur LTJ -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-gem"></i>
                        <span>Kandungan 5 Unsur LTJ (Opsional)</span>
                    </div>
                    
                    <div class="form-grid-5">
                        <div class="form-group">
                            <label class="form-label" for="nd_content">
                                Nd (%)
                            </label>
                            <input type="number" id="nd_content" name="nd_content" class="form-control unsur-ltj" 
                                   step="0.01" min="0" max="100" value="{{ old('nd_content') }}" 
                                   placeholder="0.00">
                            <span class="help-text">Neodymium</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="y_content">
                                Y (%)
                            </label>
                            <input type="number" id="y_content" name="y_content" class="form-control unsur-ltj" 
                                   step="0.01" min="0" max="100" value="{{ old('y_content') }}" 
                                   placeholder="0.00">
                            <span class="help-text">Yttrium</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="ce_content">
                                Ce (%)
                            </label>
                            <input type="number" id="ce_content" name="ce_content" class="form-control unsur-ltj" 
                                   step="0.01" min="0" max="100" value="{{ old('ce_content') }}" 
                                   placeholder="0.00">
                            <span class="help-text">Cerium</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="la_content">
                                La (%)
                            </label>
                            <input type="number" id="la_content" name="la_content" class="form-control unsur-ltj" 
                                   step="0.01" min="0" max="100" value="{{ old('la_content') }}" 
                                   placeholder="0.00">
                            <span class="help-text">Lanthanum</span>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="pr_content">
                                Pr (%)
                            </label>
                            <input type="number" id="pr_content" name="pr_content" class="form-control unsur-ltj" 
                                   step="0.01" min="0" max="100" value="{{ old('pr_content') }}" 
                                   placeholder="0.00">
                            <span class="help-text">Praseodymium</span>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3" style="background: rgba(62,92,116,0.05); border: 1px solid rgba(62,92,116,0.2); border-radius: 10px; padding: 0.75rem 1rem;">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Total unsur:</strong> <span id="totalUnsur">0.00%</span> (maksimal 100%)
                    </div>

                    @error('nd_content')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- SECTION 4: Data Kontainer & Lokasi -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-truck"></i>
                        <span>Data Kontainer & Lokasi</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="container_code">Kode Kontainer</label>
                        <input type="text" id="container_code" name="container_code" class="form-control" 
                               placeholder="Kosongkan untuk auto-generate" value="{{ old('container_code') }}">
                        <span class="help-text">
                            <i class="bi bi-magic me-1"></i>
                            Format: K-TMH-XXXX (akan di-generate otomatis jika dikosongkan)
                        </span>
                        @error('container_code')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="origin_location">
                            Lokasi Asal Warehouse <span class="required-mark">*</span>
                        </label>
                        <input type="text" id="origin_location" name="origin_location" class="form-control" 
                               value="{{ old('origin_location') }}" required placeholder="Contoh: Gudang PT Timah Bangka">
                        @error('origin_location')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SECTION 5: Koordinat GPS (OPSIONAL) -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-geo-alt-fill"></i>
                        <span>Koordinat GPS (Opsional)</span>
                    </div>
                    
                    <div class="form-grid-2">
                        <div class="form-group">
                            <label class="form-label" for="current_latitude">Latitude</label>
                            <input type="number" id="current_latitude" name="current_latitude" class="form-control" 
                                   step="0.00000001" min="-90" max="90" value="{{ old('current_latitude') }}" 
                                   placeholder="-7.250445">
                            <span class="help-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Contoh: -7.250445 (Surabaya)
                            </span>
                            @error('current_latitude')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label" for="current_longitude">Longitude</label>
                            <input type="number" id="current_longitude" name="current_longitude" class="form-control" 
                                   step="0.00000001" min="-180" max="180" value="{{ old('current_longitude') }}" 
                                   placeholder="112.768845">
                            <span class="help-text">
                                <i class="bi bi-info-circle me-1"></i>
                                Contoh: 112.768845 (Surabaya)
                            </span>
                            @error('current_longitude')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="current_location_name">Nama Lokasi</label>
                        <input type="text" id="current_location_name" name="current_location_name" class="form-control" 
                               value="{{ old('current_location_name') }}" 
                               placeholder="Contoh: Gudang PT Timah Bangka">
                        <span class="help-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Nama lokasi akan digunakan sebagai referensi (bisa berbeda dengan GPS)
                        </span>
                        @error('current_location_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SECTION 6: Dokumentasi / Evidence (OPSIONAL) -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-camera-fill"></i>
                        <span>Dokumentasi / Evidence (Opsional)</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="evidence_photos">Upload Foto</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="evidence_photos" name="evidence_photos[]" class="form-control" 
                                   accept="image/jpeg,image/png,image/jpg" multiple>
                        </div>
                        <span class="help-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Format: JPG, PNG (maksimal 5MB per file). Anda dapat memilih multiple files.
                        </span>
                        @error('evidence_photos.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="evidence_videos">Upload Video (CCTV/Dokumentasi)</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="evidence_videos" name="evidence_videos[]" class="form-control" 
                                   accept="video/mp4,video/avi,video/mov" multiple>
                        </div>
                        <span class="help-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Format: MP4, AVI, MOV (maksimal 50MB per file). Untuk video distribusi/proses.
                        </span>
                        @error('evidence_videos.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="evidence_documents">Upload Dokumen Pendukung</label>
                        <div class="file-upload-wrapper">
                            <input type="file" id="evidence_documents" name="evidence_documents[]" class="form-control" 
                                   accept=".pdf,.doc,.docx,.xlsx" multiple>
                        </div>
                        <span class="help-text">
                            <i class="bi bi-info-circle me-1"></i>
                            Format: PDF, DOC, DOCX, XLSX (maksimal 10MB per file). Untuk sertifikat/laporan.
                        </span>
                        @error('evidence_documents.*')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- SECTION 7: Keterangan -->
                <div class="form-section">
                    <div class="form-section-title">
                        <i class="bi bi-card-text"></i>
                        <span>Keterangan</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="keterangan">Catatan / Remarks</label>
                        <textarea id="keterangan" name="keterangan" class="form-control" rows="3" 
                                  placeholder="Tambahkan catatan tambahan (opsional)">{{ old('keterangan') }}</textarea>
                    </div>
                </div>

                <!-- Preview Perhitungan -->
                <div class="preview-box">
                    <h6><i class="bi bi-calculator me-2"></i>Preview Perhitungan Massa LTJ</h6>
                    <div class="preview-item">
                        <span class="preview-label">Berat Material (kg):</span>
                        <span class="preview-value" id="previewWeight">0.00 kg</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Konsentrat (%):</span>
                        <span class="preview-value" id="previewKonsentrat">0.00%</span>
                    </div>
                    <div class="preview-item">
                        <span class="preview-label">Massa LTJ (kg):</span>
                        <span class="preview-value" id="previewMassaLtj" style="color: var(--gold); font-size: 1.1rem;">0.00 kg</span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Simpan & Generate Kode Batch
                    </button>
                    <a href="{{ route('batches.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const initialWeightInput = document.getElementById('initial_weight');
    const weightUnitSelect = document.getElementById('weight_unit');
    const konsentratInput = document.getElementById('konsentrat_persen');
    const unsurInputs = document.querySelectorAll('.unsur-ltj');
    
    // Auto-calculate preview massa LTJ
    function updatePreview() {
        const weight = parseFloat(initialWeightInput.value) || 0;
        const unit = weightUnitSelect.value;
        const konsentrat = parseFloat(konsentratInput.value) || 0;
        
        // Konversi ke kg
        const weightInKg = unit === 'ton' ? weight * 1000 : weight;
        
        // Hitung massa LTJ
        const massaLtj = weightInKg * (konsentrat / 100);
        
        // Update preview
        document.getElementById('previewWeight').textContent = weightInKg.toFixed(2) + ' kg';
        document.getElementById('previewKonsentrat').textContent = konsentrat.toFixed(2) + '%';
        document.getElementById('previewMassaLtj').textContent = massaLtj.toFixed(2) + ' kg';
    }
    
    // Auto-calculate total 5 unsur LTJ
    function updateTotalUnsur() {
        let total = 0;
        unsurInputs.forEach(input => {
            total += parseFloat(input.value) || 0;
        });
        
        const totalSpan = document.getElementById('totalUnsur');
        totalSpan.textContent = total.toFixed(2) + '%';
        
        // Warning jika > 100%
        if (total > 100) {
            totalSpan.style.color = '#dc3545';
            totalSpan.style.fontWeight = 'bold';
        } else {
            totalSpan.style.color = 'var(--primary)';
            totalSpan.style.fontWeight = '600';
        }
    }
    
    // Event listeners
    initialWeightInput.addEventListener('input', updatePreview);
    weightUnitSelect.addEventListener('change', updatePreview);
    konsentratInput.addEventListener('input', updatePreview);
    
    unsurInputs.forEach(input => {
        input.addEventListener('input', updateTotalUnsur);
    });
    
    // Initial calculation
    updatePreview();
    updateTotalUnsur();
    
    // Form validation sebelum submit
    document.getElementById('batchForm').addEventListener('submit', function(e) {
        let totalUnsur = 0;
        unsurInputs.forEach(input => {
            totalUnsur += parseFloat(input.value) || 0;
        });
        
        if (totalUnsur > 100) {
            e.preventDefault();
            alert('Total kandungan 5 unsur LTJ tidak boleh melebihi 100%! Saat ini: ' + totalUnsur.toFixed(2) + '%');
            return false;
        }
    });
});
</script>
@endsection