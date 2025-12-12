@extends('layouts.app')

@section('title', 'Dashboard Warehouse')

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

    .kpi-container {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        flex: 1;
        background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
        transition: all 0.3s ease;
    }

    .kpi-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(62,92,116,0.18);
    }

    .kpi-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        margin-bottom: 1rem;
    }

    .kpi-value {
        font-size: 2rem;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 0.25rem;
    }

    .kpi-label {
        font-size: 0.9rem;
        color: rgba(62,92,116,0.7);
        font-weight: 500;
    }

    .chart-section {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .table-container {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .table thead th {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        font-weight: 600;
        padding: 1rem 0.75rem;
        border: none;
        text-align: center;
    }

    .table thead th:first-child { border-radius: 10px 0 0 0; }
    .table thead th:last-child { border-radius: 0 10px 0 0; }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(62,92,116,0.08);
        text-align: center;
    }

    .table tbody tr:hover {
        background: rgba(62,92,116,0.03);
    }

    .badge {
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        color: white;
    }

    .badge-success {
        background: linear-gradient(135deg, #198754 0%, #4caf50 100%);
        color: white;
    }

    @media (max-width: 992px) {
        .kpi-container { flex-wrap: wrap; }
        .kpi-card { flex: 1 1 calc(50% - 0.5rem); }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0;">
            <i class="bi bi-boxes me-2"></i>Warehouse - Gudang Konsentrat
        </h2>
        <span style="color: rgba(62,92,116,0.6);">{{ now()->format('l, d F Y') }}</span>
    </div>

    <!-- KPI Cards - Stock Real-time -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);">
                <i class="bi bi-inbox"></i>
            </div>
            <div class="kpi-value">{{ $stats['pending_receive'] }}</div>
            <div class="kpi-label">Pending Receive</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                <i class="bi bi-gem"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['zircon_stock'], 0) }}</div>
            <div class="kpi-label">Zircon Stock (kg)</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['ilmenite_stock'], 0) }}</div>
            <div class="kpi-label">Ilmenite Stock (kg)</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #27ae60 0%, #229954 100%);">
                <i class="bi bi-archive"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['monasit_stock'], 0) }}</div>
            <div class="kpi-label">Monasit Stock (kg)</div>
        </div>
    </div>

    <!-- Stock Composition Chart (3 Warna) -->
    @if($stockComposition->count() > 0)
    <div class="chart-section">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-pie-chart me-2"></i>Komposisi Stok Warehouse (Real-time)
        </h5>
        <canvas id="stockPieChart" height="80"></canvas>
    </div>
    @endif

    <!-- Pending Receive dari Dry Process -->
    @if($pendingReceive->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-exclamation-triangle me-2"></i>Batch Pending Receive (CP4) - Dari Dry Process
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Material</th>
                    <th>Berat (kg)</th>
                    <th>Kandungan (%)</th>
                    <th>Parent Batch</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingReceive as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>
                        <span class="badge" style="background: 
                            {{ $batch->productCode->material == 'ZIRCON' ? '#e74c3c' : 
                               ($batch->productCode->material == 'ILMENITE' ? '#9b59b6' : '#27ae60') }}; 
                            color: white;">
                            {{ $batch->productCode->material }}
                        </span>
                    </td>
                    <td>{{ number_format($batch->current_weight, 2) }}</td>
                    <td>{{ $batch->konsentrat_persen ? number_format($batch->konsentrat_persen, 2) . '%' : '-' }}</td>
                    <td>{{ $batch->parentBatch->batch_code ?? '-' }}</td>
                    <td>
                        <form action="{{ route('warehouse.receive', $batch) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Receive batch ini dan tambahkan ke stock warehouse?')">
                                <i class="bi bi-check-circle"></i> Receive & Add to Stock
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Split Batches - Siap Dispatch ke Lab -->
    @php
    $splitBatches = \App\Models\Batch::where('process_stage', 'warehouse')
        ->where('is_split', true)
        ->where('status', 'created')
        ->with('productCode', 'splitParent')
        ->orderBy('created_at', 'desc')
        ->get();
    @endphp

    @if($splitBatches->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-send me-2"></i>Split Batches - Siap Dispatch ke Lab
        </h5>
        <p style="color: rgba(62,92,116,0.7); margin-bottom: 1rem; font-size: 0.95rem;">
            Batch monasit yang sudah di-split dan siap dikirim ke Lab/Project Plan untuk analisis LTJ.
        </p>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Parent Batch</th>
                    <th>Material</th>
                    <th>Berat (kg)</th>
                    <th>Split Ratio</th>
                    <th>Created</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($splitBatches as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>{{ $batch->splitParent->batch_code ?? '-' }}</td>
                    <td>
                        <span class="badge" style="background: #9b59b6; color: white;">
                            {{ $batch->productCode->material ?? 'MON' }}
                        </span>
                    </td>
                    <td>{{ number_format($batch->current_weight, 2) }}</td>
                    <td>
                        <span class="badge badge-success">
                            {{ number_format(($batch->split_ratio ?? 0) * 100, 2) }}%
                        </span>
                    </td>
                    <td>{{ $batch->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <form action="{{ route('warehouse.dispatch-lab', $batch) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary" 
                                    onclick="return confirm('Dispatch batch {{ $batch->batch_code }} ke Lab/Project Plan?')">
                                <i class="bi bi-send"></i> Dispatch ke Lab
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Current Stock Management -->
    @if($stockedBatches->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-archive me-2"></i>Current Stock - Manajemen Konsentrat
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Material</th>
                    <th>Berat (kg)</th>
                    <th>Kandungan (%)</th>
                    <th>Received At</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockedBatches as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>
                        <span class="badge" style="background: 
                            {{ $batch->productCode->material == 'ZIRCON' ? '#e74c3c' : 
                               ($batch->productCode->material == 'ILMENITE' ? '#9b59b6' : '#27ae60') }}; 
                            color: white;">
                            {{ $batch->productCode->material }}
                        </span>
                    </td>
                    <td>{{ number_format($batch->current_weight, 2) }}</td>
                    <td>{{ $batch->konsentrat_persen ? number_format($batch->konsentrat_persen, 2) . '%' : '-' }}</td>
                    <td>{{ $batch->updated_at->format('d M Y H:i') }}</td>
                    <td>
                        @if(in_array($batch->productCode->material, ['ZIRCON', 'ILMENITE']))
                            <a href="{{ route('warehouse.export-form', $batch) }}" class="btn btn-sm btn-primary">
                                <i class="bi bi-box-arrow-right"></i> Export
                            </a>
                        @elseif($batch->productCode->material == 'MON')
                            <a href="{{ route('warehouse.split-lab-form', $batch) }}" class="btn btn-sm btn-warning">
                                <i class="bi bi-scissors"></i> Split untuk Lab
                            </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Recent Exports -->
    @if($recentExports->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-box-arrow-right me-2"></i>Export Terbaru
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Material</th>
                    <th>Berat (kg)</th>
                    <th>Tipe</th>
                    <th>Destination</th>
                    <th>Tanggal Export</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentExports as $export)
                <tr>
                    <td><strong>{{ $export->batch->batch_code }}</strong></td>
                    <td>{{ $export->batch->productCode->material ?? '-' }}</td>
                    <td>{{ number_format($export->weight_kg, 2) }}</td>
                    <td>
                        <span class="badge {{ $export->export_type == 'export' ? 'badge-primary' : 'badge-success' }}">
                            {{ strtoupper($export->export_type) }}
                        </span>
                    </td>
                    <td>{{ $export->destination }}</td>
                    <td>{{ $export->exported_at->format('d M Y H:i') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
@if($stockComposition->count() > 0)
const ctx = document.getElementById('stockPieChart').getContext('2d');
new Chart(ctx, {
    type: 'pie',
    data: {
        labels: @json($stockComposition->pluck('material')),
        datasets: [{
            data: @json($stockComposition->pluck('weight')),
            backgroundColor: ['#e74c3c', '#9b59b6', '#27ae60'], // Zircon (merah), Ilmenite (ungu), Monasit (hijau)
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
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value.toFixed(2) + ' kg (' + percentage + '%)';
                    }
                }
            }
        }
    }
});
@endif
</script>
@endpush
@endsection