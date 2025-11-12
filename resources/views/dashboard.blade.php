@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container py-5">
    <div class="card" style="max-width: 600px; margin: 0 auto;">
        <div class="card-body text-center py-5">
            <i class="bi bi-exclamation-triangle-fill" style="font-size: 5rem; color: var(--warning);"></i>
            
            <h2 class="mt-4 mb-3" style="color: var(--dark);">Dashboard Tidak Tersedia</h2>
            
            <p class="text-muted mb-4">
                Role Anda: <strong>{{ auth()->user()->getRoleLabel() }}</strong>
                <br>Dashboard untuk role ini belum dikonfigurasi atau terjadi kesalahan sistem.
                <br><br>
                Silakan hubungi administrator untuk bantuan.
            </p>

            <div class="alert alert-info text-start">
                <strong><i class="bi bi-info-circle me-2"></i>Informasi Akun:</strong>
                <ul class="mb-0 mt-2" style="list-style: none; padding-left: 0;">
                    <li><strong>Nama:</strong> {{ auth()->user()->name }}</li>
                    <li><strong>Email:</strong> {{ auth()->user()->email }}</li>
                    <li><strong>Role:</strong> {{ auth()->user()->getRoleLabel() }}</li>
                    @if(auth()->user()->partner)
                    <li><strong>Partner:</strong> {{ auth()->user()->partner->name }}</li>
                    @endif
                </ul>
            </div>

            <div style="display: flex; gap: 0.5rem; justify-content: center; margin-top: 2rem;">
                <a href="{{ route('traceability.search') }}" class="btn btn-primary">
                    <i class="bi bi-search me-2"></i>Pencarian Traceability
                </a>
                <a href="{{ route('batches.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-box-seam me-2"></i>Daftar Batch
                </a>
            </div>

            <div class="mt-4">
                <form action="{{ route('logout') }}" method="POST" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-box-arrow-right me-1"></i>Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection