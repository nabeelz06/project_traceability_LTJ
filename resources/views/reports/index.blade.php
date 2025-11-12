@extends('layouts.app')

@section('title', 'Laporan & Analitik')

@section('content')
<div class="container py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-file-earmark-bar-graph" style="color: var(--primary);"></i>
        Laporan & Analitik
    </h1>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Batch</h6>
                    <h2 class="mb-0" style="color: var(--primary);">{{ $stats['total_batches'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Batch Aktif</h6>
                    <h2 class="mb-0" style="color: var(--info);">{{ $stats['active_batches'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Mitra</h6>
                    <h2 class="mb-0" style="color: var(--success);">{{ $stats['total_partners'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Log</h6>
                    <h2 class="mb-0" style="color: var(--warning);">{{ $stats['total_logs'] }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Types -->
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-graph-up me-2"></i>Laporan Operasional
                </div>
                <div class="card-body">
                    <p class="text-muted">Laporan aktivitas batch, pengiriman, dan penerimaan</p>
                    <a href="{{ route('reports.operational') }}" class="btn btn-primary">
                        <i class="bi bi-file-earmark-text me-1"></i>Lihat Laporan
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-journal-check me-2"></i>Log Audit Batch
                </div>
                <div class="card-body">
                    <p class="text-muted">Riwayat lengkap semua aktivitas batch</p>
                    <a href="{{ route('admin.logs.batch') }}" class="btn btn-primary">
                        <i class="bi bi-list-check me-1"></i>Lihat Log
                    </a>
                </div>
            </div>
        </div>

        @if(auth()->user()->isSuperAdmin() || auth()->user()->isGovernment())
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-shield-check me-2"></i>Log Sistem
                </div>
                <div class="card-body">
                    <p class="text-muted">Log keamanan dan aktivitas sistem</p>
                    <a href="{{ route('admin.logs.system') }}" class="btn btn-primary">
                        <i class="bi bi-database me-1"></i>Lihat Log
                    </a>
                </div>
            </div>
        </div>
        @endif

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-download me-2"></i>Export Data
                </div>
                <div class="card-body">
                    <p class="text-muted">Download laporan dalam format Excel atau PDF</p>
                    <button class="btn btn-outline-primary" onclick="alert('Coming soon')">
                        <i class="bi bi-file-excel me-1"></i>Export Excel
                    </button>
                    <button class="btn btn-outline-danger ms-2" onclick="alert('Coming soon')">
                        <i class="bi bi-file-pdf me-1"></i>Export PDF
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection