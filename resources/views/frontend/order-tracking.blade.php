@extends('frontend.layouts.app')
@section('title', 'Track Your Orders')

@section('content')
@php
    $statusMap = [
        'Pending'    => 0,
        'Confirmed'  => 1,
        'Preparing'  => 2,
        'Ready'      => 2,
        'Delivering' => 3,
        'Delivered'  => 4,
        'Cancelled'  => -1,
    ];
    $statusLabels = [
        'Pending'    => ['color'=>'#fbbf24','bg'=>'rgba(251,191,36,0.1)','icon'=>'fa-clock'],
        'Confirmed'  => ['color'=>'#60a5fa','bg'=>'rgba(96,165,250,0.1)','icon'=>'fa-check-circle'],
        'Preparing'  => ['color'=>'#a78bfa','bg'=>'rgba(167,139,250,0.1)','icon'=>'fa-fire-burner'],
        'Ready'      => ['color'=>'#4ade80','bg'=>'rgba(74,222,128,0.1)','icon'=>'fa-box-open'],
        'Delivering' => ['color'=>'#ff6b35','bg'=>'rgba(255,107,53,0.1)','icon'=>'fa-truck-fast'],
        'Delivered'  => ['color'=>'#10b981','bg'=>'rgba(16,185,129,0.1)','icon'=>'fa-house-circle-check'],
        'Cancelled'  => ['color'=>'#f87171','bg'=>'rgba(248,113,113,0.1)','icon'=>'fa-ban'],
    ];
    $steps = [
        ['icon' => 'fa-clock',          'label' => 'Pending',     'sub' => 'Received'],
        ['icon' => 'fa-check-double',   'label' => 'Confirmed',   'sub' => 'Accepted'],
        ['icon' => 'fa-fire-flame-curved','label' => 'Preparing', 'sub' => 'Cooking'],
        ['icon' => 'fa-motorcycle',     'label' => 'On the Way',  'sub' => 'Moving'],
        ['icon' => 'fa-flag-checkered', 'label' => 'Delivered',   'sub' => 'Enjoy!'],
    ];
    $totalSteps = count($steps) - 1;
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="">
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<style>
    :root {
        --track-accent: #ff6b35;
        --track-bg: rgba(255, 255, 255, 0.03);
        --track-border: rgba(255, 255, 255, 0.08);
    }

    [data-theme="light"] {
        --track-bg: rgba(0, 0, 0, 0.02);
        --track-border: rgba(0, 0, 0, 0.05);
    }

    .tracking-section {
        padding: calc(var(--nav-h) + 60px) 0 100px;
        min-height: 100vh;
        background: radial-gradient(circle at top right, rgba(255, 107, 53, 0.05), transparent 400px),
                    radial-gradient(circle at bottom left, rgba(255, 167, 38, 0.03), transparent 400px);
    }

    .page-header-v2 {
        margin-bottom: 50px;
        text-align: center;
    }

    .page-header-v2 h1 {
        font-family: 'Outfit', sans-serif;
        font-weight: 900;
        letter-spacing: -1px;
        font-size: 2.8rem;
        background: linear-gradient(135deg, var(--text-primary), var(--text-secondary));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 12px;
    }

    .order-card-v2 {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--track-border);
        transition: var(--transition-bounce);
        margin-bottom: 30px;
    }

    .order-card-v2:hover {
        transform: translateY(-5px);
        border-color: rgba(255, 107, 53, 0.3);
        box-shadow: var(--shadow-glow);
    }

    .status-pill {
        padding: 6px 16px;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: inset 0 0 12px rgba(255,255,255,0.05);
    }

    /* Progress bar enhancements */
    .progress-container {
        position: relative;
        display: flex;
        justify-content: space-between;
        margin: 40px 0 20px;
        padding: 0 10px;
    }

    .progress-line {
        position: absolute;
        top: 22px;
        left: 30px;
        right: 30px;
        height: 4px;
        background: var(--track-border);
        z-index: 0;
        border-radius: 10px;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), var(--accent));
        border-radius: 10px;
        transition: width 1.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 0 15px var(--primary-glow);
    }

    .step-item {
        position: relative;
        z-index: 1;
        flex: 1;
        text-align: center;
    }

    .step-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        margin: 0 auto 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: var(--transition-bounce);
        background: var(--bg-card2);
        border: 2px solid var(--track-border);
        color: var(--text-muted);
    }

    .step-item.completed .step-icon-box {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 15px var(--primary-glow);
    }

    .step-item.active .step-icon-box {
        background: #fff;
        color: var(--primary);
        border-color: var(--primary);
        box-shadow: 0 0 0 6px var(--primary-glow);
        transform: scale(1.15);
        animation: pulse-glow 2s infinite;
    }

    .step-text {
        font-size: 0.7rem;
        font-weight: 700;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .active .step-text { color: var(--primary); }
    .completed .step-text { color: var(--text-primary); }

    /* Rating & Feedback */
    .rating-section {
        background: rgba(255, 255, 255, 0.02);
        border-radius: 16px;
        padding: 24px;
        margin-top: 25px;
        border: 1px solid var(--track-border);
    }

    .premium-stars {
        display: flex;
        flex-direction: row-reverse;
        justify-content: center;
        gap: 12px;
        margin: 15px 0;
    }

    .premium-stars input { display: none; }
    .premium-stars label {
        font-size: 2.2rem;
        color: var(--track-border);
        cursor: pointer;
        transition: var(--transition-bounce);
    }

    .premium-stars label:hover,
    .premium-stars label:hover ~ label,
    .premium-stars input:checked ~ label {
        color: #fbbf24;
        filter: drop-shadow(0 0 8px rgba(251, 191, 36, 0.4));
        transform: scale(1.2);
    }

    .feedback-area {
        background: var(--bg-glass2);
        border: 1px solid var(--track-border);
        border-radius: 12px;
        color: var(--text-primary);
        padding: 12px 16px;
        font-size: 0.9rem;
        width: 100%;
        transition: var(--transition);
        margin-bottom: 15px;
    }

    .feedback-area:focus {
        background: rgba(255,255,255,0.05);
        border-color: var(--primary);
        outline: none;
    }

    .live-dot {
        width: 8px;
        height: 8px;
        background: #f87171;
        border-radius: 50%;
        display: inline-block;
        animation: ripple 1.5s infinite;
    }

    .chat-link {
        color: var(--text-secondary);
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: var(--transition);
    }

    .chat-link:hover {
        background: var(--track-border);
        box-shadow: 0 0 10px rgba(0,0,0,0.1);
    }
    
    .custom-div-icon {
        transition: transform 3s linear;
    }
</style>
@endpush

<section class="tracking-section">
    <div class="container" style="max-width: 850px">

        <div class="page-header-v2 reveal">
            <h1>Track Your Orders</h1>
            <p style="color: var(--text-secondary); max-width: 500px; margin: 0 auto;">
                Real-time updates on your delicious meals. We've split your multi-kitchen order for faster delivery!
            </p>
        </div>

        <!-- Live Map Container Removed from here -->

        @foreach($orders as $order)
        @php
            $status = $order->OrderStatus ?? 'Pending';
            $currentIdx = $statusMap[$status] ?? 0;
            $isCancelled = ($status === 'Cancelled');
            $sl = $statusLabels[$status] ?? $statusLabels['Pending'];
            $progressPct = $isCancelled ? 0 : min(100, round(($currentIdx / $totalSteps) * 100));
            $vendorName = $order->kitchenOwner->KitchenName ?? ($order->caterer->FullName ?? 'BiteHub Vendor');
        @endphp

        <div class="glass-card order-card-v2 reveal" id="order-card-{{ $order->OrderID }}">
            <div style="padding: 30px">
                <!-- Card Header -->
                <div class="flex-between" style="margin-bottom: 30px">
                    <div>
                        <h3 style="font-size: 1.4rem; color: var(--text-primary); margin-bottom: 4px;">{{ $vendorName }}</h3>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <span style="font-size: 0.85rem; color: var(--text-muted);">#ORD-{{ $order->OrderID }}</span>
                            @if($status === 'Delivering')
                            <span style="font-size: 0.75rem; color: #f87171; display:flex; align-items:center; gap:5px;">
                                <span class="live-dot"></span> LIVE
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="status-pill" style="background: {{ $sl['bg'] }}; color: {{ $sl['color'] }};">
                        <i class="fas {{ $sl['icon'] }}"></i> {{ $status }}
                    </div>
                </div>
                
                <!-- Individual Order Map -->
                <div id="trackingMap-{{ $order->OrderID }}" style="height:280px; width:100%; border-radius: var(--radius); margin-bottom: 30px; display:none; position:relative; box-shadow: inset 0 0 10px rgba(0,0,0,0.1); border: 1px solid var(--track-border); overflow:hidden">
                    <button onclick="focusMap({{ $order->OrderID }})" style="position:absolute; bottom:15px; right:15px; z-index:1000; width:40px; height:40px; border-radius:50%; background:var(--bg-glass); border:1px solid var(--track-border); color:var(--text-primary); cursor:pointer; box-shadow:var(--shadow-glow); backdrop-filter: blur(10px); display:flex; align-items:center; justify-content:center;" title="Center Map">
                        <i class="fas fa-crosshairs"></i>
                    </button>
                </div>

                <!-- Progress Tracker -->
                @if(!$isCancelled)
                <div class="progress-container">
                    <div class="progress-line">
                        <div class="progress-fill" style="width: {{ $progressPct }}%"></div>
                    </div>
                    @foreach($steps as $i => $step)
                        @php
                            $isCompleted = $i < $currentIdx;
                            $isActive = $i === $currentIdx;
                            $stepClass = $isCompleted ? 'completed' : ($isActive ? 'active' : '');
                        @endphp
                        <div class="step-item {{ $stepClass }}">
                            <div class="step-icon-box">
                                <i class="fas {{ $step['icon'] }}"></i>
                            </div>
                            <span class="step-text">{{ $step['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                @else
                <div style="background: rgba(248,113,113,0.08); padding: 25px; border-radius: var(--radius); text-align: center; border: 1px dashed var(--danger); color: var(--danger);">
                    <i class="fas fa-circle-exclamation fa-2x mb-2" style="display:block"></i>
                    <h4 style="margin:0">This order was cancelled</h4>
                    <p style="margin:5px 0 0; font-size:0.85rem; opacity:0.8">If you have any questions, please contact our support.</p>
                </div>
                @endif

                <!-- Delivery PIN -->
                @if($status === 'Delivering' && $order->DeliveryCode)
                <div style="margin: 30px 0; background: rgba(96,165,250,0.06); border: 1px dashed var(--info); border-radius: 16px; padding: 20px; text-align: center;">
                    <div style="font-size: 0.75rem; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 2px; margin-bottom: 8px;">Delivery Security PIN</div>
                    <div style="font-size: 2.2rem; font-weight: 900; letter-spacing: 12px; color: var(--info); font-family: 'Courier New', monospace;">{{ $order->DeliveryCode }}</div>
                    <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 10px;">Please show this code to the courier only when you receive your order.</p>
                </div>
                @endif


                <!-- Order Items & Details -->
                <div style="margin-top: 35px; border-top: 1px solid var(--track-border); padding-top: 30px;">
                    <h4 style="font-size: 1.1rem; color: var(--text-primary); margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-receipt" style="color: var(--primary);"></i> Order Details
                    </h4>
                    
                    <div style="background: rgba(255,255,255,0.02); border-radius: 16px; padding: 20px; border: 1px solid var(--track-border);">
                        <div style="margin-bottom: 20px;">
                            @foreach($order->menuItems as $item)
                            @php $itemPrice = $item->DiscountPrice ?? $item->ItemPrice; @endphp
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                <div style="display: flex; gap: 15px;">
                                    <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(255,107,53,0.1); display: flex; align-items: center; justify-content: center; font-weight: 800; color: var(--primary); font-size: 0.8rem; flex-shrink: 0; border: 1px solid rgba(255,107,53,0.2);">
                                        {{ $item->pivot->Quantity }}x
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem; margin-bottom: 2px;">{{ $item->ItemName }}</div>
                                        <div style="font-size: 0.8rem; color: var(--text-muted);">{{ number_format($itemPrice, 2) }} EGP / unit</div>
                                    </div>
                                </div>
                                <div style="font-weight: 700; color: var(--text-primary); font-size: 0.95rem;">{{ number_format($itemPrice * $item->pivot->Quantity, 2) }} EGP</div>
                            </div>
                            @endforeach
                        </div>

                        <div style="border-top: 1px solid rgba(255,255,255,0.05); padding-top: 15px; margin-top: 15px;">
                            @php
                                $isMealPlan = ($order->OrderType === 'Meal Plan');
                                $subtotal = $order->menuItems->sum(function($item) { 
                                    return ($item->DiscountPrice ?? $item->ItemPrice) * $item->pivot->Quantity; 
                                });
                                
                                if ($isMealPlan) {
                                    $deliveryFee = 0;
                                    $subtotalDisplay = "Included in Plan";
                                    $deliveryDisplay = "Included in Plan";
                                    $totalDisplay = "Prepaid (Subscription)";
                                } else {
                                    $deliveryFee = $order->TotalPrice - $subtotal;
                                    // Fallback for edge cases where math is weird
                                    if ($deliveryFee < 0) {
                                        $deliveryFee = 15.00;
                                        $subtotal = max(0, $order->TotalPrice - 15.00);
                                    }
                                    $subtotalDisplay = number_format($subtotal, 2) . " EGP";
                                    $deliveryDisplay = number_format($deliveryFee, 2) . " EGP";
                                    $pointsDisc = $order->PointsDiscount ?? 0;
                                    $finalTotal = max(0, $order->TotalPrice - $pointsDisc);
                                    $totalDisplay = number_format($finalTotal, 2) . " EGP";
                                }
                            @endphp
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
                                <span style="color: var(--text-secondary);">Subtotal</span>
                                <span style="color: var(--text-primary);">{{ $subtotalDisplay }}</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem;">
                                <span style="color: var(--text-secondary);">Delivery Fee</span>
                                <span style="color: var(--text-primary);">{{ $deliveryDisplay }}</span>
                            </div>

                            @if(!$isMealPlan && ($order->PointsDiscount ?? 0) > 0)
                            <div style="display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 0.9rem; color: #f59e0b;">
                                <span><i class="fas fa-star"></i> BitePoints Discount</span>
                                <span>- {{ number_format($order->PointsDiscount, 2) }} EGP</span>
                            </div>
                            @endif

                            <div style="display: flex; justify-content: space-between; margin-top: 15px; padding-top: 15px; border-top: 1px solid rgba(255,107,53,0.2);">
                                <span style="font-weight: 800; color: var(--text-primary);">Grand Total</span>
                                <span style="font-weight: 900; color: var(--primary); font-size: 1.2rem;">{{ $totalDisplay }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Meta Info (Address & Payment) -->
                    @php
                        $address = 'N/A';
                        if ($order->SpecialRequests && str_contains($order->SpecialRequests, 'Delivery: ')) {
                            $parts = explode('Delivery: ', $order->SpecialRequests);
                            $address = $parts[1] ?? 'N/A';
                            if (str_contains($address, '[Session:')) {
                                $address = explode('[Session:', $address)[0];
                            }
                            $address = trim($address);
                        }
                    @endphp
                    <div style="margin-top: 20px; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                        <div style="background: rgba(255,255,255,0.015); border-radius: 12px; padding: 15px; border: 1px solid var(--track-border);">
                            <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 8px; font-weight: 800;">Delivery Address</div>
                            <div style="font-size: 0.85rem; color: var(--text-primary); line-height: 1.4;">
                                <i class="fas fa-location-dot" style="color: var(--primary); margin-right: 6px;"></i>
                                {{ $address }}
                            </div>
                        </div>
                        <div style="background: rgba(255,255,255,0.015); border-radius: 12px; padding: 15px; border: 1px solid var(--track-border);">
                            <div style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; color: var(--text-muted); margin-bottom: 8px; font-weight: 800;">Payment Method</div>
                            <div style="font-size: 0.85rem; color: var(--text-primary);">
                                <i class="fas fa-wallet" style="color: var(--primary); margin-right: 6px;"></i>
                                {{ $order->payment->Method ?? 'Cash on Delivery' }} ({{ $order->payment->Status ?? 'Unpaid' }})
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Actions -->
                <div style="border-top: 1px solid var(--track-border); margin-top: 30px; padding-top: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <a href="{{ route('frontend.chat.order', $order->OrderID) }}" class="chat-link">
                        <i class="fas fa-comment-dots"></i> Chat with {{ $vendorName }}
                    </a>
                    
                    <div style="display: flex; gap: 12px; align-items: center;">
                        @php
                            $isRefundablePayment = true; // Temporarily allowed for all payment types to facilitate testing
                            $canRefund = $isRefundablePayment && $order->TotalPrice > 0 && \Carbon\Carbon::parse($order->CreatedAt)->addDays(3)->isFuture() && !in_array($order->OrderStatus, ['Cancelled', 'Refunded']);
                        @endphp



                        @if($status === 'Pending')
                        <form action="{{ route('frontend.order.cancel', $order->OrderID) }}" method="POST">
                            @csrf
                            <button type="submit" style="background: transparent; border: none; color: var(--danger); font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-trash-alt"></i> Cancel Order
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- Rating Interface (Post-Delivery) -->
                @if($status === 'Delivered')
                    @php $review = $reviews[$order->OrderID] ?? null; @endphp
                    <div class="rating-section">
                        @if($review)
                            <div style="text-align: center;">
                                <div style="color: #fbbf24; font-size: 1.5rem; margin-bottom: 8px;">
                                    @for($i=1; $i<=5; $i++) <i class="fas fa-star" style="opacity: {{ $i <= $review->Rating ? '1' : '0.15' }}"></i> @endfor
                                </div>
                                <h5 style="margin: 0; color: var(--text-primary);">Rated {{ $review->Rating }} Stars</h5>
                                @if($review->Comment)
                                    <p style="margin: 10px 0 0; font-size: 0.85rem; color: var(--text-muted); font-style: italic;">"{{ $review->Comment }}"</p>
                                @endif
                            </div>
                        @else
                            <form action="{{ route('frontend.order.rate', $order->OrderID) }}" method="POST">
                                @csrf
                                <h5 style="text-align: center; margin-bottom: 5px;">Share your experience</h5>
                                <div class="premium-stars">
                                    <input type="radio" id="star5-{{ $order->OrderID }}" name="rating" value="5" required /><label for="star5-{{ $order->OrderID }}"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star4-{{ $order->OrderID }}" name="rating" value="4" /><label for="star4-{{ $order->OrderID }}"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star3-{{ $order->OrderID }}" name="rating" value="3" /><label for="star3-{{ $order->OrderID }}"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star2-{{ $order->OrderID }}" name="rating" value="2" /><label for="star2-{{ $order->OrderID }}"><i class="fas fa-star"></i></label>
                                    <input type="radio" id="star1-{{ $order->OrderID }}" name="rating" value="1" /><label for="star1-{{ $order->OrderID }}"><i class="fas fa-star"></i></label>
                                </div>
                                <textarea name="comment" class="feedback-area" rows="2" placeholder="Tell us how the food was..."></textarea>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill">Submit Review</button>
                            </form>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endforeach

        <div style="text-align: center; margin-top: 40px;">
            <a href="{{ route('frontend.home') }}" class="btn btn-secondary rounded-pill px-5">Back to Browse</a>
        </div>

    </div>
</section>



@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>
<script>


const idString = "{{ $id_string }}";
let maps = {};
let markers = {};
let routes = {};

const getStatusIcon = (status) => {
    let iconClass = 'fa-clock';
    let bgColor = '#fbbf24';
    
    if (status === 'Confirmed') { iconClass = 'fa-check-double'; bgColor = '#60a5fa'; }
    if (status === 'Preparing') { iconClass = 'fa-fire-flame-curved'; bgColor = '#a78bfa'; }
    if (status === 'Delivering') { iconClass = 'fa-motorcycle'; bgColor = '#ff6b35'; }
    if (status === 'Delivered' || status === 'Ready') { iconClass = 'fa-flag-checkered'; bgColor = '#10b981'; }

    return L.divIcon({
        className: 'custom-div-icon',
        html: `<div style="background-color: ${bgColor}; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 16px;"><i class="fas ${iconClass}"></i></div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 18]
    });
};

function focusMap(id) {
    if (maps[id] && markers[id] && markers[id].bounds && markers[id].bounds.length > 0) {
        maps[id].fitBounds(markers[id].bounds, {padding: [30, 30], maxZoom: 15});
    }
}

function updateMap(orderData) {
    Object.keys(orderData).forEach(id => {
        const d = orderData[id];
        if (d.status === 'Cancelled') return;
        
        const mapDiv = document.getElementById('trackingMap-' + id);
        if (!mapDiv) return;
        
        mapDiv.style.display = 'block';
        if (!maps[id]) {
            maps[id] = L.map('trackingMap-' + id).setView([30.0444, 31.2357], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap'
            }).addTo(maps[id]);
        }

        if (!markers[id]) markers[id] = {};
        let bounds = [];
        const currentIcon = getStatusIcon(d.status);
        
        const destIcon = L.divIcon({
            className: 'custom-div-icon',
            html: `<div style="background-color: #10b981; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 50%; color: white; border: 2px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); font-size: 16px; opacity: 0.5;"><i class="fas fa-flag-checkered"></i></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 18]
        });

        // Status: Pending, Confirmed, Preparing
        if (['Pending', 'Confirmed', 'Preparing'].includes(d.status)) {
            if (d.kitchen_lat && d.kitchen_lng) {
                if (!markers[id].kitchen) {
                    markers[id].kitchen = L.marker([d.kitchen_lat, d.kitchen_lng], {icon: currentIcon}).addTo(maps[id]);
                } else {
                    markers[id].kitchen.setLatLng([d.kitchen_lat, d.kitchen_lng]).setIcon(currentIcon);
                }
                bounds.push([d.kitchen_lat, d.kitchen_lng]);
            }
            if (markers[id].driver) { maps[id].removeLayer(markers[id].driver); delete markers[id].driver; }
            if (markers[id].dest) { maps[id].removeLayer(markers[id].dest); delete markers[id].dest; }
            if (routes[id]) { maps[id].removeControl(routes[id]); delete routes[id]; }
        }
        
        // Status: Delivering
        else if (d.status === 'Delivering') {
            if (d.driver_lat && d.driver_lng) {
                if (!markers[id].driver) {
                    markers[id].driver = L.marker([d.driver_lat, d.driver_lng], {icon: currentIcon}).addTo(maps[id]);
                } else {
                    markers[id].driver.setLatLng([d.driver_lat, d.driver_lng]).setIcon(currentIcon);
                }
                bounds.push([d.driver_lat, d.driver_lng]);
            }
            if (d.delivery_lat && d.delivery_lng) {
                if (!markers[id].dest) {
                    markers[id].dest = L.marker([d.delivery_lat, d.delivery_lng], {icon: destIcon}).addTo(maps[id]);
                } else {
                    markers[id].dest.setLatLng([d.delivery_lat, d.delivery_lng]);
                }
                bounds.push([d.delivery_lat, d.delivery_lng]);
            }
            
            if (d.driver_lat && d.driver_lng && d.delivery_lat && d.delivery_lng) {
                if (!routes[id]) {
                    routes[id] = L.Routing.control({
                        waypoints: [
                            L.latLng(d.driver_lat, d.driver_lng),
                            L.latLng(d.delivery_lat, d.delivery_lng)
                        ],
                        routeWhileDragging: false,
                        addWaypoints: false,
                        show: false,
                        createMarker: function() { return null; },
                        lineOptions: {
                            styles: [{color: '#ff6b35', opacity: 0.8, weight: 5}]
                        }
                    }).addTo(maps[id]);
                } else {
                    routes[id].setWaypoints([
                        L.latLng(d.driver_lat, d.driver_lng),
                        L.latLng(d.delivery_lat, d.delivery_lng)
                    ]);
                }
            }
            if (markers[id].kitchen) { maps[id].removeLayer(markers[id].kitchen); delete markers[id].kitchen; }
        }

        // Status: Delivered or Ready
        else if (d.status === 'Delivered' || d.status === 'Ready') {
            if (d.delivery_lat && d.delivery_lng) {
                if (!markers[id].dest) {
                    markers[id].dest = L.marker([d.delivery_lat, d.delivery_lng], {icon: currentIcon}).addTo(maps[id]);
                } else {
                    markers[id].dest.setLatLng([d.delivery_lat, d.delivery_lng]).setIcon(currentIcon);
                }
                bounds.push([d.delivery_lat, d.delivery_lng]);
            }
            if (markers[id].driver) { maps[id].removeLayer(markers[id].driver); delete markers[id].driver; }
            if (routes[id]) { maps[id].removeControl(routes[id]); delete routes[id]; }
            if (markers[id].kitchen) { maps[id].removeLayer(markers[id].kitchen); delete markers[id].kitchen; }
        }

        markers[id].bounds = bounds;
        if (bounds.length > 0) {
            maps[id].fitBounds(bounds, {padding: [50, 50], maxZoom: 15});
            setTimeout(() => { maps[id].invalidateSize(); }, 300);
        }
    });
}

function poll() {
    fetch(`/order-tracking/${idString}/data`)
        .then(res => res.json())
        .then(data => {
            updateMap(data);
        });
}

setInterval(poll, 3000);
poll();
</script>
@endpush
@endsection
