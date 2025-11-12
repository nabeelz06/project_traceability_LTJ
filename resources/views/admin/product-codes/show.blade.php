@extends('layouts.app')

@section('title', 'Detail Product Code')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-box" style="color: var(--primary);"></i>
            Product Code: {{ $productCode->code }}
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('admin.product-codes.index') }}" class="btn" style="background: var(--secondary); color: white;">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('admin.product-codes.edit', $productCode) }}" class="btn btn-primary">
                <i class="bi bi-pencil me-1"></i>Edit
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small">KODE</label>
                    <p class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: var(--primary);">{{ $productCode->code }}</p>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small">STAGE</label>
                    <p class="mb-0">
                        @if($productCode->stage == 'RAW')
                        <span class="badge badge-secondary">Bahan Mentah</span>
                        @elseif($productCode->stage == 'MID')
                        <span class="badge badge-info">Hasil Pengolahan</span>
                        @else
                        <span class="badge badge-success">Produk Akhir</span>
                        @endif
                    </p>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small">JUMLAH BATCH</label>
                    <p class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: var(--info);">{{ $productCode->batches_count }}</p>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label fw-bold text-muted small">STATUS</label>
                    <p class="mb-0">
                        @if($productCode->batches_count > 0)
                        <span class="badge badge-success">Aktif Digunakan</span>
                        @else
                        <span class="badge badge-secondary">Belum Digunakan</span>
                        @endif
                    </p>
                </div>

                <div class="col-12 mb-3">
                    <label class="form-label fw-bold text-muted small">DESKRIPSI</label>
                    <p class="mb-0" style="font-size: 1.1rem;">{{ $productCode->description }}</p>
                </div>

                @if($productCode->specifications)
                <div class="col-12 mb-3">
                    <label class="form-label fw-bold text-muted small">SPESIFIKASI</label>
                    <p class="mb-0" style="white-space: pre-line;">{{ $productCode->specifications }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if($productCode->batches_count == 0)
    <div class="card mt-3">
        <div class="card-body text-center py-4">
            <i class="bi bi-inbox" style="font-size: 3rem; color: rgba(11,37,69,0.3);"></i>
            <h5 class="mt-3 text-muted">Belum Ada Batch</h5>
            <p class="text-muted">Product code ini belum pernah digunakan untuk batch manapun.</p>
            <form action="{{ route('admin.product-codes.destroy', $productCode) }}" method="POST" 
                  onsubmit="return confirm('Yakin ingin menghapus product code ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger">
                    <i class="bi bi-trash me-1"></i>Hapus Product Code
                </button>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection