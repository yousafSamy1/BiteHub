@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">

<style>
/* ── Page Styles ─────────────────────────── */
.rpt-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 60%, #1e293b 100%);
    border: 1px solid rgba(139,92,246,0.2);
    border-radius: 20px;
    padding: 34px 40px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
}
.rpt-hero::after {
    content: '';
    position: absolute;
    top: -60px; right: -40px;
    width: 260px; height: 260px;
    background: radial-gradient(circle, rgba(139,92,246,0.2) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}

.rpt-stat-card {
    background: #1e293b;
    border-radius: 16px;
    padding: 22px 26px;
    border: 1px solid rgba(255,255,255,0.06);
    display: flex;
    align-items: center;
    gap: 18px;
    transition: transform 0.2s, box-shadow 0.2s;
}
.rpt-stat-card:hover { transform: translateY(-3px); box-shadow: 0 10px 24px rgba(0,0,0,0.25); }
.rpt-stat-icon {
    width: 52px; height: 52px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.rpt-stat-label { color: #94a3b8; font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
.rpt-stat-value { color: #f1f5f9; font-size: 1.9rem; font-weight: 900; line-height: 1; }

.filter-bar {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 16px;
    padding: 18px 22px;
    display: flex; gap: 12px; flex-wrap: wrap; align-items: center;
    margin-bottom: 20px;
}
.filter-input {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: #f1f5f9;
    padding: 9px 14px;
    font-size: 0.88rem;
    transition: border-color 0.2s;
    min-width: 150px;
}
.filter-input:focus { outline: none; border-color: #8b5cf6; }
.filter-input option { background: #1e293b; color: #f1f5f9; }
.btn-filter {
    background: linear-gradient(135deg, #7c3aed, #6366f1);
    color: #fff;
    border: none;
    border-radius: 10px;
    padding: 9px 22px;
    font-weight: 700;
    cursor: pointer;
    font-size: 0.88rem;
    transition: all 0.2s;
}
.btn-filter:hover { box-shadow: 0 4px 16px rgba(99,102,241,0.35); }
.btn-reset { background: rgba(255,255,255,0.05); color: #94a3b8; border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 9px 18px; font-size: 0.88rem; cursor: pointer; }
.btn-reset:hover { background: rgba(255,255,255,0.1); color: #f1f5f9; }

.rpt-table-card {
    background: #1e293b;
    border: 1px solid rgba(255,255,255,0.06);
    border-radius: 18px;
    overflow: hidden;
}
.rpt-table { width: 100%; border-collapse: separate; border-spacing: 0; }
.rpt-table th {
    background: rgba(255,255,255,0.02);
    color: #64748b;
    font-size: 0.73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    padding: 14px 18px;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    white-space: nowrap;
}
.rpt-table td {
    padding: 15px 18px;
    border-bottom: 1px solid rgba(255,255,255,0.03);
    color: #cbd5e1;
    font-size: 0.9rem;
    vertical-align: middle;
}
.rpt-table tbody tr { transition: background 0.15s; }
.rpt-table tbody tr:hover { background: rgba(255,255,255,0.025); }

.badge-st { display: inline-block; padding: 4px 13px; border-radius: 20px; font-size: 0.73rem; font-weight: 800; letter-spacing: 0.03em; white-space: nowrap; }
.badge-st-open       { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
.badge-st-inprogress { background: rgba(6,182,212,0.12);  color: #22d3ee; border: 1px solid rgba(6,182,212,0.25); }
.badge-st-resolved   { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
.badge-st-closed     { background: rgba(100,116,139,0.12);color: #94a3b8; border: 1px solid rgba(100,116,139,0.25); }

.badge-sender { display: inline-block; padding: 3px 10px; border-radius: 8px; font-size: 0.72rem; font-weight: 700; }
.badge-sender-customer { background: rgba(96,165,250,0.12); color: #60a5fa; }
.badge-sender-kitchen  { background: rgba(251,191,36,0.12); color: #fbbf24; }
.badge-sender-caterer  { background: rgba(167,139,250,0.12);color: #c084fc; }

.btn-view-ticket {
    background: rgba(99,102,241,0.12);
    color: #818cf8;
    border: 1px solid rgba(99,102,241,0.2);
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 0.8rem;
    font-weight: 700;
    text-decoration: none;
    transition: all 0.15s;
    white-space: nowrap;
}
.btn-view-ticket:hover { background: rgba(99,102,241,0.25); color: #a5b4fc; }

.quick-status-form select {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 8px;
    color: #94a3b8;
    padding: 5px 10px;
    font-size: 0.78rem;
    cursor: pointer;
}
.quick-status-form select:focus { outline: none; border-color: #6366f1; }
</style>

<!-- Hero -->
<div class="rpt-hero">
    <div style="position:relative; z-index:1;">
        <h2 style="color:#f1f5f9; font-weight:900; font-size:1.8rem; margin-bottom:8px;">🆘 Reports & Support Tickets</h2>
        <p style="color:#94a3b8; margin:0; font-size:1rem;">Manage and respond to support requests from customers, kitchens, and caterers.</p>
    </div>
</div>

<!-- Stats Row -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
        <div class="rpt-stat-card">
            <div class="rpt-stat-icon" style="background:rgba(139,92,246,0.15);">🎫</div>
            <div>
                <div class="rpt-stat-label">Total Tickets</div>
                <div class="rpt-stat-value">{{ number_format($stats['total']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="rpt-stat-card">
            <div class="rpt-stat-icon" style="background:rgba(245,158,11,0.15);">🔓</div>
            <div>
                <div class="rpt-stat-label">Open</div>
                <div class="rpt-stat-value" style="color:#fbbf24;">{{ number_format($stats['open']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="rpt-stat-card">
            <div class="rpt-stat-icon" style="background:rgba(6,182,212,0.15);">⚙️</div>
            <div>
                <div class="rpt-stat-label">In Progress</div>
                <div class="rpt-stat-value" style="color:#22d3ee;">{{ number_format($stats['inprogress']) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="rpt-stat-card">
            <div class="rpt-stat-icon" style="background:rgba(16,185,129,0.15);">✅</div>
            <div>
                <div class="rpt-stat-label">Resolved / Closed</div>
                <div class="rpt-stat-value" style="color:#34d399;">{{ number_format($stats['resolved']) }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<form method="GET" action="{{ route('admin.reports') }}" class="filter-bar">
    <input type="text" name="search" class="filter-input" placeholder="🔍 Search subject, name..." value="{{ request('search') }}" style="min-width:200px;">

    <select name="sender_type" class="filter-input">
        <option value="">All Sender Types</option>
        <option value="Customer"     {{ request('sender_type') == 'Customer'     ? 'selected' : '' }}>🛒 Customer</option>
        <option value="KitchenOwner" {{ request('sender_type') == 'KitchenOwner' ? 'selected' : '' }}>🍳 Kitchen Owner</option>
        <option value="Caterer"      {{ request('sender_type') == 'Caterer'      ? 'selected' : '' }}>🎉 Caterer</option>
    </select>

    <select name="status" class="filter-input">
        <option value="">All Statuses</option>
        <option value="Open"       {{ request('status') == 'Open'       ? 'selected' : '' }}>Open</option>
        <option value="InProgress" {{ request('status') == 'InProgress' ? 'selected' : '' }}>In Progress</option>
        <option value="Resolved"   {{ request('status') == 'Resolved'   ? 'selected' : '' }}>Resolved</option>
        <option value="Closed"     {{ request('status') == 'Closed'     ? 'selected' : '' }}>Closed</option>
    </select>

    <input type="date" name="from_date" class="filter-input" placeholder="From date" value="{{ request('from_date') }}" title="From date">
    <input type="date" name="to_date"   class="filter-input" placeholder="To date"   value="{{ request('to_date') }}"   title="To date">

    <button type="submit" class="btn-filter"><i data-feather="filter" style="width:13px" class="me-1"></i> Filter</button>
    <a href="{{ route('admin.reports') }}" class="btn-reset">Reset</a>
</form>

<!-- Table -->
<div class="rpt-table-card">
    <div style="padding: 20px 22px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <h5 style="color:#f1f5f9; font-weight:700; margin:0; font-size:1rem;">
            <i data-feather="life-buoy" style="width:17px; color:#8b5cf6" class="me-2"></i>All Tickets
        </h5>
        <span style="color:#475569; font-size:0.82rem;">{{ $tickets->total() }} records found</span>
    </div>

    <div class="table-responsive mt-3">
        <table class="rpt-table">
            <thead>
                <tr>
                    <th>#ID</th>
                    <th>Sender</th>
                    <th>Category</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td>
                        <span style="color:#818cf8; font-weight:800;">#{{ $ticket->TicketID }}</span>
                        @if($ticket->OrderID)
                            <div style="font-size:0.75rem; color:#475569; margin-top:2px;">📦 Order #{{ $ticket->OrderID }}</div>
                        @endif
                    </td>
                    <td>
                        <div style="font-weight:700; color:#e2e8f0; font-size:0.9rem;">{{ $ticket->UserName }}</div>
                        <div style="margin-top:4px;">
                            @php
                                $senderClass = match($ticket->SenderType) {
                                    'Customer'     => 'badge-sender-customer',
                                    'KitchenOwner' => 'badge-sender-kitchen',
                                    'Caterer'      => 'badge-sender-caterer',
                                    default        => '',
                                };
                                $senderLabel = match($ticket->SenderType) {
                                    'Customer'     => '🛒 Customer',
                                    'KitchenOwner' => '🍳 Kitchen',
                                    'Caterer'      => '🎉 Caterer',
                                    default        => $ticket->SenderType,
                                };
                            @endphp
                            <span class="badge-sender {{ $senderClass }}">{{ $senderLabel }}</span>
                        </div>
                        <div style="font-size:0.75rem; color:#475569; margin-top:2px;">{{ $ticket->UserEmail }}</div>
                    </td>
                    <td>
                        <span style="background: rgba(99,102,241,0.08); color:#a5b4fc; padding:3px 10px; border-radius:8px; font-size:0.78rem; font-weight:700;">
                            {{ $ticket->Category }}
                        </span>
                    </td>
                    <td style="max-width:220px;">
                        <div style="color:#e2e8f0; font-weight:600; font-size:0.88rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $ticket->Subject }}">
                            {{ $ticket->Subject }}
                        </div>
                        @if($ticket->AdminReply)
                            <div style="font-size:0.72rem; color:#34d399; margin-top:4px;">✅ Admin replied</div>
                        @else
                            <div style="font-size:0.72rem; color:#64748b; margin-top:4px;">⏳ Awaiting reply</div>
                        @endif
                    </td>
                    <td>
                        @php
                            $sc = match($ticket->Status) {
                                'Open'       => 'badge-st-open',
                                'InProgress' => 'badge-st-inprogress',
                                'Resolved'   => 'badge-st-resolved',
                                default      => 'badge-st-closed',
                            };
                        @endphp
                        <span class="badge-st {{ $sc }}">{{ $ticket->Status }}</span>
                    </td>
                    <td style="color:#475569; font-size:0.82rem; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}<br>
                        <span style="font-size:0.75rem;">{{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}</span>
                    </td>
                    <td>
                        <div style="display:flex; flex-direction:column; gap:8px; align-items:flex-start;">
                            @php
                                $isResolved = ($ticket->Status == 'Resolved' || $ticket->Status == 'Closed');
                            @endphp
                            <a href="{{ route('admin.reports.show', $ticket->TicketID) }}" class="btn-view-ticket" style="{{ $isResolved ? 'background: rgba(100,116,139,0.1); color: #94a3b8; border-color: rgba(100,116,139,0.2);' : '' }}">
                                <i data-feather="{{ $isResolved ? 'eye' : 'message-circle' }}" style="width:12px" class="me-1"></i> 
                                {{ $isResolved ? 'View Details' : 'View & Reply' }}
                            </a>
                            <!-- Quick Status Update -->
                            @if(!$isResolved)
                            <form method="POST" action="{{ route('admin.reports.status', $ticket->TicketID) }}" class="quick-status-form d-flex gap-1">
                                @csrf
                                <select name="status" onchange="this.form.submit()" title="Update status">
                                    <option value="Open"       {{ $ticket->Status=='Open'       ? 'selected':'' }}>Open</option>
                                    <option value="InProgress" {{ $ticket->Status=='InProgress' ? 'selected':'' }}>In Progress</option>
                                    <option value="Resolved"   {{ $ticket->Status=='Resolved'   ? 'selected':'' }}>Resolved</option>
                                    <option value="Closed"     {{ $ticket->Status=='Closed'     ? 'selected':'' }}>Closed</option>
                                </select>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align:center; padding: 50px; color:#475569;">
                        <div style="font-size:2.5rem; margin-bottom:10px; opacity:0.4;">📭</div>
                        <div style="font-weight:600;">No tickets found.</div>
                        @if(request()->anyFilled(['search','status','sender_type','from_date','to_date']))
                            <a href="{{ route('admin.reports') }}" style="color:#818cf8; font-size:0.85rem; margin-top:8px; display:block;">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tickets->hasPages())
    <div style="padding: 20px 22px;">
        {{ $tickets->withQueryString()->links() }}
    </div>
    @endif
</div>

</div>
@endsection
