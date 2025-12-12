@extends('layouts.app')

@section('title', 'Hasil Analisis LTJ')

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

    .info-card {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 2rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .ltj-element {
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
        border-radius: 12px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 4px 15px rgba(62,92,116,0.08);
        transition: all 0.3s ease;
    }

    .ltj-element:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(62,92,116,0.15);
    }

    .element-symbol {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }

    .element-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
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
                <i class="bi bi-clipboard-data me-2"></i>Hasil Analisis LTJ
            </h2>
        </div>
        <p style="color: rgba(62,92,116,0.7); margin: 0;">Detail kandungan 5 unsur Logam Tanah Jarang</p>
    </div>

    <!-- Batch Info -->
    <div class="info-card">
        <div class="row">
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">BATCH CODE</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">{{ $batch->batch_code }}</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">BERAT SAMPLE</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">{{ number_format($batch->current_weight, 2) }} kg</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">ANALYST</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">{{ $analysis->analyst->name ?? '-' }}</h5>
            </div>
            <div class="col-md-3">
                <small style="color: rgba(62,92,116,0.7); font-weight: 600;">TANGGAL ANALISIS</small>
                <h5 style="color: var(--primary); margin-top: 0.25rem;">{{ $analysis->analyzed_at->format('d M Y') }}</h5>
            </div>
        </div>
    </div>

    <!-- 5 Unsur LTJ -->
    <div class="info-card">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.5rem;">
            <i class="bi bi-gem me-2"></i>Kandungan 5 Unsur LTJ
        </h5>
        <div class="row g-3">
            <div class="col-md-2">
                <div class="ltj-element" style="border-left: 4px solid #e74c3c;">
                    <div class="element-symbol" style="color: #e74c3c;">Nd</div>
                    <div class="element-name" style="font-size: 0.85rem; color: rgba(62,92,116,0.7);">Neodymium</div>
                    <div class="element-value">{{ number_format($analysis->nd_content, 2) }}%</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="ltj-element" style="border-left: 4px solid #3498db;">
                    <div class="element-symbol" style="color: #3498db;">La</div>
                    <div class="element-name" style="font-size: 0.85rem; color: rgba(62,92,116,0.7);">Lanthanum</div>
                    <div class="element-value">{{ number_format($analysis->la_content, 2) }}%</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="ltj-element" style="border-left: 4px solid #f39c12;">
                    <div class="element-symbol" style="color: #f39c12;">Ce</div>
                    <div class="element-name" style="font-size: 0.85rem; color: rgba(62,92,116,0.7);">Cerium</div>
                    <div class="element-value">{{ number_format($analysis->ce_content, 2) }}%</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="ltj-element" style="border-left: 4px solid #2ecc71;">
                    <div class="element-symbol" style="color: #2ecc71;">Y</div>
                    <div class="element-name" style="font-size: 0.85rem; color: rgba(62,92,116,0.7);">Yttrium</div>
                    <div class="element-value">{{ number_format($analysis->y_content, 2) }}%</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="ltj-element" style="border-left: 4px solid #9b59b6;">
                    <div class="element-symbol" style="color: #9b59b6;">Pr</div>
                    <div class="element-name" style="font-size: 0.85rem; color: rgba(62,92,116,0.7);">Praseodymium</div>
                    <div class="element-value">{{ number_format($analysis->pr_content, 2) }}%</div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="ltj-element" style="border-left: 4px solid var(--primary); background: linear-gradient(135deg, var(--primary-light) 0%, rgba(255,255,255,0.9) 100%);">
                    <div class="element-symbol" style="color: var(--primary);">Σ</div>
                    <div class="element-name" style="font-size: 0.85rem; color: rgba(62,92,116,0.7);">Total Recovery</div>
                    <div class="element-value" style="font-size: 1.75rem;">{{ number_format($analysis->total_recovery, 2) }}%</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pie Chart -->
    <div class="info-card">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.5rem;">
            <i class="bi bi-pie-chart me-2"></i>Komposisi LTJ
        </h5>
        <canvas id="ltjPieChart" height="100"></canvas>
    </div>

    <!-- Notes -->
    @if($analysis->notes)
    <div class="info-card">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1rem;">
            <i class="bi bi-file-text me-2"></i>Catatan Analisis
        </h5>
        <p style="color: rgba(62,92,116,0.8); margin: 0;">{{ $analysis->notes }}</p>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('ltjPieChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: ['Nd (Neodymium)', 'La (Lanthanum)', 'Ce (Cerium)', 'Y (Yttrium)', 'Pr (Praseodymium)'],
        datasets: [{
            data: [
                {{ $analysis->nd_content }},
                {{ $analysis->la_content }},
                {{ $analysis->ce_content }},
                {{ $analysis->y_content }},
                {{ $analysis->pr_content }}
            ],
            backgroundColor: ['#e74c3c', '#3498db', '#f39c12', '#2ecc71', '#9b59b6'],
            borderWidth: 3,
            borderColor: '#fff'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    font: { size: 14, family: 'Poppins', weight: 'bold' },
                    padding: 15,
                    usePointStyle: true,
                    pointStyle: 'circle'
                }
            },
            tooltip: {
                backgroundColor: 'rgba(62, 92, 116, 0.9)',
                titleFont: { size: 14, family: 'Poppins', weight: 'bold' },
                bodyFont: { size: 13, family: 'Poppins' },
                padding: 12,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed;
                        return label + ': ' + value.toFixed(2) + '%';
                    }
                }
            }
        }
    }
});
</script>
@endpush
@endsection