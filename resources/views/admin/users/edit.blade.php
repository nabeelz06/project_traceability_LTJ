@extends('layouts.app')

@section('title', 'Edit User')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-pencil-square" style="color: var(--primary);"></i>
        Edit User: {{ $user->name }}
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" 
                               value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" id="roleSelect" class="form-select" required>
                            <option value="super_admin" {{ old('role', $user->role) == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin PT Timah</option>
                            <option value="operator" {{ old('role', $user->role) == 'operator' ? 'selected' : '' }}>Operator</option>
                            <option value="mitra_middlestream" {{ old('role', $user->role) == 'mitra_middlestream' ? 'selected' : '' }}>Mitra Middlestream</option>
                            <option value="mitra_downstream" {{ old('role', $user->role) == 'mitra_downstream' ? 'selected' : '' }}>Mitra Downstream</option>
                            <option value="g_bim" {{ old('role', $user->role) == 'g_bim' ? 'selected' : '' }}>Government (BIM)</option>
                            <option value="g_esdm" {{ old('role', $user->role) == 'g_esdm' ? 'selected' : '' }}>Government (ESDM)</option>
                        </select>
                        @error('role')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="partnerField" style="{{ in_array($user->role, ['mitra_middlestream', 'mitra_downstream']) ? '' : 'display:none;' }}">
                        <label class="form-label">Partner *</label>
                        <select name="partner_id" class="form-select">
                            <option value="">-- Pilih Partner --</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id', $user->partner_id) == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }} ({{ $partner->getTypeLabel() }})
                            </option>
                            @endforeach
                        </select>
                        @error('partner_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nomor Telepon</label>
                        <input type="text" name="phone" class="form-control" 
                               value="{{ old('phone', $user->phone) }}" placeholder="08xxxxxxxxxx">
                        @error('phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="is_active" class="form-select" required>
                            <option value="1" {{ old('is_active', $user->is_active) == 1 ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('is_active', $user->is_active) == 0 ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('is_active')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                    <a href="{{ route('admin.users.show', $user) }}" class="btn" style="background: var(--secondary); color: white;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('roleSelect').addEventListener('change', function() {
    const partnerField = document.getElementById('partnerField');
    const mitraRoles = ['mitra_middlestream', 'mitra_downstream'];
    
    if (mitraRoles.includes(this.value)) {
        partnerField.style.display = 'block';
    } else {
        partnerField.style.display = 'none';
    }
});
</script>
@endsection