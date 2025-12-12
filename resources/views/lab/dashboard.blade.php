@extends('layouts.app')

@section('title', 'Dashboard Lab/Project Plan')

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

    .debug-box {
        background: #fff3cd;
        border: 2px solid #ffc107;
        border-radius: 8px;
        padding: 1rem;
        margin-bottom: 1.5rem;
        font-size: 0.9rem;
    }

    @media (max-width: 992px) {
        .kpi-container { flex-wrap: wrap; }
        .kpi-card { flex: 1 1 calc(50% - 0.5rem); }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0;">
            <i class="bi bi-microscope me-2"></i>Lab/Project Plan - Analisis LTJ
        </h2>
        <span style="color: rgba(62,92,116,0.6);">{{ now()->format('l, d F Y') }}</span>
    </div>

    <!-- DEBUG MODE (HAPUS SETELAH TESTING) -->
    @php
    $debugMode = true; // Set false setelah berhasil
    if ($debugMode) {
        $allSplitBatches = \App\Models\Batch::where('is_split', true)->with('productCode')->get();
        echo "<div class='debug-box'>";
        echo "<strong>🔍 DEBUG MODE:</strong><br>";
        echo "Total split batches: " . $allSplitBatches->count() . "<br>";
        foreach ($allSplitBatches as $b) {
            echo "- {$b->batch_code}: status={$b->status}, process_stage={$b->process_stage}, ";
            echo "material=" . ($b->productCode ? $b->productCode->material : 'NULL') . "<br>";
        }
        echo "</div>";
    }
    @endphp

    <!-- KPI Cards -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);">
                <i class="bi bi-inbox"></i>
            </div>
            <div class="kpi-value">{{ $stats['pending_receive'] }}</div>
            <div class="kpi-label">Pending Receive</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);">
                <i class="bi bi-clipboard-data"></i>
            </div>
            <div class="kpi-value">{{ $stats['pending_analysis'] }}</div>
            <div class="kpi-label">Pending Analysis</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #28a745 0%, #4caf50 100%);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="kpi-value">{{ $stats['completed_analysis'] }}</div>
            <div class="kpi-label">Completed</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #6eb5c0 100%);">
                <i class="bi bi-graph-up"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['avg_recovery'], 1) }}</div>
            <div class="kpi-label">Avg Recovery (%)</div>
        </div>
    </div>

    <!-- Pending Receive -->
    @if($pendingReceive->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-exclamation-triangle me-2"></i>Batch Pending Receive (CP5)
        </h5>
        <p style="color: rgba(62,92,116,0.7); margin-bottom: 1rem; font-size: 0.95rem;">
            Sample monasit dari warehouse yang menunggu konfirmasi penerimaan.
        </p>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Parent Batch</th>
                    <th>Berat (kg)</th>
                    <th>Location</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingReceive as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>{{ $batch->splitParent->batch_code ?? '-' }}</td>
                    <td>{{ number_format($batch->current_weight, 2) }}</td>
                    <td>
                        <span class="badge badge-warning">{{ $batch->current_location }}</span>
                    </td>
                    <td>
                        <form action="{{ route('lab.receive', $batch) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Konfirmasi penerimaan batch {{ $batch->batch_code }}?')">
                                <i class="bi bi-check-circle"></i> Receive (CP5)
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-exclamation-triangle me-2"></i>Batch Pending Receive (CP5)
        </h5>
        <div style="text-align: center; padding: 3rem; color: rgba(62,92,116,0.5);">
            <i class="bi bi-inbox" style="font-size: 4rem; display: block; margin-bottom: 1rem;"></i>
            <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">Belum ada sample yang dikirim dari Warehouse</p>
            <small>Sample monasit akan muncul di sini setelah warehouse melakukan dispatch</small>
        </div>
    </div>
    @endif

    <!-- Pending Analysis -->
    @if($pendingAnalysis->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-clipboard-data me-2"></i>Batch Pending Analysis
        </h5>
        <p style="color: rgba(62,92,116,0.7); margin-bottom: 1rem; font-size: 0.95rem;">
            Sample yang sudah diterima dan menunggu analisis kandungan LTJ.
        </p>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Product</th>
                    <th>Berat (kg)</th>
                    <th>Diterima</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingAnalysis as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>{{ $batch->productCode->description ?? '-' }}</td>
                    <td>{{ number_format($batch->current_weight, 2) }}</td>
                    <td>{{ $batch->updated_at->format('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('lab.analysis-form', $batch) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-microscope"></i> Analisis Kandungan LTJ
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-clipboard-data me-2"></i>Batch Pending Analysis
        </h5>
        <div style="text-align: center; padding: 3rem; color: rgba(62,92,116,0.5);">
            <i class="bi bi-clipboard-data" style="font-size: 4rem; display: block; margin-bottom: 1rem;"></i>
            <p style="font-size: 1.1rem; margin-bottom: 0.5rem;">Belum ada sample yang siap dianalisis</p>
            <small>Receive sample dari section di atas untuk memulai analisis</small>
        </div>
    </div>
    @endif

    <!-- Recent Analysis -->
    @if($recentAnalysis->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-clock-history me-2"></i>Hasil Analisis Terbaru
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Nd (%)</th>
                    <th>La (%)</th>
                    <th>Ce (%)</th>
                    <th>Y (%)</th>
                    <th>Pr (%)</th>
                    <th>Recovery (%)</th>
                    <th>Tanggal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentAnalysis as $analysis)
                <tr>
                    <td><strong>{{ $analysis->batch->batch_code }}</strong></td>
                    <td>{{ number_format($analysis->nd_content, 2) }}</td>
                    <td>{{ number_format($analysis->la_content, 2) }}</td>
                    <td>{{ number_format($analysis->ce_content, 2) }}</td>
                    <td>{{ number_format($analysis->y_content, 2) }}</td>
                    <td>{{ number_format($analysis->pr_content, 2) }}</td>
                    <td><strong style="color: var(--primary);">{{ number_format($analysis->total_recovery, 2) }}</strong></td>
                    <td>{{ $analysis->analyzed_at->format('d M Y') }}</td>
                    <td>
                        <a href="{{ route('lab.view-analysis', $analysis->batch) }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-eye"></i> View
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-clock-history me-2"></i>Hasil Analisis Terbaru
        </h5>
        <div style="text-align: center; padding: 3rem; color: rgba(62,92,116,0.5);">
            <i class="bi bi-inbox" style="font-size: 4rem; display: block; margin-bottom: 1rem;"></i>
            <p style="font-size: 1.1rem;">Belum ada analisis yang dikerjakan</p>
        </div>
    </div>
    @endif
</div>
@endsection