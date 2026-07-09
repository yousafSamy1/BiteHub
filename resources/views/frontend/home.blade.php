@extends('frontend.layouts.app')
@section('title', 'Home')
@section('nav-home', 'active')

@section('content')

<!-- ═══════════════ HERO ═══════════════ -->
<section class="hero">
    <style>
        .ad-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--bg-card2);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            width: 44px;
            height: 44px;
            border-radius: 50%;
            cursor: pointer;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .ad-nav:hover {
            background: var(--primary);
            border-color: var(--primary);
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 8px 20px rgba(255,107,53,0.3);
        }
        .ad-nav:active { transform: translateY(-50%) scale(0.95); }
        .ad-nav.prev { left: 15px; }
        .ad-nav.next { right: 15px; }
        .ad-nav i { font-size: 1.1rem; }
        
        .kitchen-ad-slide { display: none; }
        .kitchen-ad-slide:first-child { display: flex; }
        
        @media (max-width: 768px) {
            .kitchen-ad-slide { flex-direction: column !important; text-align: center !important; justify-content: center !important; padding: 25px 15px !important; min-height: auto !important; }
            .kitchen-ad-slide h2 { font-size: 1.4rem !important; margin-bottom: 10px !important; line-height: 1.3 !important; }
            .kitchen-ad-slide p { display: none !important; }
            .kitchen-ad-slide > div { align-items: center !important; justify-content: center !important; text-align: center !important; }
            .kitchen-ad-slide .btn { width: 100% !important; margin-top: 10px !important; padding: 12px !important; font-size: 1rem !important; }
            .kitchen-ad-slide span { margin: 0 auto 10px auto !important; display: inline-block !important; }
            .kitchen-ad-slide div[style*="width:72px"] { width: 56px !important; height: 56px !important; font-size: 1.5rem !important; }
        }
    </style>
    <div class="hero-bg">

        <div id="hero-particles"></div>
    </div>
    <div class="container">
        <div class="hero-inner">
            <!-- Left content -->
            <div class="hero-content">
                <div class="hero-badge animate-fadeInLeft">
                    <i class="fas fa-fire"></i> &nbsp;#1 Home Food Platform
                </div>
                <h1 class="animate-fadeInUp delay-1">
                    Homemade Food,<br>
                    <span class="highlight">Delivered Fresh</span>
                </h1>
                <p class="animate-fadeInUp delay-2">
                    Discover amazing home kitchens and caterers near you. Order authentic homemade meals delivered straight to your doorstep.
                </p>
                <div class="hero-actions animate-fadeInUp delay-3">
                    <a href="{{ route('frontend.browse') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-utensils"></i> Explore Kitchens
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline btn-lg">
                        <i class="fas fa-store"></i> Join as Kitchen
                    </a>
                </div>
                <div class="hero-stats animate-fadeInUp delay-4">
                    <div class="hero-stat">
                        <h3>500+</h3>
                        <p>Home Kitchens</p>
                    </div>
                    <div class="hero-stat">
                        <h3>10K+</h3>
                        <p>Happy Customers</p>
                    </div>
                    <div class="hero-stat">
                        <h3>50K+</h3>
                        <p>Orders Delivered</p>
                    </div>
                </div>
            </div>
            <!-- Right visual -->
            <div class="hero-visual">
                <div class="hero-floating-items">
                    <span class="floating-food">🍕</span>
                    <span class="floating-food">🥗</span>
                    <span class="floating-food">🍜</span>
                    <span class="floating-food">🍰</span>
                    <span class="floating-food">🥘</span>
                </div>
                <div class="hero-blob">
                    <div class="hero-food-emoji">🍽️</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ═══════════════ KITCHEN ADS ═══════════════ -->
@if(isset($kitchenAds) && $kitchenAds->isNotEmpty())
<section class="section" style="padding-bottom:0; margin-top:-40px; position:relative; z-index:10;">
    <div class="container">
        <div class="reveal" style="display:flex;align-items:center;gap:12px;">
            @if($kitchenAds->count() > 1)
            <button class="ad-nav prev" onclick="moveAd(-1, 'kitchen')" style="position:static; transform:none;"><i class="fas fa-chevron-left"></i></button>
            @endif

            <div style="flex:1;position:relative;overflow:hidden;border-radius:24px; box-shadow:0 20px 40px rgba(0,0,0,0.3);">
                @foreach($kitchenAds as $adIdx => $ad)
                @php
                    $bgUrl = $ad->BackgroundImage ? asset('upload/ad_images/'.$ad->BackgroundImage) : asset('upload/website_assets/hero.png');
                @endphp
                <div class="ad-slide kitchen-ad-slide" style="display:{{ $adIdx === 0 ? 'flex' : 'none' }};position:relative;min-height:260px;padding:40px;align-items:center;gap:30px;background-image:url('{{ $bgUrl }}');background-size:cover;background-position:center;overflow:hidden;">
                    <!-- Dark Overlay for Readability -->
                    <div style="position:absolute;inset:0;background:linear-gradient(to right, rgba(0,0,0,0.9) 0%, rgba(0,0,0,0.6) 50%, rgba(0,0,0,0.3) 100%);z-index:1;"></div>
                    
                    <div style="flex-shrink:0;width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:2rem;box-shadow:0 8px 25px rgba(255,107,53,0.5);border:2px solid rgba(255,255,255,0.2);z-index:2;">👩‍🍳</div>
                    
                    <div style="flex:1;min-width:0;z-index:2;">
                        <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px">
                            <span style="background:var(--primary);color:#fff;font-size:0.75rem;font-weight:900;padding:5px 14px;border-radius:30px;text-transform:uppercase;letter-spacing:1.5px;box-shadow:0 4px 12px rgba(255,107,53,0.4)">Kitchen Ads</span>
                            @if($ad->kitchenOwner)<span style="color:#f1f5f9;font-size:0.95rem;font-weight:700;letter-spacing:0.5px;text-shadow:0 1px 2px rgba(0,0,0,0.5);">by {{ $ad->kitchenOwner->KitchenName }}</span>@endif
                        </div>
                        <h2 style="font-size:2.2rem;font-weight:900;margin:0 0 10px;color:#fff;text-shadow:0 2px 10px rgba(0,0,0,0.8); letter-spacing:-0.5px;">{{ $ad->Title }}</h2>
                        @if($ad->Description)<p style="color:#e2e8f0;margin:0;font-size:1.15rem;line-height:1.6;max-width:85%;text-shadow:0 1px 4px rgba(0,0,0,0.8);">{{ $ad->Description }}</p>@endif
                    </div>
                    
                    @if($ad->kitchenOwner)
                    <a href="{{ route('frontend.kitchen', $ad->kitchenOwner->KitchenOwnerID) }}" class="btn btn-primary" style="flex-shrink:0;border-radius:50px;font-size:1.1rem;font-weight:800;padding:16px 32px;white-space:nowrap;z-index:2;box-shadow:0 10px 25px rgba(255,107,53,0.5); transition:0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">Visit Kitchen <i class="fas fa-arrow-right ms-2"></i></a>
                    @endif
                </div>
                @endforeach
                
                @if($kitchenAds->count() > 1)
                <div style="position:absolute;bottom:15px;left:0;right:0;display:flex;justify-content:center;gap:8px;z-index:3;">
                    @foreach($kitchenAds as $adIdx => $ad)
                    <button onclick="showAd({{ $adIdx }}, 'kitchen')" id="kitchenAdDot-{{ $adIdx }}" style="width:8px;height:8px;border-radius:50%;border:none;cursor:pointer;background:{{ $adIdx === 0 ? 'var(--primary)' : 'rgba(255,255,255,0.3)' }};transition:all 0.3s;padding:0;"></button>
                    @endforeach
                </div>
                @endif
            </div>

            @if($kitchenAds->count() > 1)
            <button class="ad-nav next" onclick="moveAd(1, 'kitchen')" style="position:static; transform:none;"><i class="fas fa-chevron-right"></i></button>
            @endif
        </div>
    </div>
</section>
@endif

<!-- ═══════════════ HOW IT WORKS ═══════════════ -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="subtitle">Simple Process</span>
            <h2>How It Works</h2>
            <p>Get delicious homemade food in just 3 easy steps</p>
        </div>
        <div class="grid grid-3">
            <div class="glass-card step-card reveal">
                <div class="step-number">1</div>
                <h3>Browse Kitchens</h3>
                <p>Explore verified home kitchens and caterers in your area with real reviews and ratings.</p>
            </div>
            <div class="glass-card step-card reveal">
                <div class="step-number">2</div>
                <h3>Place Your Order</h3>
                <p>Choose your favorite meals, customize them, and place your order with secure payment.</p>
            </div>
            <div class="glass-card step-card reveal">
                <div class="step-number">3</div>
                <h3>Enjoy at Home</h3>
                <p>Track your delivery in real-time and enjoy fresh homemade food at your doorstep.</p>
            </div>
        </div>
    </div>
</section>

@auth
@if(Auth::user()->Role === 'Customer' && $recentCustomerOrders->isNotEmpty())
<!-- ═══════════════ ORDER AGAIN ═══════════════ -->
<section class="section" id="order-again-section" style="background: var(--bg-dark); overflow: hidden;">
    <style>
        /* ── Order Again Section ── */
        #order-again-section .section-header .subtitle {
            color: var(--primary);
        }
        .oa-track-wrap {
            position: relative;
        }
        .oa-track {
            display: flex;
            gap: 22px;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: none;
            padding: 12px 4px 20px;
            cursor: grab;
            user-select: none;
        }
        .oa-track::-webkit-scrollbar { display: none; }
        .oa-track.is-dragging { cursor: grabbing; }

        .oa-card {
            flex: 0 0 320px;
            scroll-snap-align: start;
            background: var(--glass-bg);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            backdrop-filter: blur(12px);
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
        }
        .oa-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 50px rgba(255, 107, 53, 0.18);
            border-color: rgba(255, 107, 53, 0.35);
        }
        .oa-card-header {
            padding: 18px 20px 14px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
        }
        .oa-order-meta {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }
        .oa-order-id {
            font-weight: 800;
            font-size: 1rem;
            color: var(--text-primary);
        }
        .oa-order-date {
            font-size: 0.78rem;
            color: var(--text-muted);
        }
        .oa-status-badge {
            padding: 5px 14px;
            border-radius: 30px;
            font-size: 0.73rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }
        .oa-status-delivered { background: rgba(25,135,84,0.18); color: #4ade80; border: 1px solid rgba(74,222,128,0.25); }
        .oa-status-pending   { background: rgba(255,167,38,0.18); color: #fbbf24; border: 1px solid rgba(251,191,36,0.25); }
        .oa-status-cancelled { background: rgba(220,53,69,0.18);  color: #f87171; border: 1px solid rgba(248,113,113,0.25); }
        .oa-status-default   { background: rgba(150,150,150,0.15); color: var(--text-muted); border: 1px solid rgba(150,150,150,0.2); }

        .oa-items-list {
            padding: 14px 20px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 110px;
        }
        .oa-item-row {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .oa-item-thumb {
            width: 46px;
            height: 46px;
            border-radius: 10px;
            object-fit: cover;
            background: var(--bg-darker);
            flex-shrink: 0;
            border: 1px solid var(--glass-border);
        }
        .oa-item-info {
            flex: 1;
            min-width: 0;
        }
        .oa-item-name {
            font-size: 0.88rem;
            font-weight: 600;
            color: var(--text-primary);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .oa-item-qty {
            font-size: 0.76rem;
            color: var(--text-muted);
            margin-top: 2px;
        }
        .oa-item-price {
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--primary);
            flex-shrink: 0;
        }
        .oa-more-badge {
            display: inline-block;
            background: rgba(255,107,53,0.12);
            color: var(--primary);
            border: 1px solid rgba(255,107,53,0.25);
            border-radius: 8px;
            font-size: 0.78rem;
            padding: 4px 10px;
            font-weight: 600;
            margin-top: 4px;
        }
        .oa-card-footer {
            padding: 14px 20px 18px;
            border-top: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .oa-total {
            display: flex;
            flex-direction: column;
            gap: 1px;
        }
        .oa-total-label { font-size: 0.73rem; color: var(--text-muted); }
        .oa-total-val   { font-size: 1.15rem; font-weight: 800; color: var(--text-primary); }
        .oa-reorder-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, var(--primary), var(--accent));
            color: #fff;
            border: none;
            padding: 11px 22px;
            border-radius: 50px;
            font-size: 0.88rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 6px 20px rgba(255,107,53,0.35);
            text-decoration: none;
        }
        .oa-reorder-btn:hover {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 10px 30px rgba(255,107,53,0.5);
            color: #fff;
        }
        .oa-reorder-btn:active { transform: scale(0.97); }
        .oa-reorder-btn .spinner {
            width: 16px; height: 16px;
            border: 2px solid rgba(255,255,255,0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: oaSpin 0.7s linear infinite;
            display: none;
        }
        @keyframes oaSpin { to { transform: rotate(360deg); } }

        /* Arrow nav */
        .oa-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: var(--bg-card2);
            backdrop-filter: blur(8px);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            width: 42px; height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 10;
            transition: all 0.25s ease;
        }
        .oa-arrow:hover { background: var(--primary); border-color: var(--primary); box-shadow: 0 6px 18px rgba(255,107,53,0.4); }
        .oa-arrow.prev { left: -16px; }
        .oa-arrow.next { right: -16px; }
        @media(max-width:640px){
            .oa-arrow { display: none; }
            .oa-card  { flex: 0 0 285px; }
        }
    </style>

    <div class="container">
        <div class="section-header reveal">
            <span class="subtitle"><i class="fas fa-rotate-right" style="margin-right:6px"></i>Order Again</span>
            <h2>Your Recent Orders</h2>
            <p>Loved something before? Reorder with a single click and pay instantly.</p>
        </div>

        <div class="oa-track-wrap">
            <!-- Prev arrow -->
            <button class="oa-arrow prev" onclick="oaMove(-1)" aria-label="Previous">
                <i class="fas fa-chevron-left"></i>
            </button>

            <div class="oa-track" id="oaTrack">
                @foreach($recentCustomerOrders as $ro)
                @php
                    $roItems   = $ro->menuItems;
                    $showItems = $roItems->take(3);
                    $moreCount = max(0, $roItems->count() - 3);
                    $statusMap = [
                        'Delivered' => 'delivered',
                        'Pending'   => 'pending',
                        'Cancelled' => 'cancelled',
                    ];
                    $statusCls  = 'oa-status-' . ($statusMap[$ro->OrderStatus] ?? 'default');
                    $statusIcon = match($ro->OrderStatus) {
                        'Delivered' => 'fa-circle-check',
                        'Pending'   => 'fa-clock',
                        'Cancelled' => 'fa-ban',
                        default     => 'fa-circle-dot',
                    };

                    // Build full JSON for all items → used by JS to inject into cart
                    $roCartItems = [];
                    foreach ($roItems as $rci) {
                        $rciImg = '';
                        if ($rci->images->count() > 0) {
                            $dbImg  = $rci->images->first()->Image;
                            $rciImg = str_starts_with($dbImg,'http') ? $dbImg : url('upload/item_images/'.$dbImg);
                        } else {
                            $rcin   = strtolower($rci->ItemName);
                            $rciMap = 'grills.png';
                            if(str_contains($rcin,'koshari'))  $rciMap = 'koshari.png';
                            elseif(str_contains($rcin,'pasta') || str_contains($rcin,'macarona')) $rciMap = 'pasta.png';
                            elseif(str_contains($rcin,'salad') || str_contains($rcin,'healthy') || str_contains($rcin,'keto') || str_contains($rcin,'tayebat')) $rciMap = 'healthy.png';
                            elseif(str_contains($rcin,'fish')  || str_contains($rcin,'seafood')  || str_contains($rcin,'shrimp')) $rciMap = 'seafood.png';
                            elseif(str_contains($rcin,'cake')  || str_contains($rcin,'sweet')    || str_contains($rcin,'kunafa')  || str_contains($rcin,'baklava')) $rciMap = 'sweets.png';
                            elseif(str_contains($rcin,'soup')  || str_contains($rcin,'lentil'))  $rciMap = 'soup.png';
                            elseif(str_contains($rcin,'juice') || str_contains($rcin,'drink')    || str_contains($rcin,'coffee')) $rciMap = 'drinks.png';
                            elseif(str_contains($rcin,'foul')  || str_contains($rcin,'falafel')) $rciMap = 'foul_falafel.png';
                            elseif(str_contains($rcin,'mahshi')|| str_contains($rcin,'stuffed')) $rciMap = 'mahshi.png';
                            $rciImg = url('upload/website_assets/'.$rciMap);
                        }
                        $roCartItems[] = [
                            'id'    => $rci->MenuItemID,
                            'name'  => $rci->ItemName,
                            'price' => (float)($rci->DiscountPrice ?? $rci->ItemPrice),
                            'image' => $rciImg,
                            'qty'   => (int)($rci->pivot->Quantity ?? 1),
                            'note'  => '',
                            'kitchen_id' => $rci->KitchenOwnerID,
                            'caterer_id' => $rci->CatererID,
                        ];
                    }
                    $roItemsJson = json_encode($roCartItems, JSON_HEX_QUOT | JSON_HEX_APOS);
                @endphp
                <div class="oa-card reveal">
                    <!-- Header -->
                    <div class="oa-card-header">
                        <div class="oa-order-meta">
                            <span class="oa-order-id">#{{ $ro->KitchenOrderNumber ?? $ro->OrderID }}</span>
                            <span class="oa-order-date">
                                <i class="fas fa-calendar-alt" style="font-size:.7rem;margin-right:4px"></i>
                                {{ \Carbon\Carbon::parse($ro->CreatedAt ?? now())->format('M j, Y') }}
                            </span>
                        </div>
                        <span class="oa-status-badge {{ $statusCls }}">
                            <i class="fas {{ $statusIcon }}" style="margin-right:5px"></i>{{ $ro->OrderStatus }}
                        </span>
                    </div>

                    <!-- Items -->
                    <div class="oa-items-list">
                        @foreach($showItems as $ri)
                        @php
                            $riImg = '';
                            if($ri->images->count() > 0) {
                                $dbImg = $ri->images->first()->Image;
                                $riImg = str_starts_with($dbImg,'http') ? $dbImg : url('upload/item_images/'.$dbImg);
                            } else {
                                $rn = strtolower($ri->ItemName);
                                $rmap = 'grills.png';
                                if(str_contains($rn,'koshari'))  $rmap = 'koshari.png';
                                elseif(str_contains($rn,'pasta') || str_contains($rn,'macarona')) $rmap = 'pasta.png';
                                elseif(str_contains($rn,'salad') || str_contains($rn,'healthy') || str_contains($rn,'keto') || str_contains($rn,'tayebat')) $rmap = 'healthy.png';
                                elseif(str_contains($rn,'fish')  || str_contains($rn,'seafood') || str_contains($rn,'shrimp')) $rmap = 'seafood.png';
                                elseif(str_contains($rn,'cake')  || str_contains($rn,'sweet') || str_contains($rn,'kunafa') || str_contains($rn,'baklava')) $rmap = 'sweets.png';
                                elseif(str_contains($rn,'soup')  || str_contains($rn,'lentil')) $rmap = 'soup.png';
                                elseif(str_contains($rn,'juice') || str_contains($rn,'drink') || str_contains($rn,'coffee')) $rmap = 'drinks.png';
                                elseif(str_contains($rn,'foul')  || str_contains($rn,'falafel')) $rmap = 'foul_falafel.png';
                                elseif(str_contains($rn,'mahshi') || str_contains($rn,'stuffed')) $rmap = 'mahshi.png';
                                $riImg = url('upload/website_assets/'.$rmap);
                            }
                            $riPrice = $ri->DiscountPrice ?? $ri->ItemPrice;
                            $riQty   = $ri->pivot->Quantity ?? 1;
                        @endphp
                        <div class="oa-item-row">
                            <img src="{{ $riImg }}" alt="{{ $ri->ItemName }}" class="oa-item-thumb">
                            <div class="oa-item-info">
                                <div class="oa-item-name">{{ $ri->ItemName }}</div>
                                <div class="oa-item-qty">× {{ $riQty }}</div>
                            </div>
                            <span class="oa-item-price">{{ number_format($riPrice * $riQty, 0) }}<small style="font-weight:500;font-size:.7rem"> EGP</small></span>
                        </div>
                        @endforeach
                        @if($moreCount > 0)
                            <span class="oa-more-badge">+{{ $moreCount }} more item{{ $moreCount > 1 ? 's' : '' }}</span>
                        @endif
                    </div>

                    <!-- Footer -->
                    <div class="oa-card-footer">
                        <div class="oa-total">
                            <span class="oa-total-label">Total Paid</span>
                            <span class="oa-total-val">{{ number_format($ro->TotalPrice, 2) }} <small style="font-size:.7rem;font-weight:500">EGP</small></span>
                        </div>
                        <button type="button"
                                class="oa-reorder-btn"
                                id="reorder-btn-{{ $ro->OrderID }}"
                                data-items='{{ $roItemsJson }}'
                                onclick="oaReorder(this)">
                            <span class="spinner" id="reorder-spin-{{ $ro->OrderID }}"></span>
                            <i class="fas fa-rotate-right" id="reorder-icon-{{ $ro->OrderID }}"></i>
                            Reorder
                        </button>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Next arrow -->
            <button class="oa-arrow next" onclick="oaMove(1)" aria-label="Next">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>
@endif
@endauth


<section class="section section-alt">
    <div class="container">
        <div class="section-header reveal">
            <span class="subtitle">Explore</span>
            <h2>Browse by Category</h2>
        </div>
        <div class="grid grid-4 reveal">
            @php $catIcons = ['🍽️','🍰','🥗','🥤','🍳','🍲','🥙','🍱']; @endphp
            @foreach($categories as $i => $cat)
            <a href="{{ route('frontend.menu', ['cat' => $cat->CategoryID]) }}" class="glass-card cat-card">
                <div class="cat-icon">{{ $catIcons[$i] ?? '🍴' }}</div>
                <h3>{{ $cat->Name }}</h3>
                <p>{{ Str::limit($cat->Description ?? 'Explore dishes', 45) }}</p>
            </a>
            @endforeach
        </div>
    </div>
</section>


<!-- ═══════════════ FEATURED KITCHENS ═══════════════ -->
<section class="section">
    <div class="container">
        <div class="section-header reveal">
            <span class="subtitle">Top Rated</span>
            <h2>Kitchens</h2>
            <p>Verified home kitchens with the best reviews from our community</p>
        </div>
        <div class="grid grid-3">
            @foreach($kitchens as $k)
            @php
                $rating = $k->average_rating;
                $reviews = $k->review_count;
                $isSponsored = $sponsoredKitchenIds->contains($k->KitchenOwnerID);
            @endphp
            <a href="{{ route('frontend.kitchen', $k->KitchenOwnerID) }}" class="card kitchen-card reveal">
                @php
                    $kImg = 'default_k.png';
                    $kn = strtolower($k->KitchenName);
                    if(str_contains($kn, 'mama')) $kImg = 'mama.png';
                    elseif(str_contains($kn, 'rania')) $kImg = 'rania.png';
                    elseif(str_contains($kn, 'amira')) $kImg = 'hero.png';
                    elseif(str_contains($kn, 'fatma')) $kImg = 'upper_egypt.png';
                    elseif(str_contains($kn, 'nour') || str_contains($kn, 'delights')) $kImg = 'mediterranean.png';
                    elseif(str_contains($kn, 'heba') || str_contains($kn, 'healthy')) $kImg = 'healthy.png';
                    elseif(str_contains($kn, 'samira') || str_contains($kn, 'seafood') || str_contains($kn, 'alex')) $kImg = 'seafood.png';

                    $kRawImg = $k->photo ?? $k->Image ?? null;
                    $kProfileUrl = (!empty($kRawImg) && !str_contains($kRawImg, 'no_image') && file_exists(public_path('upload/admin_images/'.$kRawImg))) ? asset('upload/admin_images/'.$kRawImg) : asset('upload/website_assets/'.$kImg);
                @endphp
                <div class="card-img" style="background:linear-gradient(135deg,rgba(17,17,17,0.4),rgba(17,17,17,0.1));display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;height:240px;position:relative;overflow:hidden">
                    <img src="{{ $kProfileUrl }}" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;opacity:0.25;z-index:0;filter:blur(3px)">
                    <img src="{{ $kProfileUrl }}" style="width:96px;height:96px;border-radius:50%;border:4px solid var(--primary);object-fit:cover;box-shadow:0 8px 25px rgba(255,107,53,0.4);z-index:1" alt="{{ $k->KitchenName }}">
                    <h3 style="color:var(--text-primary);font-size:1.25rem;z-index:1;font-weight:800;text-shadow:0 2px 4px rgba(0,0,0,0.1)">{{ $k->KitchenName }}</h3>
                    @if($k->VerifyStatus === 'Verified')
                    <span class="kitchen-badge" style="z-index:1;background:rgba(25,135,84,0.9);box-shadow:0 4px 10px rgba(0,0,0,0.15);padding:4px 12px;border-radius:20px;color:#fff;font-size:0.75rem"><i class="fas fa-check"></i> Verified</span>
                    @endif
                    @if($isSponsored)
                    <span style="z-index:1;position:absolute;top:10px;right:10px;background:linear-gradient(135deg,var(--primary),var(--accent));padding:4px 12px;border-radius:20px;color:#fff;font-size:0.72rem;font-weight:800;box-shadow:0 4px 12px rgba(255,107,53,0.4)"><i class="fas fa-star me-1"></i>Promoted</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="kitchen-rating">
                        <i class="fas fa-star"></i> {{ $rating }}
                        <span style="color:var(--text-muted);font-weight:400">({{ $reviews }} reviews)</span>
                    </div>
                    <p class="card-text">{{ Str::limit($k->Description ?? 'Delicious homemade food made with love.', 80) }}</p>
                    <div class="kitchen-meta">
                        <span><i class="fas fa-clock"></i> 
                            @if(!empty($k->OpeningTime) && !empty($k->ClosingTime))
                                {{ \Carbon\Carbon::parse($k->OpeningTime)->format('g:i A') }} – {{ \Carbon\Carbon::parse($k->ClosingTime)->format('g:i A') }}
                            @else
                                9:00 AM – 10:00 PM
                            @endif
                        </span>
                        <span><i class="fas fa-location-dot"></i> {{ $k->Location ?? 'Cairo' }}</span>
                        @php $cStatus = $k instanceof \App\Models\KitchenOwner ? $k->current_status : 'Open'; @endphp
                        <span class="badge-status badge-{{ strtolower($cStatus) }}">{{ $cStatus }}</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>

<!-- ═══════════════ FEATURED CATERERS ═══════════════ -->
<section class="section section-alt">
    <div class="container">
        <div class="section-header reveal">
            <span class="subtitle">Premium</span>
            <h2>Caterers</h2>
            <p>Professional catering services for your events and gatherings</p>
        </div>
        <div class="grid grid-3">
            @foreach($caterers as $c)
            @php
                $rating = $c->average_rating;
                $reviews = $c->review_count;
                $isSponsored = $sponsoredCatererIds->contains($c->CatererID);
            @endphp
            <a href="{{ route('frontend.caterer', $c->CatererID) }}" class="card kitchen-card reveal">
                <div class="card-img" style="background:linear-gradient(135deg,rgba(17,17,17,0.4),rgba(17,17,17,0.1));display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;height:240px;position:relative;overflow:hidden">
                    <img src="{{ asset('upload/website_assets/packages.png') }}" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;opacity:0.25;z-index:0;filter:blur(3px)">
                    <div style="width:96px;height:96px;border-radius:50%;border:4px solid var(--primary);background:#333;display:flex;align-items:center;justify-content:center;font-size:3rem;z-index:1;box-shadow:0 8px 25px rgba(255,107,53,0.4)">👨‍🍳</div>
                    <h3 style="color:var(--text-primary);font-size:1.25rem;z-index:1;font-weight:800;text-shadow:0 2px 4px rgba(0,0,0,0.1)">{{ $c->BusinessName }}</h3>
                    @if($isSponsored)
                    <span style="z-index:1;position:absolute;top:10px;right:10px;background:linear-gradient(135deg,var(--primary),var(--accent));padding:4px 12px;border-radius:20px;color:#fff;font-size:0.72rem;font-weight:800;box-shadow:0 4px 12px rgba(255,107,53,0.4)"><i class="fas fa-star me-1"></i>Promoted</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="kitchen-rating">
                        <i class="fas fa-star"></i> {{ $rating }}
                        <span style="color:var(--text-muted);font-weight:400">({{ $reviews }} reviews)</span>
                    </div>
                    <p class="card-text">{{ Str::limit($c->Description ?? 'Professional catering services for all occasions.', 80) }}</p>
                    <div class="kitchen-meta">
                        <span><i class="fas fa-calendar-check"></i> Book in advance</span>
                        <span><i class="fas fa-location-dot"></i> Service City-wide</span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>


<section class="section" style="background:var(--bg-darker)">
    <div class="container">
        <div class="section-header">
            <h2 class="reveal">🔥 Popular Dishes</h2>
            <p class="reveal">Discover the most loved meals across the platform.</p>
        </div>
        <div class="grid grid-4">
            @php $foodEmojis = ['🍕','🍜','🥗','🍗','🥘','🍱','🥙','🍛']; @endphp
            @foreach(collect($popular)->take(8) as $idx => $item)
            <div class="card menu-card reveal">
                <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.1),rgba(255,167,38,0.05));display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;height:180px">
                    @php
                        $itemImg = null;
                        if($item->images->count() > 0) {
                            $dbImg = $item->images->first()->Image;
                            $itemImg = str_starts_with($dbImg, 'http') ? $dbImg : url('upload/item_images/'.$dbImg);
                        } else {
                            $mappedImg = 'grills.png'; // Grill/Meat Default
                            $in = strtolower($item->ItemName);
                            if(str_contains($in, 'koshari')) $mappedImg = 'koshari.png';
                            elseif(str_contains($in, 'mahshi') || str_contains($in, 'waraq') || str_contains($in, 'stuffed')) $mappedImg = 'mahshi.png';
                            elseif(str_contains($in, 'foul') || str_contains($in, 'falafel') || str_contains($in, 'breakfast')) $mappedImg = 'foul_falafel.png';
                            elseif(str_contains($in, 'pasta') || str_contains($in, 'macarona') || str_contains($in, 'bechamel') || str_contains($in, 'lasagna')) $mappedImg = 'pasta.png';
                            elseif(str_contains($in, 'molokhia') || str_contains($in, 'green') || str_contains($in, 'salad') || str_contains($in, 'keto') || str_contains($in, 'tayebat') || str_contains($in, 'healthy') || str_contains($in, 'acai')) $mappedImg = 'healthy.png'; // Green theme for Molokhia
                            elseif(str_contains($in, 'fish') || str_contains($in, 'shrimp') || str_contains($in, 'seafood') || str_contains($in, 'sayadeya')) $mappedImg = 'seafood.png';
                            elseif(str_contains($in, 'dessert') || str_contains($in, 'sweet') || str_contains($in, 'baklava') || str_contains($in, 'kunafa') || str_contains($in, 'qatayef') || str_contains($in, 'basbousa') || str_contains($in, 'ali') || str_contains($in, 'cake')) $mappedImg = 'sweets.png';
                            elseif(str_contains($in, 'soup') || str_contains($in, 'lentil') || str_contains($in, 'orzo')) $mappedImg = 'soup.png';
                            elseif(str_contains($in, 'fattah') || str_contains($in, 'mansaf') || str_contains($in, 'kabsa') || str_contains($in, 'roz')) $mappedImg = 'traditional_rice.png';
                            elseif(str_contains($in, 'juice') || str_contains($in, 'mango') || str_contains($in, 'sahlab') || str_contains($in, 'coffee') || str_contains($in, 'drink')) $mappedImg = 'drinks.png';
                            elseif(str_contains($in, 'wedding') || str_contains($in, 'corporate') || str_contains($in, 'package')) $mappedImg = 'packages.png';
                            
                            $itemImg = url('upload/website_assets/'.$mappedImg);
                        }
                    @endphp
                    <img src="{{ $itemImg }}" style="width:100%;height:100%;object-fit:cover;transition: transform 0.5s ease;" class="hover-zoom">
                    <span style="position:absolute;top:10px;left:10px;background:rgba(255,107,53,0.85);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,0.2);color:#fff;padding:4px 14px;border-radius:20px;font-size:0.75rem;font-weight:700">{{ $item->CatName ?? 'Food' }}</span>
                </div>
                <div class="card-body">
                    <h3 class="card-title">{{ $item->ItemName }}</h3>
                    <p class="card-text">{{ Str::limit($item->Description ?? 'Freshly prepared homemade dish.', 65) }}</p>
                    <div class="flex-between" style="margin-top:auto">
                        @if($item->DiscountPrice)
                            <span>
                                <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.85rem;margin-right:6px">{{ number_format($item->ItemPrice, 2) }}</span>
                                <span class="menu-price text-success">{{ number_format($item->DiscountPrice, 2) }}<small> EGP</small></span>
                            </span>
                        @else
                            <span class="menu-price">{{ number_format($item->ItemPrice, 2) }}<small> EGP</small></span>
                        @endif
                        <button class="btn btn-primary btn-sm" onclick="addToCart({{ $item->MenuItemID }},'{{ addslashes($item->ItemName) }}',{{ $item->DiscountPrice ?: $item->ItemPrice }},'{{ $itemImg ?? '' }}',{{ $item->KitchenOwnerID ?? 'null' }},{{ $item->CatererID ?? 'null' }})" style="border-radius:50%;width:38px;height:38px;padding:0;justify-content:center">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- ═══════════════ CTA ═══════════════ -->
<section class="section">
    <div class="container">
        <div class="glass-card text-center reveal" style="padding:72px 40px;background:linear-gradient(160deg,rgba(255,107,53,0.07) 0%,rgba(255,167,38,0.03) 100%);border-color:rgba(255,107,53,0.2)">
            <div style="width:72px;height:72px;border-radius:24px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;margin:0 auto 24px;font-size:2rem;box-shadow:0 8px 30px rgba(255,107,53,0.4)">🏪</div>
            <h2 style="letter-spacing:-0.5px;margin-bottom:16px">Ready to Start Your Business?</h2>
            <p style="color:var(--text-secondary);max-width:520px;margin:0 auto 36px;font-size:1.05rem;line-height:1.7">Join hundreds of home cooks and caterers. Reach thousands of customers and grow your food business with BiteHub.</p>
            <div style="display:flex;gap:16px;justify-content:center;flex-wrap:wrap">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg"><i class="fas fa-store"></i> Register Kitchen</a>
                <a href="{{ route('register') }}" class="btn btn-outline btn-lg"><i class="fas fa-concierge-bell" style="margin-right:8px"></i>Become a Caterer</a>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
// Ad Carousel
var adStates = {
    kitchen: { current: 0, slides: document.querySelectorAll('.kitchen-ad-slide') },
    caterer: { current: 0, slides: document.querySelectorAll('.caterer-ad-slide') }
};

function showAd(idx, type) {
    var state = adStates[type];
    if (!state || state.slides.length === 0) return;
    
    // Normalize index
    if (idx < 0) idx = state.slides.length - 1;
    if (idx >= state.slides.length) idx = 0;
    
    state.slides.forEach(function(s, i) {
        s.style.display = i === idx ? 'block' : 'none';
        var dot = document.getElementById(type + 'AdDot-' + i);
        if (dot) dot.style.background = i === idx ? 'var(--primary)' : 'rgba(255,255,255,0.2)';
    });
    state.current = idx;
}

function moveAd(step, type) {
    var state = adStates[type];
    if (!state) return;
    showAd(state.current + step, type);
}

// Auto rotate
Object.keys(adStates).forEach(function(type) {
    var state = adStates[type];
    if (state.slides.length > 0) {
        showAd(0, type); // Initialize first slide
        if (state.slides.length > 1) {
            setInterval(function() { moveAd(1, type); }, 5000);
        }
    }
});

// Particles
if (typeof createParticles === 'function') createParticles('#hero-particles', 30);
// Counter animation for stats
document.addEventListener('DOMContentLoaded', function() {
    const stats = document.querySelectorAll('.hero-stat h3');
    stats.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(20px)';
        el.style.transition = 'all 0.6s ease';
    });
    setTimeout(() => {
        stats.forEach((el, i) => {
            setTimeout(() => {
                el.style.opacity = '1';
                el.style.transform = 'none';
            }, i * 150);
        });
    }, 800);

    // ── Order Again Slider ──────────────────────────────────────────────────
    const oaTrack = document.getElementById('oaTrack');
    if (oaTrack) {
        const CARD_W = 320 + 22; // card width + gap

        // Arrow navigation
        window.oaMove = function(dir) {
            oaTrack.scrollBy({ left: dir * CARD_W, behavior: 'smooth' });
        };

        // Drag to scroll (mouse)
        let isDragging = false, startX = 0, scrollStart = 0;
        oaTrack.addEventListener('mousedown', e => {
            isDragging = true;
            startX     = e.pageX - oaTrack.offsetLeft;
            scrollStart = oaTrack.scrollLeft;
            oaTrack.classList.add('is-dragging');
        });
        document.addEventListener('mouseup', () => {
            isDragging = false;
            oaTrack.classList.remove('is-dragging');
        });
        oaTrack.addEventListener('mousemove', e => {
            if (!isDragging) return;
            e.preventDefault();
            const x    = e.pageX - oaTrack.offsetLeft;
            const walk = (x - startX) * 1.4;
            oaTrack.scrollLeft = scrollStart - walk;
        });
        oaTrack.addEventListener('mouseleave', () => {
            isDragging = false;
            oaTrack.classList.remove('is-dragging');
        });

        // Touch swipe
        let touchStartX = 0;
        oaTrack.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, { passive: true });
        oaTrack.addEventListener('touchend',   e => {
            const diff = touchStartX - e.changedTouches[0].clientX;
            if (Math.abs(diff) > 40) oaMove(diff > 0 ? 1 : -1);
        });

        // Prevent form submission drag confusion
        oaTrack.querySelectorAll('form').forEach(f => {
            f.addEventListener('mousedown', e => e.stopPropagation());
        });
    }
});

// Reorder: inject items directly into localStorage cart, then go to /cart
function oaReorder(btn) {
    // Show loading state
    const spin = btn.querySelector('.spinner');
    const icon = btn.querySelector('.fas.fa-rotate-right');
    btn.disabled = true;
    btn.style.opacity = '0.8';
    if (spin) spin.style.display = 'inline-block';
    if (icon) icon.style.display = 'none';

    // Parse items embedded in the button
    let newItems = [];
    try { newItems = JSON.parse(btn.dataset.items || '[]'); } catch(e) {}

    if (!newItems.length) {
        showToast('No items found for this order.', 'error');
        btn.disabled = false; btn.style.opacity = '1';
        if (spin) spin.style.display = 'none';
        if (icon) icon.style.display = '';
        return;
    }

    // Load current cart, merge items
    let cart = JSON.parse(localStorage.getItem('bitehub_cart') || '[]');

    newItems.forEach(function(ni) {
        // Each item is added as its own quantity (matching original order qty)
        const existIdx = cart.findIndex(c => c.id === ni.id && (c.note || '') === '');
        if (existIdx !== -1) {
            cart[existIdx].qty += ni.qty;
        } else {
            cart.push({
                id    : ni.id,
                name  : ni.name,
                price : parseFloat(ni.price),
                image : ni.image,
                qty   : parseInt(ni.qty) || 1,
                note  : ''
            });
        }
    });

    // Save & update badge
    localStorage.setItem('bitehub_cart', JSON.stringify(cart));
    if (typeof updateCartBadge === 'function') updateCartBadge();

    // Brief feedback then navigate to cart
    if (typeof showToast === 'function') {
        showToast('Items added to cart! Redirecting…', 'success');
    }
    setTimeout(() => { window.location.href = '/cart'; }, 600);
}
</script>

<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const userId = "{{ auth()->id() ?? 'guest' }}";
        const hasSeenTour = localStorage.getItem('bitehub_tour_home_' + userId);
        
        if (!hasSeenTour) {
            const driver = window.driver.js.driver;
            const driverObj = driver({
                showProgress: true,
                steps: [
                    { element: '.hero-actions', popover: { title: 'Welcome to BiteHub!', description: 'Start your journey here by exploring kitchens or joining as a partner.', side: "bottom", align: 'start' }},
                    { element: '.category-slider', popover: { title: 'Discover Cuisines', description: 'Filter our active items quickly by scrolling through categories.', side: "top", align: 'start' }},
                    { element: '.popular-menu', popover: { title: 'Popular Menu', description: 'Check out the most highly-rated homemade dishes around you.', side: "top", align: 'start' }}
                ],
                onDestroyStarted: () => {
                    localStorage.setItem('bitehub_tour_home_' + userId, 'true');
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
