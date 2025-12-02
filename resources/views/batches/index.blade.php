@extends('layouts.app')

@section('title', 'Daftar Batch - Traceability LTJ')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --primary: #3e5c74;
        --primary-dark: #2d4454;
        --primary-light: #e8eef3;
        --gold: #c5a572;
    }

    body {
        font-family: 'Poppins', system-ui, sans-serif;
        background: linear-gradient(135deg, var(--primary-light) 0%, #f5f8fa 100%);
        color: #2d4454;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 1.5rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-header h2 {
        font-weight: 700;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin: 0;
    }

    /* Card */
    .card {
        border-radius: 14px;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(10px);
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
        border: 1px solid rgba(62,92,116,0.08);
        margin-bottom: 1.5rem;
    }

    .card-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border-radius: 14px 14px 0 0 !important;
        padding: 1rem 1.5rem;
        font-weight: 600;
        border: none;
    }

    .card-body {
        padding: 1.5rem;
    }

    /* Form Elements */
    .form-label {
        font-weight: 600;
        color: var(--primary);
        font-size: 0.9rem;
        margin-bottom: 0.5rem;
    }

    .form-control, .form-select {
        border: 1px solid rgba(62,92,116,0.2);
        border-radius: 10px;
        padding: 0.65rem 1rem;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
    }

    .form-control:focus, .form-select:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(62,92,116,0.1);
        outline: none;
    }

    /* Grid Filter */
    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        align-items: end;
    }

    /* Buttons */
    .btn {
        padding: 0.65rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        font-family: 'Poppins', sans-serif;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(62,92,116,0.3);
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(62,92,116,0.4);
        color: white;
    }

    .btn-secondary {
        background: linear-gradient(135deg, #858796 0%, #9fa1b0 100%);
        color: white;
    }

    .btn-success {
        background: linear-gradient(135deg, #198754 0%, #4caf50 100%);
        color: white;
    }

    .btn-sm {
        padding: 0.45rem 1rem;
        font-size: 0.875rem;
    }

    /* Table */
    .table {
        width: 100%;
        margin: 0;
    }

    .table thead th {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        font-weight: 600;
        font-size: 0.85rem;
        padding: 1rem 0.75rem;
        border: none;
        text-align: center;
        white-space: nowrap;
    }

    .table thead th:first-child {
        border-radius: 10px 0 0 0;
    }

    .table thead th:last-child {
        border-radius: 0 10px 0 0;
    }

    .table tbody td {
        padding: 1rem 0.75rem;
        vertical-align: middle;
        border-bottom: 1px solid rgba(62,92,116,0.08);
        text-align: center;
    }

    .table tbody tr:hover {
        background: rgba(62,92,116,0.03);
    }

    /* Badge */
    .badge {
        padding: 0.4rem 0.75rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.75rem;
    }

    .badge-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        color: white;
    }

    .badge-success {
        background: linear-gradient(135deg, #198754 0%, #4caf50 100%);
        color: white;
    }

    .badge-warning {
        background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
        color: #000;
    }

    .badge-info {
        background: linear-gradient(135deg, #0dcaf0 0%, #6eb5c0 100%);
        color: white;
    }

    .badge-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
    }

    .badge-secondary {
        background: linear-gradient(135deg, #6c757d 0%, #858796 100%);
        color: white;
    }

    /* Pagination */
    .pagination {
        margin-top: 1.5rem;
        justify-content: center;
    }

    .pagination .page-link {
        border: 1px solid rgba(62,92,116,0.2);
        color: var(--primary);
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border-radius: 8px;
        font-weight: 600;
    }

    .pagination .page-link:hover {
        background: var(--primary);
        color: white;
        border-color: var(--primary);
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border-color: var(--primary);
    }
</style>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="bi bi-box-seam me-2"></i>Manajemen Batch</h2>
        @if(auth()->user()->canCreateParentBatch())
        <a href="{{ route('batches.create') }}" class="btn btn-success">
            <i class="bi bi-plus-circle me-1"></i>Buat Batch Baru
        </a>
        @endif
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Filter Card -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-funnel-fill me-2"></i>Filter & Pencarian
        </div>
        <div class="card-body">
            <form action="{{ route('batches.index') }}" method="GET">
                <div class="filter-grid">
                    <!-- Search Input -->
                    <div class="form-group">
                        <label for="search" class="form-label">Cari Batch / Lot / Kontainer</label>
                        <input type="text" name="search" id="search" class="form-control" 
                               value="{{ request('search') }}" 
                               placeholder="Contoh: B-2025...">
                    </div>

                    <!-- Filter Product Code -->
                    <div class="form-group">
                        <label for="product_code_id" class="form-label">Filter Produk</label>
                        <select name="product_code_id" id="product_code_id" class="form-select">
                            <option value="">Semua Produk</option>
                            @foreach($productcodes as $product)
                                <option value="{{ $product->id }}" {{ request('product_code_id') == $product->id ? 'selected' : '' }}>
                                    {{ $product->code }} - {{ $product->description }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Status -->
                    <div class="form-group">
                        <label for="status" class="form-label">Filter Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Aktif</option>
                            <option value="in_transit" {{ request('status') == 'in_transit' ? 'selected' : '' }}>Dalam Pengiriman</option>
                            <option value="delivered" {{ request('status') == 'delivered' ? 'selected' : '' }}>Terkirim</option>
                            <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Diproses</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                    </div>

                    <div class="form-group">
                        <a href="{{ route('batches.index') }}" class="btn btn-secondary w-100">
                            <i class="bi bi-x-circle me-1"></i>Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Batch List Card -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-list-ul me-2"></i>Daftar Batch ({{ $batches->total() }} total)
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Batch Code</th>
                            <th>Lot Number</th>
                            <th>Produk</th>
                            <th>Tonase</th>
                            <th>Konsentrat</th>
                            <th>Massa LTJ (Kg)</th>
                            <th>Status</th>
                            <th>Pemilik</th>
                            <th>Lokasi</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td>
                                <a href="{{ route('batches.show', $batch->id) }}" 
                                   style="color: var(--primary); font-weight: 600; text-decoration: none;">
                                    {{ $batch->batch_code }}
                                </a>
                            </td>
                            <td style="font-weight: 500;">{{ $batch->lot_number }}</td>
                            <td>
                                <span style="font-size: 0.85rem; color: rgba(62,92,116,0.7);">
                                    {{ $batch->productCode->code ?? '-' }}
                                </span>
                            </td>
                            <td style="font-weight: 600;">{{ number_format($batch->tonase, 2) }}</td>
                            <td style="font-weight: 600;">{{ number_format($batch->konsentrat_persen, 2) }}%</td>
                            <td style="font-weight: 700; color: var(--gold);">
                                {{ number_format($batch->massa_ltj_kg, 2) }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $batch->status == 'active' ? 'success' : ($batch->status == 'in_transit' ? 'info' : 'secondary') }}">
                                    {{ $batch->getStatusLabel() }}
                                </span>
                            </td>
                            <td style="font-size: 0.85rem;">
                                {{ $batch->currentPartner->name ?? '-' }}
                            </td>
                            <td style="font-size: 0.85rem; max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $batch->origin_location ?? '-' }}
                            </td>
                            <td style="font-size: 0.85rem; white-space: nowrap;">
                                {{ $batch->created_at->format('d/m/Y') }}
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('batches.show', $batch->id) }}" 
                                       class="btn btn-sm btn-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($batch->canBeEdited() && auth()->user()->canCreateParentBatch())
                                    <a href="{{ route('batches.edit', $batch->id) }}" 
                                       class="btn btn-sm btn-secondary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" style="text-align: center; padding: 3rem; color: rgba(62,92,116,0.5);">
                                <i class="bi bi-inbox" style="font-size: 3rem; display: block; margin-bottom: 1rem;"></i>
                                <p style="margin: 0;">Tidak ada data batch yang ditemukan.</p>
                                @if(auth()->user()->canCreateParentBatch())
                                <a href="{{ route('batches.create') }}" class="btn btn-success mt-3">
                                    <i class="bi bi-plus-circle me-1"></i>Buat Batch Pertama
                                </a>
                                @endif
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($batches->hasPages())
            <div class="d-flex justify-content-center mt-4">
                {{ $batches->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
// Auto-hide alerts setelah 5 detik
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        });
    }, 5000);
});
</script>
@endsection