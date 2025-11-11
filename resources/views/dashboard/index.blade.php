@extends('layouts.app')

@section('title', 'Global Dashboard - Course System')

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

    .page-header {
        margin-bottom: 1.5rem;
        animation: fadeInDown 0.6s ease;
    }

    .page-header h2 {
        font-family: 'Poppins', inherit;
        font-weight: 700;
        color: #0b2545;
        font-size: 1.85rem;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .kpi-card {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        padding: 1.75rem;
        border-radius: var(--card-radius);
        color: white;
        box-shadow: 0 10px 28px rgba(13,110,253,0.25);
        transition: all 0.3s ease;
        animation: fadeInUp 0.7s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        animation: pulse 3s ease-in-out infinite;
    }

    @keyframes pulse {
        0%, 100% { transform: scale(1); opacity: 0.5; }
        50% { transform: scale(1.1); opacity: 0.8; }
    }

    .kpi-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 15px 40px rgba(13,110,253,0.35);
    }

    .kpi-card.info {
        background: linear-gradient(135deg, #0dcaf0 0%, #7be6ff 100%);
        box-shadow: 0 10px 28px rgba(13,202,240,0.25);
    }

    .kpi-card.warning {
        background: linear-gradient(135deg, #ffc107 0%, #ffd86b 100%);
        box-shadow: 0 10px 28px rgba(255,193,7,0.25);
    }

    .kpi-card.success {
        background: linear-gradient(135deg, #198754 0%, #4bd08f 100%);
        box-shadow: 0 10px 28px rgba(25,135,84,0.25);
    }

    .kpi-label {
        font-size: 0.95rem;
        font-weight: 600;
        opacity: 0.95;
        margin-bottom: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        position: relative;
        z-index: 1;
    }

    .kpi-value {
        font-size: 2.5rem;
        font-weight: 700;
        letter-spacing: -1px;
        position: relative;
        z-index: 1;
    }

    .card {
        border-radius: var(--card-radius);
        background: var(--glass);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        box-shadow: 0 10px 28px rgba(11,37,69,0.08);
        border: 1px solid rgba(11,37,69,0.04);
        margin-bottom: 1.5rem;
        animation: fadeInUp 0.7s ease;
    }

    .card-header {
        padding: 1.25rem 1.5rem;
        background: linear-gradient(135deg, rgba(13,110,253,0.08) 0%, rgba(58,160,255,0.08) 100%);
        border-bottom: 2px solid rgba(13,110,253,0.1);
        font-weight: 700;
        color: #0b2545;
        font-size: 1.1rem;
        border-radius: var(--card-radius) var(--card-radius) 0 0;
    }

    .card-body {
        padding: 1.5rem;
    }

    .alert {
        padding: 1rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        border: 2px solid;
        font-weight: 500;
        animation: slideIn 0.5s ease;
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

    .alert-warning {
        background: linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%);
        border-color: rgba(255,193,7,0.3);
        color: #856404;
    }

    .alert-danger {
        background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
        border-color: rgba(220,53,69,0.3);
        color: #721c24;
    }

    .alert-info {
        background: linear-gradient(135deg, #e1f5fe 0%, #b3e5fc 100%);
        border-color: rgba(13,202,240,0.3);
        color: #0c5460;
    }

    .chart-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
    }

    .table {
        margin-bottom: 0;
        font-family: 'Poppins', inherit;
    }

    .table thead th {
        background: linear-gradient(90deg, #0b6edc 0%, #0d6efd 100%);
        color: #fff;
        font-weight: 700;
        padding: 0.9rem 1rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        padding: 0.9rem 1rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(11,37,69,0.06);
        color: #0b2545;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background: rgba(13,110,253,0.05);
        transform: translateX(3px);
    }

    .badge {
        padding: 0.35rem 0.7rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #7be6ff 100%);
        color: white;
    }

    .badge-default {
        background: linear-gradient(135deg, #6c757d 0%, #8a9099 100%);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: rgba(11,37,69,0.4);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
    }

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

    @media (max-width: 768px) {
        .chart-grid {
            grid-template-columns: 1fr;
        }

        .kpi-grid {
            grid-template-columns: 1fr;
        }
    }

    .link-primary {
        color: #0d6efd;
        font-weight: 600;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .link-primary:hover {
        color: #0b5ed7;
        text-decoration: underline;
    }
</style>

<div class="container py-4">
    <div class="page-header">
        <h2><i class="bi bi-speedometer2 me-2"></i>Global Dashboard</h2>
    </div>
    
    {{-- KPI Cards --}}
    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="kpi-label"><i class="bi bi-box-seam me-2"></i>Total Active Batches</div>
            <div class="kpi-value">{{ $totalBatchesActive }}</div>
        </div>
        
        <div class="kpi-card info">
            <div class="kpi-label"><i class="bi bi-truck me-2"></i>In Transit</div>
            <div class="kpi-value">{{ $batchesInTransit }}</div>
        </div>
        
        <div class="kpi-card warning">
            <div class="kpi-label"><i class="bi bi-hourglass-split me-2"></i>Processed</div>
            <div class="kpi-value">{{ $batchesProcessed }}</div>
        </div>
        
        <div class="kpi-card success">
            <div class="kpi-label"><i class="bi bi-check-circle me-2"></i>Delivered</div>
            <div class="kpi-value">{{ $batchesDelivered }}</div>
        </div>
    </div>
    
    {{-- Alerts --}}
    @if(isset($alerts) && count($alerts) > 0)
    <div class="card">
        <div class="card-header">
            <i class="bi bi-bell me-2"></i>Important Notifications
        </div>
        <div class="card-body">
            @foreach($alerts as $alert)
            <div class="alert alert-{{ $alert['type'] }}">
                <i class="bi bi-{{ $alert['type'] == 'danger' ? 'exclamation-triangle' : ($alert['type'] == 'warning' ? 'exclamation-circle' : 'info-circle') }} me-2"></i>
                {{ $alert['message'] }}
                @if(isset($alert['link']))
                    <a href="{{ $alert['link'] }}" class="link-primary ms-3">
                        View Details <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif
    
    {{-- Charts Row --}}
    <div class="chart-grid">
        {{-- Volume Chart --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-bar-chart me-2"></i>Batch Volume (Last 7 Days)
            </div>
            <div class="card-body">
                <canvas id="volumeChart" height="250"></canvas>
            </div>
        </div>
        
        {{-- Partner Performance --}}
        <div class="card">
            <div class="card-header">
                <i class="bi bi-trophy me-2"></i>Partner Performance (Top 5)
            </div>
            <div class="card-body">
                @if($partnerPerformance->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Partner Name</th>
                                <th>Type</th>
                                <th>Completed</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partnerPerformance as $partner)
                            <tr>
                                <td><strong>{{ $partner->name }}</strong></td>
                                <td><span class="badge badge-info">{{ ucfirst($partner->type) }}</span></td>
                                <td><strong style="color: #0d6efd; font-size: 1.1rem;">{{ $partner->batches_count }}</strong></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="empty-state">
                    <i class="bi bi-inbox"></i>
                    <h6>No partner data available</h6>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    {{-- Recent Activities --}}
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Recent Activities
        </div>
        <div class="card-body">
            @if($recentActivities->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Batch</th>
                            <th>Action</th>
                            <th>Actor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentActivities as $log)
                        <tr>
                            <td><small><strong>{{ $log->created_at->format('d/m/Y H:i') }}</strong></small></td>
                            <td>
                                <a href="{{ route('batches.show', $log->batch_id) }}" class="link-primary">
                                    <i class="bi bi-box me-1"></i>
                                    {{ $log->batch->batch_code ?? 'N/A' }}
                                </a>
                            </td>
                            <td>{{ $log->action }}</td>
                            <td><strong>{{ $log->actor->name ?? 'N/A' }}</strong></td>
                            <td>
                                @if($log->new_status)
                                    <span class="badge badge-default">
                                        {{ $log->new_status }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-state">
                <i class="bi bi-inbox"></i>
                <h6>No recent activities</h6>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
// Volume Chart
const volumeCtx = document.getElementById('volumeChart').getContext('2d');
const volumeData = @json($volumeChart);

new Chart(volumeCtx, {
    type: 'bar',
    data: {
        labels: volumeData.map(d => {
            const date = new Date(d.date);
            return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' });
        }),
        datasets: [{
            label: 'Batch Count',
            data: volumeData.map(d => d.total),
            backgroundColor: 'rgba(13, 110, 253, 0.8)',
            borderColor: 'rgba(13, 110, 253, 1)',
            borderWidth: 2,
            borderRadius: 8,
            hoverBackgroundColor: 'rgba(13, 110, 253, 1)'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: 'rgba(11, 37, 69, 0.95)',
                titleFont: {
                    family: 'Poppins',
                    size: 14,
                    weight: 'bold'
                },
                bodyFont: {
                    family: 'Poppins',
                    size: 13
                },
                padding: 12,
                borderRadius: 8,
                displayColors: false
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    font: {
                        family: 'Poppins',
                        size: 11,
                        weight: '600'
                    },
                    color: 'rgba(11, 37, 69, 0.7)'
                }
            },
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1,
                    font: {
                        family: 'Poppins',
                        size: 11,
                        weight: '600'
                    },
                    color: 'rgba(11, 37, 69, 0.7)'
                },
                grid: {
                    color: 'rgba(11, 37, 69, 0.05)',
                    drawBorder: false
                }
            }
        },
        animation: {
            duration: 1000,
            easing: 'easeInOutQuart'
        }
    }
});
</script>
@endpush
@endsection