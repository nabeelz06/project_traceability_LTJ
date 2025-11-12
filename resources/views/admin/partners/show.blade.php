@extends('layouts.app')

@section('title', 'Detail Mitra')

@section('content')
<div class="container py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-building" style="color: var(--primary);"></i>
            Detail Mitra: {{ $partner->name }}
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.partners.index') }}" class="btn" style="background: var(--secondary); color: white;">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('admin.partners.edit', $partner) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-3">Status Mitra</h6>
                    <div class="text-center mb-3">
                        @if($partner->status == 'approved')
                        <span class="badge badge-success" style="font-size: 1rem; padding: 0.5rem 1rem;">Approved</span>
                        @elseif($partner->status == 'pending')
                        <span class="badge badge-warning" style="font-size: 1rem; padding: 0.5rem 1rem;">Pending Review</span>
                        @else
                        <span class="badge badge-danger" style="font-size: 1rem; padding: 0.5rem 1rem;">Rejected</span>
                        @endif
                    </div>

                    @if($partner->status == 'pending')
                    <form action="{{ route('admin.partners.approve', $partner) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn btn-success w-100">
                            <i class="bi bi-check-circle me-1"></i>Approve Mitra
                        </button>
                    </form>
                    <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#rejectModal">
                        <i class="bi bi-x-circle me-1"></i>Reject Mitra
                    </button>
                    @endif

                    <hr class="my-3">

                    <div class="mb-3">
                        <small class="text-muted">Tipe Mitra</small>
                        <p class="mb-0"><strong>{{ $partner->getTypeLabel() }}</strong></p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Jumlah User</small>
                        <p class="mb-0"><strong>{{ $partner->users->count() }}</strong></p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Jumlah Batch</small>
                        <p class="mb-0"><strong>{{ $partner->batches->count() }}</strong></p>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted">Terdaftar</small>
                        <p class="mb-0">{{ $partner->created_at->format('d M Y') }}</p>
                    </div>

                    @if($partner->verification_doc)
                    <hr class="my-3">
                    <a href="{{ Storage::url($partner->verification_doc) }}" target="_blank" class="btn btn-outline-primary w-100">
                        <i class="bi bi-file-earmark-pdf me-1"></i>Lihat Dokumen Verifikasi
                    </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-info-circle me-2"></i>Informasi Mitra
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">NAMA MITRA</label>
                            <p class="mb-0" style="font-size: 1.1rem;">{{ $partner->name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-muted small">TIPE</label>
                            <p class="mb-0"><span class="badge badge-info">{{ $partner->getTypeLabel() }}</span></p>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold text-muted small">ALAMAT</label>
                            <p class="mb-0">{{ $partner->address }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-person me-2"></i>Data PIC (Person In Charge)
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-muted small">NAMA PIC</label>
                            <p class="mb-0">{{ $partner->pic_name }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-muted small">TELEPON</label>
                            <p class="mb-0">{{ $partner->pic_phone }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-bold text-muted small">EMAIL</label>
                            <p class="mb-0">{{ $partner->pic_email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            @if($partner->users->count() > 0)
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-people me-2"></i>User Terdaftar ({{ $partner->users->count() }})
                </div>
                <div class="card-body" style="padding: 0;">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($partner->users as $user)
                            <tr>
                                <td><strong>{{ $user->name }}</strong></td>
                                <td>{{ $user->email }}</td>
                                <td><span class="badge badge-secondary">{{ $user->getRoleLabel() }}</span></td>
                                <td>
                                    @if($user->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                    @else
                                    <span class="badge badge-danger">Tidak Aktif</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle me-2"></i>Reject Mitra</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.partners.reject', $partner) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Anda akan menolak mitra: <strong>{{ $partner->name }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Alasan Penolakan *</label>
                        <textarea name="rejection_reason" class="form-control" rows="4" required 
                                  placeholder="Jelaskan alasan penolakan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Reject Mitra</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection