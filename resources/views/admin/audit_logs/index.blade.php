@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <h4 class="mb-0"><i data-feather="activity" class="me-2"></i>Audit Logs</h4>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 15%;">Time</th>
                            <th style="width: 20%;">Administrator</th>
                            <th style="width: 15%;">Action</th>
                            <th style="width: 30%;">Details</th>
                            <th style="width: 10%;">IP</th>
                            <th style="width: 10%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->CreatedAt)->diffForHumans() }} <br> <small class="text-muted">{{ $log->CreatedAt }}</small></td>
                            <td>
                                <div class="fw-bold">{{ $log->user->FullName ?? 'Unknown' }}</div>
                                <small class="text-muted">{{ $log->user->Email ?? 'N/A' }}</small>
                            </td>
                            <td>
                                @php
                                    $actionClass = 'secondary';
                                    if(str_contains($log->Action, 'DELETE')) $actionClass = 'danger';
                                    if(str_contains($log->Action, 'POST')) $actionClass = 'success';
                                    if(str_contains($log->Action, 'PUT') || str_contains($log->Action, 'PATCH')) $actionClass = 'warning';
                                @endphp
                                <span class="badge bg-{{ $actionClass }}">{{ $log->Action }}</span>
                            </td>
                            <td>
                                <div class="text-wrap" style="font-size: 0.8rem; color: #cbd5e1;">
                                    @php
                                        $detailsArr = json_decode($log->Details, true);
                                        $summaryParts = [];
                                        if($detailsArr) {
                                            foreach($detailsArr as $k => $v) {
                                                if(!in_array($k, ['_token', '_method', 'ajax', 'password'])) {
                                                    $valStr = is_array($v) ? json_encode($v) : $v;
                                                    $summaryParts[] = "<strong>$k:</strong> " . Str::limit($valStr, 30);
                                                }
                                            }
                                        }
                                    @endphp
                                    {!! count($summaryParts) > 0 ? implode(', ', array_slice($summaryParts, 0, 3)) : '<span class="text-muted">No changes</span>' !!}
                                    @if(count($summaryParts) > 3) ... @endif
                                </div>
                            </td>
                            <td class="text-muted" style="font-size: 0.8rem;">{{ $log->IPAddress }}</td>
                            <td>
                                <button type="button" class="btn btn-outline-primary btn-xs view-log-btn" 
                                    data-action="{{ $log->Action }}"
                                    data-admin="{{ $log->user->FullName ?? 'Unknown' }}"
                                    data-time="{{ $log->CreatedAt }}"
                                    data-details="{{ $log->Details }}">
                                    <i data-feather="eye" style="width: 12px; height: 12px;"></i> View
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No audit logs found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($logs->hasPages())
        <div class="card-footer">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="auditDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: #0f172a; border: 1px solid rgba(255,255,255,0.1); border-radius: 16px;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.05); padding: 20px;">
                <h5 class="modal-title" style="color: #f8fafc; display: flex; align-items: center; gap: 10px;">
                    <i data-feather="info" class="text-primary"></i> <span id="modalActionTitle">Action Details</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div class="mb-4" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Performed By</div>
                        <div id="modalAdmin" style="font-weight: 700; color: #f1f5f9; font-size: 1rem;"></div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px;">Date & Time</div>
                        <div id="modalTime" style="color: #cbd5e1; font-size: 0.9rem;"></div>
                    </div>
                </div>

                <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">Payload Data</div>
                <div id="modalDetailsBox" style="background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); border-radius: 12px; padding: 18px; font-family: monospace; max-height: 400px; overflow-y: auto;">
                </div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.05); padding: 15px 20px;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailModal = new bootstrap.Modal(document.getElementById('auditDetailModal'));
    
    document.querySelectorAll('.view-log-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const action = this.getAttribute('data-action');
            const admin = this.getAttribute('data-admin');
            const time = this.getAttribute('data-time');
            let details = {};
            
            try {
                details = JSON.parse(this.getAttribute('data-details'));
            } catch(e) {
                console.error("Invalid JSON", e);
            }

            document.getElementById('modalActionTitle').textContent = action;
            document.getElementById('modalAdmin').textContent = admin;
            document.getElementById('modalTime').textContent = time;
            
            const box = document.getElementById('modalDetailsBox');
            box.innerHTML = '';
            
            if (!details || Object.keys(details).length === 0) {
                box.innerHTML = '<div class="text-center text-muted py-3">No data recorded</div>';
            } else {
                let html = '<div style="display: flex; flex-direction: column; gap: 10px;">';
                for (const [key, value] of Object.entries(details)) {
                    const displayValue = typeof value === 'object' ? JSON.stringify(value, null, 2) : value;
                    html += `
                        <div style="padding-bottom: 8px; border-bottom: 1px solid rgba(255,255,255,0.03);">
                            <div style="color: var(--primary); font-weight: 700; font-size: 0.7rem; text-transform: uppercase;">${key}</div>
                            <div style="color: #f1f5f9; font-size: 0.85rem; word-break: break-all; white-space: pre-wrap;">${displayValue}</div>
                        </div>
                    `;
                }
                html += '</div>';
                box.innerHTML = html;
            }
            
            detailModal.show();
        });
    });
});
</script>
@endsection
