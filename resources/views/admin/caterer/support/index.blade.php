@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">

<style>
.support-hero {
    background: linear-gradient(135deg, #be185d 0%, #9333ea 100%);
    border-radius: 18px;
    padding: 32px 40px;
    color: #fff;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
}
.support-hero::after {
    content: '';
    position: absolute;
    top: -40%;
    right: -5%;
    width: 280px; height: 280px;
    background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
    border-radius: 50%;
    pointer-events: none;
}
.support-card {
    background: #1e293b;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,0.06);
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.support-card .card-title { color: #f1f5f9; font-weight: 700; }
.form-label-dark { color: #94a3b8; font-weight: 600; font-size: 0.85rem; }
.input-dark {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 10px;
    color: #f1f5f9;
    padding: 10px 14px;
    width: 100%;
    transition: border-color 0.2s;
}
.input-dark:focus { outline: none; border-color: #a855f7; background: rgba(168,85,247,0.07); color: #f1f5f9; }
.input-dark option { background: #1e293b; color: #f1f5f9; }
.btn-submit-ticket {
    background: linear-gradient(135deg, #be185d, #9333ea);
    color: #fff;
    border: none;
    border-radius: 10px;
    font-weight: 700;
    padding: 12px 28px;
    transition: all 0.2s;
    cursor: pointer;
}
.btn-submit-ticket:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(147,51,234,0.35); }
.ticket-row { transition: background 0.15s; }
.ticket-row:hover { background: rgba(255,255,255,0.03); }
.badge-status-open       { background: rgba(245,158,11,0.15);  color: #fbbf24; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; border: 1px solid rgba(245,158,11,0.3); }
.badge-status-inprogress { background: rgba(6,182,212,0.15);   color: #22d3ee; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; border: 1px solid rgba(6,182,212,0.3); }
.badge-status-resolved   { background: rgba(16,185,129,0.15);  color: #34d399; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; border: 1px solid rgba(16,185,129,0.3); }
.badge-status-closed     { background: rgba(148,163,184,0.15); color: #94a3b8; padding: 4px 12px; border-radius: 20px; font-weight: 700; font-size: 0.75rem; border: 1px solid rgba(148,163,184,0.3); }
</style>

<!-- Page Header -->
<div class="support-hero">
    <div style="z-index:1; position:relative;">
        <h2 class="fw-bold mb-1" style="font-size:1.8rem;">🎉 Caterer Support Center</h2>
        <p class="mb-0" style="opacity:0.82; font-size:1rem;">Submit a request to the admin team for any problem or concern. We typically respond within 24 hours.</p>
    </div>
</div>

<div class="row g-4">

    <!-- ── Submit New Ticket ─────────────────────────────────────────── -->
    <div class="col-xl-5">
        <div class="support-card p-4">
            <h5 class="card-title mb-4"><i data-feather="plus-circle" style="width:18px;color:#a855f7" class="me-2"></i>Submit New Request</h5>

            <form method="POST" action="{{ route('caterer.support.store') }}">
                @csrf

                <div class="mb-3">
                    <label class="form-label-dark mb-1">Problem Category <span class="text-danger">*</span></label>
                    <select name="category" class="input-dark" required>
                        <option value="">— Select a category —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label-dark mb-1">Subject <span class="text-danger">*</span></label>
                    <input type="text" name="subject" class="input-dark" placeholder="Brief summary of the issue" value="{{ old('subject') }}" required>
                    @error('subject')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <div class="mb-4">
                    <label class="form-label-dark mb-1">Description <span class="text-danger">*</span></label>
                    <textarea name="description" class="input-dark" rows="6" placeholder="Describe the issue in detail — include relevant catering request IDs, event dates, customer behavior, etc..." required>{{ old('description') }}</textarea>
                    @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>

                <button type="submit" class="btn-submit-ticket w-100">
                    <i data-feather="send" style="width:15px" class="me-2"></i> Submit Request
                </button>
            </form>
        </div>

        <!-- Info Box -->
        <div class="support-card p-4 mt-4" style="border-color: rgba(168,85,247,0.2);">
            <h6 style="color:#d8b4fe; font-weight:700; margin-bottom:12px;"><i data-feather="info" style="width:15px" class="me-2"></i>Common Issues We Handle</h6>
            <ul class="list-unstyled mb-0" style="color:#94a3b8; font-size:0.9rem;">
                <li class="mb-2">💳 Payment delays or billing discrepancies</li>
                <li class="mb-2">📋 Catering requests that have problematic requirements</li>
                <li class="mb-2">😤 Rude or abusive customer behavior</li>
                <li class="mb-2">📜 Contract or agreement disputes</li>
                <li class="mb-2">⚙️ Platform bugs affecting your operations</li>
                <li class="mb-0">🏛️ Policy violations or unfair account actions</li>
            </ul>
        </div>
    </div>

    <!-- ── My Tickets ────────────────────────────────────────────────── -->
    <div class="col-xl-7">
        <div class="support-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title mb-0"><i data-feather="list" style="width:18px;color:#c084fc" class="me-2"></i>My Submitted Requests</h5>
                <span style="color:#94a3b8; font-size:0.85rem;">{{ $tickets->total() }} total</span>
            </div>

            @if($tickets->isEmpty())
                <div class="text-center py-5" style="color:#475569;">
                    <i data-feather="inbox" style="width:48px; height:48px; opacity:0.3;"></i>
                    <p class="mt-3 mb-0">No support requests yet.<br><span style="font-size:0.85rem;">Use the form on the left to submit one.</span></p>
                </div>
            @else
                <div class="table-responsive">
                    <table style="width:100%; border-collapse:separate; border-spacing:0;">
                        <thead>
                            <tr>
                                <th style="color:#64748b; font-size:0.75rem; font-weight:700; text-transform:uppercase; padding:10px 12px; border-bottom:1px solid rgba(255,255,255,0.06);">Ticket</th>
                                <th style="color:#64748b; font-size:0.75rem; font-weight:700; text-transform:uppercase; padding:10px 12px; border-bottom:1px solid rgba(255,255,255,0.06);">Category</th>
                                <th style="color:#64748b; font-size:0.75rem; font-weight:700; text-transform:uppercase; padding:10px 12px; border-bottom:1px solid rgba(255,255,255,0.06);">Status</th>
                                <th style="color:#64748b; font-size:0.75rem; font-weight:700; text-transform:uppercase; padding:10px 12px; border-bottom:1px solid rgba(255,255,255,0.06);">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr class="ticket-row">
                                <td style="padding:14px 12px; border-bottom:1px solid rgba(255,255,255,0.03);">
                                    <div style="color:#f1f5f9; font-weight:600; font-size:0.9rem;">#{{ $ticket->TicketID }}</div>
                                    <div style="color:#64748b; font-size:0.8rem; margin-top:2px;">{{ Str::limit($ticket->Subject, 38) }}</div>
                                    @if($ticket->AdminReply)
                                        <div style="margin-top:6px;">
                                            <span style="background:rgba(16,185,129,0.1); color:#34d399; font-size:0.72rem; padding:2px 8px; border-radius:10px; border:1px solid rgba(16,185,129,0.2);">
                                                <i data-feather="message-circle" style="width:10px" class="me-1"></i> Admin replied
                                            </span>
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:14px 12px; border-bottom:1px solid rgba(255,255,255,0.03); color:#94a3b8; font-size:0.85rem;">{{ $ticket->Category }}</td>
                                <td style="padding:14px 12px; border-bottom:1px solid rgba(255,255,255,0.03);">
                                    @php
                                        $sc = match($ticket->Status) {
                                            'Open'       => 'badge-status-open',
                                            'InProgress' => 'badge-status-inprogress',
                                            'Resolved'   => 'badge-status-resolved',
                                            default      => 'badge-status-closed',
                                        };
                                    @endphp
                                    <span class="{{ $sc }}">{{ $ticket->Status }}</span>
                                </td>
                                <td style="padding:14px 12px; border-bottom:1px solid rgba(255,255,255,0.03); color:#64748b; font-size:0.82rem;">
                                    {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y') }}
                                </td>
                            </tr>
                            @if($ticket->AdminReply)
                            <tr>
                                <td colspan="4" style="padding:0 12px 14px 12px; border-bottom:1px solid rgba(255,255,255,0.03);">
                                    <div style="background:rgba(16,185,129,0.06); border:1px solid rgba(16,185,129,0.15); border-radius:10px; padding:12px 16px;">
                                        <div style="color:#34d399; font-size:0.75rem; font-weight:700; margin-bottom:6px;"><i data-feather="shield" style="width:12px" class="me-1"></i> Admin Response</div>
                                        <div style="color:#cbd5e1; font-size:0.88rem; line-height:1.6;">{{ $ticket->AdminReply }}</div>
                                    </div>
                                </td>
                            </tr>
                            @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">{{ $tickets->links() }}</div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
