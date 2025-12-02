@extends('layouts.app')

@section('title', 'Dashboard Admin - Traceability LTJ PT Timah')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --primary: #3e5c74;
        --primary-dark: #2d4454;
        --primary-light: #e8eef3;
        --gold: #c5a572;
    }

    body {
        font-family: 'Poppins', system-ui, sans-serif;
        background: linear-gradient(135deg, var(--primary-light) 0%, #f5f8fa 100%);
        color: #2d4454;
    }

    /* KPI Cards - 4 Kolom Horizontal */
    .kpi-container {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
        width: 100%;
    }

    .kpi-card {
        flex: 1;
        min-width: 0;
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

    /* Chart Section */
    .chart-section {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
    }

    .chart-section h5 {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Table Styles */
    .table-container {
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        border-radius: 14px;
        padding: 1.75rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
        overflow-x: auto;
    }

    .table {
        width: 100%;
        margin-bottom: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 1rem 0.75rem;
        border: none;
        text-align: center;
        white-space: nowrap;
    }

    .table thead th:first-child {
        border-radius: 10px 0 0 0;
    }

    .table thead th:last-child {
        border-radius: 0 10px 0 0;
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(62,92,116,0.08);
        font-size: 0.9rem;
        text-align: center;
    }

    .table tbody tr:hover {
        background: rgba(62,92,116,0.03);
    }

    /* Badge Styles */
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

    .badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
        color: #000;
    }

    .badge-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #6eb5c0 100%);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #858796 100%);
        color: white;
    }

    /* Unsur LTJ Badges */
    .unsur-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        margin: 0.15rem;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .unsur-nd {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .unsur-y {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
    }

    .unsur-ce {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        color: white;
    }

    .unsur-la {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        color: white;
    }

    .unsur-pr {
        background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        color: #000;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .kpi-container {
            flex-wrap: wrap;
        }
        
        .kpi-card {
            flex: 1 1 calc(50% - 0.5rem);
        }
    }

    @media (max-width: 576px) {
        .kpi-card {
            flex: 1 1 100%;
        }
    }
</style>

<div class="container-fluid py-4">
    <!-- Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 style="font-weight: 700; color: var(--primary); margin: 0;">
            <i class="bi bi-speedometer2 me-2"></i>Dashboard Admin
        </h2>
        <span style="color: rgba(62,92,116,0.6);">{{ now()->format('l, d F Y') }}</span>
    </div>

    <!-- Alerts -->
    @if(count($alerts) > 0)
        @foreach($alerts as $alert)
        <div class="alert alert-{{ $alert['type'] }} alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ $alert['message'] }}
            @if(isset($alert['link']))
                <a href="{{ $alert['link'] }}" class="alert-link ms-2">Lihat detail</a>
            @endif
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endforeach
    @endif

    <!-- KPI Cards - 4 Kolom Horizontal -->
    <div class="kpi-container">
        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);">
                <i class="bi bi-box-seam"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['total_batches']) }}</div>
            <div class="kpi-label">Total Batch Aktif</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #0dcaf0 0%, #6eb5c0 100%);">
                <i class="bi bi-truck"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['batches_in_transit']) }}</div>
            <div class="kpi-label">Dalam Pengiriman</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);">
                <i class="bi bi-gear-fill"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['active_batches']) }}</div>
            <div class="kpi-label">Diproses</div>
        </div>

        <div class="kpi-card">
            <div class="kpi-icon" style="background: linear-gradient(135deg, #198754 0%, #4caf50 100%);">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <div class="kpi-value">{{ number_format($stats['batches_delivered']) }}</div>
            <div class="kpi-label">Terkirim</div>
        </div>
    </div>

    <!-- Volume Batch Chart -->
    <div class="chart-section">
        <h5>
            <i class="bi bi-bar-chart-line me-2"></i>
            Volume Batch (7 Hari Terakhir)
        </h5>
        <canvas id="volumeChart" height="80"></canvas>
    </div>

    <!-- Aktivitas Terbaru dengan Kolom 5 Unsur LTJ -->
    <div class="table-container">
        <h5 style="color: var(--primary); font-weight: 700; margin-bottom: 1.25rem;">
            <i class="bi bi-clock-history me-2"></i>
            Aktivitas Terbaru
        </h5>

        <table class="table">
            <thead>
                <tr>
                    <th>Batch</th>
                    <th>Time Stamp</th>
                    <th>Aktivitas</th>
                    <th>User</th>
                    <th>Tonase (ton)</th>
                    <th>Konsentrat (%)</th>
                    <th colspan="5" style="background: linear-gradient(135deg, var(--gold) 0%, #a58960 100%);">Kandungan Unsur LTJ (%)</th>
                    <th>Massa LTJ (Kg)</th>
                    <th>Keterangan</th>
                </tr>
                <tr style="background: linear-gradient(135deg, rgba(197,165,114,0.2) 0%, rgba(197,165,114,0.1) 100%);">
                    <th colspan="6"></th>
                    <th style="font-size: 0.75rem; padding: 0.5rem;">Nd</th>
                    <th style="font-size: 0.75rem; padding: 0.5rem;">Y</th>
                    <th style="font-size: 0.75rem; padding: 0.5rem;">Ce</th>
                    <th style="font-size: 0.75rem; padding: 0.5rem;">La</th>
                    <th style="font-size: 0.75rem; padding: 0.5rem;">Pr</th>
                    <th colspan="2"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentActivities as $activity)
                <tr>
                    <td>
                        @if($activity['batch_id'])
                            <a href="{{ route('batches.show', $activity['batch_id']) }}" 
                               style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                {{ $activity['batch_code'] }}
                            </a>
                        @else
                            <span style="color: #6c757d;">{{ $activity['batch_code'] }}</span>
                        @endif
                    </td>
                    <td style="font-size: 0.85rem; white-space: nowrap;">
                        {{ $activity['timestamp']->format('d/m/Y H:i:s') }}
                    </td>
                    <td>
                        <span class="badge badge-primary">{{ $activity['aktivitas'] }}</span>
                    </td>
                    <td style="font-weight: 500;">{{ $activity['user'] }}</td>
                    <td style="font-weight: 600;">{{ number_format($activity['tonase'], 2) }}</td>
                    <td style="font-weight: 600;">{{ number_format($activity['konsentrat'], 2) }}%</td>
                    
                    <!-- 5 Kolom Unsur LTJ -->
                    <td>
                        @if(!empty($activity['nd_content']))
                            <span class="unsur-badge unsur-nd">{{ number_format($activity['nd_content'], 2) }}%</span>
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($activity['y_content']))
                            <span class="unsur-badge unsur-y">{{ number_format($activity['y_content'], 2) }}%</span>
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($activity['ce_content']))
                            <span class="unsur-badge unsur-ce">{{ number_format($activity['ce_content'], 2) }}%</span>
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($activity['la_content']))
                            <span class="unsur-badge unsur-la">{{ number_format($activity['la_content'], 2) }}%</span>
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                    <td>
                        @if(!empty($activity['pr_content']))
                            <span class="unsur-badge unsur-pr">{{ number_format($activity['pr_content'], 2) }}%</span>
                        @else
                            <span style="color: #ccc;">-</span>
                        @endif
                    </td>
                    
                    <td style="font-weight: 700; color: var(--gold);">
                        {{ number_format($activity['massa_ltj'], 2) }}
                    </td>
                    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-align: left;">
                        {{ $activity['keterangan'] }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" style="text-align: center; padding: 2rem; color: rgba(62,92,116,0.5);">
                        <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                        Tidak ada aktivitas terbaru
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Volume Chart (Line Chart)
    const volumeCtx = document.getElementById('volumeChart').getContext('2d');
    const volumeData = @json($volumeChart);
    
    new Chart(volumeCtx, {
        type: 'line',
        data: {
            labels: volumeData.map(item => item.date),
            datasets: [{
                label: 'Jumlah Batch',
                data: volumeData.map(item => item.total),
                borderColor: 'rgb(62, 92, 116)',
                backgroundColor: 'rgba(62, 92, 116, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                pointRadius: 5,
                pointBackgroundColor: 'rgb(62, 92, 116)',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: 'rgb(197, 165, 114)',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: 'rgba(62, 92, 116, 0.9)',
                    padding: 12,
                    titleFont: {
                        size: 14,
                        weight: 'bold'
                    },
                    bodyFont: {
                        size: 13
                    },
                    borderColor: 'rgba(197, 165, 114, 0.5)',
                    borderWidth: 1
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(62, 92, 116, 0.05)'
                    }
                },
                x: {
                    ticks: {
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        color: 'rgba(62, 92, 116, 0.05)'
                    }
                }
            }
        }
    });

    // Auto-hide alerts setelah 5 detik
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endsection