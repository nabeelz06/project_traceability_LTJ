@extends('layouts.app')

@section('title', 'Operator Scan Dashboard')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --bg: #cfeeff;
        --card-radius: 14px;
        --glass: rgba(255,255,255,0.98);
        --accent: #0d6efd;
    }

    body {
        font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(180deg, var(--bg) 0%, #eaf9ff 100%);
        min-height: 100vh;
        color: #0b2545;
    }

    .scan-dashboard-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .header-section {
        margin-bottom: 2rem;
        animation: fadeInDown 0.6s ease;
    }

    .dashboard-title {
        font-family: 'Poppins', inherit;
        font-weight: 700;
        color: #0b2545;
        font-size: 1.85rem;
        margin: 0 0 0.5rem 0;
        letter-spacing: -0.5px;
    }

    .dashboard-subtitle {
        color: #6c757d;
        font-size: 1rem;
        margin: 0;
    }

    /* --- Modern Alerts --- */
    .alert-section {
        margin-bottom: 2rem;
    }

    .alert-modern {
        display: flex;
        align-items: flex-start;
        padding: 1rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        font-size: 0.95rem;
        line-height: 1.5;
        animation: slideIn 0.5s ease;
        font-weight: 500;
        border: 2px solid;
    }

    .alert-icon {
        margin-right: 0.75rem;
        flex-shrink: 0;
    }

    .alert-success-modern {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        border-color: rgba(5, 150, 105, 0.3);
        color: #065f46;
    }

    .alert-danger-modern {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        border-color: rgba(220, 53, 69, 0.3);
        color: #721c24;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    /* --- Grid Layout --- */
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    /* --- Modern Card Styling --- */
    .menu-card {
        display: flex;
        flex-direction: column;
        background: var(--glass);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border-radius: var(--card-radius);
        padding: 2rem;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 10px 28px rgba(11,37,69,0.08);
        border: 1px solid rgba(11,37,69,0.04);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        animation: fadeInUp 0.7s ease;
    }

    .menu-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, transparent, currentColor, transparent);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .menu-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(11,37,69,0.15);
        border-color: rgba(11,37,69,0.08);
    }

    .menu-card:hover::before {
        opacity: 1;
    }

    /* Card Icon Wrappers */
    .card-icon-wrapper {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        transition: transform 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .card-icon-wrapper::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        background: radial-gradient(circle, rgba(255,255,255,0.3) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .menu-card:hover .card-icon-wrapper {
        transform: scale(1.1) rotate(-5deg);
    }

    .menu-card:hover .card-icon-wrapper::before {
        opacity: 1;
    }

    /* Color Themes for Icons & Cards */
    .card-checkout .card-icon-wrapper {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #dc2626;
    }
    .card-checkout::before { color: #dc2626; }

    .card-checkin .card-icon-wrapper {
        background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
        color: #059669;
    }
    .card-checkin::before { color: #059669; }

    .card-tasks .card-icon-wrapper {
        background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
        color: #2563eb;
    }
    .card-tasks::before { color: #2563eb; }

    .card-history .card-icon-wrapper {
        background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
        color: #4b5563;
    }
    .card-history::before { color: #4b5563; }

    /* Card Content */
    .card-content {
        flex-grow: 1;
    }

    .card-content h2 {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        letter-spacing: 0.025em;
        font-family: 'Poppins', inherit;
    }

    .card-checkout h2 { color: #dc2626; }
    .card-checkin h2 { color: #059669; }
    .card-tasks h2 { color: #2563eb; }
    .card-history h2 { color: #4b5563; }

    .card-content p {
        color: #6b7280;
        font-size: 0.95rem;
        margin-bottom: 0;
        line-height: 1.5;
    }

    /* Card Action (Arrow at bottom) */
    .card-action {
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-weight: 600;
        font-size: 0.9rem;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(11,37,69,0.06);
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }

    .card-checkout .card-action { color: #dc2626; }
    .card-checkin .card-action { color: #059669; }
    .card-tasks .card-action { color: #2563eb; }
    .card-history .card-action { color: #4b5563; }

    .menu-card:hover .card-action {
        opacity: 1;
        transform: translateY(0);
    }

    .card-action svg {
        transition: transform 0.3s ease;
    }

    .menu-card:hover .card-action svg {
        transform: translateX(5px);
    }

    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .menu-grid {
            grid-template-columns: 1fr;
        }

        .dashboard-title {
            font-size: 1.5rem;
        }

        .scan-dashboard-container {
            padding: 1.5rem 1rem;
        }
    }
</style>

<div class="scan-dashboard-container">
    <div class="header-section">
        <h1 class="dashboard-title"><i class="bi bi-qr-code-scan me-2"></i>Dashboard Operator Scan</h1>
        <p class="dashboard-subtitle">Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong>.</p>
    </div>

    {{-- Bagian Alert / Notifikasi --}}
    <div class="alert-section">
        @if(session('success'))
            <div class="alert-modern alert-success-modern">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif
        
        @if($errors->any())
            <div class="alert-modern alert-danger-modern">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="alert-icon">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <div>
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    {{-- Grid Menu Utama --}}
    <div class="menu-grid">
        
        <!-- Card Check-Out -->
        <a href="{{ route('scan.checkout') }}" class="menu-card card-checkout">
            <div class="card-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="17 8 12 3 7 8"></polyline>
                    <line x1="12" y1="3" x2="12" y2="15"></line>
                </svg>
            </div>
            <div class="card-content">
                <h2>CHECK-OUT</h2>
                <p>Kirim Batch dari Gudang</p>
            </div>
            <div class="card-action">
                <span>Mulai Scan</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </div>
        </a>
        
        <!-- Card Check-In -->
        <a href="{{ route('scan.checkin') }}" class="menu-card card-checkin">
            <div class="card-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                    <polyline points="7 10 12 15 17 10"></polyline>
                    <line x1="12" y1="15" x2="12" y2="3"></line>
                </svg>
            </div>
            <div class="card-content">
                <h2>CHECK-IN</h2>
                <p>Terima Batch di Gudang</p>
            </div>
            <div class="card-action">
                <span>Mulai Scan</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </div>
        </a>

        <!-- Card Tasks -->
        <a href="{{ route('scan.tasks') }}" class="menu-card card-tasks">
            <div class="card-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path>
                    <rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect>
                    <path d="M9 14l2 2 4-4"></path>
                </svg>
            </div>
            <div class="card-content">
                <h2>Tugas</h2>
                <p>Lihat daftar tugas pengiriman</p>
            </div>
            <div class="card-action">
                <span>Lihat Detail</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </div>
        </a>

        <!-- Card History -->
        <a href="{{ route('scan.history') }}" class="menu-card card-history">
            <div class="card-icon-wrapper">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div class="card-content">
                <h2>History</h2>
                <p>Lihat riwayat scan Anda</p>
            </div>
            <div class="card-action">
                <span>Buka Riwayat</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                    <polyline points="12 5 19 12 12 19"></polyline>
                </svg>
            </div>
        </a>

    </div>
</div>

@endsection