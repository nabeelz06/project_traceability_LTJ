@extends('layouts.app')

@section('title', 'Check-In Batch')

@section('content')
<div class="container py-4" style="max-width: 600px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700; text-align: center;">
        <i class="bi bi-box-arrow-in-down" style="color: var(--success);"></i>
        Check-In Penerimaan
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('scan.checkin.process') }}" method="POST">
                @csrf

                <!-- Scan/Input Tag -->
                <div class="mb-4">
                    <label class="form-label fw-bold">Scan RFID Tag atau Input Manual</label>
                    <div style="position: relative;">
                        <input type="text" name="tag_uid" id="tagInput" class="form-control form-control-lg" 
                               placeholder="Scan atau ketik Tag UID..." 
                               style="padding-right: 100px;" required autofocus>
                        <button type="button" class="btn btn-success" 
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
                    
                    <div class="alert alert-info mt-3 mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Verifikasi batch sesuai dengan dokumen pengiriman
                    </div>
                </div>

                <!-- Notes -->
                <div class="mb-4">
                    <label class="form-label">Catatan Penerimaan</label>
                    <textarea name="notes" class="form-control" rows="3" 
                              placeholder="Kondisi batch saat diterima..."></textarea>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <a href="{{ route('scan.index') }}" class="btn" style="background: var(--secondary); color: white; flex: 1;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-success" style="flex: 2;">
                        <i class="bi bi-check-circle me-1"></i>Konfirmasi Check-In
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function startScanner() {
    alert('Fitur kamera scanner akan diimplementasi dengan library RFID scanner');
}
</script>
@endsection