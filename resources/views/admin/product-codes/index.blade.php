@extends('layouts.app')

@section('title', 'Product Code Management - Course System')

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

    .code-display {
        font-family: 'Monaco', 'Courier New', monospace;
        font-weight: 700;
        color: #0d6efd;
        font-size: 1rem;
        letter-spacing: 1px;
        padding: 0.3rem 0.6rem;
        background: rgba(13,110,253,0.08);
        border-radius: 6px;
        display: inline-block;
    }

    .badge {
        padding: 0.4rem 0.8rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .badge-primary {
        background: linear-gradient(135deg, #0d6efd 0%, #3aa0ff 100%);
        color: white;
    }

    .badge-success {
        background: linear-gradient(135deg, #198754 0%, #4bd08f 100%);
        color: white;
    }

    .btn-edit {
        background: linear-gradient(135deg, #4e73df 0%, #6c8ef7 100%);
        color: white;
        padding: 0.4rem 0.8rem;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        text-decoration: none;
        display: inline-block;
    }

    .btn-edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(78,115,223,0.3);
        color: white;
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
</style>

<div class="container py-4">
    <div class="page-header">
        <h2>Product Code Management</h2>
        <a href="{{ route('admin.product-codes.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Create Product Code
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Stage</th>
                            <th>Material</th>
                            <th>Spec</th>
                            <th>Description</th>
                            <th>Total Batches</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productCodes as $code)
                        <tr>
                            <td><span class="code-display">{{ $code->code }}</span></td>
                            <td>
                                <span class="badge badge-{{ $code->stage == 'TIM' ? 'primary' : 'success' }}">
                                    {{ $code->stage }}
                                </span>
                            </td>
                            <td><strong>{{ $code->material }}</strong></td>
                            <td>{{ $code->spec }}</td>
                            <td>{{ $code->description }}</td>
                            <td><strong>{{ $code->batches_count }}</strong></td>
                            <td>
                                <a href="{{ route('admin.product-codes.edit', $code) }}" class="btn-edit">
                                    <i class="bi bi-pencil me-1"></i> Edit
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="bi bi-inbox"></i>
                                    <h6>No product code data available</h6>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $productCodes->links() }}
            </div>
        </div>
    </div>
</div>
@endsection