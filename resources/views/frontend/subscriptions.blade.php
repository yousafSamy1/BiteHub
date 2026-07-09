@extends('frontend.layouts.app')
@section('title', 'Subscription Plans')
@section('nav-subs', 'active')

@section('content')
<div class="page-header" style="background: linear-gradient(180deg, var(--bg-dark), var(--bg-dark)); padding: 80px 0 40px; border:none">
    <div class="container text-center">
        <h1 style="font-size: 3rem; margin-bottom: 12px; font-weight: 900; letter-spacing: -1.5px; color:var(--text-primary)">Meal <span class="highlight">Plans Hub</span></h1>
        <p style="color: var(--text-muted); font-size: 1.1rem; max-width: 600px; margin: 0 auto; opacity:0.8">Manage your active subscriptions and discover new kitchen plans.</p>
    </div>
</div>

<div class="container" style="margin-top: -40px; position: relative; z-index: 10;">
    <!-- ═══════════════ BUILD NEW PLAN (TOP BANNER) ═══════════════ -->
    <div class="glass-card reveal" style="padding:40px; margin-bottom:40px; border:1px solid var(--primary-border); background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(255,167,38,0.05)); border-radius:30px; position:relative; overflow:hidden">
        <div style="position:absolute; top:-20px; right:-20px; width:150px; height:150px; background:var(--primary); filter:blur(100px); opacity:0.1; z-index:0"></div>
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:30px; position:relative; z-index:1">
            <div style="display:flex; align-items:center; gap:24px">
                <div style="width:72px; height:72px; border-radius:20px; background:var(--primary); display:flex; align-items:center; justify-content:center; color:#fff; font-size:2rem; box-shadow:0 10px 25px rgba(255,107,53,0.3)">
                    <i class="fas fa-wand-magic-sparkles"></i>
                </div>
                <div style="max-width:500px">
                    <h2 style="font-size:1.8rem; font-weight:900; margin-bottom:8px; color:var(--text-primary); letter-spacing:-0.5px">Build Your Custom Meal Plan</h2>
                    <p style="color:var(--text-secondary); font-size:1rem; margin:0; line-height:1.5">Pick your favorite dishes, set your schedule, and enjoy fresh homemade food delivered daily.</p>
                </div>
            </div>
            <a href="{{ route('frontend.meal_plan_builder') }}" class="btn btn-primary" style="padding:18px 36px; border-radius:18px; font-weight:800; font-size:1.1rem; box-shadow:0 8px 25px rgba(255,107,53,0.4)">
                <i class="fas fa-plus-circle me-2"></i> START BUILDING NOW
            </a>
        </div>
    </div>
    <!-- ═══════════════ SUBSCRIPTION FILTER BAR ═══════════════ -->
    <div class="glass-card reveal" style="padding:15px; margin-bottom:30px; border-radius:20px; display:flex; align-items:center; justify-content:center; gap:10px; flex-wrap:wrap; border:1px solid var(--border-color)">
        <button onclick="filterSubscriptions('Pending')" class="filter-btn active" data-filter="Pending">Pending</button>
        <button onclick="filterSubscriptions('Active')" class="filter-btn" data-filter="Active">Active</button>
        <button onclick="filterSubscriptions('Closed')" class="filter-btn" data-filter="Closed">Closed</button>
    </div>

    <style>
        .filter-btn {
            padding: 8px 20px; border-radius: 12px; border: 1px solid transparent;
            background: transparent; color: var(--text-muted); font-weight: 700;
            font-size: 0.9rem; transition: all 0.3s ease; cursor: pointer;
        }
        .filter-btn:hover { background: rgba(255,255,255,0.05); color: var(--text-primary); }
        .filter-btn.active {
            background: var(--primary); color: #fff; border-color: var(--primary);
            box-shadow: 0 5px 15px rgba(255,107,53,0.3);
        }
        
        @keyframes chefPulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.02); opacity: 0.8; }
            100% { transform: scale(1); opacity: 1; }
        }
        
        @keyframes subtleShimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
        
        @keyframes pulseGlow {
            0% { box-shadow: 0 0 0 0 rgba(255,107,53, 0.4); transform: scale(1); }
            50% { box-shadow: 0 0 0 10px rgba(255,107,53, 0); transform: scale(1.05); }
            100% { box-shadow: 0 0 0 0 rgba(255,107,53, 0); transform: scale(1); }
        }
        
        .chef-review-box {
            animation: chefPulse 3s infinite ease-in-out;
            background: linear-gradient(90deg, rgba(255,167,38,0.03) 25%, rgba(255,167,38,0.08) 50%, rgba(255,167,38,0.03) 75%);
            background-size: 200% 100%;
            animation: chefPulse 4s infinite ease-in-out, subtleShimmer 6s infinite linear;
        }

        .glassy-layer {
            background: rgba(255, 255, 255, 0.04);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.2);
            border-radius: 20px;
        }
        
        /* Toast Styles */
        .toast-container { position: fixed; top: 100px; right: 40px; z-index: 10000; display: flex; flex-direction: column; gap: 12px; pointer-events: none; }
        .toast-item { background: var(--bg-card); border: 1px solid var(--border-color); padding: 16px 24px; border-radius: 16px; min-width: 280px; display: flex; align-items: center; gap: 15px; transform: translateX(120%); transition: 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55); box-shadow: 0 10px 30px rgba(0,0,0,0.2); backdrop-filter: blur(20px); }
        .toast-item.show { transform: translateX(0); }
        .toast-indicator { width: 4px; height: 30px; border-radius: 4px; }
        .toast-content { font-weight: 700; font-size: 0.9rem; color: var(--text-primary); }
        
        /* ══════════════ RESPONSIVE OVERRIDES ══════════════ */
        @media (max-width: 768px) {
            .page-header h1 { font-size: clamp(1.8rem, 8vw, 2.5rem) !important; }
            .glass-card { padding: 20px !important; }
            .sub-card-item > div { padding-left: 15px !important; padding-right: 15px !important; }
            
            /* Banner layout fixes */
            .glass-card.reveal > div[style*="display:flex"] { flex-direction: column !important; text-align: center; align-items: center !important; }
            .glass-card.reveal .btn-primary { width: 100%; justify-content: center; margin-top: 10px; }
            
            .filter-btn { padding: 8px 14px; font-size: 0.8rem; }
        }
            /* Make footers stack */
            .sub-card-item div[style*="justify-content:space-between"] { flex-wrap: wrap !important; gap: 15px; }
            .sub-card-item div[style*="justify-content:flex-end"] { flex-wrap: wrap !important; justify-content: stretch !important; flex-direction: column; }
            .sub-card-item div[style*="justify-content:flex-end"] button { width: 100%; margin-bottom: 5px; }
            
            /* Inner Card Adjustments */
            .chef-review-box, .glassy-layer { display: block !important; width: 100% !important; min-width: 0 !important; }
            
            /* Confirm Modal */
            #confirmModal .glass-card { margin: 15px !important; width: calc(100% - 30px) !important; padding: 25px !important;}
        }
        @media (max-width: 480px) {
            .toast-container { right: 15px; left: 15px; top: 80px; align-items: center; }
            .toast-item { min-width: 100%; }
        }
    </style>

    <!-- Empty State Container (Hidden by default) -->
    <div id="emptyState" style="display:none; text-align:center; padding:80px 20px; background:var(--bg-card); border-radius:30px; border:1px dashed var(--border-color); margin-bottom:40px">
        <div style="font-size:4rem; margin-bottom:20px; opacity:0.3">🕵️‍♂️</div>
        <h3 style="color:var(--text-primary); margin-bottom:10px">No Plans Found</h3>
        <p style="color:var(--text-muted); margin-bottom:25px">We couldn't find any subscriptions matching this filter.</p>
        <button onclick="filterSubscriptions('all')" class="btn btn-outline" style="border-radius:12px">View All Plans</button>
    </div>

    <!-- Pending Requests Section -->
    <div class="sub-section" style="margin-bottom:40px">
        @if(isset($pendingSubscriptions) && $pendingSubscriptions->count() > 0)
            <h3 style="font-size:1.3rem; margin-bottom:20px; display:flex; align-items:center; gap:12px; color:var(--text-primary)">
                <i class="fas fa-hourglass-half" style="color:var(--accent)"></i> Pending Requests
            </h3>
            @foreach($pendingSubscriptions as $sub)
            <div class="glass-card reveal mb-4 sub-card-item" data-status="Pending" style="padding:0; border-radius:24px; border:1px solid rgba(255,167,38,0.1); background:rgba(255,167,38,0.02); overflow:hidden">
                <!-- Header -->
                <div style="padding:15px 25px; border-bottom:1px solid rgba(255,167,38,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:15px">
                    <div style="display:flex; align-items:center; gap:12px">
                        <div style="width:36px; height:36px; border-radius:10px; background:rgba(255,167,38,0.1); display:flex; align-items:center; justify-content:center; color:var(--accent); font-size:1rem">⏳</div>
                        <div>
                            <div style="font-weight:800; font-size:0.95rem; color:var(--text-primary)">{{ $sub->DurationDays }} Days Custom Order</div>
                            <div style="font-size:0.75rem; color:var(--text-muted)">Requested on {{ \Carbon\Carbon::parse($sub->StartDate)->format('d M') }} ({{ $sub->MealsPerDay }} meals/day)</div>
                        </div>
                    </div>
                    <div style="text-align:right">
                        @if($sub->Status === 'AwaitingPayment')
                            <span class="badge" style="background:rgba(74,222,128,0.1); color:#4ade80; font-size:0.6rem; padding:4px 12px; border-radius:15px; text-transform:uppercase; font-weight:800; border:1px solid #4ade8022">Approved & Quoted</span>
                        @else
                            <span class="badge" style="background:rgba(255,167,38,0.1); color:var(--accent); font-size:0.6rem; padding:4px 12px; border-radius:15px; text-transform:uppercase; font-weight:800; border:1px solid var(--accent-border)">Under Review</span>
                        @endif
                    </div>
                </div>
                
                <!-- Content -->
                <div style="padding:20px 25px">
                    <div class="row align-items-center">
                        <div class="col-lg-7">
                            @if($sub->kitchen)
                            <div style="margin-bottom:10px; font-size:0.85rem">
                                <span style="color:var(--text-muted)">From: </span>
                                <strong style="color:var(--accent)">{{ $sub->kitchen->KitchenName }}</strong>
                            </div>
                            @endif
                            
                            @if($sub->menuItems->count() > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:12px">
                                @foreach($sub->menuItems as $item)
                                    <span style="padding:3px 10px; background:var(--bg-card); border:1px solid var(--border-color); border-radius:8px; font-size:0.75rem; font-weight:700; color:var(--text-primary)">
                                        {{ $item->ItemName }}
                                    </span>
                                @endforeach
                            </div>
                            @endif
                            
                            @if(!empty($sub->PreferredTimes))
                            <div style="display:flex; flex-wrap:wrap; gap:6px">
                                @foreach($sub->PreferredTimes as $time)
                                    <span style="padding:3px 10px; background:rgba(255,255,255,0.02); border:1px solid var(--border-color); border-radius:8px; font-size:0.7rem; font-weight:600; color:var(--text-muted)">
                                        <i class="far fa-clock me-1"></i> {{ $time }}
                                    </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        
                        <div class="col-lg-5 text-lg-end" style="margin-top:15px; margin-top:lg-0">
                            <div class="glassy-layer chef-review-box" style="padding:22px; display:inline-block; min-width:260px; text-align:left; border:1px solid rgba(255,167,38,0.2)">
                                @if($sub->Status === 'AwaitingPayment')
                                    <div style="display:flex; justify-content:space-between; margin-bottom:5px; font-size:0.8rem">
                                        <span style="color:var(--text-muted)">Kitchen Quote</span>
                                        <span style="font-weight:700; color:var(--text-primary)">{{ number_format($sub->Price, 2) }} EGP</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.8rem">
                                        <span style="color:var(--text-muted)">Delivery Fees ({{ ($sub->DurationDays ?? 0) * ($sub->MealsPerDay ?? 1) }} Meals)</span>
                                        <span style="font-weight:700; color:var(--text-primary)">{{ number_format($sub->DeliveryCharge, 2) }} EGP</span>
                                    </div>
                                    <div style="display:flex; justify-content:space-between; margin-bottom:10px; font-size:0.9rem; border-top:1px dashed var(--border-color); padding-top:10px">
                                        <span style="color:var(--text-primary); font-weight:800">Total Price</span>
                                        <span style="font-weight:900; color:var(--primary)">{{ number_format($sub->total_price, 2) }} EGP</span>
                                    </div>
                                    <div class="input-group input-group-sm mb-2" style="box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius:10px; overflow:hidden">
                                        <input type="number" id="p-amt-{{ $sub->SubscriptionID }}" class="form-control" value="{{ round($sub->remaining_balance) }}" min="1" max="{{ $sub->remaining_balance }}" style="background:var(--bg-dark); color:var(--text-primary); border:none; font-weight:700">
                                    </div>
                                    <div style="display:flex; gap:5px; margin-bottom:10px">
                                        <button onclick="document.getElementById('p-amt-{{ $sub->SubscriptionID }}').value = Math.round({{ $sub->total_price * 0.3 }})" class="btn" style="background:var(--bg-card2); color:var(--text-secondary); font-size:0.6rem; font-weight:800; border:1px solid var(--border-color); padding:4px 8px; border-radius:6px; transition:0.2s">30% DEP</button>
                                        <button onclick="document.getElementById('p-amt-{{ $sub->SubscriptionID }}').value = {{ $sub->remaining_balance }}" class="btn" style="background:var(--bg-card2); color:var(--text-secondary); font-size:0.6rem; font-weight:800; border:1px solid var(--border-color); padding:4px 8px; border-radius:6px; transition:0.2s">FULL</button>
                                    </div>
                                    <button onclick="payInstallment({{ $sub->SubscriptionID }}, document.getElementById('p-amt-{{ $sub->SubscriptionID }}').value, {{ round($sub->total_price * 0.3) }}, {{ $sub->remaining_balance }})" class="btn btn-primary w-100 btn-sm" style="font-weight:900; border-radius:10px; box-shadow: 0 5px 15px rgba(255,107,53,0.4)">PAY NOW</button>
                                @else
                                    <div style="text-align:center">
                                        <div style="width:36px; height:36px; border-radius:50%; background:rgba(255,167,38,0.1); display:inline-flex; align-items:center; justify-content:center; color:var(--accent); font-size:1.1rem; margin-bottom:10px; box-shadow: 0 0 15px rgba(255,167,38,0.2)">
                                            <i class="fas fa-spinner fa-spin"></i>
                                        </div>
                                        <div style="font-size:0.75rem; font-weight:900; color:var(--accent); text-transform:uppercase; letter-spacing:1.5px">Master Chef Reviewing</div>
                                        <p style="font-size:0.65rem; color:var(--text-muted); margin:5px 0 0; line-height:1.4">We're verifying the menu and schedule for your custom quote.</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Footer: Chat with Kitchen -->
                @php $hasChat = \App\Models\LiveChat::where('SubscriptionID', $sub->SubscriptionID)->exists(); @endphp
                <div style="padding:10px 25px 15px; display:flex; justify-content:space-between; align-items:center;">
                    <button onclick="deletePendingRequest({{ $sub->SubscriptionID }})" class="btn btn-sm" style="border-radius:12px; font-size:0.75rem; font-weight:700; padding:7px 16px; background:rgba(248,113,113,0.1); color:#f87171; border:1px solid rgba(248,113,113,0.2)">
                        <i class="fas fa-trash-alt me-1"></i> Cancel Request
                    </button>
                    <a href="{{ route('frontend.subscriptions.chat', $sub->SubscriptionID) }}"
                       style="display:inline-flex; align-items:center; gap:6px; padding:7px 16px; border-radius:12px; font-size:0.75rem; font-weight:700; text-decoration:none; background:rgba(99,102,241,0.1); color:#818cf8; border:1px solid rgba(99,102,241,0.2);">
                        <i class="fas fa-comment-dots"></i>
                        {{ $hasChat ? 'Continue Chat 💬' : 'Chat with Kitchen' }}
                    </a>
                </div>
            </div>
            @endforeach
        @endif
    </div>

    <!-- Active Plans Section -->
    <div class="sub-section" style="margin-bottom:40px">
        @if(isset($mySubscriptions) && $mySubscriptions->count() > 0)
            <h3 style="font-size:1.3rem; margin-bottom:20px; display:flex; align-items:center; gap:12px; color:var(--text-primary)">
                <i class="fas fa-calendar-check" style="color:var(--primary)"></i> My Plans
            </h3>
            @foreach($mySubscriptions as $sub)
            @php
                $planColors = ['Daily'=>'#60a5fa','Weekly'=>'#f59e0b','Monthly'=>'#4ade80'];
                $planColor  = $planColors[$sub->PlanTime] ?? 'var(--primary)';
                $daysLeft   = max(0, \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($sub->EndDate), false));
                $isExpired  = \Carbon\Carbon::now()->gt(\Carbon\Carbon::parse($sub->EndDate));
                
                if (in_array($sub->Status, ['Cancelled', 'Paused'])) {
                    $displayStatus = $sub->Status;
                } else {
                    $displayStatus = $isExpired ? 'Expired' : $sub->Status;
                }
                
                $cardStatus = in_array($displayStatus, ['Paused','Cancelled','Expired']) ? 'Closed' : 'Active';
            @endphp
            <div class="glass-card reveal mb-4 sub-card-item" data-status="{{ $cardStatus }}" style="padding:0; border-radius:24px; border:1px solid {{ $planColor }}22; background:rgba(255,255,255,0.01); overflow:hidden; box-shadow:0 10px 30px rgba(0,0,0,0.1)">
                <!-- Card Header -->
                <div style="padding:15px 25px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:15px; background:{{ $planColor }}05; border-bottom:1px solid var(--border-color)">
                    <div style="display:flex; align-items:center; gap:12px">
                        <div style="width:40px; height:40px; border-radius:10px; background:{{ $planColor }}15; display:flex; align-items:center; justify-content:center; font-size:1.2rem; border:1px solid {{ $planColor }}22">
                            {{ $sub->PlanTime === 'Monthly' ? '🏆' : ($sub->PlanTime === 'Weekly' ? '⭐' : '☀️') }}
                        </div>
                        <div>
                            <div style="font-weight:900; font-size:1rem; color:{{ $planColor }}">{{ $sub->PlanTime }} Subscription Plan</div>
                            <div style="font-size:0.75rem; color:var(--text-muted); font-weight:500">
                                <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($sub->EndDate)->format('d M Y') }}
                                <span style="margin:0 8px; opacity:0.3">|</span>
                                <strong style="color:{{ $planColor }}">{{ $daysLeft }} days left</strong>
                            </div>
                        </div>
                    </div>
                    <div style="text-align:right">
                        <div style="font-size:1.1rem; font-weight:900; color:var(--text-primary); letter-spacing:-0.5px">{{ number_format($sub->total_price, 2) }} <small style="font-size:0.65rem; opacity:0.6">EGP</small></div>
                        @if(in_array($displayStatus, ['Cancelled', 'Expired', 'Paused']))
                            <span class="badge" style="background:rgba(248,113,113,0.1); color:#f87171; font-size:0.55rem; padding:3px 10px; border-radius:12px; font-weight:800; text-transform:uppercase; border:1px solid #f8717144">{{ $displayStatus }}</span>
                        @elseif($sub->is_fully_paid)
                            <span class="badge" style="background:rgba(74,222,128,0.1); color:#4ade80; font-size:0.55rem; padding:3px 10px; border-radius:12px; font-weight:800; text-transform:uppercase; border:1px solid #4ade8022">Fully Paid</span>
                        @else
                            <span class="badge" style="background:rgba(255,167,38,0.08); color:var(--accent); font-size:0.55rem; padding:3px 10px; border-radius:12px; font-weight:800; text-transform:uppercase; border:1px solid var(--accent-border)">Refill Needed</span>
                        @endif
                    </div>
                </div>

                <div style="padding:20px 25px">
                    <div class="row">
                        <!-- Left Side: Order Details -->
                        <div class="col-lg-7">
                            @if($sub->menuItems->count() > 0)
                            <div style="font-size:0.65rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:10px">Subscribed Menu</div>
                            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:15px">
                                @foreach($sub->menuItems as $item)
                                @php $s = $item->pivot->Status; @endphp
                                <div style="padding:5px 12px; border-radius:10px; font-size:0.8rem; font-weight:700; background:var(--bg-card); color:var(--text-primary); border:1px solid var(--border-color); display:flex; align-items:center; gap:6px">
                                    <span style="width:6px; height:6px; border-radius:50%; background:{{ $s==='Approved'?'#4ade80':($s==='Rejected'?'#f87171':'#fbbf24') }}"></span>
                                    {{ $item->ItemName }}
                                </div>
                                @endforeach
                            </div>
                            @endif

                            @if(!empty($sub->PreferredTimes))
                            <div style="font-size:0.65rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:1px; font-weight:800; margin-bottom:8px">Delivery Schedule</div>
                            <div style="display:flex; flex-wrap:wrap; gap:6px">
                                @foreach($sub->PreferredTimes as $time)
                                <div style="padding:4px 10px; border-radius:8px; font-size:0.75rem; font-weight:600; background:rgba(255,255,255,0.02); color:var(--text-muted); border:1px solid var(--border-color)">
                                    <i class="far fa-clock me-1" style="color:{{ $planColor }}"></i> {{ $time }}
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- Right Side: Pricing & Payments -->
                        <div class="col-lg-5" style="margin-top:15px; margin-top:lg-0">
                            <div class="glassy-layer" style="padding:24px; border:1px solid {{ $planColor }}22">
                                @if(in_array($displayStatus, ['Cancelled', 'Expired']))
                                @php
                                    $plan = $sub->kitchenPlan;
                                    $totalMeals = max(1, (int)($sub->DurationDays ?? 1) * (int)($sub->MealsPerDay ?? 1));
                                    $totalPlanPrice = (float)(($sub->Price ?? 0) + ($sub->DeliveryCharge ?? 0));
                                    $pricePerMeal = $totalPlanPrice / $totalMeals;
                                    $deliveredCount = $sub->orders->where('OrderStatus', 'Delivered')->count();
                                    $consumedCost = $deliveredCount * $pricePerMeal;
                                    $paidAmount = (float)($sub->PaidAmount ?? 0);
                                    $refundAmount = max(0, $paidAmount - $consumedCost);
                                @endphp
                                {{-- Plan Price --}}
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.82rem">
                                    <span style="color:var(--text-muted)">Plan Price</span>
                                    <span style="font-weight:700; color:var(--text-primary)">{{ number_format($totalPlanPrice, 2) }} <small>EGP</small></span>
                                </div>
                                {{-- Amount Paid --}}
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.82rem">
                                    <span style="color:var(--text-muted)">Amount Paid</span>
                                    <span style="font-weight:700; color:#4ade80">{{ number_format($paidAmount, 2) }} <small>EGP</small></span>
                                </div>
                                {{-- Meals Delivered --}}
                                <div style="display:flex; justify-content:space-between; margin-bottom:8px; font-size:0.82rem">
                                    <span style="color:var(--text-muted)">Meals Delivered</span>
                                    <span style="font-weight:700; color:var(--text-primary)">{{ $deliveredCount }} / {{ $totalMeals }}</span>
                                </div>
                                {{-- Consumed Cost --}}
                                <div style="display:flex; justify-content:space-between; margin-bottom:12px; font-size:0.82rem; padding-bottom:12px; border-bottom:1px dashed var(--border-color)">
                                    <span style="color:var(--text-muted)">Consumed Cost</span>
                                    <span style="font-weight:700; color:#f87171">{{ number_format($consumedCost, 2) }} <small>EGP</small></span>
                                </div>
                                {{-- Refund --}}
                                @if($refundAmount > 0)
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:0.9rem">
                                    <span style="color:#4ade80; font-weight:800"><i class="fas fa-wallet me-1"></i> Refunded to Wallet</span>
                                    <span style="font-weight:900; color:#4ade80">{{ number_format($refundAmount, 2) }} <small>EGP</small></span>
                                </div>
                                @endif
                                {{-- Status + Renew --}}
                                <div style="padding:16px; background:rgba(248,113,113,0.05); border:1px solid rgba(248,113,113,0.15); border-radius:14px; text-align:center">
                                    <div style="color:#f87171; font-weight:800; font-size:0.8rem; margin-bottom:10px">
                                        <i class="fas fa-times-circle me-1"></i> {{ strtoupper($displayStatus) }}
                                    </div>
                                    <button onclick="confirmRenew({{ $sub->SubscriptionID }})" class="btn btn-primary btn-sm" style="font-weight:900; border-radius:10px; font-size:0.75rem; padding:8px 20px">
                                        <i class="fas fa-redo me-1"></i> RENEW NOW
                                    </button>
                                </div>
                                @else
                                <div style="display:flex; justify-content:space-between; margin-bottom:6px; font-size:0.85rem">
                                    <span style="color:var(--text-muted)">Running Total</span>
                                    <span style="font-weight:700">{{ number_format($sub->total_price, 2) }} <small>EGP</small></span>
                                </div>
                                <div style="display:flex; justify-content:space-between; margin-bottom:15px; font-size:0.85rem; color:#4ade80">
                                    <span>Paid to Date</span>
                                    <span style="font-weight:800">{{ number_format($sub->PaidAmount, 2) }} <small>EGP</small></span>
                                </div>
                                @endif

                                @if(in_array($displayStatus, ['Cancelled', 'Expired']))
                                {{-- Already handled above --}}
                                @elseif($displayStatus === 'Paused')
                                <div style="padding:15px; background:rgba(255,167,38,0.05); border:1px solid rgba(255,167,38,0.15); border-radius:16px; text-align:center; color:var(--accent); box-shadow: 0 5px 15px rgba(255,167,38,0.1)">
                                    <i class="fas fa-pause-circle me-1"></i> <span style="font-weight:800; font-size:0.85rem">PAUSED</span>
                                </div>
                                @elseif(!$sub->is_fully_paid)
                                <div style="padding:15px; background:{{ $sub->is_overdue ? 'rgba(248,113,113,0.05)' : 'rgba(255,167,38,0.03)' }}; border:1px solid {{ $sub->is_overdue ? 'rgba(248,113,113,0.2)' : 'rgba(255,167,38,0.1)' }}; border-radius:16px; box-shadow: inset 0 2px 10px rgba(0,0,0,0.1)">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px">
                                        <span style="font-size:0.7rem; font-weight:800; color:{{ $sub->is_overdue ? '#f87171' : 'var(--accent)' }}; text-transform:uppercase; letter-spacing:0.5px">Remaining Balance</span>
                                        <span style="font-size:1.25rem; font-weight:900; color:var(--text-primary)">{{ number_format($sub->remaining_balance, 2) }}</span>
                                    </div>
                                    <div class="input-group input-group-sm mb-3" style="box-shadow: 0 4px 15px rgba(0,0,0,0.2); border-radius:10px; overflow:hidden">
                                        <input type="number" id="amt-{{ $sub->SubscriptionID }}" class="form-control" value="{{ round($sub->remaining_balance) }}" style="background:var(--bg-dark); color:var(--text-primary); border:none; font-weight:700; font-size:1rem; padding:10px">
                                        <button onclick="payInstallment({{ $sub->SubscriptionID }}, document.getElementById('amt-{{ $sub->SubscriptionID }}').value, 1, {{ $sub->remaining_balance }})" class="btn btn-primary" style="font-weight:900; padding:0 20px; font-size:0.8rem">PAY NOW</button>
                                    </div>
                                    <div style="font-size:0.65rem; color:var(--text-muted); text-align:center">
                                        <i class="far fa-clock me-1"></i> Due by: {{ \Carbon\Carbon::parse($sub->deadline)->format('d M Y') }}
                                    </div>
                                </div>
                                @else
                                <div style="padding:15px; background:rgba(74,222,128,0.05); border:1px solid rgba(74,222,128,0.15); border-radius:16px; text-align:center; color:#4ade80; box-shadow: 0 5px 15px rgba(74,222,128,0.1)">
                                    <i class="fas fa-check-circle me-1"></i> <span style="font-weight:800; font-size:0.85rem">FULLY PAID & ACTIVE</span>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Quick Actions -->
                @php 
                    $isClosed = in_array($displayStatus, ['Cancelled', 'Expired']);
                    $isManageable = in_array($sub->Status, ['Active', 'Paused']); 
                    $todayOrders = $sub->orders; // Already filtered for nearest upcoming in Controller
                    $activeOrder = $todayOrders->first();
                    $showProgress = ($activeOrder || $displayStatus === 'Active') && !$isClosed;
                @endphp
                
                @if($showProgress)
                    @php
                        if ($activeOrder) {
                            $status = $activeOrder->OrderStatus ?? 'Pending';
                        } else {
                            $status = 'Upcoming';
                        }

                        $statusMap = [
                            'Upcoming'   => 0,
                            'Pending'    => 0,
                            'Confirmed'  => 1,
                            'Preparing'  => 2,
                            'Ready'      => 2,
                            'Delivering' => 3,
                            'Delivered'  => 4,
                            'Cancelled'  => -1,
                        ];
                        $cIdx = $statusMap[$status] ?? 0;
                        $progressPct = ($status === 'Cancelled') ? 0 : min(100, round(($cIdx / 4) * 100));
                        
                        $steps = [
                            ['icon' => 'fa-clock', 'label' => 'Pending'],
                            ['icon' => 'fa-check', 'label' => 'Confirmed'],
                            ['icon' => 'fa-fire',  'label' => 'Preparing'],
                            ['icon' => 'fa-motorcycle', 'label' => 'Delivering'],
                            ['icon' => 'fa-flag-checkered', 'label' => 'Delivered'],
                        ];
                    @endphp
                    <div style="padding:15px 25px 20px; border-top:1px dashed var(--border-color); background:rgba(255,255,255,0.015); border-radius: 0 0 0 0;">
                        <div style="font-size:0.7rem; color:var(--text-muted); font-weight:800; text-transform:uppercase; letter-spacing:1px; margin-bottom:15px; display:flex; justify-content:space-between; align-items:center">
                            <span>Today's Delivery Status @if($activeOrder) • Order #{{ $activeOrder->DeliveryCode ?? $activeOrder->OrderID }} • {{ $activeOrder->DeliveryTime ?? 'Any Time' }} @endif</span>
                            <span style="color:var(--primary); background:rgba(255,107,53,0.1); padding:3px 10px; border-radius:12px; border:1px solid rgba(255,107,53,0.2)">{{ $status }}</span>
                        </div>
                        <div style="position:relative; display:flex; justify-content:space-between; align-items:center">
                            <div style="position:absolute; top:50%; left:5%; right:5%; height:3px; background:var(--bg-dark); transform:translateY(-50%); z-index:0; border-radius:4px">
                                <div style="height:100%; width:{{ $progressPct }}%; background:linear-gradient(90deg,var(--primary),var(--accent)); border-radius:4px; transition:width 1s ease"></div>
                            </div>
                            
                            @foreach($steps as $i => $step)
                                @php
                                    $isDone = ($i <= $cIdx && $status !== 'Cancelled');
                                    $isActive = ($i === $cIdx && $status !== 'Cancelled');
                                    
                                    if ($isActive) {
                                        $bg = 'linear-gradient(135deg,var(--primary),var(--accent))';
                                        $cColor = '#fff';
                                        $borderColor = 'transparent';
                                        $shadow = 'animation: pulseGlow 2s infinite;';
                                    } elseif ($isDone) {
                                        $bg = 'var(--bg-card2)';
                                        $cColor = 'var(--primary)';
                                        $borderColor = 'var(--primary)';
                                        $shadow = '';
                                    } else {
                                        $bg = 'var(--bg-dark)';
                                        $cColor = 'var(--text-muted)';
                                        $borderColor = 'var(--border-color)';
                                        $shadow = '';
                                    }
                                @endphp
                                <div style="z-index:1; position:relative; background:var(--bg-card); border-radius:50%; padding:3px">
                                    <div style="width:30px; height:30px; border-radius:50%; background:{{ $bg }}; border:1px solid {{ $borderColor }}; display:flex; align-items:center; justify-content:center; color:{{ $cColor }}; font-size:0.75rem; {{ $shadow }}" title="{{ $step['label'] }}">
                                        <i class="fas {{ $step['icon'] }}"></i>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($isManageable || $isClosed)
                <div style="padding:10px 25px; background:rgba(0,0,0,0.1); border-top:1px solid var(--border-color); display:flex; justify-content:flex-end; align-items:center; gap:10px">
                    @php
                        // Determine if we need to show the track button (only if delivering usually, or if user explicitly wants order tracking)
                        // The progress bar above is usually enough, but we can keep the button for map tracking
                        $showTrack = false;
                        if ($activeOrder && $activeOrder->OrderStatus === 'Delivering') {
                            $showTrack = true;
                        }
                    @endphp

                    @if($showTrack)
                        <a href="{{ route('frontend.tracking', $activeOrder->OrderID) }}" class="btn btn-xs" 
                           style="background:rgba(59,130,246,0.15); color:#60a5fa; border:1px solid rgba(59,130,246,0.3); font-weight:800; border-radius:8px; padding:6px 15px; font-size:0.75rem; text-decoration:none">
                            <i class="fas fa-map-marker-alt me-1"></i> TRACK DELIVERY
                        </a>
                        <div style="flex:1"></div>
                    @endif

                    @if($isClosed)
                        <button onclick="confirmRenew({{ $sub->SubscriptionID }})" class="btn btn-xs" style="background:rgba(255,107,53,0.12); color:var(--primary); border:1px solid rgba(255,107,53,0.25); font-weight:800; border-radius:8px; padding:6px 15px; font-size:0.7rem">
                            <i class="fas fa-redo me-1"></i> RENEW PLAN
                        </button>
                    @else
                        @if($sub->Status === 'Paused')
                            <button onclick="subscriptionAction('resume', {{ $sub->SubscriptionID }})" class="btn btn-xs" style="background:rgba(74,222,128,0.1); color:#4ade80; border:1px solid rgba(74,222,128,0.2); font-weight:800; border-radius:8px; padding:4px 12px; font-size:0.65rem">RE-ACTIVATE</button>
                        @else
                            <button onclick="showReasonModal('pause', {{ $sub->SubscriptionID }})" class="btn btn-xs" style="background:rgba(255,167,38,0.08); color:var(--accent); border:1px solid rgba(255,167,38,0.15); font-weight:800; border-radius:8px; padding:4px 12px; font-size:0.65rem">PAUSE PLAN</button>
                        @endif

                        @php
                            $stDate = \Carbon\Carbon::parse($sub->StartDate);
                            $lastSubPayment = $sub->payments->last();
                            $isOnlineSub = $lastSubPayment && in_array($lastSubPayment->Method, ['Card', 'Online']);
                            $canRefundSub = $isOnlineSub && $sub->Price > 0 && ($stDate->isFuture() || \Carbon\Carbon::now()->diffInDays($stDate) <= 1) && !in_array($sub->Status, ['Cancelled', 'Refunded']);
                        @endphp
                        @if($canRefundSub)
                            <button onclick="openRefundModal({{ $sub->SubscriptionID }}, 'Subscription')" class="btn btn-xs" style="background:rgba(255,107,53,0.12); color:var(--primary); border:1px solid rgba(255,107,53,0.25); font-weight:800; border-radius:8px; padding:4px 12px; font-size:0.65rem">REFUND</button>
                        @endif

                        <button onclick="showReasonModal('cancel', {{ $sub->SubscriptionID }})" class="btn btn-xs" style="background:rgba(248,113,113,0.08); color:#f87171; border:1px solid rgba(248,113,113,0.15); font-weight:800; border-radius:8px; padding:4px 12px; font-size:0.65rem">CANCEL</button>
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        @endif
    </div>
</div>

    <!-- FAQ mini -->
    <div class="glass-card reveal" style="padding:36px;margin-top:48px">
        <h3 style="margin-bottom:24px;text-align:center">Frequently Asked Questions</h3>
        <div class="grid grid-2" style="gap:20px">
            @foreach([
                ['Q: Can I cancel anytime?', 'A: Yes! You can cancel or change your plan at any time with no penalties.'],
                ['Q: What if I skip a day?', 'A: No problem — credit rolls over to your next delivery.'],
                ['Q: Can I choose my kitchen?', 'A: Absolutely. Pick from any verified kitchen on BiteHub.'],
                ['Q: How does delivery work?', 'A: We deliver to your door at your preferred time daily.'],
            ] as $faq)
            <div style="padding:18px;background:var(--bg-card2);border-radius:14px;border:1px solid var(--border-color)">
                <div style="font-weight:700;color:var(--primary);font-size:0.9rem;margin-bottom:6px">{{ $faq[0] }}</div>
                <div style="color:var(--text-secondary);font-size:0.88rem;line-height:1.6">{{ $faq[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>


    <!-- ═══════════════ REASON MODAL ═══════════════ -->
    <div id="reasonModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(8px)">
        <div class="glass-card" style="width:100%; max-width:450px; padding:32px; border:1px solid var(--primary-border)">
            <h3 id="modalTitle" style="font-size:1.4rem; margin-bottom:12px; color:var(--text-primary)">Subscription Action</h3>
            <p id="modalDesc" style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px">Please provide a reason for this change.</p>
            
            <textarea id="actionReason" class="form-control" rows="4" placeholder="Type your reason here..." style="background:var(--bg-dark); color:var(--text-primary); border:1px solid var(--border-color); border-radius:12px; padding:15px; margin-bottom:20px; outline:none"></textarea>
            
            <div style="display:flex; gap:12px">
                <button onclick="closeModal()" class="btn btn-outline" style="flex:1; border-radius:12px">Cancel</button>
                <button id="confirmBtn" class="btn btn-primary" style="flex:1; border-radius:12px">Confirm</button>
            </div>
        </div>
    </div>
    
    <!-- ═══════════════ CONFIRM MODAL ═══════════════ -->
    <div id="confirmModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(8px)">
        <div class="glass-card" style="width:100%; max-width:400px; padding:32px; border:1px solid var(--primary-border); border-radius: 20px;">
            <div style="font-size:3rem; margin-bottom:15px; color:var(--primary); text-align:center;"><i class="fas fa-question-circle"></i></div>
            <h3 style="font-size:1.4rem; margin-bottom:12px; color:var(--text-primary); text-align:center;">Confirm Action</h3>
            <p id="confirmDesc" style="color:var(--text-muted); font-size:0.9rem; margin-bottom:25px; text-align:center;">Are you sure?</p>
            
            <div style="display:flex; gap:12px">
                <button onclick="document.getElementById('confirmModal').style.display='none'" class="btn btn-outline" style="flex:1; border-radius:12px">Cancel</button>
                <button id="confirmActionBtn" class="btn btn-primary" style="flex:1; border-radius:12px">Yes, Proceed</button>
            </div>
        </div>
    </div>

    <!-- ═══════════════ REFUND REQUEST MODAL ═══════════════ -->
    <div id="refundRequestModal" class="modal-overlay" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:9999; align-items:center; justify-content:center; backdrop-filter:blur(8px)">
        <div class="glass-card" style="width:100%; max-width:450px; padding:32px; border:1px solid var(--primary-border); border-radius: 20px;">
            <div style="padding:0; display:flex; justify-content:space-between; align-items:center; margin-bottom: 20px;">
                <h3 style="margin:0; color:var(--primary); font-weight:800; font-size:1.3rem;">💰 Request Refund</h3>
                <button onclick="closeRefundModal()" style="background:none; border:none; color:var(--text-muted); font-size:1.8rem; cursor:pointer; line-height:1;">&times;</button>
            </div>
            <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:20px">Request refund for <strong id="refundItemName"></strong>.</p>
            
            <form method="POST" action="{{ route('frontend.refund.request') }}">
                @csrf
                <input type="hidden" name="refundable_id" id="refundIdInput">
                <input type="hidden" name="refundable_type" id="refundTypeInput">
                
                <div style="margin-bottom:22px;">
                    <label style="display:block; color:var(--text-muted); font-size:0.8rem; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; margin-bottom:8px;">Reason for refund *</label>
                    <textarea name="reason" rows="4" style="width:100%; background:var(--bg-dark); border:1px solid var(--border-color); border-radius:12px; color:var(--text-primary); padding:15px; font-size:0.93rem; resize:vertical;" placeholder="Please explain why you are requesting a refund..." required></textarea>
                </div>
                
                <div style="padding: 15px; background: rgba(255,107,53,0.05); border: 1px solid rgba(255,107,53,0.1); border-radius: 12px; margin-bottom: 25px;">
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0; line-height: 1.5;">
                        <i class="fas fa-info-circle" style="color: var(--primary); margin-right: 5px;"></i>
                        Your request will be reviewed by admin. Approved refunds are credited to your wallet.
                    </p>
                </div>

                <div style="display:flex; gap:12px">
                    <button type="button" onclick="closeRefundModal()" class="btn btn-outline" style="flex:1; border-radius:12px">Cancel</button>
                    <button type="submit" class="btn btn-primary" style="flex:1; border-radius:12px; font-weight:800;">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
</div>
</section>
@push('scripts')
<script>
    function filterSubscriptions(status) {
        // Update active button
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-filter') === status) btn.classList.add('active');
        });

        // Filter cards
        const cards = document.querySelectorAll('.sub-card-item');
        cards.forEach(card => {
            if (card.getAttribute('data-status') === status) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });

        // Toggle Section Headers based on visible children
        document.querySelectorAll('.sub-section').forEach(section => {
            const hasVisibleCards = Array.from(section.querySelectorAll('.sub-card-item')).some(c => c.style.display !== 'none');
            section.style.display = hasVisibleCards ? 'block' : 'none';
        });

        // Empty state check
        const visibleCards = Array.from(cards).filter(c => c.style.display !== 'none');
        document.getElementById('emptyState').style.display = visibleCards.length > 0 ? 'none' : 'block';
    }

    let currentAction = null;
    let currentSubId = null;

    function showReasonModal(action, id) {
        currentAction = action;
        currentSubId = id;
        const modal = document.getElementById('reasonModal');
        const title = document.getElementById('modalTitle');
        const desc = document.getElementById('modalDesc');
        const btn = document.getElementById('confirmBtn');

        if (action === 'pause') {
            title.innerText = 'Pause Subscription';
            desc.innerText = 'Tell us why you want to pause your plan temporarily.';
            btn.innerText = 'Pause Plan';
        } else {
            title.innerText = 'Cancel Subscription';
            desc.innerText = 'We are sorry to see you go. Please tell us why you are cancelling.';
            btn.innerText = 'Cancel Plan';
        }

        modal.style.display = 'flex';
        document.getElementById('actionReason').value = '';
    }

    function openRefundModal(id, type) {
        document.getElementById('refundItemName').textContent = (type === 'Order' ? 'Order #' : 'Subscription #') + id;
        document.getElementById('refundIdInput').value = id;
        document.getElementById('refundTypeInput').value = type;
        document.getElementById('refundRequestModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeRefundModal() {
        document.getElementById('refundRequestModal').style.display = 'none';
        document.body.style.overflow = '';
    }

    function closeModal() {
        document.getElementById('reasonModal').style.display = 'none';
    }

    function showToast(m, t) {
        const c = document.getElementById('toastContainer');
        if(!c) {
            const tC = document.createElement('div');
            tC.id = 'toastContainer';
            tC.className = 'toast-container';
            document.body.appendChild(tC);
        }
        const cnt = document.getElementById('toastContainer');
        const e = document.createElement('div');
        e.className = `toast-item ${t}`;
        let color = t === 'warning' ? '#f59e0b' : (t === 'error' ? '#ef4444' : '#10b981');
        e.innerHTML = `<div class="toast-indicator" style="background:${color}"></div><div class="toast-content">${m}</div>`;
        cnt.appendChild(e);
        setTimeout(() => e.classList.add('show'), 100);
        setTimeout(() => { e.classList.remove('show'); setTimeout(() => e.remove(), 400); }, 3000);
    }

    document.getElementById('confirmBtn').onclick = function() {
        const reason = document.getElementById('actionReason').value.trim();
        if (!reason) {
            showToast('Please provide a reason.', 'error');
            return;
        }
        subscriptionAction(currentAction, currentSubId, reason);
    };

    function subscriptionAction(action, id, reason = '') {
        const url = action === 'resume' 
            ? `/subscription/${id}/resume` 
            : (action === 'pause' ? `/subscription/${id}/pause` : `/subscription/${id}/cancel`);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                location.reload();
            } else {
                showToast('Error: ' + d.message, 'error');
            }
        })
        .catch(e => showToast('Action failed: ' + e, 'error'));
    }

    function deletePendingRequest(id) {
        document.getElementById('confirmDesc').innerText = "Are you sure you want to cancel and delete this request? This action cannot be undone.";
        const actionBtn = document.getElementById('confirmActionBtn');
        
        actionBtn.onclick = function() {
            document.getElementById('confirmModal').style.display = 'none';
            fetch(`/subscription/${id}/delete-pending`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    location.reload();
                } else {
                    showToast('Error: ' + d.message, 'error');
                }
            })
            .catch(e => showToast('Action failed: ' + e, 'error'));
        };
        
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function payInstallment(subId, amount, min, max) {
        amount = parseFloat(amount);
        min = parseFloat(min);
        max = parseFloat(max);
        
        if (isNaN(amount) || amount < min) {
            showToast(`The minimum payment allowed is ${min} EGP.`, 'warning');
            return;
        }
        if (amount > max) {
            showToast(`The maximum payment allowed is the remaining balance of ${max} EGP.`, 'warning');
            return;
        }

        document.getElementById('confirmDesc').innerText = `Are you sure you want to proceed to checkout and pay ${amount.toLocaleString()} EGP for this subscription?`;
        const actionBtn = document.getElementById('confirmActionBtn');
        actionBtn.onclick = function() {
            window.location.href = `/subscription/${subId}/pay?amount=${amount}`;
        };
        document.getElementById('confirmModal').style.display = 'flex';
    }

    function confirmRenew(id) {
        document.getElementById('confirmDesc').innerText = "Are you sure you want to renew this subscription? A new request will be sent to the kitchen for approval and you will be quoted a new price.";
        const actionBtn = document.getElementById('confirmActionBtn');
        
        // Create a temporary form to submit POST request
        actionBtn.onclick = function() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/subscription/${id}/renew`;
            
            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);
            
            document.body.appendChild(form);
            form.submit();
        };
        document.getElementById('confirmModal').style.display = 'flex';
    }

    document.addEventListener('DOMContentLoaded', () => {
        const reveals = document.querySelectorAll('.reveal');
        reveals.forEach((el, i) => {
            setTimeout(() => {
                el.classList.add('visible');
            }, i * 50);
        });
        
        filterSubscriptions('Pending');
    });
</script>
@endpush
@endsection
