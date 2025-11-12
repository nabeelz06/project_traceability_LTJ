@extends('layouts.app')

@section('title', 'Detail User')

@section('content')
<div class="container py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-person" style="color: var(--primary);"></i>
            Detail User: {{ $user->name }}
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.users.index') }}" class="btn" style="background: var(--secondary); color: white;">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-body text-center py-4">
                    <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--primary); color: white; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-size: 2.5rem; font-weight: 700;">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="mb-1">{{ $user->name }}</h5>
                    <p class="text-muted mb-2">{{ $user->email }}</p>
                    <span class="badge badge-primary">{{ $user->getRoleLabel() }}</span>
                    
                    @if($user->partner)
                    <div class="mt-3 p-2" style="background: #f8f9fa; border-radius: 6px;">
                        <small class="text-muted">Partner</small>
                        <p class="mb-0"><strong>{{ $user->partner->name }}</strong></p>
                    </div>
                    @endif

                    <div class="mt-3">
                        @if($user->is_active)
                        <span class="badge badge-success">Aktif</span>
                        @else
                        <span class="badge badge-danger">Tidak Aktif</span>
                        @endif
                    </div>

                    <hr class="my-3">

                    <div class="text-start">
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-phone me-2"></i>
                            {{ $user->phone ?? 'Tidak ada' }}
                        </small>
                        <small class="text-muted d-block mb-2">
                            <i class="bi bi-calendar me-2"></i>
                            Bergabung: {{ $user->created_at->format('d M Y') }}
                        </small>
                        <small class="text-muted d-block">
                            <i class="bi bi-clock me-2"></i>
                            Update: {{ $user->updated_at->format('d M Y, H:i') }}
                        </small>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-lightning me-2"></i>Aksi
                </div>
                <div class="card-body">
                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" class="mb-2">
                        @csrf
                        <button type="submit" class="btn {{ $user->is_active ? 'btn-warning' : 'btn-success' }} w-100">
                            <i class="bi bi-toggle-{{ $user->is_active ? 'off' : 'on' }} me-1"></i>
                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                        </button>
                    </form>
                    @endif

                    <button type="button" class="btn btn-outline-warning w-100 mb-2" 
                            data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                        <i class="bi bi-key me-1"></i>Reset Password
                    </button>

                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" 
                          onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100">
                            <i class="bi bi-trash me-1"></i>Hapus User
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Aktivitas Terbaru
                </div>
                <div class="card-body" style="max-height: 500px; overflow-y: auto;">
                    @forelse($recentActivities as $activity)
                    <div class="mb-2 p-3" style="background: #f8f9fa; border-radius: 6px; border-left: 3px solid var(--primary);">
                        <strong style="color: var(--primary);">{{ $activity->getActionLabel() }}</strong>
                        <br><small class="text-muted">
                            Batch: <a href="{{ route('batches.show', $activity->batch) }}">{{ $activity->batch->batch_code }}</a> • 
                            {{ $activity->created_at->format('d M Y, H:i') }}
                        </small>
                        @if($activity->notes)
                        <br><small class="text-muted">{{ $activity->notes }}</small>
                        @endif
                    </div>
                    @empty
                    <p class="text-muted text-center py-4">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reset Password -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-key me-2"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.reset-password', $user) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        Password akan direset untuk user: <strong>{{ $user->name }}</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru *</label>
                        <input type="password" name="new_password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password *</label>
                        <input type="password" name="new_password_confirmation" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Reset Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection