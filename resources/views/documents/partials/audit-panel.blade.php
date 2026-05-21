<div class="table-card mb-4">
    <div class="table-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span class="heading">Issuance Audit Log</span>
        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" id="refreshAuditLogs"
            style="border-radius:7px;font-size:12px;font-weight:600;">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
    <div class="p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:13px;">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Details</th>
                        <th>User</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody id="auditLogsBody">
                    @forelse($auditLogs as $log)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $log->action }}</span></td>
                            <td style="max-width:320px;">{{ $log->description ?? '—' }}</td>
                            <td>{{ $log->user?->email ?? 'System' }}</td>
                            <td title="{{ $log->created_at->format('M d, Y g:i A') }}">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr id="auditLogsEmpty">
                            <td colspan="4" class="text-muted text-center py-4">No issuance activity recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
