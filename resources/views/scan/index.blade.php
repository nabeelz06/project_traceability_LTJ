@extends('layouts.app')

@section('title', 'Operator Dashboard')

@section('content')
<div class="container py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700; text-align: center;">
        <i class="bi bi-person-badge" style="color: var(--primary);"></i>
        Operator Dashboard
    </h1>

    <div class="row g-3 mb-4">
        <!-- Check-Out Button -->
        <div class="col-md-6">
            <a href="{{ route('scan.checkout') }}" class="scan-card" style="text-decoration: none; display: block;">
                <div class="card" style="border: 3px solid var(--primary); transition: all 0.3s;">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-box-arrow-right" style="font-size: 4rem; color: var(--primary);"></i>
                        <h3 class="mt-3" style="color: var(--primary);">Check-Out</h3>
                        <p class="text-muted mb-0">Scan & kirim batch</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Check-In Button -->
        <div class="col-md-6">
            <a href="{{ route('scan.checkin') }}" class="scan-card" style="text-decoration: none; display: block;">
                <div class="card" style="border: 3px solid var(--success); transition: all 0.3s;">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-box-arrow-in-down" style="font-size: 4rem; color: var(--success);"></i>
                        <h3 class="mt-3" style="color: var(--success);">Check-In</h3>
                        <p class="text-muted mb-0">Terima batch masuk</p>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Scan Hari Ini</h6>
                    <h2 class="mb-0" style="color: var(--primary);">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Check-Out</h6>
                    <h2 class="mb-0" style="color: var(--info);">0</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Check-In</h6>
                    <h2 class="mb-0" style="color: var(--success);">0</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru
        </div>
        <div class="card-body">
            <p class="text-muted text-center py-3">Belum ada aktivitas hari ini</p>
        </div>
    </div>
</div>

<style>
.scan-card:hover .card {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}
</style>
@endsection