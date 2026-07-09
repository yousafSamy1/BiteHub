@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content" style="background: #090e1a; min-height: 100vh;">

<!-- Background Glow Effects -->
<div style="position: absolute; top: 0; right: 0; width: 600px; height: 600px; background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 70%); pointer-events: none; z-index: 0;"></div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap');
    
    .ticket-wrapper { 
        font-family: 'Outfit', sans-serif; 
        position: relative; 
        z-index: 1; 
        max-width: 1400px; 
        margin: 0 auto;
        color: #e2e8f0;
    }
    
    .premium-card {
        background: rgba(17, 25, 40, 0.75);
        backdrop-filter: blur(20px) saturate(180%);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 30px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .metric-card {
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid rgba(255, 255, 255, 0.05);
        border-radius: 20px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .neon-badge {
        padding: 6px 18px;
        border-radius: 100px;
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .neon-indigo { background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); box-shadow: 0 0 15px rgba(99, 102, 241, 0.2); }
    .neon-gold { background: rgba(251, 191, 36, 0.15); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.3); box-shadow: 0 0 15px rgba(251, 191, 36, 0.2); }
    .neon-emerald { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); box-shadow: 0 0 15px rgba(16, 185, 129, 0.2); }

    .glow-btn {
        border-radius: 18px;
        padding: 14px 28px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    .glow-btn-primary { background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); }
    .glow-btn-primary:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 30px rgba(99, 102, 241, 0.6); }
    
    .glow-btn-gold { background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #1e293b; box-shadow: 0 10px 25px rgba(251, 191, 36, 0.4); }
    .glow-btn-gold:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 15px 30px rgba(251, 191, 36, 0.6); }

    .desc-box {
        background: #0f172a;
        border: 1px solid rgba(255,255,255,0.05);
        border-radius: 20px;
        padding: 25px;
        color: #94a3b8;
        line-height: 1.8;
        font-size: 1rem;
        position: relative;
    }
    .desc-box::after { content: '"'; position: absolute; top: 10px; right: 20px; font-size: 4rem; color: rgba(255,255,255,0.03); font-family: serif; }

    .form-input-premium {
        background: rgba(0,0,0,0.3);
        border: 1px solid rgba(255,255,255,0.1);
        border-radius: 15px;
        color: #fff;
        padding: 15px;
        width: 100%;
        transition: 0.3s;
    }
    .form-input-premium:focus { border-color: #6366f1; background: rgba(0,0,0,0.5); outline: none; }
</style>

<div class="ticket-wrapper" style="padding-top: 20px;">
    
    <!-- Header Area -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('admin.reports') }}" class="btn-back">
                <i data-feather="arrow-left" style="width: 20px;"></i>
            </a>
            <div>
                <h2 style="color:#fff; font-weight:800; margin:0; font-size:1.6rem;">Ticket <span class="text-primary">#{{ $ticket->TicketID }}</span></h2>
                <div class="d-flex align-items-center gap-2 mt-1">
                    <span class="neon-badge {{ $ticket->Status == 'Open' ? 'neon-indigo' : ($ticket->Status == 'Resolved' ? 'neon-emerald' : 'neon-gold') }}">
                        {{ $ticket->Status }}
                    </span>
                    <span class="text-muted small"><i data-feather="calendar" style="width:12px;"></i> {{ \Carbon\Carbon::parse($ticket->created_at)->format('d M, Y') }}</span>
                </div>
            </div>
        </div>
        
        <div class="d-flex gap-2">
             @foreach(['InProgress' => 'neon-indigo', 'Resolved' => 'neon-emerald', 'Closed' => 'neon-gold'] as $st => $cls)
                @if($ticket->Status !== $st && $ticket->Status !== 'Resolved' && $ticket->Status !== 'Closed')
                <form method="POST" action="{{ route('admin.reports.status', $ticket->TicketID) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $st }}">
                    <button type="submit" class="neon-badge {{ $cls }}" style="cursor:pointer; background:transparent; border-width: 1px;">Set {{ $st }}</button>
                </form>
                @endif
            @endforeach
            
            @if($ticket->Status == 'Resolved' || $ticket->Status == 'Closed')
                <form method="POST" action="{{ route('admin.reports.status', $ticket->TicketID) }}">
                    @csrf
                    <input type="hidden" name="status" value="Open">
                    <button type="submit" class="neon-badge neon-indigo" style="cursor:pointer; background:transparent; border-width: 1px;"><i data-feather="refresh-cw" style="width:10px;"></i> Re-open Ticket</button>
                </form>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Details & History -->
        <div class="col-lg-8">
            
            <!-- Customer & Subject Card -->
            <div class="premium-card p-4 mb-4">
                <div class="d-flex justify-content-between mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar-main">
                             @php
                                $avatarUrl = (!empty($ticket->UserImage) && file_exists(public_path('upload/admin_images/'.$ticket->UserImage)))
                                    ? url('upload/admin_images/'.$ticket->UserImage)
                                    : 'https://ui-avatars.com/api/?name='.urlencode($ticket->UserName ?? 'U').'&background=6366f1&color=fff&bold=true';
                            @endphp
                            <img src="{{ $avatarUrl }}" style="width:50px; height:50px; border-radius:15px; object-fit:cover;">
                        </div>
                        <div>
                            <h5 class="text-white mb-0">{{ $ticket->UserName }}</h5>
                            <span class="text-muted small">{{ $ticket->UserEmail }} • <span class="text-info">{{ $ticket->SenderType }}</span></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-dark border border-secondary px-3 py-2" style="border-radius:10px;">{{ $ticket->Category }}</span>
                    </div>
                </div>

                <div class="p-3 rounded mb-4" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.05);">
                    <h6 class="text-primary fw-bold small mb-2" style="text-transform:uppercase; letter-spacing:1px;">Subject</h6>
                    <p class="text-white fw-bold mb-3" style="font-size:1.1rem;">{{ $ticket->Subject }}</p>
                    <h6 class="text-muted small mb-2" style="text-transform:uppercase; letter-spacing:1px;">Description</h6>
                    <div class="text-light opacity-75" style="line-height:1.7;">{!! nl2br(e($ticket->Description)) !!}</div>
                </div>

                @if($ticket->AdminReply)
                <div class="reply-history mt-4 pt-4 border-top border-secondary border-opacity-25">
                    <h6 class="text-success fw-bold small mb-3 d-flex align-items-center gap-2">
                        <i data-feather="message-square" style="width:14px;"></i> Last Admin Response
                    </h6>
                    <div class="p-3 rounded" style="background: rgba(16,185,129,0.05); border-left: 3px solid #10b981;">
                        <p class="text-light mb-0" style="font-size:0.95rem; line-height:1.6;">{{ $ticket->AdminReply }}</p>
                        <div class="text-muted mt-2 text-end" style="font-size:0.7rem;">Updated: {{ \Carbon\Carbon::parse($ticket->updated_at)->diffForHumans() }}</div>
                    </div>
                </div>
                @endif
            </div>

            @if($ticket->OrderID && $order && ($ticket->Status !== 'Resolved' && $ticket->Status !== 'Closed'))
            <!-- Order Context Card -->
            <div class="premium-card p-4">
                <h6 class="text-muted fw-bold small mb-3 d-flex align-items-center gap-2">
                    <i data-feather="package" style="width:14px;"></i> Linked Order Details
                </h6>
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-white fw-bold mb-1" style="font-size: 1.1rem;">Order #{{ $order->OrderID }}</p>
                        @if($order->kitchenOwner)
                            <p class="text-muted small mb-0">Kitchen: <span class="text-info">{{ $order->kitchenOwner->KitchenName }}</span></p>
                        @elseif($order->caterer)
                            <p class="text-muted small mb-0">Caterer: <span class="text-warning">{{ $order->caterer->BusinessName }}</span></p>
                        @else
                            <p class="text-muted small mb-0">Provider: <span class="text-muted">N/A</span></p>
                        @endif
                    </div>
                    <div class="text-end">
                        <div class="text-white fw-bold" style="font-size:1.2rem;">{{ number_format($order->TotalPrice, 2) }} EGP</div>
                        <span class="badge bg-success bg-opacity-10 text-success small">{{ $order->OrderStatus }}</span>
                    </div>
                </div>
                <hr class="my-3 opacity-25">
                <div class="d-grid">
                    <button onclick="toggleRefund()" class="btn btn-sm btn-outline-warning py-2" style="border-radius:10px;">
                        <i data-feather="dollar-sign" style="width:14px;"></i> Open Financial Resolution Tools
                    </button>
                </div>

                <!-- Collapsible Refund Form -->
                <div id="refundContent" style="max-height: 0; overflow: hidden; transition: 0.4s;">
                    <form method="POST" action="{{ route('admin.reports.process_refund', $ticket->TicketID) }}" class="mt-4 p-3 rounded" style="background: rgba(0,0,0,0.2);">
                        @csrf
                        <div class="row g-3">
                            <div class="col-6">
                                <label class="text-muted small mb-1">Refund Amount</label>
                                <input type="number" step="0.01" name="refund_amount" value="{{ $order->TotalPrice }}" class="form-control bg-dark text-white border-secondary">
                            </div>
                            <div class="col-6">
                                <label class="text-muted small mb-1">Loyalty Points</label>
                                <input type="number" name="loyalty_points" value="0" class="form-control bg-dark text-white border-secondary">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 mt-3 fw-bold">Execute Refund</button>
                    </form>
                </div>
            </div>
            @endif

        </div>

        <!-- Side Column: Action Form -->
        <div class="col-lg-4">
            @if($ticket->Status !== 'Resolved' && $ticket->Status !== 'Closed')
            <div class="premium-card p-4 sticky-top" style="top: 20px;">
                <h5 class="text-white mb-4 d-flex align-items-center gap-2">
                    <i data-feather="send" class="text-primary"></i> Send Response
                </h5>
                <form method="POST" action="{{ route('admin.reports.reply', $ticket->TicketID) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="text-muted small mb-2 d-block">Resolution Notes <span class="text-danger">*</span></label>
                        <textarea name="admin_reply" class="form-input-premium" rows="8" placeholder="Type your response to the user..." required style="min-height: 200px;">{{ old('admin_reply') }}</textarea>
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-muted small mb-2 d-block">Update Ticket Status</label>
                         <select name="status" class="form-input-premium">
                            <option value="InProgress" {{ $ticket->Status == 'InProgress' ? 'selected' : '' }}>InProgress</option>
                            <option value="Resolved" {{ $ticket->Status == 'Resolved' ? 'selected' : '' }}>Resolved</option>
                            <option value="Closed" {{ $ticket->Status == 'Closed' ? 'selected' : '' }}>Closed</option>
                        </select>
                    </div>

                    <button type="submit" class="glow-btn glow-btn-primary w-100 py-3">
                        SUBMIT RESPONSE
                    </button>
                </form>

                <div class="mt-4 p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px dashed rgba(255,255,255,0.1);">
                    <p class="text-muted small mb-0">
                        <i data-feather="info" style="width:12px;"></i> Sending a response will notify the user and update the ticket in their dashboard.
                    </p>
                </div>
            </div>
            @else
            <div class="premium-card p-4 sticky-top" style="top: 20px; border-color: rgba(16,185,129,0.3); background: rgba(16,185,129,0.02);">
                <div class="text-center py-4">
                    <div class="mb-3" style="width:60px; height:60px; background: rgba(16,185,129,0.1); border-radius:50%; display:inline-flex; align-items:center; justify-content:center; color:#10b981;">
                        <i data-feather="lock" style="width:30px; height:30px;"></i>
                    </div>
                    <h5 class="text-white mb-2">Ticket Archiving</h5>
                    <p class="text-muted small px-3">This ticket has been marked as <strong>{{ $ticket->Status }}</strong>. Further responses are disabled.</p>
                    <hr class="my-4 opacity-10">
                    <p class="text-muted small">To send a new message, please <span class="text-primary fw-bold">Re-open</span> the ticket using the button above.</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
    .btn-back { background: rgba(255,255,255,0.05); width: 45px; height: 45px; border-radius: 12px; display: flex; align-items: center; justify-content: center; color: #fff; transition: 0.3s; }
    .btn-back:hover { background: rgba(99,102,241,0.2); transform: translateX(-3px); }
</style>

<script>
    function toggleRefund() {
        const content = document.getElementById('refundContent');
        content.style.maxHeight = content.style.maxHeight === '0px' || content.style.maxHeight === '' ? '500px' : '0px';
    }
</script>

</div>
@endsection
