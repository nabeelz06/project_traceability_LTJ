@extends('layouts.app')

@section('title', 'Input Analisis LTJ')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="bi bi-microscope" style="color: var(--timah-blue);"></i>
            Analisis LTJ: {{ $batch->batch_code }}
        </h1>
        <p class="text-gray-600">Input Kadar 5 Unsur Logam Tanah Jarang</p>
    </div>

    <!-- Batch Info -->
    <div class="card mb-4">
        <div class="card-header" style="background: var(--timah-blue); color: white;">
            <h5 class="mb-0">Informasi Sampel</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <p class="text-muted mb-1">Batch Code:</p>
                    <p class="fw-bold">{{ $batch->batch_code }}</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted mb-1">Berat Sampel:</p>
                    <p class="fw-bold">{{ number_format($batch->current_weight, 2) }} kg</p>
                </div>
                <div class="col-md-4">
                    <p class="text-muted mb-1">Diterima:</p>
                    <p class="fw-bold">{{ $batch->created_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Analysis Form -->
    <div class="card">
        <div class="card-header" style="background: var(--timah-blue); color: white;">
            <h5 class="mb-0">Form Analisis Kandungan LTJ</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('lab.store-analysis', $batch) }}" method="POST">
                @csrf

                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Catatan:</strong> Input persentase kadar untuk setiap unsur LTJ. Total tidak harus 100%.
                </div>

                <!-- 5 Unsur LTJ -->
                <div class="row">
                    <!-- Neodymium (Nd) -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label">
                                <strong>Neodymium (Nd)</strong> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="nd_content" class="form-control" min="0" max="100" step="0.01" required id="nd_content">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Untuk magnet permanen, baterai EV</small>
                        </div>
                    </div>

                    <!-- Lanthanum (La) -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label">
                                <strong>Lanthanum (La)</strong> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="la_content" class="form-control" min="0" max="100" step="0.01" required id="la_content">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Untuk katalis, baterai hybrid</small>
                        </div>
                    </div>

                    <!-- Cerium (Ce) -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label">
                                <strong>Cerium (Ce)</strong> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="ce_content" class="form-control" min="0" max="100" step="0.01" required id="ce_content">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Untuk katalis, polishing compound</small>
                        </div>
                    </div>

                    <!-- Yttrium (Y) -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label">
                                <strong>Yttrium (Y)</strong> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="y_content" class="form-control" min="0" max="100" step="0.01" required id="y_content">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Untuk phosphor, keramik advanced</small>
                        </div>
                    </div>

                    <!-- Praseodymium (Pr) -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label">
                                <strong>Praseodymium (Pr)</strong> <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="number" name="pr_content" class="form-control" min="0" max="100" step="0.01" required id="pr_content">
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Untuk magnet, keramik warna</small>
                        </div>
                    </div>

                    <!-- Total Recovery -->
                    <div class="col-md-6 mb-4">
                        <div class="form-group">
                            <label class="form-label">
                                <strong>Total Recovery Rate</strong>
                            </label>
                            <div class="input-group">
                                <input type="number" name="total_recovery" class="form-control" min="0" max="100" step="0.01" id="total_recovery" readonly>
                                <span class="input-group-text">%</span>
                            </div>
                            <small class="text-muted">Auto-calculated dari total 5 unsur</small>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div class="form-group">
                    <label class="form-label">Catatan Analisis</label>
                    <textarea name="notes" class="form-control" rows="3" placeholder="Metode analisis, kondisi sampel, dll"></textarea>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Simpan Hasil Analisis
                    </button>
                    <a href="{{ route('lab.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Auto-calculate total recovery
const inputs = ['nd_content', 'la_content', 'ce_content', 'y_content', 'pr_content'];
inputs.forEach(id => {
    document.getElementById(id).addEventListener('input', calculateTotal);
});

function calculateTotal() {
    let total = 0;
    inputs.forEach(id => {
        total += parseFloat(document.getElementById(id).value) || 0;
    });
    document.getElementById('total_recovery').value = total.toFixed(2);
}
</script>
@endpush
@endsection