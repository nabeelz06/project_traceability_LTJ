@extends('layouts.app')

@section('title', 'Export Konsentrat')

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
        background: linear-gradient(135deg, rgba(62,92,116,0.05) 0%, rgba(62,92,116,0.02) 100%);
        border-left: 4px solid var(--primary);
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
                <i class="bi bi-box-arrow-right me-2"></i>Export Konsentrat
            </h2>
        </div>
        <p style="color: rgba(62,92,116,0.7); margin: 0;">Pencatatan export ke luar negeri atau penjualan domestik</p>
    </div>

    <!-- Batch Info Card -->
    <div class="info-box">
        <div class="row">
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">BATCH CODE</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">{{ $batch->batch_code }}</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">MATERIAL</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">
                    <span class="badge" style="background: 
                        {{ $batch->productCode->material == 'ZIRCON' ? '#e74c3c' : '#9b59b6' }}; 
                        color: white; padding: 0.5rem 1rem; font-size: 0.9rem;">
                        {{ $batch->productCode->material }}
                    </span>
                </h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">BERAT</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">{{ number_format($batch->current_weight, 2) }} kg</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">KANDUNGAN</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">{{ number_format($batch->konsentrat_persen ?? 0, 2) }}%</h5>
            </div>
        </div>
    </div>

    <!-- Export Form -->
    <div class="form-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.5rem;">
            <i class="bi bi-file-earmark-text me-2"></i>Form Export
        </h5>

        <form action="{{ route('warehouse.export', $batch) }}" method="POST">
            @csrf

            <!-- Export Type -->
            <div class="mb-3">
                <label class="form-label">Tipe Export <span class="text-danger">*</span></label>
                <select name="export_type" class="form-select" required>
                    <option value="">-- Pilih Tipe --</option>
                    <option value="export">🌍 Export (Luar Negeri)</option>
                    <option value="domestic">🇮🇩 Penjualan Domestik (Indonesia)</option>
                </select>
                <small class="text-muted">Pilih tujuan pengiriman konsentrat</small>
            </div>

            <!-- Destination -->
            <div class="mb-3">
                <label class="form-label">Tujuan / Destination <span class="text-danger">*</span></label>
                <input type="text" name="destination" class="form-control" 
                       placeholder="Contoh: China Metallurgy Corp / PT Smelter Indonesia" 
                       required>
                <small class="text-muted">Nama negara/perusahaan tujuan</small>
            </div>

            <!-- Manifest Number -->
            <div class="mb-3">
                <label class="form-label">Nomor Manifest / Dokumen</label>
                <input type="text" name="manifest_number" class="form-control" 
                       placeholder="Contoh: MAN/2024/001">
                <small class="text-muted">Nomor surat jalan atau dokumen export (opsional)</small>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="form-label">Catatan</label>
                <textarea name="notes" class="form-control" rows="3" 
                          placeholder="Catatan tambahan tentang export ini (opsional)"></textarea>
            </div>

            <!-- Warning Info -->
            <div class="alert alert-warning" style="border-left: 4px solid #ffc107;">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>Perhatian:</strong> Setelah export, batch ini akan dihapus dari stock warehouse dan berat akan menjadi 0 kg.
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary" onclick="return confirm('Yakin ingin export batch ini? Stock warehouse akan berkurang.')">
                    <i class="bi bi-check-circle me-2"></i>Konfirmasi Export
                </button>
                <a href="{{ route('warehouse.dashboard') }}" class="btn btn-secondary">
                    <i class="bi bi-x-circle me-2"></i>Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection