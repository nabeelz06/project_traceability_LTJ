@extends('layouts.app')

@section('title', 'Manajemen Mitra')

@section('content')
<div class="container-fluid py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-building" style="color: var(--primary);"></i>
            Manajemen Mitra
        </h1>
        <a href="{{ route('admin.partners.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Mitra
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.partners.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari nama mitra..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">Semua Tipe</option>
                            <option value="upstream" {{ request('type') == 'upstream' ? 'selected' : '' }}>Upstream</option>
                            <option value="middlestream" {{ request('type') == 'middlestream' ? 'selected' : '' }}>Middlestream</option>
                            <option value="downstream" {{ request('type') == 'downstream' ? 'selected' : '' }}>Downstream</option>
                            <option value="end_user" {{ request('type') == 'end_user' ? 'selected' : '' }}>End User</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
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
                            <th>Nama Mitra</th>
                            <th>Tipe</th>
                            <th>PIC</th>
                            <th>Kontak</th>
                            <th>Status</th>
                            <th>User</th>
                            <th>Batch</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                        <tr>
                            <td>
                                <strong>{{ $partner->name }}</strong>
                                <br><small class="text-muted">{{ Str::limit($partner->address, 40) }}</small>
                            </td>
                            <td><span class="badge badge-info">{{ $partner->getTypeLabel() }}</span></td>
                            <td>
                                {{ $partner->pic_name }}
                                <br><small class="text-muted">{{ $partner->pic_phone }}</small>
                            </td>
                            <td>{{ $partner->pic_email }}</td>
                            <td>
                                @if($partner->status == 'approved')
                                <span class="badge badge-success">Approved</span>
                                @elseif($partner->status == 'pending')
                                <span class="badge badge-warning">Pending</span>
                                @else
                                <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td>{{ $partner->users_count }}</td>
                            <td>{{ $partner->batches_count }}</td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="{{ route('admin.partners.show', $partner) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.partners.edit', $partner) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($partner->status == 'pending')
                                    <form action="{{ route('admin.partners.approve', $partner) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Approve">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Tidak ada data mitra</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    @if($partners->hasPages())
    <div class="mt-3">
        {{ $partners->appends(request()->query())->links() }}
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