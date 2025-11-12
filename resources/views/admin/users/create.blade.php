@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="container py-4" style="max-width: 800px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-person-plus" style="color: var(--primary);"></i>
        Tambah User Baru
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name') }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" 
                               value="{{ old('email') }}" required>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                        <small class="text-muted">Minimal 8 karakter</small>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Konfirmasi Password *</label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Role *</label>
                        <select name="role" id="roleSelect" class="form-select" required>
                            <option value="">-- Pilih Role --</option>
                            <option value="super_admin" {{ old('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin PT Timah</option>
                            <option value="operator" {{ old('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                            <option value="mitra_middlestream" {{ old('role') == 'mitra_middlestream' ? 'selected' : '' }}>Mitra Middlestream</option>
                            <option value="mitra_downstream" {{ old('role') == 'mitra_downstream' ? 'selected' : '' }}>Mitra Downstream</option>
                            <option value="g_bim" {{ old('role') == 'g_bim' ? 'selected' : '' }}>Government (BIM)</option>
                            <option value="g_esdm" {{ old('role') == 'g_esdm' ? 'selected' : '' }}>Government (ESDM)</option>
                        </select>
                        @error('role')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3" id="partnerField" style="display: none;">
                        <label class="form-label">Partner *</label>
                        <select name="partner_id" class="form-select">
                            <option value="">-- Pilih Partner --</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partner_id') == $partner->id ? 'selected' : '' }}>
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
                               value="{{ old('phone') }}" placeholder="08xxxxxxxxxx">
                        @error('phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                    <a href="{{ route('admin.users.index') }}" class="btn" style="background: var(--secondary); color: white;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i>Simpan User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Show/hide partner field based on role
document.getElementById('roleSelect').addEventListener('change', function() {
    const partnerField = document.getElementById('partnerField');
    const mitraRoles = ['mitra_middlestream', 'mitra_downstream'];
    
    if (mitraRoles.includes(this.value)) {
        partnerField.style.display = 'block';
    } else {
        partnerField.style.display = 'none';
    }
});

// Trigger on load if old value exists
if (document.getElementById('roleSelect').value) {
    document.getElementById('roleSelect').dispatchEvent(new Event('change'));
}
</script>
@endsection