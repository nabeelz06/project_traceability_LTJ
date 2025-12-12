<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Regulator PT Timah</title>
    <style>
        /* PT Timah Professional PDF Styling */
        @page {
            margin: 2cm 1.5cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10pt;
            line-height: 1.4;
            color: #2c3e50;
        }

        /* Header with Logo */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #3e5c74;
        }

        .logo {
            width: 120px;
            margin-bottom: 15px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #3e5c74;
            margin-bottom: 5px;
        }

        .report-title {
            font-size: 16pt;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 10px;
        }

        .report-date {
            font-size: 9pt;
            color: #7f8c8d;
            margin-top: 5px;
        }

        /* Section Styling */
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }

        .section-title {
            font-size: 13pt;
            font-weight: bold;
            color: #3e5c74;
            margin-bottom: 12px;
            padding-bottom: 5px;
            border-bottom: 2px solid #e8eef3;
        }

        .section-subtitle {
            font-size: 11pt;
            font-weight: bold;
            color: #2d4454;
            margin-top: 15px;
            margin-bottom: 8px;
        }

        /* KPI Cards Grid */
        .kpi-grid {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .kpi-row {
            display: table-row;
        }

        .kpi-card {
            display: table-cell;
            width: 25%;
            padding: 12px;
            text-align: center;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
        }

        .kpi-label {
            font-size: 8pt;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .kpi-value {
            font-size: 16pt;
            font-weight: bold;
            color: #3e5c74;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            background: #3e5c74;
            color: white;
            font-weight: bold;
            padding: 8px 6px;
            text-align: center;
            font-size: 9pt;
            border: 1px solid #2d4454;
        }

        td {
            padding: 7px 6px;
            border: 1px solid #dee2e6;
            text-align: center;
            font-size: 9pt;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        /* Info Box */
        .info-box {
            background: #e8eef3;
            padding: 12px;
            border-left: 4px solid #3e5c74;
            margin-bottom: 15px;
        }

        .info-label {
            font-weight: bold;
            color: #2d4454;
            margin-bottom: 3px;
        }

        .info-value {
            color: #2c3e50;
            font-size: 11pt;
        }

        /* Badge Styles */
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-primary { background: #0d6efd; color: white; }
        .badge-success { background: #198754; color: white; }
        .badge-warning { background: #ffc107; color: #000; }
        .badge-danger { background: #dc3545; color: white; }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 8pt;
            color: #7f8c8d;
            padding-top: 10px;
            border-top: 1px solid #dee2e6;
        }

        /* Page Break */
        .page-break {
            page-break-after: always;
        }

        /* Quarterly Analysis Grid */
        .quarterly-grid {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .quarterly-row {
            display: table-row;
        }

        .quarterly-cell {
            display: table-cell;
            width: 25%;
            padding: 10px;
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            text-align: center;
        }

        .quarter-label {
            font-weight: bold;
            color: #3e5c74;
            margin-bottom: 8px;
            font-size: 10pt;
        }

        .quarter-metric {
            margin-bottom: 5px;
            font-size: 9pt;
        }

        .quarter-value {
            font-weight: bold;
            color: #2c3e50;
        }
    </style>
</head>
<body>
    <!-- Header with Logo -->
    <div class="header">
        {{-- <img src="{{ public_path('images/logo-timah.png') }}" alt="PT Timah" class="logo"> --}}
        <div style="width: 120px; height: 120px; margin: 0 auto 15px; background: #3e5c74; border-radius: 60px; display: flex; align-items: center; justify-content: center; color: white; font-size: 48pt; font-weight: bold;">PT</div>
        <div class="company-name">PT TIMAH TBK</div>
        <div class="report-title">LAPORAN MONITORING REGULATOR</div>
        <div class="report-title" style="font-size: 14pt; margin-top: 5px;">
            Sistem Traceability Logam Tanah Jarang (LTJ)
        </div>
        <div class="report-date">
            Tanggal Laporan: {{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}
        </div>
    </div>

    <!-- Executive Summary -->
    <div class="section">
        <div class="section-title">I. RINGKASAN EKSEKUTIF</div>
        
        <div class="kpi-grid">
            <div class="kpi-row">
                <div class="kpi-card">
                    <div class="kpi-label">Zircon Stock</div>
                    <div class="kpi-value">{{ number_format($warehouseStock['zircon'], 0) }} kg</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Ilmenite Stock</div>
                    <div class="kpi-value">{{ number_format($warehouseStock['ilmenite'], 0) }} kg</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Monasit Stock</div>
                    <div class="kpi-value">{{ number_format($warehouseStock['monasit'], 0) }} kg</div>
                </div>
                <div class="kpi-card">
                    <div class="kpi-label">Total Export</div>
                    <div class="kpi-value">{{ number_format($totalExport, 0) }} kg</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cadangan Konsentrat -->
    <div class="section">
        <div class="section-title">II. CADANGAN KONSENTRAT WAREHOUSE</div>
        
        <table>
            <thead>
                <tr>
                    <th>Material</th>
                    <th>Stock Tersedia (kg)</th>
                    <th>Persentase (%)</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalStock = $warehouseStock['zircon'] + $warehouseStock['ilmenite'] + $warehouseStock['monasit'];
                @endphp
                <tr>
                    <td><strong>Zircon</strong></td>
                    <td>{{ number_format($warehouseStock['zircon'], 2) }}</td>
                    <td>{{ $totalStock > 0 ? number_format(($warehouseStock['zircon'] / $totalStock) * 100, 1) : 0 }}%</td>
                    <td><span class="badge badge-success">Ready</span></td>
                </tr>
                <tr>
                    <td><strong>Ilmenite</strong></td>
                    <td>{{ number_format($warehouseStock['ilmenite'], 2) }}</td>
                    <td>{{ $totalStock > 0 ? number_format(($warehouseStock['ilmenite'] / $totalStock) * 100, 1) : 0 }}%</td>
                    <td><span class="badge badge-success">Ready</span></td>
                </tr>
                <tr>
                    <td><strong>Monasit</strong></td>
                    <td>{{ number_format($warehouseStock['monasit'], 2) }}</td>
                    <td>{{ $totalStock > 0 ? number_format(($warehouseStock['monasit'] / $totalStock) * 100, 1) : 0 }}%</td>
                    <td><span class="badge badge-warning">Pending Lab</span></td>
                </tr>
                <tr style="background: #e8eef3; font-weight: bold;">
                    <td>TOTAL</td>
                    <td>{{ number_format($totalStock, 2) }}</td>
                    <td>100%</td>
                    <td>-</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Cadangan LTJ -->
    <div class="section">
        <div class="section-title">III. CADANGAN LOGAM TANAH JARANG (LTJ)</div>
        
        <div class="info-box">
            <div class="info-label">Total Recovery Rate (Average)</div>
            <div class="info-value">{{ number_format($ltjStock['total_recovery'], 2) }}%</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Unsur LTJ</th>
                    <th>Simbol</th>
                    <th>Kandungan Average (%)</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Neodymium</td>
                    <td><strong>Nd</strong></td>
                    <td>{{ number_format($ltjStock['nd'], 2) }}</td>
                    <td>Magnet permanen</td>
                </tr>
                <tr>
                    <td>Lanthanum</td>
                    <td><strong>La</strong></td>
                    <td>{{ number_format($ltjStock['la'], 2) }}</td>
                    <td>Katalis, optik</td>
                </tr>
                <tr>
                    <td>Cerium</td>
                    <td><strong>Ce</strong></td>
                    <td>{{ number_format($ltjStock['ce'], 2) }}</td>
                    <td>Polishing, katalis</td>
                </tr>
                <tr>
                    <td>Yttrium</td>
                    <td><strong>Y</strong></td>
                    <td>{{ number_format($ltjStock['y'], 2) }}</td>
                    <td>LED, laser</td>
                </tr>
                <tr>
                    <td>Praseodymium</td>
                    <td><strong>Pr</strong></td>
                    <td>{{ number_format($ltjStock['pr'], 2) }}</td>
                    <td>Magnet, keramik</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Data Export 1 Bulan Terakhir -->
    <div class="section">
        <div class="section-title">IV. DATA EKSPOR (1 BULAN TERAKHIR)</div>
        
        <div class="info-box">
            <div class="info-label">Total Berat Export</div>
            <div class="info-value">{{ number_format($totalExport, 2) }} kg</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Batch Code</th>
                    <th>Material</th>
                    <th>Berat (kg)</th>
                    <th>Tipe</th>
                    <th>Destination</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exports as $export)
                <tr>
                    <td>{{ $export->exported_at->format('d/m/Y') }}</td>
                    <td>{{ $export->batch->batch_code }}</td>
                    <td>{{ $export->batch->productCode->material ?? '-' }}</td>
                    <td>{{ number_format($export->weight_kg, 2) }}</td>
                    <td>
                        <span class="badge {{ $export->export_type == 'export' ? 'badge-primary' : 'badge-success' }}">
                            {{ strtoupper($export->export_type) }}
                        </span>
                    </td>
                    <td>{{ $export->destination }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: #6c757d;">Tidak ada data export dalam 1 bulan terakhir</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Analisis Per Kuartal -->
    <div class="section">
        <div class="section-title">V. ANALISIS PER KUARTAL (1 TAHUN TERAKHIR)</div>
        
        <div class="quarterly-grid">
            <div class="quarterly-row">
                @foreach($quarterlyAnalysis as $quarter)
                <div class="quarterly-cell">
                    <div class="quarter-label">{{ $quarter['quarter'] }}</div>
                    <div class="quarter-metric">
                        <span style="color: #6c757d;">Produksi:</span><br>
                        <span class="quarter-value">{{ number_format($quarter['production'], 0) }} kg</span>
                    </div>
                    <div class="quarter-metric">
                        <span style="color: #6c757d;">Export:</span><br>
                        <span class="quarter-value">{{ number_format($quarter['exports'], 0) }} kg</span>
                    </div>
                    <div class="quarter-metric">
                        <span style="color: #6c757d;">Batches:</span><br>
                        <span class="quarter-value">{{ $quarter['batches_count'] }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <div class="section-subtitle">Tren Kenaikan Produksi & Export per Kuartal</div>
        <table>
            <thead>
                <tr>
                    <th>Kuartal</th>
                    <th>Total Produksi (kg)</th>
                    <th>Total Export (kg)</th>
                    <th>Jumlah Batch</th>
                    <th>Export Rate (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quarterlyAnalysis as $quarter)
                <tr>
                    <td><strong>{{ $quarter['quarter'] }}</strong></td>
                    <td>{{ number_format($quarter['production'], 2) }}</td>
                    <td>{{ number_format($quarter['exports'], 2) }}</td>
                    <td>{{ $quarter['batches_count'] }}</td>
                    <td>
                        @php
                            $exportRate = $quarter['production'] > 0 
                                ? ($quarter['exports'] / $quarter['production']) * 100 
                                : 0;
                        @endphp
                        {{ number_format($exportRate, 1) }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Log Aktivitas -->
    <div class="section">
        <div class="section-title">VI. LOG AKTIVITAS SISTEM (1 BULAN TERAKHIR)</div>
        
        <table>
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Waktu</th>
                    <th>Aktivitas</th>
                    <th>Batch Code</th>
                    <th>Pelaku</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentLogs->take(30) as $log)
                <tr>
                    <td>{{ $log->created_at->format('d/m/Y') }}</td>
                    <td>{{ $log->created_at->format('H:i') }}</td>
                    <td>{{ $log->getActionLabel() }}</td>
                    <td>{{ $log->batch->batch_code }}</td>
                    <td>{{ $log->actor->name ?? 'System' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #6c757d;">Tidak ada aktivitas dalam 1 bulan terakhir</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Kesimpulan & Rekomendasi -->
    <div class="section">
        <div class="section-title">VII. KESIMPULAN & REKOMENDASI</div>
        
        <div class="info-box">
            <div class="info-label">Status Operasional</div>
            <div class="info-value">
                <span class="badge badge-success">NORMAL - SISTEM BERJALAN BAIK</span>
            </div>
        </div>

        <div style="margin-top: 15px; line-height: 1.6;">
            <p style="margin-bottom: 10px;"><strong>Kesimpulan:</strong></p>
            <ul style="margin-left: 20px; margin-bottom: 15px;">
                <li>Total cadangan konsentrat di warehouse: <strong>{{ number_format($totalStock, 2) }} kg</strong></li>
                <li>Average recovery rate LTJ: <strong>{{ number_format($ltjStock['total_recovery'], 2) }}%</strong></li>
                <li>Total export 1 bulan terakhir: <strong>{{ number_format($totalExport, 2) }} kg</strong></li>
                <li>Sistem traceability berfungsi dengan baik dan tercatat {{ $recentLogs->count() }} aktivitas</li>
            </ul>

            <p style="margin-bottom: 10px;"><strong>Rekomendasi:</strong></p>
            <ul style="margin-left: 20px;">
                <li>Monitoring berkala terhadap stock warehouse untuk mencegah kekurangan material</li>
                <li>Optimalisasi proses pemisahan untuk meningkatkan recovery rate LTJ</li>
                <li>Dokumentasi lengkap untuk setiap batch yang di-export</li>
                <li>Audit rutin terhadap sistem traceability setiap kuartal</li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>Dokumen ini dibuat secara otomatis oleh Sistem Traceability PT Timah Tbk</div>
        <div>© {{ date('Y') }} PT Timah Tbk - Confidential & Proprietary</div>
    </div>
</body>
</html>