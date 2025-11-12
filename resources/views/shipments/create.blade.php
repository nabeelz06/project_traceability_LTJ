@extends('layouts.app')

@section('title', 'Jadwalkan Pengiriman')

@section('content')
<div class="container py-4" style="max-width: 900px;">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-truck" style="color: var(--primary);"></i>
        Jadwalkan Pengiriman Baru
    </h1>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('shipments.store') }}" method="POST">
                @csrf

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Batch yang Akan Dikirim *</label>
                        <select name="batch_id" id="batchSelect" class="form-select" required>
                            <option value="">-- Pilih Batch --</option>
                            @foreach($batches as $batch)
                            <option value="{{ $batch->id }}" {{ old('batch_id') == $batch->id ? 'selected' : '' }}
                                    data-code="{{ $batch->batch_code }}"
                                    data-product="{{ $batch->product_code }}"
                                    data-weight="{{ $batch->formatted_weight }}">
                                {{ $batch->batch_code }} - {{ $batch->product_code }} ({{ $batch->formatted_weight }})
                            </option>
                            @endforeach
                        </select>
                        @error('batch_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tujuan (Partner) *</label>
                        <select name="destination_partner_id" class="form-select" required>
                            <option value="">-- Pilih Partner Tujuan --</option>
                            @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('destination_partner_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }} ({{ $partner->getTypeLabel() }})
                            </option>
                            @endforeach
                        </select>
                        @error('destination_partner_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Operator yang Ditugaskan</label>
                        <select name="assigned_operator_id" class="form-select">
                            <option value="">-- Pilih Operator --</option>
                            @foreach($operators as $operator)
                            <option value="{{ $operator->id }}" {{ old('assigned_operator_id') == $operator->id ? 'selected' : '' }}>
                                {{ $operator->name }}
                            </option>
                            @endforeach
                        </select>
                        <small class="text-muted">Opsional - dapat ditentukan kemudian</small>
                        @error('assigned_operator_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Jadwal Pengiriman *</label>
                        <input type="datetime-local" name="scheduled_at" class="form-control" 
                               value="{{ old('scheduled_at') }}" required>
                        @error('scheduled_at')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Info Kendaraan</label>
                        <input type="text" name="vehicle_info" class="form-control" 
                               value="{{ old('vehicle_info') }}" 
                               placeholder="Contoh: Truk B 1234 XYZ">
                        @error('vehicle_info')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-12 mb-3">
                        <label class="form-label">Catatan Pengiriman</label>
                        <textarea name="notes" class="form-control" rows="3" 
                                  placeholder="Catatan khusus untuk pengiriman ini...">{{ old('notes') }}</textarea>
                        @error('notes')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <!-- Batch Info Preview -->
                <div id="batchPreview" style="display: none;" class="p-3 mb-3" style="background: #f8f9fa; border-radius: 8px;">
                    <h6 class="text-muted mb-2">Preview Batch yang Dipilih</h6>
                    <div id="batchDetails"></div>
                </div>

                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                    <a href="{{ route('shipments.index') }}" class="btn" style="background: var(--secondary); color: white;">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-calendar-check me-1"></i>Jadwalkan Pengiriman
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('batchSelect').addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const preview = document.getElementById('batchPreview');
    const details = document.getElementById('batchDetails');
    
    if (this.value) {
        const code = option.getAttribute('data-code');
        const product = option.getAttribute('data-product');
        const weight = option.getAttribute('data-weight');
        
        details.innerHTML = `
            <p class="mb-1"><strong>Batch Code:</strong> ${code}</p>
            <p class="mb-1"><strong>Product:</strong> ${product}</p>
            <p class="mb-0"><strong>Berat:</strong> ${weight}</p>
        `;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});
</script>
@endsection