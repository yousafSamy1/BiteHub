@extends('frontend.layouts.app')
@section('title', 'Ticket #' . $ticket->TicketID)

@section('content')
<style>
.sup-wrap { padding: calc(var(--nav-h, 80px) + 40px) 0 80px; }
.sup-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 36px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.t-badge { display: inline-block; padding: 5px 16px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; }
.t-badge-open       { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
.t-badge-inprogress { background: rgba(6,182,212,0.12);  color: #22d3ee; border: 1px solid rgba(6,182,212,0.25); }
.t-badge-resolved   { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
.t-badge-closed     { background: rgba(100,116,139,0.12);color: #94a3b8; border: 1px solid rgba(100,116,139,0.25); }
.meta-row { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; margin-bottom: 6px; }
.meta-label { color: var(--text-muted); font-size: 0.8rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; min-width: 100px; }
.meta-value { color: var(--text-primary); font-size: 0.92rem; font-weight: 600; }
.description-box {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 20px 24px;
    color: var(--text-secondary);
    font-size: 0.95rem;
    line-height: 1.8;
    white-space: pre-wrap;
    margin-top: 12px;
}
.reply-box {
    background: rgba(16,185,129,0.05);
    border: 1px solid rgba(16,185,129,0.2);
    border-radius: 16px;
    padding: 24px 28px;
    margin-top: 28px;
}
.reply-header { color: #34d399; font-weight: 800; font-size: 1rem; margin-bottom: 16px; display: flex; align-items: center; gap: 10px; }
.reply-text { color: var(--text-secondary); font-size: 0.95rem; line-height: 1.8; white-space: pre-wrap; }
.btn-back {
    background: rgba(255,255,255,0.06);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
    border-radius: 10px;
    padding: 10px 22px;
    font-weight: 700;
    font-size: 0.9rem;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
}
.btn-back:hover { background: rgba(255,255,255,0.1); color: var(--text-primary); }
.no-reply-box {
    background: rgba(245,158,11,0.05);
    border: 1px solid rgba(245,158,11,0.15);
    border-radius: 14px;
    padding: 20px 24px;
    margin-top: 28px;
    display: flex;
    align-items: center;
    gap: 14px;
    color: #fbbf24;
}
</style>

<section class="sup-wrap">
<div class="container" style="max-width: 760px;">

    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('customer.support') }}" class="btn-back">
            ← Back to Support
        </a>
    </div>

    <div class="sup-card reveal">

        <!-- Ticket Header -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 16px; margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1px solid var(--border-color);">
            <div>
                <div style="font-size: 0.8rem; color: var(--text-muted); font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px;">Support Ticket</div>
                <h2 style="margin: 0 0 10px; font-size: 1.5rem; font-weight: 900; color: var(--text-primary);">
                    #{{ $ticket->TicketID }} — {{ $ticket->Subject }}
                </h2>
                @php
                    $cls = match($ticket->Status) {
                        'Open'       => 't-badge-open',
                        'InProgress' => 't-badge-inprogress',
                        'Resolved'   => 't-badge-resolved',
                        default      => 't-badge-closed',
                    };
                @endphp
                <span class="t-badge {{ $cls }}">{{ $ticket->Status }}</span>
            </div>
            <div style="text-align:right; color: var(--text-muted); font-size: 0.85rem;">
                <div>Submitted</div>
                <div style="font-weight:700; color: var(--text-secondary);">{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Ticket Meta -->
        <div class="sup-meta-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; margin-bottom: 24px;">
            <style>@media(max-width:576px){ .sup-meta-grid { grid-template-columns: 1fr !important; } }</style>
            <div>
                <div class="meta-label">Category</div>
                <div class="meta-value" style="background: rgba(255,107,53,0.08); color: #fb923c; padding: 4px 12px; border-radius: 8px; display: inline-block; font-size:0.85rem;">{{ $ticket->Category }}</div>
            </div>
            <div>
                <div class="meta-label">Sender Type</div>
                <div class="meta-value">{{ $ticket->SenderType }}</div>
            </div>
            @if($ticket->OrderID)
            <div>
                <div class="meta-label">Related Order</div>
                <div class="meta-value">
                    <a href="{{ route('frontend.tracking', $ticket->OrderID) }}" style="color: var(--primary); font-weight: 700;">#{{ $ticket->OrderID }} →</a>
                </div>
            </div>
            @endif
            <div>
                <div class="meta-label">Last Updated</div>
                <div class="meta-value">{{ \Carbon\Carbon::parse($ticket->updated_at)->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Ticket Description -->
        <div>
            <div class="meta-label mb-1" style="font-size:0.78rem;">Your Report</div>
            <div class="description-box">{{ $ticket->Description }}</div>
        </div>

        <!-- Admin Reply -->
        @if($ticket->AdminReply)
            <div class="reply-box">
                <div class="reply-header">
                    <span style="background: rgba(16,185,129,0.15); width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">🛡️</span>
                    Admin Response
                </div>
                <div class="reply-text">{{ $ticket->AdminReply }}</div>
            </div>
        @else
            <div class="no-reply-box">
                <span style="font-size: 1.5rem;">⏳</span>
                <div>
                    <div style="font-weight: 700; margin-bottom: 4px;">Awaiting Admin Response</div>
                    <div style="font-size: 0.85rem; opacity: 0.8;">Our team is reviewing your report. We typically respond within 24 hours.</div>
                </div>
            </div>
        @endif

    </div>

    <!-- Action Bar -->
    <div style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
        <a href="{{ route('customer.support') }}" class="btn-back">← All My Tickets</a>
        @if(!in_array($ticket->Status, ['Resolved', 'Closed']))
            <span style="display:flex; align-items:center; gap:8px; color:var(--text-muted); font-size:0.85rem; padding:10px 0;">
                <span style="width:8px; height:8px; background: #fbbf24; border-radius:50%; display:inline-block; animation: pulse 2s infinite;"></span>
                Ticket is open — admin will respond soon
            </span>
        @endif
    </div>

</div>
</section>

<style>
@keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
</style>
@endsection
