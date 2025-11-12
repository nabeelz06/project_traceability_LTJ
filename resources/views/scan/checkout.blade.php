@extends('layouts.app')

@section('title', 'Check-Out Batch')

@section('content')
<div class="container py-4" style="max-width: 600px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700; text-align: center;">
        <i class="bi bi-box-arrow-right" style="color: var(--primary);"></i>
        Check-Out Pengiriman
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('scan.checkout.process') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <!-- Scan/Input Tag -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Scan RFID Tag atau Input Manual</label>
                    <div style="position: relative;">
                        <input type="text" name="tag_uid" id="tagInput" class="form-control form-control-lg" 
                               placeholder="Scan atau ketik Tag UID..." 
                               style="padding-right: 100px;" required autofocus>
                        <button type="button" class="btn btn-primary" 
                                style="position: absolute; right: 5px; top: 5px;"
                                onclick="startScanner()">
                            <i class="bi bi-camera"></i> Scan
                        </button>
                    </div>
                    @error('tag_uid')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <!-- Batch Info (akan muncul setelah scan) -->
                <div id="batchInfo" style="display: none;" class="mb-4 p-3" style="background: #f8f9fa; border-radius: 8px;">
                    <h6 class="text-muted mb-3">Informasi Batch</h6>
                    <div id="batchDetails"></div>
                </div>

                <!-- GPS Location -->
                <div class="mb-3">
                    <label class="form-label">Lokasi GPS (Opsional)</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" name="gps_location" id="gpsInput" class="form-control" 
                               placeholder="Otomatis terdeteksi..." readonly>
                        <button type="button" class="btn btn-outline-primary" onclick="getLocation()">
                            <i class="bi bi-geo-alt"></i>
                        </button>
                    </div>
                </div>

                <!-- Photo Evidence -->
                <div class="mb-3">
                    <label class="form-label">Foto Bukti (Opsional)</label>
                    <input type="file" name="photo" class="form-control" accept="image/*" capture="environment">
                    <small class="text-muted">Foto kontainer atau batch sebelum pengiriman</small>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="form-label">Catatan</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Catatan pengiriman..."></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('scan.index') }}" class="btn" style="background: var(--secondary); color: white; flex: 1;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary" style="flex: 2;">
                        <i class="bi bi-check-circle me-1"></i>Konfirmasi Check-Out
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function getLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            document.getElementById('gpsInput').value = 
                position.coords.latitude + ', ' + position.coords.longitude;
        });
    } else {
        alert('Geolocation tidak didukung browser Anda');
    }
}

function startScanner() {
    alert('Fitur kamera scanner akan diimplementasi dengan library RFID scanner');
}

// Auto-detect GPS on load
window.onload = function() {
    getLocation();
};
</script>
@endsection