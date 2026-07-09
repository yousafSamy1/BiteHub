@extends('frontend.layouts.app')
@section('title', $caterer->FullName)
@section('nav-browse', 'active')

@section('content')
    @php $rating = $caterer->average_rating;
    $reviews = $caterer->review_count; @endphp
    @php
        $kImg = 'default_k.png';
        $kn = strtolower($caterer->FullName);
        if (str_contains($kn, 'mama'))
            $kImg = 'mama.png';
        elseif (str_contains($kn, 'rania'))
            $kImg = 'rania.png';
        elseif (str_contains($kn, 'amira'))
            $kImg = 'hero.png';
        elseif (str_contains($kn, 'fatma'))
            $kImg = 'upper_egypt.png';
        elseif (str_contains($kn, 'nour'))
            $kImg = 'mediterranean.png';
        elseif (str_contains($kn, 'heba') || str_contains($kn, 'healthy'))
            $kImg = 'healthy.png';
        elseif (str_contains($kn, 'samira') || str_contains($kn, 'seafood') || str_contains($kn, 'alex'))
            $kImg = 'seafood.png';
    @endphp

    <!-- Caterer Profile Header -->
    <div class="container" style="padding-top:calc(var(--nav-h) + 40px); margin-bottom: 20px;">
        <style>
            @media(max-width: 768px) {
                .caterer-header {
                    flex-direction: column !important;
                    text-align: center !important;
                    padding: 30px 20px !important;
                    gap: 20px !important;
                }

                .caterer-header img {
                    width: 100px !important;
                    height: 100px !important;
                }

                .caterer-header>div:last-child {
                    justify-content: center !important;
                    width: 100%;
                }

                .caterer-header .btn {
                    width: 100% !important;
                    justify-content: center !important;
                }
            }
        </style>
        <div class="glass-card reveal caterer-header"
            style="padding:40px 32px; display:flex; align-items:center; gap:32px; flex-wrap:wrap; position:relative; overflow:hidden;">
            <!-- Subtle Glow instead of noisy blurred image -->
            <div
                style="position:absolute;inset:0;background:radial-gradient(ellipse at top right, rgba(255,107,53,0.08) 0%, transparent 60%);pointer-events:none;z-index:0">
            </div>

            <div style="position:relative;flex-shrink:0;z-index:1">
                @php
                    $kRawImg = $caterer->photo ?? $caterer->Image ?? null;
                    $catererProfileImg = (!empty($kRawImg) && !str_contains($kRawImg, 'no_image') && file_exists(public_path('upload/admin_images/' . $kRawImg))) ? asset('upload/admin_images/' . $kRawImg) : asset('upload/website_assets/' . $kImg);
                @endphp
                <img src="{{ $catererProfileImg }}"
                    style="width:120px;height:120px;border-radius:50%;border:4px solid var(--primary);object-fit:cover;box-shadow:0 8px 32px rgba(255,107,53,0.3)"
                    alt="{{ $caterer->FullName }}">
                @if($caterer->VerifyStatus === 'Verified')
                    <span
                        style="position:absolute;bottom:4px;right:4px;width:30px;height:30px;background:var(--success);border-radius:50%;display:flex;align-items:center;justify-content:center;border:3px solid var(--bg-dark);box-shadow:0 4px 10px rgba(0,0,0,0.2)"><i
                            class="fas fa-check" style="color:#fff;font-size:0.75rem"></i></span>
                @endif
            </div>
            <div style="flex:1;z-index:1">
                <h1
                    style="font-size:2.2rem;margin-bottom:8px;font-weight:900;letter-spacing:-0.5px;color:var(--text-primary)">
                    {{ $caterer->FullName }}</h1>
                <div style="display:flex;gap:20px;align-items:center;flex-wrap:wrap">
                    <div class="caterer-rating"
                        style="background:rgba(255,215,0,0.1);padding:4px 12px;border-radius:20px;color:#FFD700;font-weight:700;font-size:0.9rem">
                        <i class="fas fa-star"></i> {{ $rating }} <span
                            style="color:var(--text-muted);font-weight:400;margin-left:4px">({{ $reviews }} reviews)</span>
                    </div>
                    <span style="color:var(--text-secondary);font-size:0.95rem;display:flex;align-items:center;gap:6px"><i
                            class="fas fa-location-dot" style="color:var(--primary)"></i>
                        {{ $caterer->Location ?? 'Egypt' }}</span>
                    <span style="color:var(--text-secondary);font-size:0.95rem;display:flex;align-items:center;gap:6px"><i
                            class="fas fa-clock" style="color:var(--accent)"></i>
                        @if($caterer->OpeningTime && $caterer->ClosingTime)
                            {{ \Carbon\Carbon::parse($caterer->OpeningTime)->format('g:i A') }} –
                            {{ \Carbon\Carbon::parse($caterer->ClosingTime)->format('g:i A') }}
                        @else
                            9:00 AM – 10:00 PM
                        @endif
                    </span>
                    @php $cStatus = $caterer->current_status; @endphp
                    <span class="badge-status badge-{{ strtolower($cStatus) }}">{{ $cStatus }}</span>
                    @if($caterer->VerifyStatus === 'Verified')
                        <span
                            style="background:rgba(74,222,128,0.15);color:var(--success);padding:4px 14px;border-radius:20px;font-size:0.8rem;font-weight:700;display:flex;align-items:center;gap:6px"><i
                                class="fas fa-shield-halved"></i> Verified Caterer</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap;z-index:1">
                @if($cStatus === 'Closed')
                    <button class="btn btn-outline"
                        style="padding:10px 24px;border-radius:30px;font-weight:600;opacity:0.6;cursor:not-allowed;filter:grayscale(1)"
                        disabled><i class="fas fa-magic"></i> Request Special Service</button>
                @else
                    <button class="btn btn-primary"
                        onclick="openMessengerChat(0, 'Special Service Request', 0, {{ $caterer->CatererID }}, null, 'caterer')"
                        style="padding:10px 24px;border-radius:30px;font-weight:600;background:var(--accent);border-color:var(--accent)"><i
                            class="fas fa-magic"></i> Request Special Service</button>
                @endif
                <a href="{{ route('frontend.cart') }}" class="btn btn-primary"
                    style="padding:10px 24px;border-radius:30px;font-weight:600"><i class="fas fa-shopping-bag"></i> My
                    Cart</a>
                <a href="{{ route('frontend.browse') }}" class="btn btn-outline"
                    style="padding:10px 24px;border-radius:30px;font-weight:600"><i class="fas fa-arrow-left"></i> All
                    Caterers</a>
            </div>
        </div>
    </div>

    <section class="section" style="padding-top:0">
        <div class="container">

            <!-- About -->
            <div class="glass-card mb-3 reveal" style="padding:28px">
                <h3 style="margin-bottom:12px;display:flex;align-items:center;gap:10px">
                    <span
                        style="width:36px;height:36px;border-radius:12px;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i
                            class="fas fa-info" style="color:#fff"></i></span>
                    About This Caterer
                </h3>
                <p style="color:var(--text-secondary);line-height:1.75">
                    {{ $caterer->Description ?? 'Delicious homemade food crafted with love, fresh ingredients, and authentic recipes passed down through generations.' }}
                </p>
            </div>

            <!-- Menu -->
            <div class="section-header reveal" style="text-align:left;margin-bottom:28px">
                <h2 style="font-size:1.6rem;display:flex;align-items:center;gap:12px">
                    <span
                        style="width:42px;height:42px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--accent));display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem"><i
                            class="fas fa-utensils" style="color:#fff"></i></span>
                    Menu
                    <span style="font-size:1rem;color:var(--text-muted);font-weight:400">({{ count($menuItems) }}
                        items)</span>
                </h2>
            </div>

            @if($menuItems->isEmpty())
                <div class="glass-card text-center reveal" style="padding:60px">
                    <div style="font-size:4rem;margin-bottom:16px">🍽️</div>
                    <h3>No menu items yet</h3>
                    <p style="color:var(--text-muted);margin-top:8px">This Caterer hasn't added any items yet. Check back soon!
                    </p>
                </div>
            @else
                    @php
                        $uniqueCategories = collect($menuItems)->pluck('CatName')->filter()->unique();
                        $foodEmojis = ['🍕', '🍜', '🥗', '🍗', '🥘', '🍱', '🥙', '🍛', '🍔', '🥪']; 
                    @endphp

                    @if($uniqueCategories->isNotEmpty())
                        <!-- Categories Slider (Client-Side HTML) -->
                        <div style="margin-bottom: 30px; position:relative; display:flex; align-items:center; gap:10px;">
                            <button class="btn btn-outline"
                                style="background:var(--bg-card2); border-radius:50%; width:40px; height:40px; padding:0; flex-shrink:0; border-color:var(--border-color); color:var(--text-primary); display:flex; align-items:center; justify-content:center;"
                                onclick="document.getElementById('catSliderCaterer').scrollBy({left:-200, behavior:'smooth'})"><i
                                    class="fas fa-chevron-left"></i></button>

                            <div id="catSliderCaterer"
                                style="display:flex; gap:10px; overflow-x:auto; padding-bottom:4px; padding-top:4px; scrollbar-width:none; -ms-overflow-style:none; flex:1; scroll-behavior:smooth;"
                                class="cat-slider reveal">
                                <button onclick="filterByCategory('all', this)"
                                    style="cursor:pointer; padding:8px 20px; border-radius:30px; border:1px solid var(--primary); background:rgba(255,107,53,0.15); color:var(--primary); white-space:nowrap; font-weight:700; transition:all 0.3s ease; display:flex; align-items:center; gap:8px;"
                                    class="cat-btn active">
                                    <span style="font-size:1.1rem">🍱</span> All
                                </button>
                                @foreach($uniqueCategories as $catName)
                                    @php
                                        $icon = '🍽️';
                                        if (stripos($catName, 'Meal') !== false)
                                            $icon = '🍲';
                                        if (stripos($catName, 'Dessert') !== false || stripos($catName, 'Sweet') !== false)
                                            $icon = '🍰';
                                        if (stripos($catName, 'Drink') !== false || stripos($catName, 'Beverage') !== false)
                                            $icon = '🍹';
                                        if (stripos($catName, 'Pizza') !== false)
                                            $icon = '🍕';
                                        if (stripos($catName, 'Burger') !== false || stripos($catName, 'Sandwich') !== false)
                                            $icon = '🍔';
                                        if (stripos($catName, 'Breakfast') !== false)
                                            $icon = '🥞';
                                        if (stripos($catName, 'Seafood') !== false || stripos($catName, 'Fish') !== false)
                                            $icon = '🦐';
                                        if (stripos($catName, 'Appetizer') !== false || stripos($catName, 'Salad') !== false)
                                            $icon = '🥗';
                                        if (stripos($catName, 'Vegan') !== false || stripos($catName, 'Healthy') !== false)
                                            $icon = '🥑';
                                        if (stripos($catName, 'BBQ') !== false || stripos($catName, 'Grill') !== false)
                                            $icon = '🍖';
                                        if (stripos($catName, 'Pasta') !== false)
                                            $icon = '🍝';
                                        if (stripos($catName, 'Chicken') !== false)
                                            $icon = '🍗';
                                        if (stripos($catName, 'Bakery') !== false)
                                            $icon = '🥐';
                                    @endphp
                                    <button onclick="filterByCategory('{{ $catName }}', this)"
                                        style="cursor:pointer; padding:8px 20px; border-radius:30px; border:1px solid var(--border-color); background:var(--bg-card2); color:var(--text-primary); white-space:nowrap; font-weight:700; transition:all 0.3s ease; display:flex; align-items:center; gap:8px;"
                                        class="cat-btn">
                                        <span style="font-size:1.1rem">{{ $icon }}</span> {{ $catName }}
                                    </button>
                                @endforeach
                            </div>

                            <button class="btn btn-outline"
                                style="background:var(--bg-card2); border-radius:50%; width:40px; height:40px; padding:0; flex-shrink:0; border-color:var(--border-color); color:var(--text-primary); display:flex; align-items:center; justify-content:center;"
                                onclick="document.getElementById('catSliderCaterer').scrollBy({left:200, behavior:'smooth'})"><i
                                    class="fas fa-chevron-right"></i></button>
                        </div>
                    @endif

                    <div class="grid grid-4 menu-grid">
                        @foreach($menuItems as $idx => $item)
                                <div class="card menu-card reveal">
                                    @php
                                        $itemImg = null;
                                        // 1. Check if item has uploaded images
                                        if ($item->images->count() > 0) {
                                            $dbImg = $item->images->first()->Image;
                                            $itemImg = str_starts_with($dbImg, 'http') ? $dbImg : url('upload/item_images/' . $dbImg);
                                        } else {
                                            // 2. Comprehensive Thematic fallback
                                            $mappedImg = 'grills.png'; // Grill/Meat Default
                                            $in = strtolower($item->ItemName);

                                            if (str_contains($in, 'koshari'))
                                                $mappedImg = 'koshari.png';
                                            elseif (str_contains($in, 'mahshi') || str_contains($in, 'waraq') || str_contains($in, 'stuffed'))
                                                $mappedImg = 'mahshi.png';
                                            elseif (str_contains($in, 'foul') || str_contains($in, 'falafel') || str_contains($in, 'breakfast'))
                                                $mappedImg = 'foul_falafel.png';
                                            elseif (str_contains($in, 'pasta') || str_contains($in, 'macarona') || str_contains($in, 'bechamel') || str_contains($in, 'lasagna'))
                                                $mappedImg = 'pasta.png';
                                            elseif (str_contains($in, 'molokhia') || str_contains($in, 'green') || str_contains($in, 'salad') || str_contains($in, 'keto') || str_contains($in, 'tayebat') || str_contains($in, 'healthy') || str_contains($in, 'acai'))
                                                $mappedImg = 'healthy.png'; // Green/Healthy focus
                                            elseif (str_contains($in, 'fish') || str_contains($in, 'shrimp') || str_contains($in, 'seafood') || str_contains($in, 'sayadeya') || str_contains($in, 'calamari'))
                                                $mappedImg = 'seafood.png';
                                            elseif (str_contains($in, 'dessert') || str_contains($in, 'sweet') || str_contains($in, 'baklava') || str_contains($in, 'kunafa') || str_contains($in, 'qatayef') || str_contains($in, 'basbousa') || str_contains($in, 'ali') || str_contains($in, 'cake') || str_contains($in, 'laban'))
                                                $mappedImg = 'sweets.png';
                                            elseif (str_contains($in, 'soup') || str_contains($in, 'lentil') || str_contains($in, 'orzo') || str_contains($in, 'لسان'))
                                                $mappedImg = 'soup.png';
                                            elseif (str_contains($in, 'fattah') || str_contains($in, 'mansaf') || str_contains($in, 'kabsa') || str_contains($in, 'roz'))
                                                $mappedImg = 'traditional_rice.png';
                                            elseif (str_contains($in, 'juice') || str_contains($in, 'mango') || str_contains($in, 'sahlab') || str_contains($in, 'coffee') || str_contains($in, 'drink') || str_contains($in, 'mojito'))
                                                $mappedImg = 'drinks.png';
                                            elseif (str_contains($in, 'wedding') || str_contains($in, 'corporate') || str_contains($in, 'package') || str_contains($in, 'party'))
                                                $mappedImg = 'packages.png';

                                            $itemImg = url('upload/website_assets/' . $mappedImg);
                                        }
                                    @endphp
                                    <div class="card-img"
                                        style="background:linear-gradient(135deg,rgba(255,107,53,0.1),rgba(255,167,38,0.05));position:relative;overflow:hidden;height:180px">
                                        <img src="{{ $itemImg }}"
                                            style="width:100%;height:100%;object-fit:cover;transition:transform 0.4s ease"
                                            class="hover-zoom">
                                        @if($item->CatName)
                                            <span
                                                style="position:absolute;top:10px;left:10px;background:rgba(255,107,53,0.85);backdrop-filter:blur(4px);border:1px solid rgba(255,255,255,0.2);color:#fff;padding:4px 14px;border-radius:20px;font-size:0.75rem;font-weight:700">{{ $item->CatName }}</span>
                                        @endif
                                        <div
                                            style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(0,0,0,0.8)); padding:10px 15px; text-align:left">
                                            <span
                                                style="font-size:0.75rem; color:rgba(255,255,255,0.9); font-weight:600; display:inline-flex; align-items:center; gap:6px;"><i
                                                    class="fas fa-store" style="color:var(--primary);"></i> {{ $caterer->FullName }}</span>
                                        </div>
                                    </div>
                                    <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:12px">
                                        <h3 class="card-title" style="font-size:1.1rem; font-weight:800; color:var(--text-primary)">
                                            {{ $item->ItemName }}</h3>
                                        <p class="card-text text-muted" style="font-size:0.85rem; line-height:1.5; margin:0">
                                            {{ Str::limit($item->Description ?? 'No description available.', 65) }}</p>

                                        <div class="flex-between" style="margin-top:8px">
                                            <div>
                                                @if($item->DiscountPrice)
                                                    <span
                                                        style="text-decoration:line-through;color:var(--text-muted);font-size:0.8rem;display:block;line-height:1">{{ number_format($item->ItemPrice, 0) }}
                                                        EGP</span>
                                                    <span
                                                        style="font-size:1.3rem; font-weight:800; color:var(--success)">{{ number_format($item->DiscountPrice, 0) }}<small
                                                            style="font-size:0.7rem; opacity:0.8; margin-left:4px">EGP</small></span>
                                                @else
                                                    <span
                                                        style="font-size:1.3rem; font-weight:800; color:var(--primary)">{{ number_format($item->ItemPrice, 0) }}<small
                                                            style="font-size:0.7rem; opacity:0.8; margin-left:4px">EGP</small></span>
                                                @endif
                                            </div>
                                        </div>
                                        <div style="display:flex; align-items:center; gap:6px; margin-top:10px;">
                                            <button class="btn btn-outline btn-sm"
                                                onclick='showItemDetails(@json($item), "{{ $itemImg }}", "{{ $cStatus }}")'
                                                style="height:36px; border-radius:10px; padding:0 12px; font-size:0.75rem; border:1px solid var(--primary); color:var(--primary); font-weight:700; display:flex; align-items:center; gap:4px; transition:all 0.3s ease; background:transparent;"
                                                onmouseover="this.style.background='rgba(255,107,53,0.05)'"
                                                onmouseout="this.style.background='transparent'">
                                                <i class="fas fa-eye"></i> Details
                                            </button>

                                            @if($cStatus === 'Closed')
                                                <button class="btn btn-outline btn-sm"
                                                    style="width:36px; height:36px; padding:0; border-radius:10px; opacity:0.4; cursor:not-allowed; display:flex; align-items:center; justify-content:center; border-color:var(--border-color); color:var(--text-muted)"
                                                    disabled>
                                                    <i class="fas fa-pen-nib" style="font-size:0.8rem"></i>
                                                </button>
                                                <button class="btn btn-outline btn-sm"
                                                    style="height:36px; border-radius:10px; padding:0 10px; font-size:0.75rem; font-weight:700; opacity:0.5; cursor:not-allowed; border-color:var(--border-color); color:var(--text-muted); display:flex; align-items:center; gap:4px"
                                                    disabled>
                                                    <i class="fas fa-lock"></i> Closed
                                                </button>
                                            @else
                                                <button class="btn btn-outline btn-sm"
                                                    onclick="openMessengerChat({{ $item->MenuItemID }}, '{{ addslashes($item->ItemName) }}', {{ $item->DiscountPrice ?: $item->ItemPrice }}, '{{ $item->CatererID ?? 0 }}')"
                                                    style="width:36px; height:36px; padding:0; border-radius:10px; border:1px solid var(--primary); color:var(--primary); display:flex; align-items:center; justify-content:center; transition:all 0.3s ease; background:transparent;"
                                                    onmouseover="this.style.background='rgba(255,107,53,0.05)'"
                                                    onmouseout="this.style.background='transparent'" title="Customize Request">
                                                    <i class="fas fa-pen-nib" style="font-size:0.8rem"></i>
                                                </button>
                                                <button class="btn btn-primary btn-sm"
                                                    onclick="addToCart({{ $item->MenuItemID }},'{{ addslashes($item->ItemName) }}',{{ $item->DiscountPrice ?: $item->ItemPrice }},'{{ $itemImg ?? '' }}',{{ $item->KitchenOwnerID ?? 'null' }},{{ $item->CatererID ?? 'null' }})"
                                                    style="height:36px; border-radius:10px; padding:0 12px; font-weight:700; font-size:0.8rem; display:flex; align-items:center; gap:6px; flex:1; justify-content:center; background:var(--primary); border:none; box-shadow: 0 4px 10px rgba(255,107,53,0.2)">
                                                    <i class="fas fa-plus"></i> Add
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                </div>
            @endif

        </div>
    </section>
@endsection

{{-- Premium Item Detail Modal --}}
<div id="itemDetailModal" class="modal-overlay" style="display:none;">
    <div class="modal-glass-container reveal">
        <button class="modal-close" onclick="closeItemDetails()"><i class="fas fa-times"></i></button>

        <div class="modal-layout">
            <div class="modal-image-side">
                <img id="modalItemImg" src="" alt="">
                <span id="modalCatBadge" class="cat-badge-float"></span>
            </div>

            <div class="modal-info-side">
                <h2 id="modalItemName" class="modal-title-text"></h2>

                <div class="modal-stats">
                    <div class="stat-item" id="statPrep">
                        <i class="fas fa-clock"></i>
                        <span id="modalPrepTime">--</span> min
                    </div>
                    <div class="stat-item" id="statCalories">
                        <i class="fas fa-fire"></i>
                        <span id="modalCalories">--</span> kcal
                    </div>
                    <div class="stat-item" id="statPortion">
                        <i class="fas fa-balance-scale"></i>
                        <span id="modalPortion">--</span>
                    </div>
                </div>

                <div class="modal-section">
                    <h3><i class="fas fa-align-left"></i> Description</h3>
                    <p id="modalDescription" class="modal-text"></p>
                </div>

                <div class="modal-section" id="sectionIngredients">
                    <h3><i class="fas fa-leaf"></i> Ingredients</h3>
                    <p id="modalIngredients" class="modal-text"></p>
                </div>

                <div class="modal-footer-action mt-auto">
                    <div class="price-display">
                        <div id="modalOldPrice" class="old-price"></div>
                        <div id="modalCurrentPrice" class="current-price"></div>
                    </div>

                    <div class="action-row">
                        <div class="qty-selector">
                            <button onclick="updateModalQty(-1)">-</button>
                            <input type="number" id="modalQty" value="1" min="1" readonly>
                            <button onclick="updateModalQty(1)">+</button>
                        </div>
                        <button id="modalAddToCartBtn" class="btn btn-primary modal-buy-btn">
                            <i class="fas fa-cart-plus"></i> Add to Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        animation: fadeIn 0.3s ease;
    }

    .modal-glass-container {
        background: #1a1a1a;
        border: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        border-radius: 32px;
        width: 95%;
        max-width: 950px;
        max-height: 90vh;
        overflow: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    .modal-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: white;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .modal-close:hover {
        background: var(--primary);
        transform: rotate(90deg);
    }

    .modal-layout {
        display: flex;
        height: 100%;
        min-height: 500px;
    }

    .modal-image-side {
        flex: 1.1;
        position: relative;
        background: #000;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .modal-image-side img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .cat-badge-float {
        position: absolute;
        top: 20px;
        left: 20px;
        background: var(--primary);
        color: white;
        padding: 6px 18px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 0.8rem;
        box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        z-index: 2;
    }

    .modal-info-side {
        flex: 1;
        padding: 45px;
        display: flex;
        flex-direction: column;
        overflow-y: auto;
        background: #0f0f0f;
        color: #fff;
    }

    .modal-title-text {
        font-size: 2.4rem;
        font-weight: 900;
        margin-bottom: 24px;
        color: #fff;
        letter-spacing: -1px;
    }

    .modal-stats {
        display: flex;
        gap: 12px;
        margin-bottom: 30px;
        flex-wrap: wrap;
    }

    .stat-item {
        background: rgba(255, 255, 255, 0.05);
        padding: 10px 16px;
        border-radius: 16px;
        font-size: 0.85rem;
        font-weight: 600;
        color: #ccc;
        display: flex;
        align-items: center;
        gap: 8px;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .stat-item i {
        color: var(--primary);
    }

    .modal-section {
        margin-bottom: 30px;
    }

    .modal-section h3 {
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 12px;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 800;
    }

    .modal-text {
        color: #aaa;
        line-height: 1.7;
        font-size: 1rem;
    }

    .modal-footer-action {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        padding-top: 30px;
        margin-top: auto;
    }

    .price-display {
        margin-bottom: 25px;
    }

    .old-price {
        text-decoration: line-through;
        color: #666;
        font-size: 1.1rem;
        margin-bottom: 4px;
        display: block;
    }

    .current-price {
        font-size: 2.8rem;
        font-weight: 900;
        color: #fff;
        line-height: 1;
    }

    .current-price small {
        font-size: 1rem;
        color: var(--primary);
        text-transform: uppercase;
        margin-left: 5px;
    }

    .action-row {
        display: flex;
        gap: 20px;
        align-items: center;
    }

    .qty-selector {
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 18px;
        padding: 6px;
        border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .qty-selector button {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: none;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        font-size: 1.2rem;
        cursor: pointer;
        transition: 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qty-selector button:hover {
        background: var(--primary);
    }

    .qty-selector input {
        width: 50px;
        text-align: center;
        background: transparent;
        border: none;
        color: white !important;
        font-weight: 800;
        font-size: 1.2rem;
        outline: none;
    }

    .modal-buy-btn {
        flex: 1;
        height: 50px;
        border-radius: 18px;
        font-weight: 800;
        font-size: 1.1rem;
        box-shadow: 0 10px 20px -5px rgba(255, 107, 53, 0.4);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @media (max-width: 850px) {
        .modal-layout {
            flex-direction: column;
        }

        .modal-image-side {
            height: 250px;
            flex: none;
        }

        .modal-info-side {
            padding: 30px;
        }

        .modal-title-text {
            font-size: 1.8rem;
        }
    }
</style>

<script>
    let currentModalItem = null;
    let currentModalImg = '';

    function showItemDetails(item, imgUrl, providerStatus) {
        currentModalItem = item;
        currentModalImg = imgUrl;

        document.getElementById('modalItemImg').src = imgUrl;
        document.getElementById('modalCatBadge').textContent = item.CatName || 'Meal';
        document.getElementById('modalItemName').textContent = item.ItemName;
        document.getElementById('modalDescription').textContent = item.Description || 'No description available.';

        // Ingredients
        if (item.Ingredients) {
            document.getElementById('sectionIngredients').style.display = 'block';
            document.getElementById('modalIngredients').textContent = item.Ingredients;
        } else {
            document.getElementById('sectionIngredients').style.display = 'none';
        }

        // Stats
        document.getElementById('statPrep').style.display = item.PrepTime ? 'flex' : 'none';
        document.getElementById('modalPrepTime').textContent = item.PrepTime || '--';

        document.getElementById('statCalories').style.display = item.Calories ? 'flex' : 'none';
        document.getElementById('modalCalories').textContent = item.Calories || '--';

        document.getElementById('statPortion').style.display = item.PortionSize ? 'flex' : 'none';
        document.getElementById('modalPortion').textContent = item.PortionSize || '--';

        // Price
        if (item.DiscountPrice) {
            document.getElementById('modalOldPrice').textContent = Number(item.ItemPrice).toFixed(2) + ' EGP';
            document.getElementById('modalOldPrice').style.display = 'block';
            document.getElementById('modalCurrentPrice').innerHTML = Number(item.DiscountPrice).toFixed(2) + ' <small>EGP</small>';
        } else {
            document.getElementById('modalOldPrice').style.display = 'none';
            document.getElementById('modalCurrentPrice').innerHTML = Number(item.ItemPrice).toFixed(2) + ' <small>EGP</small>';
        }

        // Action Button
        const btn = document.getElementById('modalAddToCartBtn');
        if (providerStatus === 'Closed') {
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-times"></i> Closed';
            btn.style.opacity = '0.5';
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
            btn.style.opacity = '1';
            btn.onclick = function () {
                const qty = parseInt(document.getElementById('modalQty').value);
                for (let i = 0; i < qty; i++) {
                    addToCart(item.MenuItemID, item.ItemName, item.DiscountPrice || item.ItemPrice, imgUrl, item.KitchenOwnerID, item.CatererID);
                }
                closeItemDetails();
            };
        }

        document.getElementById('modalQty').value = 1;
        document.getElementById('itemDetailModal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }

    function closeItemDetails() {
        document.getElementById('itemDetailModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function updateModalQty(delta) {
        const input = document.getElementById('modalQty');
        let val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        input.value = val;
    }

    // Close on outside click
    window.onclick = function (event) {
        const modal = document.getElementById('itemDetailModal');
        if (event.target == modal) {
            closeItemDetails();
        }
    }
</script>
<script>
    function filterByCategory(cat, btn) {
        // Buttons
        document.querySelectorAll('.cat-btn').forEach(b => {
            b.classList.remove('active');
            b.style.background = 'var(--bg-card2)';
            b.style.borderColor = 'var(--border-color)';
            b.style.color = 'var(--text-primary)';
        });
        btn.classList.add('active');
        btn.style.background = 'rgba(255,107,53,0.15)';
        btn.style.borderColor = 'var(--primary)';
        btn.style.color = 'var(--primary)';

        // Cards
        document.querySelectorAll('.menu-grid .menu-card').forEach(card => {
            if (cat === 'all') {
                card.style.display = 'block';
            } else {
                const cardCat = card.querySelector('.cat-badge')?.textContent.trim();
                card.style.display = (cardCat === cat) ? 'block' : 'none';
            }
        });
    }
</script>