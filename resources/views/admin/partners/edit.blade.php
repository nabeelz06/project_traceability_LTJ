@extends('layouts.app')

@section('title', 'Edit Mitra')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-pencil-square" style="color: var(--primary);"></i>
        Edit Mitra: {{ $partner->name }}
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.partners.update', $partner) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <h6 class="text-muted mb-3">Informasi Mitra</h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nama Mitra *</label>
                        <input type="text" name="name" class="form-control" 
                               value="{{ old('name', $partner->name) }}" required>
                        @error('name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipe Mitra *</label>
                        <select name="type" class="form-select" required>
                            <option value="middlestream" {{ old('type', $partner->type) == 'middlestream' ? 'selected' : '' }}>
                                Pengolahan (Middlestream)
                            </option>
                            <option value="downstream" {{ old('type', $partner->type) == 'downstream' ? 'selected' : '' }}>
                                Industri Pengguna (Downstream)
                            </option>
                        </select>
                        @error('type')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Alamat Lengkap *</label>
                        <textarea name="address" class="form-control" rows="3" required>{{ old('address', $partner->address) }}</textarea>
                        @error('address')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="text-muted mb-3">Data PIC (Person In Charge)</h6>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nama PIC *</label>
                        <input type="text" name="pic_name" class="form-control" 
                               value="{{ old('pic_name', $partner->pic_name) }}" required>
                        @error('pic_name')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nomor Telepon PIC *</label>
                        <input type="text" name="pic_phone" class="form-control" 
                               value="{{ old('pic_phone', $partner->pic_phone) }}" required>
                        @error('pic_phone')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">Email PIC *</label>
                        <input type="email" name="pic_email" class="form-control" 
                               value="{{ old('pic_email', $partner->pic_email) }}" required>
                        @error('pic_email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <hr class="my-4">

                <h6 class="text-muted mb-3">Dokumen & Konfigurasi</h6>
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <label class="form-label">Dokumen Verifikasi</label>
                        @if($partner->verification_doc)
                        <div class="mb-2">
                            <a href="{{ Storage::url($partner->verification_doc) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark-pdf me-1"></i>Lihat Dokumen Saat Ini
                            </a>
                        </div>
                        @endif
                        <input type="file" name="verification_doc" class="form-control" accept=".pdf,.jpg,.png">
                        <small class="text-muted">Upload file baru untuk mengganti dokumen</small>
                        @error('verification_doc')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem;">
                    <a href="{{ route('admin.partners.show', $partner) }}" class="btn" style="background: var(--secondary); color: white;">
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
@endsection