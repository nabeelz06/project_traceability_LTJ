@extends('layouts.app')

@section('title', 'Create Parent Batch - Course System')

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

    textarea.form-control {
        resize: vertical;
        min-height: 80px;
    }

    .form-grid-2 {
        display: grid;
        grid-template-columns: 2fr 1fr;
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
        <h2><i class="bi bi-box-seam me-2"></i>Create New Parent Batch</h2>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form action="{{ route('batches.store') }}" method="POST">
                @csrf
                
                <div class="form-group">
                    <label class="form-label" for="product_code_id">
                        Product Code <span class="required-mark">*</span>
                    </label>
                    <select id="product_code_id" name="product_code_id" class="form-select" required>
                        <option value="">-- Select Product Code --</option>
                        @foreach($productCodes as $code)
                        <option value="{{ $code->id }}" {{ old('product_code_id') == $code->id ? 'selected' : '' }}>
                            {{ $code->code }} - {{ $code->description }}
                        </option>
                        @endforeach
                    </select>
                    @error('product_code_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="estimated_weight">
                            Estimated Quantity/Weight <span class="required-mark">*</span>
                        </label>
                        <input type="number" id="estimated_weight" name="estimated_weight" class="form-control" 
                               step="0.01" min="0" value="{{ old('estimated_weight') }}" 
                               required placeholder="Enter weight/quantity">
                        @error('estimated_weight')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="weight_unit">
                            Unit <span class="required-mark">*</span>
                        </label>
                        <select id="weight_unit" name="weight_unit" class="form-select" required>
                            <option value="kg" {{ old('weight_unit') == 'kg' ? 'selected' : '' }}>kg</option>
                            <option value="ton" {{ old('weight_unit') == 'ton' ? 'selected' : '' }}>ton</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="container_number">Container Number</label>
                    <input type="text" id="container_number" name="container_number" class="form-control" 
                           placeholder="Leave empty for auto-generate" value="{{ old('container_number') }}">
                    <span class="help-text">
                        <i class="bi bi-info-circle me-1"></i>
                        Format: K-TMH-XXXX (will be auto-generated if left empty)
                    </span>
                    @error('container_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="origin_location">
                        Origin Warehouse Location <span class="required-mark">*</span>
                    </label>
                    <input type="text" id="origin_location" name="origin_location" class="form-control" 
                           value="{{ old('origin_location') }}" required placeholder="Enter warehouse location">
                    @error('origin_location')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="notes">Notes / Remarks</label>
                    <textarea id="notes" name="notes" class="form-control" rows="3" 
                              placeholder="Additional notes (optional)">{{ old('notes') }}</textarea>
                </div>
                
                <div class="action-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Save & Generate Batch Number
                    </button>
                    <a href="{{ route('batches.index') }}" class="btn btn-secondary">
                        <i class="bi bi-x-circle me-1"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection