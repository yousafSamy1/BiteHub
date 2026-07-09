@if($tickets->isEmpty())
    <div style="text-align:center; padding: 50px 20px; color: var(--text-muted);">
        <div style="font-size:3.5rem; margin-bottom:12px; opacity: 0.4;">📭</div>
        <p style="margin:0; font-size:0.95rem;">No reports yet.<br><span style="font-size:0.85rem;">Use the form to report an issue.</span></p>
    </div>
@else
    @foreach($tickets as $ticket)
    <a href="{{ route('customer.support.show', $ticket->TicketID) }}" class="ticket-item">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; margin-bottom:8px; flex-wrap:wrap;">
            <div>
                <span style="font-weight:800; color:var(--primary); font-size:0.85rem;">#{{ $ticket->TicketID }}</span>
                <span class="ms-2 cat-chip">{{ $ticket->Category }}</span>
            </div>
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
        <div style="font-weight:700; color:var(--text-primary); font-size:0.95rem; margin-bottom:4px;">{{ $ticket->Subject }}</div>
        <div style="color:var(--text-muted); font-size:0.82rem; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
            <span>{{ \Carbon\Carbon::parse($ticket->created_at)->format('d M Y, h:i A') }}</span>
            @if($ticket->OrderID)
                <span>📦 Order #{{ $ticket->OrderID }}</span>
            @endif
        </div>
        @if($ticket->AdminReply)
            <div class="admin-reply-box mt-2">
                <div style="color:#34d399; font-weight:700; font-size:0.75rem; margin-bottom:6px;">🛡️ Admin Response</div>
                <div style="color: var(--text-secondary); font-size:0.85rem; line-height:1.6;">{{ Str::limit($ticket->AdminReply, 120) }}</div>
            </div>
        @endif
    </a>
    @endforeach

    <!-- Custom Pagination UI -->
    @if ($tickets->hasPages())
    <div class="sup-pagination">
        <div style="flex: 1; display: flex; justify-content: flex-start;">
            @if (!$tickets->onFirstPage())
                <a href="{{ $tickets->previousPageUrl() }}" class="btn-sup-nav pagination-link">
                    <i data-feather="arrow-left" style="width:14px"></i> Previous
                </a>
            @endif
        </div>

        <div style="flex: 1; display: flex; justify-content: flex-end;">
            @if ($tickets->hasMorePages())
                <a href="{{ $tickets->nextPageUrl() }}" class="btn-sup-nav pagination-link">
                    Next <i data-feather="arrow-right" style="width:14px"></i>
                </a>
            @endif
        </div>
    </div>
    @endif
@endif
