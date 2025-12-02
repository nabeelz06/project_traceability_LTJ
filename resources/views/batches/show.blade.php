@extends('layouts.app')

@section('title', 'Detail Batch ' . $batch->batch_code)

@section('content')
<style>
    /* PT Timah Blue Theme Variables */
    :root {
        --primary: #3e5c74;
        --primary-dark: #2d4454;
        --primary-light: #e8eef3;
        --gold: #c5a572;
    }

    /* Page Header */
    .detail-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 2rem;
        border-radius: 14px;
        margin-bottom: 2rem;
        box-shadow: 0 8px 24px rgba(62,92,116,0.25);
    }

    .detail-header h2 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
    }

    .detail-header .subtitle {
        margin-top: 0.5rem;
        font-size: 1rem;
        opacity: 0.9;
    }

    /* Cards */
    .info-card {
        background: white;
        border-radius: 14px;
        padding: 1.5rem;
        box-shadow: 0 4px 16px rgba(62,92,116,0.1);
        margin-bottom: 1.5rem;
    }

    .info-card h5 {
        color: var(--primary);
        font-weight: 700;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        padding: 0.75rem 0;
        border-bottom: 1px solid rgba(62,92,116,0.1);
    }

    .info-row:last-child {
        border-bottom: none;
    }

    .info-label {
        color: rgba(62,92,116,0.8);
        font-weight: 500;
    }

    .info-value {
        color: var(--primary);
        font-weight: 600;
        text-align: right;
    }

    /* Google Maps Container */
    .map-container {
        height: 350px;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    /* Evidence Gallery */
    .evidence-gallery {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }

    .evidence-item {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }

    .evidence-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15);
    }

    .evidence-item img,
    .evidence-item video {
        width: 100%;
        height: 150px;
        object-fit: cover;
    }

    .evidence-item .doc-icon {
        width: 100%;
        height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-light) 0%, #f5f8fa 100%);
        font-size: 3rem;
        color: var(--primary);
    }

    .evidence-label {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(to top, rgba(0,0,0,0.8), transparent);
        color: white;
        padding: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    /* RFID Badge */
    .rfid-badge {
        background: linear-gradient(135deg, var(--gold) 0%, #d4b589 100%);
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 10px;
        font-family: 'Courier New', monospace;
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.5px;
        display: inline-block;
        box-shadow: 0 4px 12px rgba(197,165,114,0.3);
    }

    /* QR Code Display */
    .qr-code-display {
        text-align: center;
        padding: 1.5rem;
        background: linear-gradient(135deg, rgba(62,92,116,0.05) 0%, rgba(197,165,114,0.05) 100%);
        border-radius: 12px;
        border: 2px dashed var(--primary);
    }

    .qr-code-display img {
        max-width: 200px;
        margin: 0 auto;
        display: block;
    }

    /* LTJ Content Badges */
    .ltj-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-top: 0.5rem;
    }

    .ltj-badge {
        padding: 0.4rem 0.75rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .ltj-badge.nd { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; }
    .ltj-badge.y { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; }
    .ltj-badge.ce { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; }
    .ltj-badge.la { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; }
    .ltj-badge.pr { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); color: white; }

    /* Action Buttons */
    .action-buttons {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        margin-top: 1.5rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .detail-header {
            padding: 1.5rem;
        }

        .detail-header h2 {
            font-size: 1.35rem;
        }

        .evidence-gallery {
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        }

        .map-container {
            height: 250px;
        }
    }
</style>

<div class="container py-4">
    <!-- Header dengan Batch Code -->
    <div class="detail-header">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h2><i class="bi bi-box-seam me-2"></i>{{ $batch->batch_code }}</h2>
                <div class="subtitle">
                    <i class="bi bi-tag me-1"></i>Lot Number: {{ $batch->lot_number }}
                    @if($batch->hasRfidTag())
                        <span class="ms-3"><i class="bi bi-broadcast me-1"></i>RFID Registered</span>
                    @endif
                </div>
            </div>
            <div>
                <span class="badge {{ $batch->getStatusBadgeClass() }}" style="font-size: 1rem; padding: 0.6rem 1.2rem;">
                    {{ $batch->getStatusLabel() }}
                </span>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- LEFT COLUMN -->
        <div class="col-lg-6">
            <!-- Informasi Produk -->
            <div class="info-card">
                <h5><i class="bi bi-box"></i>Informasi Produk</h5>
                <div class="info-row">
                    <span class="info-label">Kode Produk</span>
                    <span class="info-value">{{ $batch->productCode->code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Deskripsi</span>
                    <span class="info-value">{{ $batch->productCode->description }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Container Code</span>
                    <span class="info-value">{{ $batch->container_code }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Tonase</span>
                    <span class="info-value">{{ number_format($batch->tonase, 3) }} ton</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Konsentrat LTJ</span>
                    <span class="info-value">{{ number_format($batch->konsentrat_persen, 2) }}%</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Massa LTJ</span>
                    <span class="info-value">{{ number_format($batch->massa_ltj_kg, 2) }} kg</span>
                </div>
            </div>

            <!-- Kandungan 5 Unsur LTJ -->
            @if($batch->total_ltj_content > 0)
            <div class="info-card">
                <h5><i class="bi bi-gem"></i>Kandungan 5 Unsur LTJ</h5>
                <div class="ltj-badges">
                    @if($batch->nd_content > 0)
                        <span class="ltj-badge nd">Nd: {{ number_format($batch->nd_content, 2) }}%</span>
                    @endif
                    @if($batch->y_content > 0)
                        <span class="ltj-badge y">Y: {{ number_format($batch->y_content, 2) }}%</span>
                    @endif
                    @if($batch->ce_content > 0)
                        <span class="ltj-badge ce">Ce: {{ number_format($batch->ce_content, 2) }}%</span>
                    @endif
                    @if($batch->la_content > 0)
                        <span class="ltj-badge la">La: {{ number_format($batch->la_content, 2) }}%</span>
                    @endif
                    @if($batch->pr_content > 0)
                        <span class="ltj-badge pr">Pr: {{ number_format($batch->pr_content, 2) }}%</span>
                    @endif
                </div>
                <div class="info-row mt-3">
                    <span class="info-label">Total Kandungan</span>
                    <span class="info-value">{{ number_format($batch->total_ltj_content, 2) }}%</span>
                </div>
            </div>
            @endif

            <!-- RFID Tag -->
            @if($batch->rfid_tag_full || $batch->rfid_tag_full_generated)
            <div class="info-card">
                <h5><i class="bi bi-broadcast-pin"></i>RFID Tag Traceability</h5>
                <div class="rfid-badge">
                    {{ $batch->rfid_tag_full ?? $batch->rfid_tag_full_generated }}
                </div>
                @if($batch->rfid_tag_uid)
                <div class="info-row mt-2">
                    <span class="info-label">UID</span>
                    <span class="info-value">{{ $batch->rfid_tag_uid }}</span>
                </div>
                @endif
            </div>
            @endif

            <!-- Process Parameters (jika ada) -->
            @if($batch->energy_input_kwh || $batch->water_consumption_liter || $batch->process_temperature_celsius)
            <div class="info-card">
                <h5><i class="bi bi-gear-fill"></i>Parameter Proses</h5>
                @if($batch->energy_input_kwh)
                <div class="info-row">
                    <span class="info-label">Input Energi</span>
                    <span class="info-value">{{ number_format($batch->energy_input_kwh, 2) }} kWh</span>
                </div>
                @endif
                @if($batch->water_consumption_liter)
                <div class="info-row">
                    <span class="info-label">Konsumsi Air</span>
                    <span class="info-value">{{ number_format($batch->water_consumption_liter, 2) }} L</span>
                </div>
                @endif
                @if($batch->process_temperature_celsius)
                <div class="info-row">
                    <span class="info-label">Suhu Proses</span>
                    <span class="info-value">{{ number_format($batch->process_temperature_celsius, 2) }}°C</span>
                </div>
                @endif
                @if($batch->process_ph)
                <div class="info-row">
                    <span class="info-label">pH Larutan</span>
                    <span class="info-value">{{ number_format($batch->process_ph, 2) }}</span>
                </div>
                @endif
                @if($batch->reaction_time_minutes)
                <div class="info-row">
                    <span class="info-label">Waktu Reaksi</span>
                    <span class="info-value">{{ $batch->reaction_time_minutes }} menit</span>
                </div>
                @endif
                @if($batch->efficiency_percent)
                <div class="info-row">
                    <span class="info-label">Efisiensi</span>
                    <span class="info-value">{{ number_format($batch->efficiency_percent, 2) }}%</span>
                </div>
                @endif
            </div>
            @endif
        </div>

        <!-- RIGHT COLUMN -->
        <div class="col-lg-6">
            <!-- GPS Tracking Map -->
            @if($batch->hasGpsCoordinates())
            <div class="info-card">
                <h5><i class="bi bi-geo-alt-fill"></i>Lokasi GPS Terkini</h5>
                <div class="info-row">
                    <span class="info-label">Koordinat</span>
                    <span class="info-value">{{ $batch->formatted_gps }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lokasi</span>
                    <span class="info-value">{{ $batch->current_location_name ?? $batch->current_location }}</span>
                </div>
                @if($batch->last_gps_update)
                <div class="info-row">
                    <span class="info-label">Update Terakhir</span>
                    <span class="info-value">{{ $batch->last_gps_update->format('d M Y, H:i') }}</span>
                </div>
                @endif
                
                <!-- Google Maps Embed -->
                <div class="map-container mt-3">
                    <iframe 
                        width="100%" 
                        height="100%" 
                        frameborder="0" 
                        style="border:0"
                        src="https://maps.google.com/maps?q={{ $batch->current_latitude }},{{ $batch->current_longitude }}&t=&z=15&ie=UTF8&iwloc=&output=embed">
                    </iframe>
                </div>
                
                <a href="{{ $batch->google_maps_url }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2 w-100">
                    <i class="bi bi-map me-1"></i>Buka di Google Maps
                </a>
            </div>
            @else
            <div class="info-card">
                <h5><i class="bi bi-geo-alt"></i>Lokasi</h5>
                <div class="info-row">
                    <span class="info-label">Lokasi Asal</span>
                    <span class="info-value">{{ $batch->origin_location }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Lokasi Saat Ini</span>
                    <span class="info-value">{{ $batch->current_location }}</span>
                </div>
                <div class="alert alert-info mt-3" style="font-size: 0.9rem;">
                    <i class="bi bi-info-circle me-2"></i>
                    Koordinat GPS belum tercatat untuk batch ini.
                </div>
            </div>
            @endif

            <!-- Evidence Gallery -->
            @if($batch->hasEvidence())
            <div class="info-card">
                <h5><i class="bi bi-camera-fill"></i>Dokumentasi / Evidence ({{ $batch->total_evidence_count }})</h5>
                
                <!-- Photos -->
                @if($batch->evidence_photos && count($batch->evidence_photos) > 0)
                <h6 class="mt-3 mb-2" style="color: var(--primary); font-size: 0.95rem;">
                    <i class="bi bi-image me-1"></i>Foto ({{ count($batch->evidence_photos) }})
                </h6>
                <div class="evidence-gallery">
                    @foreach($batch->evidence_photos as $photo)
                    <a href="{{ $photo['url'] }}" target="_blank" class="evidence-item">
                        <img src="{{ $photo['url'] }}" alt="{{ $photo['filename'] ?? 'Evidence Photo' }}">
                        <div class="evidence-label">{{ $photo['filename'] ?? 'Photo' }}</div>
                    </a>
                    @endforeach
                </div>
                @endif

                <!-- Videos -->
                @if($batch->evidence_videos && count($batch->evidence_videos) > 0)
                <h6 class="mt-3 mb-2" style="color: var(--primary); font-size: 0.95rem;">
                    <i class="bi bi-film me-1"></i>Video ({{ count($batch->evidence_videos) }})
                </h6>
                <div class="evidence-gallery">
                    @foreach($batch->evidence_videos as $video)
                    <a href="{{ $video['url'] }}" target="_blank" class="evidence-item">
                        <video src="{{ $video['url'] }}" preload="metadata" muted></video>
                        <div class="evidence-label">{{ $video['filename'] ?? 'Video' }}</div>
                    </a>
                    @endforeach
                </div>
                @endif

                <!-- Documents -->
                @if($batch->evidence_documents && count($batch->evidence_documents) > 0)
                <h6 class="mt-3 mb-2" style="color: var(--primary); font-size: 0.95rem;">
                    <i class="bi bi-file-earmark-text me-1"></i>Dokumen ({{ count($batch->evidence_documents) }})
                </h6>
                <div class="evidence-gallery">
                    @foreach($batch->evidence_documents as $document)
                    <a href="{{ $document['url'] }}" target="_blank" class="evidence-item">
                        <div class="doc-icon">
                            <i class="bi bi-file-earmark-pdf"></i>
                        </div>
                        <div class="evidence-label">{{ $document['filename'] ?? 'Document' }}</div>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
            @endif

            <!-- Informasi Tambahan -->
            <div class="info-card">
                <h5><i class="bi bi-info-circle"></i>Informasi Lainnya</h5>
                <div class="info-row">
                    <span class="info-label">Dibuat Oleh</span>
                    <span class="info-value">{{ $batch->creator->name ?? '-' }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dibuat Pada</span>
                    <span class="info-value">{{ $batch->created_at->format('d M Y, H:i') }}</span>
                </div>
                @if($batch->currentPartner)
                <div class="info-row">
                    <span class="info-label">Pemilik Saat Ini</span>
                    <span class="info-value">{{ $batch->currentPartner->name }}</span>
                </div>
                @endif
                @if($batch->keterangan)
                <div class="info-row">
                    <span class="info-label">Keterangan</span>
                    <span class="info-value" style="text-align: left;">{{ $batch->keterangan }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        @if($batch->canBeEdited())
        <a href="{{ route('batches.edit', $batch->id) }}" class="btn btn-primary">
            <i class="bi bi-pencil-square me-1"></i>Edit Batch
        </a>
        @endif

        <a href="{{ route('batches.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-1"></i>Kembali ke Daftar
        </a>

        @if($batch->canBeDeleted() && auth()->user()->isSuperAdmin())
        <form action="{{ route('batches.destroy', $batch->id) }}" method="POST" 
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus batch ini?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="bi bi-trash me-1"></i>Hapus Batch
            </button>
        </form>
        @endif
    </div>
</div>
@endsection