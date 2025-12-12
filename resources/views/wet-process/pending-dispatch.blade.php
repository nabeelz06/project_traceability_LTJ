@extends('layouts.app')

@section('title', 'Pending Dispatch')

@section('content')
<div class="container-fluid px-4 py-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800 mb-2">
            <i class="bi bi-hourglass-split" style="color: var(--timah-blue);"></i>
            Batch Pending Dispatch
        </h1>
        <p class="text-gray-600">Batch yang siap dikirim ke Dry Process</p>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Batch Code</th>
                            <th>Product</th>
                            <th>Berat (kg)</th>
                            <th>Lokasi</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td><strong>{{ $batch->batch_code }}</strong></td>
                            <td>{{ $batch->productCode->description ?? '-' }}</td>
                            <td>{{ number_format($batch->initial_weight, 0) }}</td>
                            <td>{{ $batch->origin_location }}</td>
                            <td>{{ $batch->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <form action="{{ route('wet-process.dispatch', $batch) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Dispatch batch ini ke Dry Process?')">
                                        <i class="bi bi-send"></i> Dispatch (CP1)
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.3;"></i>
                                <p class="mt-2">Tidak ada batch pending dispatch</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $batches->links() }}
            </div>
        </div>
    </div>
</div>
@endsection