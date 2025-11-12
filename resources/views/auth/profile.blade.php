@extends('layouts.app')

@section('title', 'Profile')

@section('content')
<div class="container py-4" style="max-width: 1000px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-person-circle" style="color: var(--primary);"></i>
        Profile Saya
    </h1>

    <div class="row">
        <!-- Profile Info -->
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
                        <small class="text-muted">Status Akun</small>
                        <p class="mb-0">
                            @if($user->is_active)
                            <span class="badge badge-success">Aktif</span>
                            @else
                            <span class="badge badge-danger">Tidak Aktif</span>
                            @endif
                        </p>
                    </div>

                    <div class="mt-2">
                        <small class="text-muted">Bergabung Sejak</small>
                        <p class="mb-0">{{ $user->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Edit Forms -->
        <div class="col-lg-8">
            <!-- Update Profile -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-pencil-square me-2"></i>Edit Profile
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Nama Lengkap *</label>
                            <input type="text" name="name" class="form-control" 
                                   value="{{ old('name', $user->name) }}" required>
                            @error('name')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                            <small class="text-muted">Email tidak dapat diubah. Hubungi administrator.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nomor Telepon</label>
                            <input type="text" name="phone" class="form-control" 
                                   value="{{ old('phone', $user->phone) }}" 
                                   placeholder="08xxxxxxxxxx">
                            @error('phone')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ $user->getRoleLabel() }}" disabled>
                            <small class="text-muted">Role tidak dapat diubah.</small>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>

            <!-- Update Password -->
            <div class="card mb-3">
                <div class="card-header">
                    <i class="bi bi-shield-lock me-2"></i>Ubah Password
                </div>
                <div class="card-body">
                    <form action="{{ route('profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Password Saat Ini *</label>
                            <input type="password" name="current_password" class="form-control" required>
                            @error('current_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password Baru *</label>
                            <input type="password" name="new_password" class="form-control" required>
                            <small class="text-muted">Minimal 8 karakter</small>
                            @error('new_password')
                                <span class="error-message">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Konfirmasi Password Baru *</label>
                            <input type="password" name="new_password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-key me-1"></i>Ubah Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-clock-history me-2"></i>Aktivitas Terakhir
                </div>
                <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                    @forelse($recentActivities as $activity)
                    <div class="mb-2 p-2" style="background: #f8f9fa; border-radius: 6px; border-left: 3px solid var(--primary);">
                        <strong style="color: var(--primary); font-size: 0.9rem;">{{ $activity->getActionLabel() }}</strong>
                        <br><small class="text-muted">
                            Batch: <a href="{{ route('batches.show', $activity->batch) }}">{{ $activity->batch->batch_code }}</a> • 
                            {{ $activity->created_at->diffForHumans() }}
                        </small>
                    </div>
                    @empty
                    <p class="text-muted text-center py-3">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection