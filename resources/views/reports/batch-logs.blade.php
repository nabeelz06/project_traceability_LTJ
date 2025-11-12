@extends('layouts.app')

@section('title', 'Log Audit Batch')

@section('content')
<div class="container-fluid py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-journal-check" style="color: var(--primary);"></i>
        Log Audit Batch
    </h1>

    <!-- Filter -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('admin.logs.batch') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="batch_id" class="form-control" 
                               placeholder="ID Batch..." 
                               value="{{ request('batch_id') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="action" class="form-select">
                            <option value="">Semua Aksi</option>
                            <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Created</option>
                            <option value="checked_out" {{ request('action') == 'checked_out' ? 'selected' : '' }}>Check-Out</option>
                            <option value="checked_in" {{ request('action') == 'checked_in' ? 'selected' : '' }}>Check-In</option>
                            <option value="status_updated" {{ request('action') == 'status_updated' ? 'selected' : '' }}>Status Updated</option>
                            <option value="corrected" {{ request('action') == 'corrected' ? 'selected' : '' }}>Corrected</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-2">
                        <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-list-check me-2"></i>Riwayat Aktivitas ({{ $logs->total() }})
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Batch</th>
                            <th>Aksi</th>
                            <th>Status</th>
                            <th>Actor</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <a href="{{ route('batches.show', $log->batch) }}" style="font-weight: 600; color: var(--primary);">
                                    {{ $log->batch->batch_code }}
                                </a>
                            </td>
                            <td>
                                <strong style="color: var(--primary);">{{ $log->getActionLabel() }}</strong>
                                @if($log->action == 'corrected')
                                <span class="badge badge-warning badge-sm ms-1">Manual</span>
                                @endif
                            </td>
                            <td>
                                @if($log->previous_status && $log->new_status)
                                <span class="badge badge-secondary badge-sm">{{ $log->previous_status }}</span>
                                <i class="bi bi-arrow-right mx-1"></i>
                                <span class="badge badge-info badge-sm">{{ $log->new_status }}</span>
                                @else
                                -
                                @endif
                            </td>
                            <td>
                                {{ $log->actor->name ?? 'System' }}
                                <br><small class="text-muted">{{ $log->actor->getRoleLabel() ?? '' }}</small>
                            </td>
                            <td>{{ Str::limit($log->notes, 50) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada log</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection