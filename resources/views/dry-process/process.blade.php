@extends('layouts.app')

@section('title', 'Input Kandungan Konsentrat')

@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    :root {
        --primary: #3e5c74;
        --primary-dark: #2d4454;
    }

    body {
        font-family: 'Poppins', system-ui, sans-serif;
        background: linear-gradient(135deg, #e8eef3 0%, #f5f8fa 100%);
    }

    .form-container {
        max-width: 900px;
        margin: 2rem auto;
        background: white;
        border-radius: 14px;
        padding: 2rem;
        box-shadow: 0 6px 20px rgba(62,92,116,0.12);
    }

    .concentrate-input {
        background: linear-gradient(135deg, rgba(62,92,116,0.05) 0%, rgba(62,92,116,0.02) 100%);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .concentrate-input:hover {
        border-color: var(--primary);
    }

    .concentrate-label {
        font-weight: 700;
        font-size: 1.1rem;
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .total-display {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    .total-display .stat {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }

    .total-display .stat:last-child {
        margin-bottom: 0;
        padding-top: 0.75rem;
        border-top: 1px solid rgba(255,255,255,0.2);
    }

    .input-group-text {
        background: var(--primary);
        color: white;
        font-weight: 600;
        border: none;
    }

    .form-control:focus {
        border-color: var(--primary);
        box-shadow: 0 0 0 0.2rem rgba(62,92,116,0.25);
    }
</style>

<div class="container-fluid py-4">
    <div class="form-container">
        <!-- Header -->
        <div class="mb-4">
            <h2 style="color: var(--primary); font-weight: 700;">
                <i class="bi bi-clipboard-check me-2"></i>Input Kandungan Konsentrat
            </h2>
            <p style="color: #6c757d;">Batch: <strong>{{ $batch->batch_code }}</strong></p>
            <p style="color: #6c757d;">Berat Total: <strong>{{ number_format($batch->current_weight, 2) }} kg</strong></p>
        </div>

        <!-- Info Alert -->
        <div class="alert alert-info mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Petunjuk:</strong> Anda dapat mengisi <strong>Persentase (%) ATAU Berat (kg)</strong> untuk setiap konsentrat. 
            Sistem akan otomatis menghitung nilai yang lain. Total tidak harus 100%.
        </div>

        <!-- Form -->
        <form action="{{ route('dry-process.process', $batch) }}" method="POST" id="processForm">
            @csrf

            <!-- Total Summary Display -->
            <div class="total-display" id="totalDisplay">
                <div class="stat">
                    <span>Total Persentase:</span>
                    <strong id="totalPercentage" style="font-size: 1.5rem;">0.00%</strong>
                </div>
                <div class="stat">
                    <span>Total Berat:</span>
                    <strong id="totalWeight" style="font-size: 1.5rem;">0.00 kg</strong>
                </div>
                <div class="stat">
                    <span>Sisa/Waste:</span>
                    <strong id="wasteInfo" style="font-size: 1.2rem;">{{ number_format($batch->current_weight, 2) }} kg</strong>
                </div>
            </div>

            <!-- Zircon -->
            <div class="concentrate-input">
                <div class="concentrate-label" style="color: #e74c3c;">
                    <i class="bi bi-gem"></i>
                    Zircon (Concentrate)
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Persentase (%):</label>
                        <div class="input-group">
                            <input type="number" name="zircon_percentage" class="form-control form-control-lg percentage-input" 
                                   data-material="zircon" step="0.01" min="0" max="100" value="0">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berat (kg):</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-lg weight-input" 
                                   data-material="zircon" step="0.01" min="0" max="{{ $batch->current_weight }}" value="0">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ilmenite -->
            <div class="concentrate-input">
                <div class="concentrate-label" style="color: #9b59b6;">
                    <i class="bi bi-box-seam"></i>
                    Ilmenite (Concentrate)
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Persentase (%):</label>
                        <div class="input-group">
                            <input type="number" name="ilmenite_percentage" class="form-control form-control-lg percentage-input" 
                                   data-material="ilmenite" step="0.01" min="0" max="100" value="0">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berat (kg):</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-lg weight-input" 
                                   data-material="ilmenite" step="0.01" min="0" max="{{ $batch->current_weight }}" value="0">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monasit -->
            <div class="concentrate-input">
                <div class="concentrate-label" style="color: #27ae60;">
                    <i class="bi bi-archive"></i>
                    Monasit (Concentrate)
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Persentase (%):</label>
                        <div class="input-group">
                            <input type="number" name="monasit_percentage" class="form-control form-control-lg percentage-input" 
                                   data-material="monasit" step="0.01" min="0" max="100" value="0">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Berat (kg):</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-lg weight-input" 
                                   data-material="monasit" step="0.01" min="0" max="{{ $batch->current_weight }}" value="0">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="mb-4">
                <label class="form-label">Catatan (Opsional):</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="Tambahkan catatan jika diperlukan">{{ old('notes') }}</textarea>
            </div>

            <!-- Buttons -->
            <div class="d-flex gap-3">
                <a href="{{ route('dry-process.dashboard') }}" class="btn btn-secondary btn-lg">
                    <i class="bi bi-arrow-left me-2"></i>Batal
                </a>
                <button type="submit" class="btn btn-primary btn-lg flex-grow-1" id="submitBtn">
                    <i class="bi bi-check-circle me-2"></i>Konfirmasi & Buat <span id="batchCount">0</span> Batch Konsentrat
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const totalWeight = {{ $batch->current_weight }};
let isUpdating = false; // Prevent circular updates

// Get all inputs
const percentageInputs = document.querySelectorAll('.percentage-input');
const weightInputs = document.querySelectorAll('.weight-input');

// Event listeners untuk percentage input
percentageInputs.forEach(input => {
    input.addEventListener('input', function() {
        if (isUpdating) return;
        isUpdating = true;
        
        const material = this.dataset.material;
        const percentage = parseFloat(this.value) || 0;
        const weight = (totalWeight * percentage) / 100;
        
        // Update corresponding weight input
        const weightInput = document.querySelector(`.weight-input[data-material="${material}"]`);
        weightInput.value = weight.toFixed(2);
        
        isUpdating = false;
        updateTotals();
    });
});

// Event listeners untuk weight input
weightInputs.forEach(input => {
    input.addEventListener('input', function() {
        if (isUpdating) return;
        isUpdating = true;
        
        const material = this.dataset.material;
        const weight = parseFloat(this.value) || 0;
        const percentage = (weight / totalWeight) * 100;
        
        // Update corresponding percentage input
        const percentageInput = document.querySelector(`.percentage-input[data-material="${material}"]`);
        percentageInput.value = percentage.toFixed(2);
        
        isUpdating = false;
        updateTotals();
    });
});

// Update totals and submit button
function updateTotals() {
    // Calculate totals
    let totalPercentage = 0;
    let totalWeightValue = 0;
    let batchCount = 0;
    
    percentageInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        totalPercentage += value;
        if (value > 0) batchCount++;
    });
    
    weightInputs.forEach(input => {
        totalWeightValue += parseFloat(input.value) || 0;
    });
    
    const waste = totalWeight - totalWeightValue;
    
    // Update display
    document.getElementById('totalPercentage').textContent = totalPercentage.toFixed(2) + '%';
    document.getElementById('totalWeight').textContent = totalWeightValue.toFixed(2) + ' kg';
    document.getElementById('wasteInfo').textContent = waste.toFixed(2) + ' kg (' + ((waste/totalWeight)*100).toFixed(2) + '%)';
    document.getElementById('batchCount').textContent = batchCount;
    
    // Enable/disable submit based on having at least one concentrate
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = batchCount === 0;
    
    // Warning jika total > 100%
    const totalDisplay = document.getElementById('totalDisplay');
    if (totalPercentage > 100 || totalWeightValue > totalWeight) {
        totalDisplay.style.background = 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)';
    } else {
        totalDisplay.style.background = 'linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%)';
    }
}

// Initial update
updateTotals();
</script>
@endsection