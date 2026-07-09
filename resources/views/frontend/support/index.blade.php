@extends('frontend.layouts.app')
@section('title', 'Support Center')

@section('content')
<style>
:root { --sup-accent: #ff6b35; }
.sup-wrap { padding: calc(var(--nav-h, 80px) + 40px) 0 80px; }

.sup-hero {
    background: linear-gradient(135deg, rgba(255,107,53,0.15), rgba(255,167,38,0.07));
    border: 1px solid rgba(255,107,53,0.2);
    border-radius: 20px;
    padding: 38px 44px;
    margin-bottom: 40px;
    position: relative;
    overflow: hidden;
}
.sup-hero::after {
    content: '';
    position: absolute;
    top: -60px; right: -60px;
    width: 220px; height: 220px;
    background: rgba(255,107,53,0.12);
    filter: blur(60px);
    border-radius: 50%;
    pointer-events: none;
}

.sup-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 32px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
}
.sup-card-title { font-size: 1.1rem; font-weight: 800; color: var(--text-primary); margin-bottom: 24px; display: flex; align-items: center; gap: 10px; }

.sup-form-label { color: var(--text-muted); font-size: 0.83rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; margin-bottom: 8px; display: block; }
.sup-input {
    background: var(--bg-glass2);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    color: var(--text-primary);
    padding: 12px 16px;
    width: 100%;
    font-size: 0.95rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}
.sup-input:focus { outline: none; border-color: var(--sup-accent); box-shadow: 0 0 0 3px rgba(255,107,53,0.12); }
.sup-input option { background: var(--bg-card); color: var(--text-primary); }

.btn-sup-submit {
    background: linear-gradient(135deg, #ff6b35, #f59e0b);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 800;
    font-size: 1rem;
    padding: 14px 28px;
    width: 100%;
    cursor: pointer;
    transition: all 0.25s;
    letter-spacing: 0.02em;
}
.btn-sup-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255,107,53,0.4); }

.ticket-item {
    background: rgba(255,255,255,0.02);
    border: 1px solid var(--border-color);
    border-radius: 14px;
    padding: 18px 22px;
    margin-bottom: 14px;
    transition: all 0.2s;
    cursor: pointer;
    text-decoration: none !important;
    display: block;
    color: inherit;
}
.ticket-item:hover { border-color: rgba(255,107,53,0.3); background: rgba(255,107,53,0.03); transform: translateX(4px); }

.t-badge {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 800;
    letter-spacing: 0.03em;
}
.t-badge-open       { background: rgba(245,158,11,0.12); color: #fbbf24; border: 1px solid rgba(245,158,11,0.25); }
.t-badge-inprogress { background: rgba(6,182,212,0.12);  color: #22d3ee; border: 1px solid rgba(6,182,212,0.25); }
.t-badge-resolved   { background: rgba(16,185,129,0.12); color: #34d399; border: 1px solid rgba(16,185,129,0.25); }
.t-badge-closed     { background: rgba(100,116,139,0.12);color: #94a3b8; border: 1px solid rgba(100,116,139,0.25); }

.cat-chip {
    display: inline-block;
    background: rgba(255,107,53,0.09);
    color: #fb923c;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 0.75rem;
    font-weight: 700;
    border: 1px solid rgba(255,107,53,0.18);
}

.admin-reply-box {
    background: rgba(16,185,129,0.06);
    border: 1px solid rgba(16,185,129,0.2);
    border-radius: 12px;
    padding: 14px 18px;
    margin-top: 10px;
}

/* ─── CUSTOM PAGINATION ─── */
.sup-pagination {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    gap: 12px;
}
.btn-sup-nav {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 11px 16px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.88rem;
    transition: all 0.25s;
    text-decoration: none !important;
    border: 1px solid var(--border-color);
    background: var(--bg-card2);
    color: var(--text-primary);
}
.btn-sup-nav:hover:not(.disabled) {
    background: linear-gradient(135deg, #ff6b35, #f59e0b);
    color: #fff;
    border-color: transparent;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,107,53,0.35);
}
.btn-sup-nav.disabled {
    opacity: 0.35;
    cursor: not-allowed;
    filter: grayscale(1);
    background: rgba(255,255,255,0.02);
}
    box-shadow: 0 4px 15px rgba(255,107,53,0.3);
}
.btn-sup-nav.disabled {
    opacity: 0.4;
    cursor: not-allowed;
    background: rgba(255,255,255,0.02);
}
</style>

<section class="sup-wrap">
<div class="container" style="max-width: 1060px;">

    <!-- Hero -->
    <div class="sup-hero reveal">
        <div style="position:relative;z-index:1;">
            <h1 style="font-size: 2rem; font-weight: 900; color: var(--text-primary); margin-bottom: 10px;">
                🆘 Support Center
            </h1>
            <p style="color: var(--text-muted); font-size: 1rem; margin-bottom: 0; max-width: 600px; line-height: 1.6;">
                Have an issue with your order, delivery, or payment? Report it here and our team will get back to you as soon as possible.
            </p>
        </div>
    </div>

    <div style="display: flex; flex-direction: row; gap: 30px; flex-wrap: wrap;">

        <!-- ── Submit Ticket Form ───────────────────────────────────── -->
        <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; gap: 30px;">
            <!-- Report Form Card -->
            <div class="sup-card reveal">
                <div class="sup-card-title">
                    <span style="background: rgba(255,107,53,0.15); color: #ff6b35; width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">✍️</span>
                    Report a Problem
                </div>

                <form method="POST" action="{{ route('customer.support.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="sup-form-label">What happened? <span style="color:#f87171">*</span></label>
                        <select name="category" class="sup-input" required>
                            <option value="">— Choose a problem type —</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                            @endforeach
                        </select>
                        @error('category')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    @if($orders->isNotEmpty())
                    <div class="mb-3">
                        <label class="sup-form-label">Related Order <span style="color: var(--text-muted); font-weight: 400;">(optional)</span></label>
                        <select name="order_id" class="sup-input">
                            <option value="">— No specific order —</option>
                            @foreach($orders as $order)
                                <option value="{{ $order->OrderID }}" {{ old('order_id') == $order->OrderID ? 'selected' : '' }}>
                                    #{{ $order->OrderID }} — {{ number_format($order->TotalPrice) }} EGP — {{ \Carbon\Carbon::parse($order->CreatedAt)->format('d M Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @endif

                    <div class="mb-3">
                        <label class="sup-form-label">Subject <span style="color:#f87171">*</span></label>
                        <input type="text" name="subject" class="sup-input" placeholder="Brief summary of your issue" value="{{ old('subject') }}" required>
                        @error('subject')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-4">
                        <label class="sup-form-label">Description <span style="color:#f87171">*</span></label>
                        <textarea name="description" class="sup-input" rows="6" placeholder="Tell us everything — what happened, when, and how it affected you. The more detail you give, the faster we can help." required>{{ old('description') }}</textarea>
                        @error('description')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    </div>

                    <button type="submit" class="btn-sup-submit">
                        🚀 Submit Report
                    </button>
                </form>
            </div>

            <!-- Common Issues Box -->
            <div class="sup-card reveal" style="border-color: rgba(255,107,53,0.15); padding: 24px; margin-bottom: 30px;">
                <div style="font-size:0.9rem; font-weight:700; color: var(--primary); margin-bottom:14px;">Common Issues</div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach([
                        ['🚫', 'Order Not Delivered', 'Your order status is Delivered but you never received it.'],
                        ['🍱', 'Wrong Items', 'You received different items than what you ordered.'],
                        ['💳', 'Payment Issue', 'You were charged incorrectly or need a refund.'],
                        ['⏰', 'Late Delivery', 'Your order arrived much later than the promised time.'],
                        ['🛵', 'Driver Behavior', 'The delivery agent was rude or unsafe.'],
                        ['🔄', 'Subscription Problem', 'Issues with your active meal subscription plan.'],
                    ] as [$icon, $title, $desc])
                    <div style="display:flex; gap:12px; align-items:flex-start;">
                        <span style="font-size:1.2rem; line-height:1;">{{ $icon }}</span>
                        <div>
                            <div style="font-weight:700; color:var(--text-primary); font-size:0.88rem;">{{ $title }}</div>
                            <div style="color:var(--text-muted); font-size:0.8rem; margin-top:2px;">{{ $desc }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ── My Tickets ─────────────────────────────────────────── -->
        <div style="flex: 1.4; min-width: 300px;">
            <div class="sup-card reveal">
                <div class="sup-card-title">
                    <span style="background: rgba(96,165,250,0.15); color: #60a5fa; width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem;">📋</span>
                    My Reports
                    <span style="margin-left:auto; font-size:0.8rem; color:var(--text-muted); font-weight:500;">{{ $tickets->total() }} total</span>
                </div>
                <div id="reports-container">
                    @include('frontend.support._tickets_list')
                </div>
            </div>
        </div>

    </div>
</div>
</section>

@push('scripts')
    <script>
        document.addEventListener('click', function(e) {
            const link = e.target.closest('.pagination-link');
            if (link) {
                e.preventDefault();
                const url = link.href;
                if (!url) return;

                const container = document.getElementById('reports-container');
                if (!container) return;

                // Visual feedback
                container.style.opacity = '0.4';
                container.style.pointerEvents = 'none';

                fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.text();
                })
                .then(html => {
                    container.innerHTML = html;
                    
                    // Re-initialize Feather Icons
                    if (typeof feather !== 'undefined') {
                        feather.replace();
                    }
                    
                    // Restore visuals
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                    
                    // Scroll slightly if container is out of view (optional, but requested not to scroll to top)
                })
                .catch(err => {
                    console.error('AJAX Pagination Error:', err);
                    container.style.opacity = '1';
                    container.style.pointerEvents = 'auto';
                });
            }
        });
    </script>
    @if(session('message'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if(typeof showToast === 'function') {
                showToast("{!! addslashes(session('message')) !!}", "{{ session('alert-type') ?? 'success' }}");
            }
        });
    </script>
    @endif
@endpush

@endsection
