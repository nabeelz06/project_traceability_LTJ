@extends('layouts.app')

@section('title', 'Daftar Batch')

@section('content')
<h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700; display: flex; align-items: center; gap: 0.75rem;">
    <i class="bi bi-box-seam" style="font-size: 1.5rem; color: var(--primary);"></i>
    Manajemen Batch
</h1>

<div class="card">
    <div class="card-header">
        <i class="bi bi-funnel-fill me-2"></i>Filter & Pencarian
    </div>
    <div class="card-body">
        <form action="{{ route('batches.index') }}" method="GET">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.5rem; align-items: flex-end;">
                
                <div class="form-group" style="margin-bottom: 0;">
                    <label for="search" class="form-label">Cari Batch / Lot / Kontainer</label>
                    <input type="text" name="search" id="search" class="form-control" value="{{ request('search') }}" placeholder="Contoh: B-2025...">
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="product_code" class="form-label">Filter Produk</label>
                    <select name="product_code" id="product_code" class="form-select">
                        <option value="">Semua Produk</option>
                        @foreach($productcodes as $product)
                            <option value="{{ $product->code }}" {{ request('product_code') == $product->code ? 'selected' : '' }}>
                                {{ $product->code }} - {{ $product->description }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label for="partner_id" class="form-label">Filter Mitra Pemilik</label>
                    <select name="partner_id" id="partner_id" class="form-select">
                        <option value="">Semua Mitra</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ request('partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-search me-1"></i>Cari
                    </button>
                    <a href="{{ route('batches.index') }}" class="btn" style="background: var(--secondary); color: white; width: 100%;">Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div style="margin-top: 1.5rem; margin-bottom: 1.5rem; text-align: right;">
    <a href="{{ route('batches.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill me-1"></i>Buat Batch Induk Baru
    </a>
</div>

<div class="card">
    <div class="card-body" style="padding: 0;">
        <table class="table">
            <thead>
                <tr>
                    <th>Batch Code</th>
                    <th>Product Code</th>
                    <th>Status</th>
                    <th>Berat Saat Ini</th>
                    <th>Pemilik</th>
                    <th>Tgl Dibuat</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($batches as $batch)
                    <tr>
                        <td>
                            <a href="{{ route('batches.show', $batch->id) }}" style="font-weight: 700; color: var(--primary); text-decoration: none;">
                                {{ $batch->batch_code }}
                            </a>
                            <div style="font-size: 0.85rem; color: var(--secondary);">Lot: {{ $batch->lot_number ?? 'N/A' }}</div>
                        </td>
                        <td>{{ $batch->product_code }}</td>
                        <td>
                            @php
                                $badgeClass = 'badge-secondary';
                                if (in_array($batch->status, ['shipped', 'in_transit'])) $badgeClass = 'badge-info';
                                elseif ($batch->status === 'received') $badgeClass = 'badge-primary';
                                elseif ($batch->status === 'processed') $badgeClass = 'badge-warning';
                                elseif ($batch->status === 'delivered') $badgeClass = 'badge-success';
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ ucfirst($batch->status) }}</span>
                        </td>
                        <td>{{ $batch->current_weight }} {{ $batch->weight_unit }}</td>
                        <td>{{ $batch->currentPartner->name ?? 'PT Timah' }}</td>
                        <td>{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('batches.show', $batch->id) }}" class="btn" style="background: var(--info); color: white; padding: 0.4rem 0.8rem; font-size: 0.8rem;">
                                <i class="bi bi-eye-fill"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 3rem; color: var(--secondary);">
                            <i class="bi bi-inbox-fill" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                            Tidak ada data batch ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($batches->hasPages())
        <div style="padding: 1.5rem; border-top: 1px solid rgba(11,37,69,0.06);">
            {{ $batches->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection

@push('styles')
<style>
    /* Kustomisasi Paginasi agar sesuai dengan UI */
    .pagination {
        display: flex;
        list-style: none;
        padding: 0;
        margin: 0;
        justify-content: center;
    }
    .page-item {
        margin: 0 0.25rem;
    }
    .page-link {
        display: block;
        padding: 0.6rem 1rem;
        text-decoration: none;
        background: var(--glass);
        border: 1px solid rgba(11,37,69,0.1);
        color: var(--primary);
        font-weight: 600;
        border-radius: 10px;
        transition: all 0.3s ease;
    }
    .page-link:hover {
        background: rgba(13,110,253,0.08);
        border-color: var(--primary);
    }
    .page-item.active .page-link {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        color: var(--white);
        border-color: var(--primary);
        box-shadow: 0 4px 12px rgba(13,110,253,0.2);
    }
    .page-item.disabled .page-link {
        color: var(--secondary);
        background: rgba(11,37,69,0.05);
        pointer-events: none;
    }
</style>
@endpush