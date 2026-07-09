@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content" style="overflow-x: hidden; padding-top: 15px;">

<style>
.support-container {
    max-width: 1300px;
    margin: 0 auto;
}
.page-title-box {
    margin-bottom: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
}
.support-card {
    background: rgba(30, 41, 59, 0.4);
    backdrop-filter: blur(15px);
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    padding: 20px;
}
.form-label-dark { color: #94a3b8; font-weight: 600; font-size: 0.8rem; }
.input-dark {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    color: #f1f5f9;
    padding: 8px 12px;
    width: 100%;
    font-size: 0.85rem;
    transition: all 0.2s;
}
.input-dark:focus { outline: none; border-color: #6366f1; background: rgba(99,102,241,0.05); }
.input-dark option { background: #1e293b; color: #fff; padding: 10px; }
.btn-submit-ticket {
    background: linear-gradient(135deg, #6366f1, #818cf8);
    color: #fff; border: none; border-radius: 12px; font-weight: 700; padding: 10px;
    transition: all 0.2s; cursor: pointer; font-size: 0.9rem;
}
.btn-submit-ticket:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(99,102,241,0.3); }
.issue-badge {
    background: rgba(99, 102, 241, 0.1);
    color: #a5b4fc;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 0.75rem;
    font-weight: 600;
    border: 1px solid rgba(99, 102, 241, 0.2);
}
.ticket-list-container {
    max-height: 520px;
    overflow-y: auto;
}
.ticket-row { border-bottom: 1px solid rgba(255,255,255,0.03); padding: 12px 0; }
.badge-status { padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 0.7rem; text-transform: uppercase; }
.status-open { background: rgba(245,158,11,0.1); color: #fbbf24; border: 1px solid rgba(245,158,11,0.2); }
.status-resolved { background: rgba(16,185,129,0.1); color: #34d399; border: 1px solid rgba(16,185,129,0.2); }

.nav-tabs-custom .nav-link {
    border: none;
    border-bottom: 2px solid transparent;
    color: #64748b;
    padding: 12px 20px;
    transition: 0.3s;
}
.nav-tabs-custom .nav-link.active {
    background: none;
    color: #6366f1;
    border-bottom: 2px solid #6366f1;
}
.nav-tabs-custom .nav-link:hover {
    color: #f1f5f9;
}
</style>

<div class="support-container">
    <!-- Header with Title and Badges -->
    <div class="page-title-box">
        <div>
            <h3 class="text-white fw-bold mb-1">🛡️ Kitchen Support Center</h3>
            <p class="text-muted small mb-0">Manage your requests and communicate with the admin team.</p>
        </div>
        <div class="text-end">
            <div class="d-flex flex-wrap gap-2 justify-content-end">
                <span class="issue-badge">💳 Billing</span>
                <span class="issue-badge">📦 Orders</span>
                <span class="issue-badge">😤 Conduct</span>
                <span class="issue-badge">⚙️ Tech</span>
            </div>
        </div>
    </div>

    <!-- Tabs Navigation -->
    <ul class="nav nav-tabs nav-tabs-custom mb-4" id="supportTabs" role="tablist" style="border-bottom: 1px solid rgba(255,255,255,0.05);">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('subject') ? '' : 'active' }}" id="history-tab" data-bs-toggle="tab" data-bs-target="#history" type="button" role="tab">
                <i data-feather="list" class="me-2" style="width:16px;"></i> Request History
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('subject') ? 'active' : '' }}" id="new-request-tab" data-bs-toggle="tab" data-bs-target="#new-request" type="button" role="tab">
                <i data-feather="plus-circle" class="me-2" style="width:16px;"></i> {{ request('subject') ? 'Submit Refund Dispute' : 'New Support Request' }}
            </button>
        </li>
    </ul>

    <div class="tab-content" id="supportTabsContent">
        <!-- History Tab -->
        <div class="tab-pane fade {{ request('subject') ? '' : 'show active' }}" id="history" role="tabpanel">
            <div class="support-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="text-white mb-0" style="font-size:1.1rem;">My Recent Tickets</h5>
                    <span class="text-muted small">{{ $tickets->total() }} Records Found</span>
                </div>

                <div class="ticket-list-container px-1" style="max-height: 550px;">
                    @forelse($tickets as $ticket)
                        <div class="ticket-row-wrapper mb-2">
                            <div class="ticket-header d-flex justify-content-between align-items-center p-3" 
                                 onclick="toggleTicket('{{ $ticket->TicketID }}')" 
                                 style="cursor:pointer; background: rgba(255,255,255,0.02); border-radius: 12px; transition: 0.2s;">
                                <div class="d-flex align-items-center gap-4">
                                    <div class="d-flex flex-column">
                                        <span class="text-white fw-bold" style="font-size: 0.9rem;">#{{ $ticket->TicketID }}</span>
                                        @if($ticket->OrderID)
                                            <span class="badge bg-primary mt-1" style="font-size: 0.6rem; width: fit-content;">📦 Order #{{ $ticket->OrderID }}</span>
                                        @endif
                                    </div>
                                    <div class="d-flex flex-column">
                                        <span class="text-info" style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase;">{{ $ticket->Category }}</span>
                                        <span class="text-light opacity-75 small">{{ Str::limit($ticket->Subject, 45) }}</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="text-muted small d-none d-md-block">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}</span>
                                    <span class="badge-status {{ $ticket->Status == 'Resolved' ? 'status-resolved' : 'status-open' }}">
                                        {{ $ticket->Status }}
                                    </span>
                                    <i data-feather="chevron-down" class="text-muted" id="icon-{{ $ticket->TicketID }}" style="width: 16px; transition: 0.3s;"></i>
                                </div>
                            </div>

                            <div id="details-{{ $ticket->TicketID }}" class="ticket-details p-4 mt-2" style="display: none; background: rgba(30, 41, 59, 0.5); border-radius: 15px; border: 1px solid rgba(255,255,255,0.05);">
                                <div class="mb-4">
                                    <span class="text-muted d-block mb-2" style="font-size: 0.7rem; text-transform: uppercase;">My Request:</span>
                                    <div class="p-3 rounded text-light" style="background: rgba(0,0,0,0.2); line-height: 1.6;">{!! nl2br(e($ticket->Description)) !!}</div>
                                </div>
                                
                                @if($ticket->AdminReply)
                                    <div class="mt-4 p-3 rounded" style="background: rgba(99, 102, 241, 0.08); border-left: 4px solid #6366f1;">
                                        <div class="text-primary fw-bold small mb-2 d-flex align-items-center gap-2">
                                            <i data-feather="shield" style="width:14px;"></i> Admin Official Response
                                        </div>
                                        <div class="text-light" style="line-height: 1.6;">{{ $ticket->AdminReply }}</div>
                                        <div class="text-muted mt-2 small text-end">Replied on: {{ \Carbon\Carbon::parse($ticket->updated_at)->format('d M Y, h:i A') }}</div>
                                    </div>
                                @else
                                    <div class="mt-3 p-2 rounded text-warning small" style="background: rgba(245,158,11,0.05); border: 1px dashed rgba(245,158,11,0.2);">
                                        <i data-feather="clock" style="width:14px;" class="me-1"></i> Under review by support team.
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i data-feather="inbox" style="width:48px; opacity:0.15;" class="mb-3"></i>
                            <p class="text-muted">No support history found.</p>
                        </div>
                    @endforelse
                </div>
                
                <div class="mt-4">
                    {{ $tickets->links() }}
                </div>
            </div>
        </div>

        <!-- New Request Tab -->
        <div class="tab-pane fade {{ request('subject') ? 'show active' : '' }}" id="new-request" role="tabpanel">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="support-card">
                        <h5 class="text-white mb-4"><i data-feather="edit-3" class="me-2 text-primary"></i> Create New Request</h5>
                        
                        <form method="POST" action="{{ route('kitchen.support.store') }}">
                            @csrf
                            <input type="hidden" name="order_id" value="{{ request('order_id') }}">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label-dark">Category</label>
                                    <select name="category" class="input-dark" required>
                                        @foreach($categories as $cat)
                                            @php 
                                                $isSelected = (old('category') == $cat) || (!old('category') && str_contains(request('subject', ''), 'Refund') && $cat == 'Payment / Billing Issue');
                                            @endphp
                                            <option value="{{ $cat }}" {{ $isSelected ? 'selected' : '' }}>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label-dark">Subject</label>
                                    <input type="text" name="subject" class="input-dark" placeholder="Summary" value="{{ old('subject', request('subject')) }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label-dark">Description</label>
                                    <textarea name="description" class="input-dark" rows="7" placeholder="Explain your issue..." required>{{ old('description', request('message')) }}</textarea>
                                </div>
                                <div class="col-12 mt-4">
                                    <button type="submit" class="btn-submit-ticket w-100 py-3">
                                        <i data-feather="send" class="me-2"></i> Send Request
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</div>

@push('custom-scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (window.feather) { feather.replace(); }
    });

    function toggleTicket(id) {
        const details = document.getElementById('details-' + id);
        const icon = document.getElementById('icon-' + id);
        
        if (details.style.display === 'none') {
            details.style.display = 'block';
            icon.style.transform = 'rotate(180deg)';
        } else {
            details.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }

    // Auto-clean URL parameters after form population to prevent stale data on refresh/sidebar clicks
    if (window.location.search) {
        setTimeout(function() {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }
</script>
@endpush

@endsection
