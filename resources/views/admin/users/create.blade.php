@extends('layouts.app')

@section('title', 'Create New User - Course System')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --bg: #cfeeff;
        --card-radius: 14px;
        --glass: rgba(255,255,255,0.98);
        --accent: #0d6efd;
    }

    body {
        font-family: 'Poppins', system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(180deg, var(--bg) 0%, #eaf9ff 100%);
        min-height: 100vh;
        color: #0b2545;
    }

    .page-header {
        margin-bottom: 1.5rem;
        animation: fadeInDown 0.6s ease;
    }

    .page-header h2 {
        font-family: 'Poppins', inherit;
        font-weight: 700;
        color: #0b2545;
        margin: 0;
        letter-spacing: -0.5px;
    }

    .card {
        border-radius: var(--card-radius);
        background: var(--glass);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        box-shadow: 0 10px 28px rgba(11,37,69,0.08);
        border: 1px solid rgba(11,37,69,0.04);
        animation: fadeInUp 0.7s ease;
    }

    .form-group {
        margin-bottom: 1.25rem;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #0b2545;
        font-size: 0.9rem;
        font-family: 'Poppins', inherit;
    }

    .form-control, .form-select {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 1px solid rgba(11,37,69,0.15);
        border-radius: 10px;
        font-size: 1rem;
        font-family: 'Poppins', inherit;
        transition: all 0.3s ease;
        background: rgba(255,255,255,0.9);
    }

    .form-control:focus, .form-select:focus {
        outline: none;
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
        background: white;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
    }

    .btn {
        padding: 0.65rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-family: 'Poppins', inherit;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(13,110,253,0.2);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(13,110,253,0.3);
    }

    .btn-secondary {
        background: linear-gradient(135deg, #858796 0%, #9fa1b0 100%);
        color: white;
    }

    .btn-secondary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(133,135,150,0.3);
    }

    .error-message {
        color: #dc3545;
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: block;
        font-family: 'Poppins', inherit;
    }

    .help-text {
        color: rgba(11,37,69,0.6);
        font-size: 0.85rem;
        margin-top: 0.25rem;
        display: block;
    }

    .form-check {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1.5rem;
        padding: 0.75rem;
        background: rgba(13,110,253,0.05);
        border-radius: 10px;
        border: 1px solid rgba(13,110,253,0.1);
    }

    .form-check input[type="checkbox"] {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .form-check label {
        margin: 0;
        font-weight: 500;
        cursor: pointer;
        font-family: 'Poppins', inherit;
    }

    .action-buttons {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
    }

    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .required-mark {
        color: #dc3545;
        margin-left: 2px;
    }

    @media (max-width: 768px) {
        .form-grid-2 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="container py-4">
    <div class="page-header">
        <h2><i class="bi bi-person-plus me-2"></i>Create New User</h2>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.users.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="name">
                        Full Name <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="name" name="name" class="form-control" 
                           value="{{ old('name') }}" required placeholder="Enter full name">
                    @error('name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="email">
                            Email <span class="required-mark">*</span>
                        </label>
                        <input type="email" id="email" name="email" class="form-control" 
                               value="{{ old('email') }}" required placeholder="user@example.com">
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="{{ old('username') }}" placeholder="Optional username">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="role">
                        Role <span class="required-mark">*</span>
                    </label>
                    <select id="role" name="role" class="form-select" required onchange="toggleFields()">
                        <option value="">-- Select Role --</option>
                        <option value="super_admin">Super Admin</option>
                        <option value="admin">Admin PT Timah</option>
                        <option value="operator">Operator</option>
                        <option value="mitra_middlestream">Mitra Middlestream</option>
                        <option value="mitra_downstream">Mitra Downstream</option>
                        <option value="auditor">Auditor</option>
                    </select>
                    @error('role')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div id="internal_fields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="nomor_pegawai">
                            Employee Number/NIP <span class="required-mark">*</span>
                        </label>
                        <input type="text" id="nomor_pegawai" name="nomor_pegawai" class="form-control" 
                               value="{{ old('nomor_pegawai') }}" placeholder="Enter employee number">
                        @error('nomor_pegawai')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                
                <div id="mitra_fields" style="display: none;">
                    <div class="form-group">
                        <label class="form-label" for="partner_id">
                            Partner/Company <span class="required-mark">*</span>
                        </label>
                        <select id="partner_id" name="partner_id" class="form-select">
                            <option value="">-- Select Partner --</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}">{{ $partner->name }} ({{ ucfirst($partner->type) }})</option>
                            @endforeach
                        </select>
                        @error('partner_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="verification_doc">Company Verification Document</label>
                        <input type="file" id="verification_doc" name="verification_doc" class="form-control" 
                               accept=".pdf,.png,.jpg">
                        <span class="help-text">Format: PDF, PNG, JPG (Max 2MB)</span>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="phone">Phone Number</label>
                    <input type="text" id="phone" name="phone" class="form-control" 
                           value="{{ old('phone') }}" placeholder="+62xxx">
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="password">
                            Password <span class="required-mark">*</span>
                        </label>
                        <input type="password" id="password" name="password" class="form-control" 
                               required placeholder="Enter password">
                        <span class="help-text">Minimum 8 characters</span>
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">
                            Confirm Password <span class="required-mark">*</span>
                        </label>
                        <input type="password" id="password_confirmation" name="password_confirmation" 
                               class="form-control" required placeholder="Re-enter password">
                    </div>
                </div>
                
                <div class="form-check">
                    <input type="checkbox" id="enable_2fa" name="enable_2fa" value="1">
                    <label for="enable_2fa">
                        <i class="bi bi-shield-check me-1"></i>
                        Enable Two-Factor Authentication (2FA)
                    </label>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Create User
                    </button>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleFields() {
    const role = document.getElementById('role').value;
    const internalRoles = ['super_admin', 'admin', 'operator'];
    const mitraRoles = ['mitra_middlestream', 'mitra_downstream'];
    
    const internalFields = document.getElementById('internal_fields');
    const mitraFields = document.getElementById('mitra_fields');
    
    if (internalRoles.includes(role)) {
        internalFields.style.display = 'block';
        mitraFields.style.display = 'none';
    } else if (mitraRoles.includes(role)) {
        internalFields.style.display = 'none';
        mitraFields.style.display = 'block';
    } else {
        internalFields.style.display = 'none';
        mitraFields.style.display = 'none';
    }
}
</script>
@endpush
@endsection