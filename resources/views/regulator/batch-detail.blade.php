@extends('layouts.app')

@section('title', 'Detail Batch - ' . $batch->batch_code)

@section('content')
<style>
    :root {
        --primary: #3e5c74;
        --gold: #c5a572;
    }

    .detail-card {
        background: white;
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 4px 12px rgba(62,92,116,0.1);
    }

    .detail-title {
        color: var(--primary);
        font-weight: 700;
        font-size: 1.2rem;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(62,92,116,0.08);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        font-weight: 600;
        color: rgba(62,92,116,0.7);
    }

    .info-value {
        font-weight: 700;
        color: var(--primary);
    }

    .badge {
        padding: 0.35rem 0.7rem;
        border-radius: 6px;
        font-weight: 600;
        font-size: 0.75rem;
    }
</style>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="color: var(--primary); font-weight: 700;">
            <i class="bi bi-box-seam me-2"></i>Detail Batch
        </h2>
        <a href="{{ route('regulator.dashboard') }}" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left"></i> Kembali
        </a>
    </div>

    <!-- Batch Info -->
    <div class="detail-card">
        <h5 class="detail-title">
            <i class="bi bi-info-circle"></i>
            Informasi Batch
        </h5>
        
        <div class="info-row">
            <span class="info-label">Batch Code</span>
            <span class="info-value">{{ $batch->batch_code }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Material</span>
            <span class="badge" style="background: 
                {{ $batch->productCode->material == 'ZIRCON' ? '#e74c3c' : 
                   ($batch->productCode->material == 'ILMENITE' ? '#9b59b6' : '#27ae60') }}; 
                color: white;">
                {{ $batch->productCode->material ?? '-' }}
            </span>
        </div>

        <div class="info-row">
            <span class="info-label">Status</span>
            <span class="badge badge-{{ $batch->status == 'completed' ? 'success' : 'primary' }}">
                {{ strtoupper($batch->status) }}
            </span>
        </div>

        <div class="info-row">
            <span class="info-label">Process Stage</span>
            <span class="info-value">{{ strtoupper($batch->process_stage ?? '-') }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Initial Weight</span>
            <span class="info-value">{{ number_format($batch->initial_weight, 2) }} kg</span>
        </div>

        <div class="info-row">
            <span class="info-label">Current Weight</span>
            <span class="info-value">{{ number_format($batch->current_weight, 2) }} kg</span>
        </div>

        <div class="info-row">
            <span class="info-label">Location</span>
            <span class="info-value">{{ $batch->current_location }}</span>
        </div>

        <div class="info-row">
            <span class="info-label">Created At</span>
            <span class="info-value">{{ $batch->created_at->format('d M Y H:i') }}</span>
        </div>
    </div>

    <!-- Lab Analysis (if exists) -->
    @if($batch->labAnalyses->count() > 0)
    <div class="detail-card">
        <h5 class="detail-title">
            <i class="bi bi-microscope"></i>
            Analisis Lab
        </h5>
        
        @foreach($batch->labAnalyses as $analysis)
        <div class="info-row">
            <span class="info-label">Neodymium (Nd)</span>
            <span class="info-value">{{ number_format($analysis->nd_content, 2) }}%</span>
        </div>
        <div class="info-row">
            <span class="info-label">Lanthanum (La)</span>
            <span class="info-value">{{ number_format($analysis->la_content, 2) }}%</span>
        </div>
        <div class="info-row">
            <span class="info-label">Cerium (Ce)</span>
            <span class="info-value">{{ number_format($analysis->ce_content, 2) }}%</span>
        </div>
        <div class="info-row">
            <span class="info-label">Yttrium (Y)</span>
            <span class="info-value">{{ number_format($analysis->y_content, 2) }}%</span>
        </div>
        <div class="info-row">
            <span class="info-label">Praseodymium (Pr)</span>
            <span class="info-value">{{ number_format($analysis->pr_content, 2) }}%</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total Recovery</span>
            <span class="info-value" style="color: var(--gold);">{{ number_format($analysis->total_recovery, 2) }}%</span>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Export Info (if exists) -->
    @if($batch->exportLogs->count() > 0)
    <div class="detail-card">
        <h5 class="detail-title">
            <i class="bi bi-box-arrow-right"></i>
            Export History
        </h5>
        
        @foreach($batch->exportLogs as $export)
        <div class="info-row">
            <span class="info-label">Destination</span>
            <span class="info-value">{{ $export->destination }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Export Type</span>
            <span class="badge badge-{{ $export->export_type == 'export' ? 'primary' : 'success' }}">
                {{ strtoupper($export->export_type) }}
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Weight Exported</span>
            <span class="info-value">{{ number_format($export->weight_kg, 2) }} kg</span>
        </div>
        <div class="info-row">
            <span class="info-label">Exported At</span>
            <span class="info-value">{{ $export->exported_at->format('d M Y H:i') }}</span>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endsection