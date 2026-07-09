@extends('frontend.layouts.app')
@section('title', 'My Dashboard')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<style>
.dashboard-wrap { padding:calc(var(--nav-h) + 40px) 0 80px; }
.hero-card {
    background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(255,167,38,0.05));
    border: 1px solid rgba(255,107,53,0.2);
    border-radius: var(--radius-lg);
    padding: 36px 40px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 30px;
    margin-bottom: 30px;
    position: relative;
    overflow: hidden;
    backdrop-filter: blur(16px);
}
.hero-card::after {
    content: ''; position: absolute; top: -50px; right: -50px;
    width: 200px; height: 200px; background: rgba(255,107,53,0.15);
    filter: blur(50px); border-radius: 50%; z-index: -1;
}

.profile-info { display: flex; align-items: center; gap: 24px; }
.avatar-lg {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, var(--primary), var(--accent));
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; font-weight: 900; color: #fff;
    box-shadow: 0 8px 24px rgba(255,107,53,0.4);
    border: 4px solid var(--bg-card);
}

.stat-box {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    padding: 20px 24px;
    border-radius: var(--radius-md);
    min-width: 160px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
.stat-box .val { font-size: 1.8rem; font-weight: 800; color: var(--text-primary); letter-spacing: -1px; margin-bottom: 4px; }
.stat-box .lbl { font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-weight: 600; }

.action-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 36px;
}
.quick-action {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.quick-action:hover {
    border-color: var(--primary);
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.3);
}
.quick-action .qa-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.3rem;
}
.qa-title { font-weight: 700; font-size: 1.05rem; color: var(--text-primary); margin-bottom: 4px; }
.qa-desc { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; }

/* Orders List */
.orders-header {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 20px;
}
.order-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 20px;
    padding: 24px 30px;
    margin-bottom: 20px;
    display: grid;
    grid-template-columns: 1.5fr 1fr 1.5fr auto;
    align-items: center;
    gap: 30px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
@media (max-width: 991px) {
    .order-card { grid-template-columns: 1fr 1fr; gap: 20px; padding: 20px; }
    .hero-card { padding: 30px 20px; justify-content: center; text-align: center; }
    .profile-info { flex-direction: column; gap: 15px; }
}
@media (max-width: 576px) {
    .order-card { grid-template-columns: 1fr; gap: 15px; }
    .order-card > div { text-align: center; }
    .hero-card { border-radius: 20px; }
    .stat-box { min-width: 100%; }
}
.order-card:hover { 
    transform: translateX(8px);
    border-color: var(--primary-border);
    background: var(--bg-card2);
}
.order-id { font-size: 1.3rem; font-weight: 900; color: var(--primary); letter-spacing: -0.5px; }
.order-date { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; font-weight: 500; }
.order-price { font-size: 1.2rem; font-weight: 800; color: var(--text-primary); }
.status-badge {
    padding: 8px 20px; border-radius: 12px; font-size: 0.75rem; font-weight: 800;
    display: inline-flex; align-items: center; gap: 8px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
@media(max-width: 768px) {
    .order-card { grid-template-columns: 1fr; gap: 16px; text-align: center; }
    .hero-card { justify-content: center; text-align: center; }
    .profile-info { flex-direction: column; }
}
@keyframes slideUp {
    from { transform: translateY(30px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}
</style>

<section class="dashboard-wrap">
<div class="container" style="max-width: 1000px;">

    <!-- 1. Hero / Profile Section -->
    <div class="hero-card reveal">
        <div class="profile-info">
            @php
                $u = auth()->user();
                $uImg = ($u->Image && file_exists(public_path('upload/admin_images/'.$u->Image)))
                    ? asset('upload/admin_images/'.$u->Image)
                    : 'https://ui-avatars.com/api/?name='.urlencode($u->FullName).'&background=ff6b35&color=fff&bold=true&size=120';
            @endphp
            <div class="avatar-lg" style="background-image: url('{{ $uImg }}'); background-size: cover; background-position: center; border: 4px solid var(--primary);">
                @if(!$u->Image) {{ strtoupper(substr($u->FullName ?? 'U', 0, 1)) }} @endif
            </div>
            <div>
                <h1 style="font-size: 2rem; margin-bottom: 6px; letter-spacing: -0.5px; color: #fff;">{{ auth()->user()->FullName }}</h1>
                <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-envelope" style="color: var(--primary)"></i> {{ auth()->user()->Email }}
                </p>
                <div style="margin-top: 12px; display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; background: rgba(255,255,255,0.1); border-radius: 20px; font-size: 0.8rem; font-weight: 600; color: var(--text-secondary);">
                    Customer Account
                </div>
            </div>
        </div>
        
        <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
            <a href="{{ route('frontend.meal_plan_builder') }}" class="btn btn-primary" style="height:fit-content; padding:15px 25px; border-radius:15px; font-weight:800; box-shadow:0 8px 20px rgba(255,107,53,0.3)">
                <i class="fas fa-plus-circle me-2"></i> CREATE NEW MEAL PLAN
            </a>
            <div class="stat-box">
                <div class="val" style="color: #4ade80;">{{ number_format($walletBalance, 2) }} <span style="font-size: 0.9rem; color: var(--text-muted)">EGP</span></div>
                <div class="lbl"><i class="fas fa-wallet" style="color: #4ade80; margin-right: 4px;"></i> Wallet Balance</div>
            </div>
            <div class="stat-box">
                <div class="val" style="color: var(--accent);">{{ number_format($loyaltyPoints) }}</div>
                <div class="lbl"><i class="fas fa-star" style="color: var(--accent); margin-right: 4px;"></i> Loyalty Points</div>
            </div>
        </div>
    </div>

    <!-- 2. Quick Actions -->
    <div style="margin-bottom: 40px;">
        <h3 style="font-size: 1.2rem; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-bolt" style="color: var(--primary);"></i> Things to do
        </h3>
        <div class="action-grid reveal">
            @php
            $actions = [
                ['href' => route('frontend.menu'),          'icon' => 'fa-utensils',    'label' => 'Order Food',     'desc' => 'Browse our full menu and satisfy your cravings.', 'color' => '#ff6b35'],
                ['href' => route('frontend.browse'),        'icon' => 'fa-store',       'label' => 'Kitchens',       'desc' => 'Explore verified home kitchens and caterers.',    'color' => '#a78bfa'],
                ['href' => route('frontend.meal_plan_builder'), 'icon' => 'fa-wand-magic-sparkles','label' => 'Meal Builder', 'desc' => 'Create your own custom weekly or monthly meal plan.', 'color' => '#60a5fa'],
                ['href' => route('frontend.cart'),          'icon' => 'fa-shopping-bag','label' => 'View Cart',      'desc' => 'Check your pending items and checkout.',         'color' => '#f472b6'],
                ['href' => route('frontend.profile') . '#addresses', 'icon' => 'fa-map-marked-alt','label'=> 'My Addresses',  'desc' => 'Manage your delivery locations.', 'color' => '#10b981'],
            ];
            @endphp
            @foreach($actions as $a)
            <a href="{{ $a['href'] }}" class="quick-action">
                <div class="qa-icon" style="background: {{ $a['color'] }}22; color: {{ $a['color'] }};">
                    <i class="fas {{ $a['icon'] }}"></i>
                </div>
                <div>
                    <div class="qa-title">{{ $a['label'] }}</div>
                    <div class="qa-desc">{{ $a['desc'] }}</div>
                </div>
                <i class="fas fa-arrow-right" style="position: absolute; bottom: 24px; right: 24px; color: var(--border-color); font-size: 1.2rem; transition: all 0.3s ease;"></i>
            </a>
            @endforeach
        </div>
    </div>

    <!-- 4. Recent Orders -->
    <div style="margin-top: 40px;">
        <div class="orders-header">
            <h3 style="font-size: 1.2rem; margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-receipt" style="color: var(--primary);"></i> My Recent Orders
            </h3>
        </div>

        <div class="reveal">
            @if($recentOrders->isEmpty())
            <div class="glass-card" style="text-align:center; padding: 60px 20px;">
                <div style="font-size: 4rem; opacity: 0.5; margin-bottom: 16px;">🛵</div>
                <h3 style="font-size: 1.2rem; margin-bottom: 8px;">No orders yet!</h3>
                <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 24px;">Your stomach is empty, let's fix that.</p>
                <a href="{{ route('frontend.menu') }}" class="btn btn-primary">Order Now</a>
            </div>
            @else
                @foreach($recentOrders as $order)
                @php
                    $sc = [
                        'Pending'    => ['bg'=>'rgba(251,191,36,0.1)','c'=>'#fbbf24','i'=>'fa-hourglass-half'],
                        'Confirmed'  => ['bg'=>'rgba(96,165,250,0.1)', 'c'=>'#60a5fa','i'=>'fa-thumbs-up'],
                        'Preparing'  => ['bg'=>'rgba(167,139,250,0.1)','c'=>'#a78bfa','i'=>'fa-fire'],
                        'Ready'      => ['bg'=>'rgba(74,222,128,0.1)', 'c'=>'#4ade80','i'=>'fa-box'],
                        'Delivering' => ['bg'=>'rgba(255,107,53,0.1)', 'c'=>'var(--primary)','i'=>'fa-motorcycle'],
                        'Delivered'  => ['bg'=>'rgba(74,222,128,0.1)', 'c'=>'#4ade80','i'=>'fa-check-circle'],
                        'Cancelled'  => ['bg'=>'rgba(248,113,113,0.1)','c'=>'#f87171','i'=>'fa-times-circle'],
                    ][$order->OrderStatus] ?? ['bg'=>'rgba(255,255,255,0.05)','c'=>'var(--text-muted)','i'=>'fa-circle'];
                @endphp
                <div class="order-card">
                    <div>
                        <div class="order-id">#{{ $order->KitchenOrderNumber ?? $order->OrderID }}</div>
                        <div class="order-date">{{ \Carbon\Carbon::parse($order->CreatedAt)->format('d M Y, h:i A') }}</div>
                    </div>

                    <div>
                        <div class="order-price">{{ number_format($order->TotalPrice, 2) }} <span style="font-size:0.8rem;color:var(--text-muted)">EGP</span></div>
                        @if($order->LoyaltyPoints > 0)
                        <div style="font-size:0.8rem;color:var(--accent);margin-top:4px;font-weight:600"><i class="fas fa-star"></i> +{{ $order->LoyaltyPoints }} pts</div>
                        @else
                        <div style="font-size:0.8rem;color:var(--text-muted);margin-top:4px;opacity:0">0 pts</div>
                        @endif
                    </div>

                    <div class="status-badge" style="background: {{ $sc['bg'] }}; color: {{ $sc['c'] }}; border: 1px solid {{ $sc['c'] }}44;">
                        <i class="fas {{ $sc['i'] }}"></i> {{ $order->OrderStatus }}
                    </div>

                    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                        <a href="{{ route('frontend.tracking', $order->OrderID) }}" class="btn btn-primary" style="padding: 8px 20px; border-radius: 8px;">
                            <i class="fas fa-location-arrow" style="margin-right: 6px;"></i> Track
                        </a>
                        
                        @php
                            $isRefundablePayment = true; // Temporarily allowed for all payment types to facilitate testing
                            $canRefund = $isRefundablePayment && $order->TotalPrice > 0 && \Carbon\Carbon::parse($order->CreatedAt)->addDays(3)->isFuture() && !in_array($order->OrderStatus, ['Cancelled', 'Refunded']);
                        @endphp


                        @if($order->supportTickets->count() > 0)
                            <button class="btn" style="padding:8px 18px; border-radius:8px; background:rgba(156,163,175,0.1); color:#9ca3af; border:1px solid rgba(156,163,175,0.3); font-weight:700; font-size:0.88rem; cursor:not-allowed; opacity: 0.7;" disabled>
                                <i class="fas fa-check-circle" style="margin-right:5px;"></i> Reported
                            </button>
                        @else
                            <button onclick="openCustomerReport({{ $order->OrderID }})" class="btn" style="padding:8px 18px; border-radius:8px; background:rgba(248,113,113,0.12); color:#f87171; border:1px solid rgba(248,113,113,0.3); font-weight:700; font-size:0.88rem; transition:all 0.2s;" onmouseover="this.style.background='rgba(248,113,113,0.22)'" onmouseout="this.style.background='rgba(248,113,113,0.12)'">
                                <i class="fas fa-exclamation-triangle" style="margin-right:5px;"></i> Report
                            </button>
                        @endif
                    </div>
                </div>
                @endforeach
            @endif
        </div>
    </div>
</div>
</section>

{{-- ══ Customer Report Order Modal ══ --}}
<div id="customerReportModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.7); backdrop-filter:blur(6px); align-items:center; justify-content:center;">
  <div style="background:var(--bg-card); border:1px solid var(--border-color); border-radius:20px; width:100%; max-width:500px; margin:20px; box-shadow:0 20px 60px rgba(0,0,0,0.5); animation: slideUp 0.25s ease;">
    <div style="padding:24px 28px 0; display:flex; justify-content:space-between; align-items:center;">
      <h4 style="margin:0; color:#f87171; font-weight:800; font-size:1.1rem;">⚠️ Report Issue — Order <span id="custReportOrderId"></span></h4>
      <button onclick="closeCustomerReport()" style="background:none; border:none; color:var(--text-muted); font-size:1.4rem; cursor:pointer; line-height:1;">&times;</button>
    </div>
    <form method="POST" action="{{ route('customer.support.store') }}" style="padding:20px 28px 28px;">
      @csrf
      <input type="hidden" name="order_id" id="custReportOrderIdInput">
      <div style="margin-bottom:16px;">
        <label style="display:block; color:var(--text-muted); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:8px;">What happened? *</label>
        <select name="category" style="width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border-color); border-radius:10px; color:var(--text-primary); padding:11px 14px; font-size:0.93rem;" required>
          <option value="" style="background:var(--bg-card); color:var(--text-primary);">— Choose a problem type —</option>
          <option value="Order Not Delivered" style="background:var(--bg-card); color:var(--text-primary);">🚫 Order Not Delivered</option>
          <option value="Wrong Items Received" style="background:var(--bg-card); color:var(--text-primary);">🍱 Wrong Items Received</option>
          <option value="Food Quality Issue" style="background:var(--bg-card); color:var(--text-primary);">😞 Food Quality Issue</option>
          <option value="Payment / Refund Issue" style="background:var(--bg-card); color:var(--text-primary);">💳 Payment / Refund Issue</option>
          <option value="Delivery Was Late" style="background:var(--bg-card); color:var(--text-primary);">⏰ Delivery Was Late</option>
          <option value="Driver / Delivery Agent Behavior" style="background:var(--bg-card); color:var(--text-primary);">🛵 Driver Behavior</option>
          <option value="App / Technical Bug" style="background:var(--bg-card); color:var(--text-primary);">🔧 App / Technical Bug</option>
          <option value="Other" style="background:var(--bg-card); color:var(--text-primary);">📝 Other</option>
        </select>
      </div>
      <div style="margin-bottom:16px;">
        <label style="display:block; color:var(--text-muted); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:8px;">Subject *</label>
        <input type="text" name="subject" id="custReportSubject" style="width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border-color); border-radius:10px; color:var(--text-primary); padding:11px 14px; font-size:0.93rem;" required>
      </div>
      <div style="margin-bottom:22px;">
        <label style="display:block; color:var(--text-muted); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:8px;">Description *</label>
        <textarea name="description" rows="4" style="width:100%; background:rgba(255,255,255,0.04); border:1px solid var(--border-color); border-radius:10px; color:var(--text-primary); padding:11px 14px; font-size:0.93rem; resize:vertical;" placeholder="Tell us what happened with this order..." required></textarea>
      </div>
      <div style="display:flex; gap:12px; justify-content:flex-end;">
        <button type="button" onclick="closeCustomerReport()" style="background:rgba(255,255,255,0.06); border:1px solid var(--border-color); border-radius:10px; color:var(--text-muted); padding:10px 22px; font-weight:700; cursor:pointer;">Cancel</button>
        <button type="submit" style="background:linear-gradient(135deg,#ef4444,#f97316); border:none; border-radius:10px; color:#fff; padding:10px 24px; font-weight:800; cursor:pointer;">
          🚀 Submit Report
        </button>
      </div>
    </form>
  </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<script>


    function openCustomerReport(orderId) {
        document.getElementById('custReportOrderId').textContent = '#' + orderId;
        document.getElementById('custReportOrderIdInput').value = orderId;
        document.getElementById('custReportSubject').value = 'Issue with Order #' + orderId;
        var modal = document.getElementById('customerReportModal');
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
    function closeCustomerReport() {
        document.getElementById('customerReportModal').style.display = 'none';
        document.body.style.overflow = '';
    }
    // Close on backdrop click
    document.getElementById('customerReportModal').addEventListener('click', function(e) {
        if (e.target === this) closeCustomerReport();
    });

    function payInstallment(subId, amount, method) {
        window.biteConfirm(`Are you sure you want to pay ${amount.toLocaleString()} EGP for this subscription via ${method}?`, function(res) {
            if (!res) return;

            // Mock payment process for now
            fetch('{{ route("frontend.subscription.pay") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    subscription_id: subId,
                    amount: amount,
                    method: method
                })
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    showToast('Payment successful!', 'success');
                    window.location.reload();
                } else {
                    showToast('Error: ' + d.message, 'error');
                }
            })
            .catch(e => showToast('Payment failed: ' + e, 'error'));
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        const userId = "{{ auth()->id() }}";
        const hasSeenTour = localStorage.getItem('bitehub_tour_customer_' + userId);
        
        if (!hasSeenTour) {
            const driver = window.driver.js.driver;
            const driverObj = driver({
                showProgress: true,
                steps: [
                    { element: '.hero-card', popover: { title: 'Welcome to your Dashboard!', description: 'Here you can track your wallet balance, loyalty points, and create new meal plans.', side: "bottom", align: 'start' }},
                    { element: '.action-grid', popover: { title: 'Quick Actions', description: 'Seamlessly jump to ordering food, viewing kitchens, or managing your addresses.', side: "top", align: 'start' }},
                    { element: '.orders-header', popover: { title: 'Order Tracking', description: 'Monitor the live status of all your recent orders right from here.', side: "bottom", align: 'start' }}
                ],
                onDestroyStarted: () => {
                    localStorage.setItem('bitehub_tour_customer_' + userId, 'true');
                    driverObj.destroy();
                },
                onPopoverRendered: (popover) => {
                    let footer = popover.wrapper.querySelector('.driver-popover-navigation-btns');
                    if (footer && !footer.querySelector('.skip-tour-btn')) {
                        let btn = document.createElement('button');
                        btn.innerHTML = 'Skip Tour';
                        btn.className = 'driver-popover-prev-btn skip-tour-btn';
                        btn.style.color = '#ef4444';
                        btn.style.borderColor = 'transparent';
                        btn.style.fontWeight = 'bold';
                        btn.onclick = () => driverObj.destroy();
                        footer.insertBefore(btn, footer.firstChild);
                    }
                }
            });
            setTimeout(() => { driverObj.drive(); }, 500);
        }
    });
</script>
@endpush
@endsection
