@extends('layouts.app')

@section('title', 'Manajemen Product Code')

@section('content')
<div class="container-fluid py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-box" style="color: var(--primary);"></i>
            Manajemen Product Code
        </h1>
        <a href="{{ route('admin.product-codes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i>Tambah Product Code
        </a>
    </div>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.product-codes.index') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-5">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari kode atau deskripsi..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="stage" class="form-select">
                            <option value="">Semua Stage</option>
                            <option value="RAW" {{ request('stage') == 'RAW' ? 'selected' : '' }}>Bahan Mentah (RAW)</option>
                            <option value="MID" {{ request('stage') == 'MID' ? 'selected' : '' }}>Hasil Pengolahan (MID)</option>
                            <option value="FINAL" {{ request('stage') == 'FINAL' ? 'selected' : '' }}>Produk Akhir (FINAL)</option>
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
                            <th>Kode</th>
                            <th>Deskripsi</th>
                            <th>Stage</th>
                            <th>Spesifikasi</th>
                            <th>Jumlah Batch</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productCodes as $code)
                        <tr>
                            <td><strong style="color: var(--primary); font-size: 1.1rem;">{{ $code->code }}</strong></td>
                            <td>{{ $code->description }}</td>
                            <td>
                                @if($code->stage == 'RAW')
                                <span class="badge badge-secondary">Bahan Mentah</span>
                                @elseif($code->stage == 'MID')
                                <span class="badge badge-info">Hasil Pengolahan</span>
                                @else
                                <span class="badge badge-success">Produk Akhir</span>
                                @endif
                            </td>
                            <td>{{ Str::limit($code->specifications, 40) }}</td>
                            <td>{{ $code->batches_count }}</td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <a href="{{ route('admin.product-codes.show', $code) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.product-codes.edit', $code) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($code->batches_count == 0)
                                    <form action="{{ route('admin.product-codes.destroy', $code) }}" method="POST" 
                                          onsubmit="return confirm('Yakin ingin menghapus product code ini?')" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada data product code</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $productCodes->links() }}
    </div>
</div>
@endsection