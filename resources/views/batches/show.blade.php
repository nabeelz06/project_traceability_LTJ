@extends('layouts.app')

@section('title', 'Detail Batch - ' . $batch->batch_code)

@section('content')
<div class="container py-4">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 0.5rem; font-weight: 700;">
                <i class="bi bi-box-seam" style="color: var(--primary);"></i>
                Detail Batch: {{ $batch->batch_code }}
            </h1>
            <p style="color: var(--secondary); margin: 0;">
                <span class="badge {{ $batch->getStatusBadgeClass() }}">{{ $batch->getStatusLabel() }}</span>
                @if($batch->isChild())
                    <span class="badge badge-info ms-2">Batch Turunan</span>
                @else
                    <span class="badge badge-primary ms-2">Batch Induk</span>
                @endif
            </p>
        </div>
        
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('batches.index') }}" class="btn" style="background: var(--secondary); color: white;">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            
            @if($batch->canBeEdited() && (auth()->user()->isSuperAdmin() || auth()->user()->isAdmin()))
                <a href="{{ route('batches.edit', $batch) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>Edit
                </a>
            @endif
        </div>
    </div>

    <!-- Informasi Batch -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Informasi Batch
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">KODE BATCH</label>
                            <p class="mb-0" style="font-size: 1.1rem; font-weight: 600;">{{ $batch->batch_code }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">NOMOR LOT</label>
                            <p class="mb-0" style="font-size: 1.1rem;">{{ $batch->lot_number }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">KODE PRODUK</label>
                            <p class="mb-0">{{ $batch->product_code }}</p>
                            <small class="text-muted">{{ $batch->productCode->description ?? '' }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">NOMOR KONTAINER</label>
                            <p class="mb-0">{{ $batch->container_code }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">BERAT AWAL</label>
                            <p class="mb-0">{{ number_format($batch->initial_weight, 2) }} {{ $batch->weight_unit }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">BERAT SAAT INI</label>
                            <p class="mb-0">{{ number_format($batch->current_weight, 2) }} {{ $batch->weight_unit }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">LOKASI SAAT INI</label>
                            <p class="mb-0">{{ $batch->current_location }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">PEMILIK SAAT INI</label>
                            <p class="mb-0">{{ $batch->currentPartner->name ?? 'PT Timah' }}</p>
                        </div>
                        
                        @if($batch->hasRfidTag())
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold text-muted small">RFID TAG UID</label>
                            <p class="mb-0">
                                <span class="badge badge-success">
                                    <i class="bi bi-check-circle me-1"></i>{{ $batch->rfid_tag_uid }}
                                </span>
                            </p>
                        </div>
                        @endif

                        @if($batch->parentBatch)
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold text-muted small">BATCH INDUK</label>
                            <p class="mb-0">
                                <a href="{{ route('batches.show', $batch->parentBatch) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-box me-1"></i>{{ $batch->parentBatch->batch_code }}
                                </a>
                            </p>
                        </div>
                        @endif

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">DIBUAT OLEH</label>
                            <p class="mb-0">{{ $batch->creator->name ?? 'N/A' }}</p>
                            <small class="text-muted">{{ $batch->created_at->format('d M Y, H:i') }}</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">UMUR BATCH</label>
                            <p class="mb-0">{{ $batch->age_in_days }} hari</p>
                        </div>
                    </div>

                    @if(!$batch->isChild() && $batch->childBatches->count() > 0)
                    <div class="mt-4 p-3" style="background: #f8f9fa; border-radius: 8px;">
                        <label class="form-label fw-bold text-muted small mb-2">PROGRESS PEMROSESAN</label>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar" role="progressbar" 
                                 style="width: {{ $batch->processed_percentage }}%;" 
                                 aria-valuenow="{{ $batch->processed_percentage }}" 
                                 aria-valuemin="0" aria-valuemax="100">
                                {{ number_format($batch->processed_percentage, 1) }}%
                            </div>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            {{ $batch->childBatches->count() }} batch turunan | 
                            Sisa: {{ number_format($batch->remaining_weight, 2) }} {{ $batch->weight_unit }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Batch Turunan (jika ada) -->
            @if(!$batch->isChild() && $batch->childBatches->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-diagram-3 me-2"></i>Batch Turunan ({{ $batch->childBatches->count() }})
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Kode Batch</th>
                                    <th>Produk</th>
                                    <th>Berat</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($batch->childBatches as $child)
                                <tr>
                                    <td><strong>{{ $child->batch_code }}</strong></td>
                                    <td>{{ $child->product_code }}</td>
                                    <td>{{ number_format($child->initial_weight, 2) }} {{ $child->weight_unit }}</td>
                                    <td><span class="badge {{ $child->getStatusBadgeClass() }}">{{ $child->getStatusLabel() }}</span></td>
                                    <td>
                                        <a href="{{ route('batches.show', $child) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- History Log -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Riwayat Aktivitas
                </div>
                <div class="card-body">
                    @forelse($batch->logs as $log)
                    <div class="log-item" style="padding: 1rem; border-left: 3px solid var(--primary); margin-bottom: 1rem; background: #f8f9fa; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: start;">
                            <div>
                                <strong style="color: var(--primary);">{{ ucfirst(str_replace('_', ' ', $log->action)) }}</strong>
                                @if($log->previous_status && $log->new_status)
                                    <span class="text-muted">
                                        - Status: <span class="badge badge-secondary">{{ $log->previous_status }}</span> 
                                        → <span class="badge {{ $log->new_status == 'delivered' ? 'badge-success' : 'badge-info' }}">{{ $log->new_status }}</span>
                                    </span>
                                @endif
                                <p class="mb-1 mt-2 text-muted">{{ $log->notes }}</p>
                                <small class="text-muted">
                                    <i class="bi bi-person"></i> {{ $log->actor->name ?? 'System' }} • 
                                    <i class="bi bi-clock"></i> {{ $log->created_at->format('d M Y, H:i') }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-lightning me-2"></i>Aksi Cepat
                </div>
                <div class="card-body">
                    @if($batch->canBeCheckedOut() && auth()->user()->isOperator())
                        <a href="{{ route('scan.checkout') }}?batch={{ $batch->id }}" class="btn btn-primary w-100 mb-2">
                            <i class="bi bi-box-arrow-right me-1"></i>Check-Out
                        </a>
                    @endif

                    @if($batch->status == 'created' && !$batch->hasRfidTag() && auth()->user()->isAdmin())
                        <button type="button" class="btn btn-info w-100 mb-2" onclick="openRfidModal()">
                            <i class="bi bi-credit-card me-1"></i>Tulis RFID Tag
                        </button>
                    @endif

                    @if($batch->status == 'created' && auth()->user()->isAdmin())
                        <form action="{{ route('batches.mark-ready', $batch) }}" method="POST" class="mb-2">
                            @csrf
                            @method('PUT')
                            <button type="submit" class="btn btn-success w-100">
                                <i class="bi bi-check-circle me-1"></i>Tandai Siap Kirim
                            </button>
                        </form>
                    @endif

                    @if($batch->status == 'received' && auth()->user()->isMitraMiddlestream())
                        <a href="{{ route('mitra.batches.create-child', $batch) }}" class="btn btn-warning w-100 mb-2">
                            <i class="bi bi-diagram-3 me-1"></i>Buat Batch Turunan
                        </a>
                    @endif

                    <a href="{{ route('traceability.tree', $batch) }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="bi bi-diagram-2 me-1"></i>Lihat Traceability Tree
                    </a>

                    @if(auth()->user()->canCorrectBatch())
                        <button type="button" class="btn btn-outline-warning w-100 mb-2" onclick="openCorrectModal()">
                            <i class="bi bi-pencil-square me-1"></i>Koreksi Data
                        </button>
                    @endif

                    @if($batch->canBeDeleted() && auth()->user()->isSuperAdmin())
                        <form action="{{ route('admin.batches.force-delete', $batch) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus batch ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-outline-danger w-100">
                                <i class="bi bi-trash me-1"></i>Hapus Batch
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Dokumen Pendukung -->
            @if($batch->documents->count() > 0)
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-file-earmark-text me-2"></i>Dokumen ({{ $batch->documents->count() }})
                </div>
                <div class="card-body">
                    @foreach($batch->documents as $doc)
                    <div class="mb-2 p-2" style="background: #f8f9fa; border-radius: 4px;">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <small class="text-muted">{{ ucfirst($doc->type) }}</small>
                                <p class="mb-0" style="font-size: 0.9rem;">{{ $doc->file_name }}</p>
                            </div>
                            <a href="{{ Storage::url($doc->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-download"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Info Tambahan -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Informasi Sistem
                </div>
                <div class="card-body">
                    <small class="text-muted d-block mb-2">
                        <strong>ID Database:</strong> {{ $batch->id }}
                    </small>
                    <small class="text-muted d-block mb-2">
                        <strong>Dibuat:</strong> {{ $batch->created_at->format('d M Y, H:i') }}
                    </small>
                    <small class="text-muted d-block">
                        <strong>Terakhir Update:</strong> {{ $batch->updated_at->format('d M Y, H:i') }}
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal RFID Write -->
<div class="modal fade" id="rfidModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-credit-card me-2"></i>Tulis RFID Tag</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="rfidForm" action="{{ route('batches.write-rfid', $batch) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Device ID</label>
                        <input type="text" name="device_id" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tag UID</label>
                        <input type="text" name="tag_uid" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Tulis Tag</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Koreksi -->
@if(auth()->user()->canCorrectBatch())
<div class="modal fade" id="correctModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square me-2"></i>Koreksi Data Batch</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.batches.correct', $batch) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Koreksi manual akan dicatat dalam log sistem.
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status Baru</label>
                        <select name="status" class="form-select" required>
                            <option value="created" {{ $batch->status == 'created' ? 'selected' : '' }}>Dibuat</option>
                            <option value="ready_to_ship" {{ $batch->status == 'ready_to_ship' ? 'selected' : '' }}>Siap Kirim</option>
                            <option value="shipped" {{ $batch->status == 'shipped' ? 'selected' : '' }}>Dalam Pengiriman</option>
                            <option value="received" {{ $batch->status == 'received' ? 'selected' : '' }}>Diterima</option>
                            <option value="processed" {{ $batch->status == 'processed' ? 'selected' : '' }}>Diproses</option>
                            <option value="delivered" {{ $batch->status == 'delivered' ? 'selected' : '' }}>Terkirim</option>
                            <option value="quarantine" {{ $batch->status == 'quarantine' ? 'selected' : '' }}>Karantina</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Koreksi *</label>
                        <textarea name="correction_reason" class="form-control" rows="4" required 
                                  placeholder="Jelaskan alasan dilakukan koreksi..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Koreksi Data</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function openRfidModal() {
    new bootstrap.Modal(document.getElementById('rfidModal')).show();
}

function openCorrectModal() {
    new bootstrap.Modal(document.getElementById('correctModal')).show();
}
</script>
@endsection