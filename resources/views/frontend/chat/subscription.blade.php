@extends('frontend.layouts.app')

@section('title', 'Plan Chat')

@section('content')
<section class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="mb-1 fw-bold" style="color:var(--text-primary)">
                    <i class="fas fa-comments me-2" style="color:var(--primary)"></i>Plan Chat
                </h4>
                <p style="color:var(--text-muted)" class="mb-0">Discuss customizations with your kitchen.</p>
            </div>
            <a href="{{ route('frontend.subscriptions') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> My Plans
            </a>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8">

                {{-- Plan Info Bar --}}
                <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:15px; padding:14px 20px; margin-bottom:16px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px;">
                    <div>
                        <span style="font-weight:800; color:var(--text-primary);">Plan:</span>
                        <span style="color:var(--text-muted); margin-left:8px;">
                            {{ \Carbon\Carbon::parse($sub->StartDate)->format('d M') }} →
                            {{ \Carbon\Carbon::parse($sub->EndDate)->format('d M Y') }}
                            &bull; {{ $sub->MealsPerDay }} meal(s)/day
                        </span>
                    </div>
                    <span style="background:{{ $sub->Status === 'PendingApproval' ? 'rgba(251,191,36,0.15)' : 'rgba(74,222,128,0.15)' }}; color:{{ $sub->Status === 'PendingApproval' ? '#fbbf24' : '#4ade80' }}; border-radius:10px; padding:4px 14px; font-weight:700; font-size:0.8rem;">
                        {{ $sub->Status }}
                    </span>
                </div>

                {{-- Chat Card --}}
                <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:20px; overflow:hidden;">

                    {{-- Header --}}
                    <div style="padding:15px 20px; border-bottom:1px solid var(--border-color); display:flex; align-items:center; gap:14px;">
                        <div style="width:44px; height:44px; border-radius:50%; background:var(--primary); display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0;">🍳</div>
                        <div>
                            <div style="font-weight:800; color:var(--text-primary); line-height:1.2;">Kitchen Support</div>
                            <small style="color:#4ade80;"><i class="fas fa-circle" style="font-size:7px;"></i> Available</small>
                        </div>
                    </div>

                    {{-- Messages --}}
                    <div id="chatBody" style="height:420px; overflow-y:auto; padding:25px; background:var(--bg-dark);">
                        @forelse($messages as $msg)
                            @php $isMe = $msg->SenderID == Auth::id(); @endphp
                            <div class="d-flex {{ $isMe ? 'justify-content-end' : 'justify-content-start' }} mb-4">
                                <div style="max-width:72%;">
                                    @if(!$isMe)
                                        <small style="color:var(--text-muted); display:block; margin-bottom:4px; font-weight:700;">Kitchen</small>
                                    @endif
                                    <div class="{{ $isMe ? 'bubble-me' : 'bubble-them' }}"
                                         style="padding:11px 16px; border-radius:{{ $isMe ? '15px 15px 3px 15px' : '15px 15px 15px 3px' }};">
                                        <p class="bubble-text" style="margin:0; font-size:0.95rem; line-height:1.55; word-break:break-word;">{{ $msg->Message }}</p>
                                    </div>
                                    <div style="font-size:0.72rem; color:var(--text-muted); margin-top:4px; {{ $isMe ? 'text-align:right' : '' }}">
                                        {{ \Carbon\Carbon::parse($msg->Timestamp)->format('H:i, d M') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div style="text-align:center; padding:70px 20px; color:var(--text-muted);">
                                <i class="fas fa-comments fa-3x" style="opacity:0.18; display:block; margin-bottom:15px;"></i>
                                <p style="margin:0;">No messages yet. Start the conversation!</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Input --}}
                    <div style="padding:16px; border-top:1px solid var(--border-color); background:var(--bg-card);">
                        @if(session('message'))
                            <div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show py-2 mb-2">
                                {{ session('message') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        <form action="{{ route('frontend.subscriptions.chat.send', $sub->SubscriptionID) }}" method="POST">
                            @csrf
                            <div style="display:flex; gap:10px;">
                                <input
                                    type="text"
                                    name="message"
                                    id="chatInput"
                                    style="flex:1; background:rgba(255,255,255,0.06); border:1px solid var(--border-color); border-radius:12px; padding:11px 18px; color:#ffffff; font-size:0.95rem; outline:none;"
                                    placeholder="Type your message..."
                                    required
                                    autofocus>
                                <button type="submit" style="background:var(--primary); border:none; border-radius:12px; padding:10px 22px; color:#fff; font-weight:700; cursor:pointer; display:flex; align-items:center; gap:7px; white-space:nowrap; flex-shrink:0;">
                                    <i class="fas fa-paper-plane"></i> Send
                                </button>
                            </div>
                        </form>
                    </div>

                </div>{{-- /chat card --}}

            </div>
        </div>
    </div>
</section>

<script>
    // Auto-scroll to bottom on page load
    window.addEventListener('load', function () {
        var el = document.getElementById('chatBody');
        if (el) el.scrollTop = el.scrollHeight;
    });

    // Auto-refresh every 5s to pick up new kitchen replies (skip if user is typing)
    setInterval(function () {
        var input = document.getElementById('chatInput');
        if (input && input.value.trim() === '') {
            location.reload();
        }
    }, 5000);
</script>
@endsection