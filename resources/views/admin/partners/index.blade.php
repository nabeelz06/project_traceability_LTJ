@extends('layouts.app')

@section('title', 'Partner Management - Course System')

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
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        margin-bottom: 1.5rem;
        animation: fadeInUp 0.7s ease;
    }

    .btn-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 10px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 12px rgba(13,110,253,0.2);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(13,110,253,0.3);
        background: linear-gradient(135deg, #0b5ed7 0%, #2a8fef 100%);
    }

    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    .form-control, .form-select {
        border-radius: 10px;
        border: 1px solid rgba(11,37,69,0.1);
        padding: 0.6rem 1rem;
        font-family: 'Poppins', inherit;
        transition: all 0.3s ease;
        background: rgba(255,255,255,0.9);
    }

    .form-control:focus, .form-select:focus {
        border-color: #0d6efd;
        box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15);
        outline: none;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.06);
        outline: 6px solid rgba(0,0,0,1);
        outline-offset: 0;
        border: 3px solid rgba(0,0,0,1);
    }

    .table {
        margin-bottom: 0;
        font-family: 'Poppins', inherit;
    }

    .table thead th {
        background: linear-gradient(90deg, #0b6edc 0%, #0d6efd 100%);
        color: #fff;
        font-weight: 700;
        padding: 1rem;
        border: none;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table tbody td {
        padding: 1rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(11,37,69,0.06);
        color: #0b2545;
    }

    .table tbody tr {
        transition: all 0.2s ease;
    }

    .table tbody tr:hover {
        background: rgba(13,110,253,0.05);
        transform: translateX(3px);
    }

    .badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #7be6ff 100%);
        color: white;
    }

    .badge-success {
        background: linear-gradient(135deg, #198754 0%, #4bd08f 100%);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ffd86b 100%);
        color: #000;
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #ff6b7a 100%);
        color: white;
    }

    .btn-action {
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-detail {
        background: linear-gradient(135deg, #36b9cc 0%, #5cd9eb 100%);
        color: white;
    }

    .btn-detail:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(54,185,204,0.3);
        color: white;
    }

    .btn-edit {
        background: linear-gradient(135deg, #4e73df 0%, #6c8ef7 100%);
        color: white;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(78,115,223,0.3);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #198754 0%, #4bd08f 100%);
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        border: none;
    }

    .btn-success:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(25,135,84,0.3);
    }

    .action-buttons {
        display: flex;
        gap: 0.4rem;
        flex-wrap: wrap;
    }

    .empty-state {
        text-align: center;
        padding: 3rem;
        color: rgba(11,37,69,0.4);
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        opacity: 0.3;
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

    .pic-info {
        font-size: 0.85rem;
    }

    .pic-phone {
        color: rgba(11,37,69,0.6);
        font-size: 0.8rem;
    }
</style>

<div class="container py-4">
    <div class="page-header">
        <h2>Partner Management</h2>
        <a href="{{ route('admin.partners.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Register New Partner
        </a>
    </div>

    {{-- Filters --}}
    <div class="card">
        <div class="card-body">
            <form method="GET" class="filter-form">
                <div>
                    <label class="form-label small fw-bold text-muted">TYPE</label>
                    <select name="type" class="form-select" onchange="this.form.submit()">
                        <option value="">All Types</option>
                        <option value="middlestream" {{ request('type') == 'middlestream' ? 'selected' : '' }}>Middlestream</option>
                        <option value="downstream" {{ request('type') == 'downstream' ? 'selected' : '' }}>Downstream</option>
                    </select>
                </div>

                <div>
                    <label class="form-label small fw-bold text-muted">STATUS</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-1"></i> Apply Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Company Name</th>
                            <th>Type</th>
                            <th>PIC</th>
                            <th>Status</th>
                            <th>Total Users</th>
                            <th>Total Batches</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($partners as $partner)
                        <tr>
                            <td><strong>{{ $partner->name }}</strong></td>
                            <td><span class="badge badge-info">{{ ucfirst($partner->type) }}</span></td>
                            <td>
                                <div class="pic-info">
                                    {{ $partner->pic_name }}
                                    <div class="pic-phone">{{ $partner->pic_phone }}</div>
                                </div>
                            </td>
                            <td>
                                @if($partner->status == 'approved')
                                    <span class="badge badge-success">Approved</span>
                                @elseif($partner->status == 'pending')
                                    <span class="badge badge-warning">Pending</span>
                                @else
                                    <span class="badge badge-danger">Rejected</span>
                                @endif
                            </td>
                            <td><strong>{{ $partner->users_count }}</strong></td>
                            <td><strong>{{ $partner->batches_count }}</strong></td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('admin.partners.show', $partner) }}" class="btn-action btn-detail">
                                        <i class="bi bi-eye me-1"></i> Detail
                                    </a>

                                    @if($partner->status == 'pending')
                                    <form action="{{ route('admin.partners.approve', $partner) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-success">
                                            <i class="bi bi-check-circle me-1"></i> Approve
                                        </button>
                                    </form>
                                    @endif

                                    <a href="{{ route('admin.partners.edit', $partner) }}" class="btn-action btn-edit">
                                        <i class="bi bi-pencil me-1"></i> Edit
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h6>No partner data available</h6>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $partners->links() }}
            </div>
        </div>
    </div>
</div>
@endsection