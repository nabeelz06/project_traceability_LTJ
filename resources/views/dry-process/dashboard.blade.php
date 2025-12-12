@extends('layouts.app')

@section('title', 'Dashboard Dry Process')

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

    .badge {
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
        color: #000;
    }

    .badge-success {
        background: linear-gradient(135deg, #198754 0%, #4caf50 100%);
        color: white;
    }

    .modal-backdrop {
        background-color: rgba(62, 92, 116, 0.5);
    }

    @media (max-width: 992px) {
        .kpi-container { flex-wrap: wrap; }
        .kpi-card { flex: 1 1 calc(50% - 0.5rem); }
    }

    @media (max-width: 576px) {
        .kpi-card { flex: 1 1 100%; }
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0;">
            <i class="bi bi-gear-wide-connected me-2"></i>Dry Process - Mineral Separation
        </h2>
        <span style="color: rgba(62,92,116,0.6);">{{ now()->format('l, d F Y') }}</span>
    </div>

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
            <div class="kpi-icon" style="background: linear-gradient(135deg, #17a2b8 0%, #6eb5c0 100%);">
                <i class="bi bi-archive"></i>
            </div>
            <div class="kpi-value">{{ $stats['in_stock'] }}</div>
            <div class="kpi-label">Stockpile (batches)</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #6f42c1 0%, #9b59b6 100%);">
                <i class="bi bi-gear-wide-connected"></i>
            </div>
            <div class="kpi-value">{{ $stats['in_processing'] }}</div>
            <div class="kpi-label">In Processing</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #28a745 0%, #4caf50 100%);">
                <i class="bi bi-check-circle"></i>
            </div>
            <div class="kpi-value">{{ $stats['processed_ready'] ?? 0 }}</div>
            <div class="kpi-label">Ready to Dispatch</div>
        </div>
    </div>

    <!-- Pending Receive dengan Modal Pilihan -->
    @if(isset($pendingReceive) && $pendingReceive->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-exclamation-triangle me-2"></i>Batch Pending Receive (CP2)
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Product</th>
                    <th>Berat (kg)</th>
                    <th>Asal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pendingReceive as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>{{ $batch->productCode->description ?? '-' }}</td>
                    <td>{{ number_format($batch->initial_weight, 0) }}</td>
                    <td>{{ $batch->origin_location }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#receiveModal{{ $batch->id }}">
                            <i class="bi bi-check-circle"></i> Receive (CP2)
                        </button>
                    </td>
                </tr>

                <!-- Modal Pilihan -->
                <div class="modal fade" id="receiveModal{{ $batch->id }}" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header" style="background: var(--primary); color: white;">
                                <h5 class="modal-title">Receive Batch: {{ $batch->batch_code }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <form action="{{ route('dry-process.receive', $batch) }}" method="POST">
                                @csrf
                                <div class="modal-body">
                                    <div class="mb-3">
                                        <label class="form-label"><strong>Pilih Aksi:</strong></label>
                                        <select name="action" class="form-select" required onchange="toggleLocationField(this, '{{ $batch->id }}')">
                                            <option value="">-- Pilih --</option>
                                            <option value="stock">Stock ke Gudang Sementara</option>
                                            <option value="process">Langsung Proses</option>
                                        </select>
                                    </div>

                                    <div class="mb-3" id="locationField{{ $batch->id }}" style="display: none;">
                                        <label class="form-label">Lokasi Stockpile:</label>
                                        <input type="text" name="location" class="form-control" placeholder="Contoh: Gudang A - Rak 1">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Catatan (Opsional):</label>
                                        <textarea name="notes" class="form-control" rows="2"></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <button type="submit" class="btn btn-success">Konfirmasi Receive</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Stockpile Management -->
    @if(isset($stockBatches) && $stockBatches->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-archive me-2"></i>Stockpile Management
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Product</th>
                    <th>Berat (kg)</th>
                    <th>Lokasi Stockpile</th>
                    <th>Stocked At</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($stockBatches as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>{{ $batch->productCode->description ?? '-' }}</td>
                    <td>{{ number_format($batch->current_weight, 0) }}</td>
                    <td>{{ $batch->stockpile_location ?? '-' }}</td>
                    <td>
                        @if($batch->stocked_at)
                            @php
                                try {
                                    $stockedDate = is_string($batch->stocked_at) ? \Carbon\Carbon::parse($batch->stocked_at) : $batch->stocked_at;
                                    echo $stockedDate->format('d M Y H:i');
                                } catch (\Exception $e) {
                                    echo $batch->stocked_at;
                                }
                            @endphp
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <form action="{{ route('dry-process.retrieve', $batch) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Ambil batch ini untuk diproses?')">
                                <i class="bi bi-arrow-up-circle"></i> Retrieve & Process
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Batches In Processing -->
    @if(isset($processingBatches) && $processingBatches->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-gear-wide-connected me-2"></i>Batch Sedang Diproses
        </h5>
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Product</th>
                    <th>Berat (kg)</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($processingBatches as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>{{ $batch->productCode->description ?? '-' }}</td>
                    <td>{{ number_format($batch->current_weight, 0) }}</td>
                    <td><span class="badge badge-warning">PROCESSING</span></td>
                    <td>
                        <a href="{{ route('dry-process.process-form', $batch) }}" class="btn btn-sm btn-primary">
                            <i class="bi bi-clipboard-check"></i> Selesai Proses & Input Kandungan
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <!-- Processed Batches Ready to Dispatch (NEW SECTION) -->
    @if(isset($processedBatches) && $processedBatches->count() > 0)
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-box-arrow-right me-2"></i>Batch Siap Dispatch ke Warehouse
        </h5>
        <p style="color: rgba(62,92,116,0.7); margin-bottom: 1rem;">
            Konsentrat hasil proses yang siap dikirim ke warehouse untuk disimpan atau diekspor.
        </p>
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
                @foreach($processedBatches as $batch)
                <tr>
                    <td><strong>{{ $batch->batch_code }}</strong></td>
                    <td>
                        <span class="badge" style="background: 
                            {{ $batch->productCode->material == 'ZIRCON' ? '#e74c3c' : 
                               ($batch->productCode->material == 'ILMENITE' ? '#9b59b6' : '#27ae60') }}; 
                            color: white;">
                            {{ $batch->productCode->material ?? '-' }}
                        </span>
                    </td>
                    <td>{{ number_format($batch->current_weight, 2) }}</td>
                    <td>{{ number_format($batch->konsentrat_persen, 2) }}%</td>
                    <td>{{ $batch->parentBatch->batch_code ?? '-' }}</td>
                    <td>
                        <form action="{{ route('dry-process.dispatch-warehouse', $batch) }}" method="POST" style="display: inline;">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Dispatch batch {{ $batch->batch_code }} ke Warehouse?')">
                                <i class="bi bi-send"></i> Dispatch (CP3)
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

<script>
function toggleLocationField(select, batchId) {
    const locationField = document.getElementById('locationField' + batchId);
    if (select.value === 'stock') {
        locationField.style.display = 'block';
        locationField.querySelector('input').required = true;
    } else {
        locationField.style.display = 'none';
        locationField.querySelector('input').required = false;
    }
}
</script>
@endsection