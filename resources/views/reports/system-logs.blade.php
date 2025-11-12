@extends('layouts.app')

@section('title', 'Log Sistem')

@section('content')
<div class="container-fluid py-4">
    <h1 style="color: var(--dark); font-size: 1.75rem; margin-bottom: 1.5rem; font-weight: 700;">
        <i class="bi bi-shield-lock" style="color: var(--primary);"></i>
        Log Sistem & Keamanan
    </h1>

    <div class="alert alert-info">
        <i class="bi bi-info-circle me-2"></i>
        <strong>Catatan:</strong> Log ini mencatat semua aktivitas sistem termasuk login, logout, dan perubahan data sensitif.
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-header">
            <i class="bi bi-clock-history me-2"></i>Riwayat Aktivitas Sistem ({{ $logs->total() }})
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y, H:i:s') }}</td>
                            <td>
                                {{ $log->user->name ?? 'System' }}
                                <br><small class="text-muted">{{ $log->user->email ?? '-' }}</small>
                            </td>
                            <td>
                                <strong>{{ str_replace('_', ' ', ucfirst($log->action)) }}</strong>
                                @if(in_array($log->action, ['user_login', 'user_logout']))
                                <i class="bi bi-person-circle ms-1" style="color: var(--info);"></i>
                                @elseif(in_array($log->action, ['partner_created', 'partner_approved', 'partner_rejected']))
                                <i class="bi bi-building ms-1" style="color: var(--primary);"></i>
                                @elseif(str_contains($log->action, 'deleted'))
                                <i class="bi bi-trash ms-1" style="color: var(--danger);"></i>
                                @endif
                            </td>
                            <td style="font-family: monospace; font-size: 0.9rem;">{{ $log->ip_address }}</td>
                            <td>
                                <small class="text-muted">{{ Str::limit($log->user_agent, 40) }}</small>
                            </td>
                            <td>
                                @if($log->details)
                                <button type="button" class="btn btn-sm btn-outline-secondary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#detailModal{{ $log->id }}">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- Modal -->
                                <div class="modal fade" id="detailModal{{ $log->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detail Log</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <pre style="background: #f8f9fa; padding: 1rem; border-radius: 4px; font-size: 0.85rem;">{{ json_encode($log->details, JSON_PRETTY_PRINT) }}</pre>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @else
                                -
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Tidak ada log sistem</td>
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