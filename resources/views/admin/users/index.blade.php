@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="container-fluid py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-people" style="color: var(--primary);"></i>
            Manajemen User
        </h1>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah User
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari nama atau email..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="role" class="form-select">
                            <option value="">Semua Role</option>
                            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="operator" {{ request('role') == 'operator' ? 'selected' : '' }}>Operator</option>
                            <option value="mitra_upstream" {{ request('role') == 'mitra_upstream' ? 'selected' : '' }}>Mitra Upstream</option>
                            <option value="mitra_middlestream" {{ request('role') == 'mitra_middlestream' ? 'selected' : '' }}>Mitra Middlestream</option>
                            <option value="mitra_downstream" {{ request('role') == 'mitra_downstream' ? 'selected' : '' }}>Mitra Downstream</option>
                            <option value="end_user" {{ request('role') == 'end_user' ? 'selected' : '' }}>End User</option>
                            <option value="auditor" {{ request('role') == 'auditor' ? 'selected' : '' }}>Auditor</option>
                            <option value="g_bim" {{ request('role') == 'g_bim' ? 'selected' : '' }}>G:BIM</option>
                            <option value="g_esdm" {{ request('role') == 'g_esdm' ? 'selected' : '' }}>G:ESDM</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="partner_id" class="form-select">
                            <option value="">Semua Partner</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Partner</th>
                            <th>Status</th>
                            <th>Terdaftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->name }}</strong>
                                @if($user->id === auth()->id())
                                <span class="badge badge-sm badge-info ms-1">You</span>
                                @endif
                            </td>
                            <td>{{ $user->email }}</td>
                            <td><span class="badge badge-secondary">{{ $user->getRoleLabel() }}</span></td>
                            <td>{{ $user->partner->name ?? '-' }}</td>
                            <td>
                                @if($user->is_active)
                                <span class="badge badge-success">Aktif</span>
                                @else
                                <span class="badge badge-danger">Tidak Aktif</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d M Y') }}</td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.users.toggle-status', $user) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" 
                                                title="{{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                            <i class="bi {{ $user->is_active ? 'bi-toggle-on' : 'bi-toggle-off' }}"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Tidak ada data user</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="mt-3">
        {{ $users->appends(request()->query())->links() }}
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Custom Pagination Styling - Konsisten dengan Design System */
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        justify-content: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .page-item {
        margin: 0;
    }
    
    .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0.6rem 1rem;
        text-decoration: none;
        background: var(--glass);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
        border: 1px solid rgba(11,37,69,0.1);
        color: var(--primary);
        font-weight: 600;
        font-size: 0.95rem;
        border-radius: 10px;
        transition: all 0.3s ease;
        font-family: 'Poppins', inherit;
    }
    
    .page-link:hover {
        background: rgba(13,110,253,0.08);
        border-color: var(--primary);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13,110,253,0.15);
    }
    
    .page-item.active .page-link {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        color: var(--white);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(13,110,253,0.3);
    }
    
    .page-item.disabled .page-link {
        color: rgba(11,37,69,0.4);
        background: rgba(11,37,69,0.05);
        border-color: rgba(11,37,69,0.08);
        pointer-events: none;
        opacity: 0.6;
    }

    /* Responsive untuk mobile */
    @media (max-width: 576px) {
        .pagination {
            gap: 0.25rem;
        }
        
        .page-link {
            min-width: 36px;
            height: 36px;
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
        }
    }
</style>
@endpush