@extends('layouts.app')

@section('title', 'Traceability Tree - ' . $batch->batch_code)

@section('content')
<div class="container-fluid py-4">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 style="color: var(--dark); font-size: 1.75rem; margin: 0; font-weight: 700;">
            <i class="bi bi-diagram-2" style="color: var(--primary);"></i>
            Traceability Tree: {{ $batch->batch_code }}
        </h1>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('traceability.search') }}" class="btn" style="background: var(--secondary); color: white;">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
            <a href="{{ route('batches.show', $batch) }}" class="btn btn-outline-primary">
                <i class="bi bi-eye me-1"></i>Detail Batch
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding: 2rem;">
            <div class="tree-container">
                {!! renderTree($tree) !!}
            </div>
        </div>
    </div>
</div>

@php
function renderTree($node, $isLast = true) {
    $batch = $node['batch'];
    $html = '<div class="tree-node" style="margin-left: ' . ($node['depth'] * 40) . 'px; margin-bottom: 1rem;">';
    
    // Branch line
    if ($node['depth'] > 0) {
        $html .= '<div style="display: flex; align-items: center; margin-bottom: 0.5rem;">';
        $html .= '<div style="width: 30px; height: 2px; background: #ddd;"></div>';
        $html .= '<i class="bi bi-arrow-right" style="color: var(--primary); margin: 0 0.5rem;"></i>';
    }
    
    // Node card
    $html .= '<div class="batch-card" style="display: inline-block; padding: 1rem; border: 2px solid var(--primary); border-radius: 8px; background: white; min-width: 350px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">';
    $html .= '<div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">';
    $html .= '<div>';
    $html .= '<strong style="font-size: 1.1rem; color: var(--primary);">' . $batch->batch_code . '</strong>';
    if ($batch->isChild()) {
        $html .= ' <span class="badge badge-info badge-sm">Turunan</span>';
    } else {
        $html .= ' <span class="badge badge-primary badge-sm">Induk</span>';
    }
    $html .= '</div>';
    $html .= '<span class="badge ' . $batch->getStatusBadgeClass() . '">' . $batch->getStatusLabel() . '</span>';
    $html .= '</div>';
    
    $html .= '<div style="font-size: 0.9rem; color: #666;">';
    $html .= '<div><i class="bi bi-box me-1"></i> <strong>Produk:</strong> ' . $batch->product_code . '</div>';
    $html .= '<div><i class="bi bi-weight me-1"></i> <strong>Berat:</strong> ' . number_format($batch->initial_weight, 2) . ' ' . $batch->weight_unit . '</div>';
    $html .= '<div><i class="bi bi-building me-1"></i> <strong>Pemilik:</strong> ' . ($batch->currentPartner->name ?? 'PT Timah') . '</div>';
    $html .= '<div><i class="bi bi-calendar me-1"></i> <strong>Dibuat:</strong> ' . $batch->created_at->format('d M Y') . '</div>';
    $html .= '</div>';
    
    $html .= '<div style="margin-top: 0.75rem;">';
    $html .= '<a href="' . route('batches.show', $batch) . '" class="btn btn-sm btn-outline-primary">';
    $html .= '<i class="bi bi-eye me-1"></i>Detail';
    $html .= '</a>';
    $html .= '</div>';
    
    $html .= '</div>'; // close batch-card
    
    if ($node['depth'] > 0) {
        $html .= '</div>'; // close flex container
    }
    
    $html .= '</div>'; // close tree-node
    
    // Render children
    if (!empty($node['children'])) {
        foreach ($node['children'] as $index => $child) {
            $isLastChild = ($index === count($node['children']) - 1);
            $html .= renderTree($child, $isLastChild);
        }
    }
    
    return $html;
}
@endphp

<style>
.tree-container {
    position: relative;
}

.tree-node {
    position: relative;
    animation: fadeIn 0.3s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateX(-10px); }
    to { opacity: 1; transform: translateX(0); }
}

.batch-card {
    transition: all 0.3s ease;
}

.batch-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}
</style>
@endsection