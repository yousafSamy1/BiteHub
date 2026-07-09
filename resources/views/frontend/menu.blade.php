@extends('frontend.layouts.app')
@section('title', 'Menu')
@section('nav-menu', 'active')

@section('content')
<section class="section" style="padding-top:120px; position:relative; z-index:10">
<div class="container">

    <form method="GET" action="{{ route('frontend.menu') }}" class="reveal" style="margin-bottom:30px;">
        <input type="hidden" name="cat" value="{{ request('cat') }}">
        
        <div class="glass-card responsive-filter-grid" style="padding:10px; border-radius:24px; border:1px solid var(--border-color); backdrop-filter:blur(20px); box-shadow:0 25px 50px -12px rgba(0,0,0,0.15); background:var(--bg-card)">
            
            <!-- Search Field -->
            <div style="position:relative; flex:1">
                <i class="fas fa-search" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); color:var(--primary); font-size:1.1rem; pointer-events:none; z-index:2"></i>
                <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search for dishes, kitchens..." style="padding-left:52px; height:58px; border-radius:18px; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); font-size:1rem; width:100%; transition:0.3s; color:#1e1e2d; font-weight:500" onfocus="this.style.borderColor='var(--primary)'; this.style.background='#fff'; this.style.boxShadow='0 0 0 4px rgba(255,107,53,0.1)'" onblur="this.style.borderColor='rgba(0,0,0,0.05)'; this.style.background='rgba(0,0,0,0.03)'; this.style.boxShadow='none'">
            </div>

            <!-- Area Dropdown -->
            <div style="position:relative; width:180px;">
                <i class="fas fa-map-marker-alt" style="position:absolute; left:18px; top:50%; transform:translateY(-50%); color:var(--primary); pointer-events:none; z-index:2"></i>
                <select name="area" id="areaSelector" class="form-control" onchange="submitFilterForm()" style="padding-left:46px; padding-right:40px; height:58px; border-radius:18px; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); font-weight:700; cursor:pointer; width:100%; transition:0.3s; color:#1e1e2d; appearance:none; outline:none;" onmouseover="this.style.background='#fff'; this.style.borderColor='var(--primary)'" onmouseout="if(document.activeElement !== this) { this.style.background='rgba(0,0,0,0.03)'; this.style.borderColor='rgba(0,0,0,0.05)' }">
                    <option value="nearby" @selected($area == 'nearby')>Near Me</option>
                    <option value="" @selected($area == '')>All Areas</option>
                    <option value="Cairo" @selected($area == 'Cairo')>Cairo</option>
                    <option value="Giza" @selected($area == 'Giza')>Giza</option>
                    <option value="Alexandria" @selected($area == 'Alexandria')>Alexandria</option>
                </select>
                <i class="fas fa-chevron-down" style="position:absolute; right:18px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:rgba(0,0,0,0.3); pointer-events:none"></i>
            </div>

            <!-- Filter Button -->
            <button type="submit" class="btn btn-primary" style="height:58px; border-radius:18px; padding:0 30px; font-weight:800; display:flex; align-items:center; gap:10px; box-shadow:0 10px 25px -5px rgba(255, 107, 53, 0.4); border:none; background:var(--primary)">
                <i class="fas fa-sliders-h" style="font-size:1.1rem"></i> 
                <span>Refine Filter</span>
            </button>
        </div>

        <!-- Tag Filters (Diet, Mood, Health) -->
        <div style="margin-top: 15px; display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
            <span style="font-size: 0.9rem; font-weight: 600; color: var(--text-primary); margin-right: 5px;"><i class="fas fa-tags" style="color:var(--primary)"></i> Filters:</span>
            @foreach($allTags as $category => $categoryTags)
                @foreach($categoryTags as $tag)
                    <label class="tag-chip {{ in_array($tag->id, $tagsFilter) ? 'active' : '' }}">
                        <input type="checkbox" name="tags[]" value="{{ $tag->id }}" onchange="submitFilterForm()" {{ in_array($tag->id, $tagsFilter) ? 'checked' : '' }} style="display: none;">
                        <i class="fas {{ $tag->icon }}"></i> {{ $tag->name }}
                    </label>
                @endforeach
            @endforeach
        </div>
    </form>

    <style>
    .tag-chip {
        padding: 6px 14px;
        border-radius: 20px;
        background: rgba(0,0,0,0.04);
        border: 1px solid rgba(0,0,0,0.08);
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .tag-chip:hover {
        background: rgba(255,107,53,0.05);
        border-color: rgba(255,107,53,0.3);
    }
    .tag-chip.active {
        background: var(--primary);
        color: #fff;
        border-color: var(--primary);
        box-shadow: 0 4px 10px rgba(255, 107, 53, 0.3);
    }

    .responsive-filter-grid {
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: nowrap;
    }
    @media(max-width: 768px) {
        .responsive-filter-grid {
            flex-wrap: wrap;
            padding: 15px !important;
            gap: 10px;
        }
        .responsive-filter-grid > div:first-child {
            width: 100% !important;
            flex: none !important;
        }
        .responsive-filter-grid > div:not(:first-child), 
        .responsive-filter-grid button {
            flex: 1;
            width: auto !important;
            min-width: 120px;
        }
    }
    </style>

    @if($noAddress ?? false)
    <div class="glass-card reveal" style="margin-bottom:25px; padding:15px 25px; border-radius:18px; border:1px solid rgba(255,167,38,0.2); background:rgba(255,167,38,0.05); display:flex; align-items:center; gap:15px;">
        <div style="width:36px; height:36px; border-radius:10px; background:rgba(255,167,38,0.2); display:flex; align-items:center; justify-content:center; color:#ff9f1c; flex-shrink:0;">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <span style="color:var(--text-primary); font-weight:500;">
            To show kitchens near you, please <a href="{{ route('frontend.addresses') }}" style="color:#ff9f1c; font-weight:700; text-decoration:none; border-bottom:1.5px solid rgba(255,167,38,0.3); transition:0.3s;" onmouseover="this.style.borderColor='#ff9f1c'" onmouseout="this.style.borderColor='rgba(255,167,38,0.3)'">set your primary address</a> first.
        </span>
    </div>
    @elseif($isProximityFiltered ?? false)
    <div class="reveal" style="margin-bottom:25px; display:flex; align-items:center; gap:12px; padding:0 5px;">
        <div style="width:32px; height:32px; border-radius:50%; background:rgba(255,107,53,0.1); display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:0.85rem;">
            <i class="fas fa-location-dot"></i>
        </div>
        <span style="color:var(--text-secondary); font-size:0.95rem; font-weight:500;">
            Showing kitchens within <strong style="color:var(--text-primary)">20 km</strong> of your saved address. 
            <a href="{{ route('frontend.addresses') }}" style="color:var(--primary); font-weight:700; margin-left:8px; text-decoration:none; font-size:0.9rem; border-bottom:1px solid rgba(255,107,53,0.3)">Manage addresses</a>
        </span>
    </div>
    @endif

    <!-- Categories Slider -->
    <div style="margin-bottom: 40px; position:relative; display:flex; align-items:center; gap:10px;">
        <button class="btn btn-outline" style="background:var(--bg-card2); border-radius:50%; width:44px; height:44px; padding:0; flex-shrink:0; border-color:var(--border-color); color:var(--text-primary); display:flex; align-items:center; justify-content:center;" onclick="document.getElementById('catSlider').scrollBy({left:-200, behavior:'smooth'})"><i class="fas fa-chevron-left"></i></button>

        <div id="catSlider" style="display:flex; gap:12px; overflow-x:auto; padding-bottom:4px; padding-top:4px; scrollbar-width:none; -ms-overflow-style:none; flex:1; scroll-behavior:smooth;" class="cat-slider reveal">
            <a href="{{ route('frontend.menu', array_merge(request()->query(), ['cat' => 0])) }}" style="text-decoration:none; padding:10px 24px; border-radius:30px; border:1px solid {{ $catFilter == 0 ? 'var(--primary)' : 'var(--border-color)' }}; background:{{ $catFilter == 0 ? 'rgba(255,107,53,0.15)' : 'rgba(255,255,255,0.05)' }}; color:{{ $catFilter == 0 ? 'var(--primary)' : 'var(--text-primary)' }}; white-space:nowrap; font-weight:700; transition:all 0.3s ease; display:flex; align-items:center; gap:8px;" class="cat-btn">
                <span style="font-size:1.1rem">🍱</span> All Categories
            </a>
            @foreach($categories as $cat)
            @php
                $icon = '🍽️';
                if(stripos($cat->Name, 'Meal') !== false) $icon = '🍲';
                if(stripos($cat->Name, 'Dessert') !== false || stripos($cat->Name, 'Sweet') !== false) $icon = '🍰';
                if(stripos($cat->Name, 'Drink') !== false || stripos($cat->Name, 'Beverage') !== false) $icon = '🍹';
                if(stripos($cat->Name, 'Pizza') !== false) $icon = '🍕';
                if(stripos($cat->Name, 'Burger') !== false || stripos($cat->Name, 'Sandwich') !== false) $icon = '🍔';
                if(stripos($cat->Name, 'Breakfast') !== false) $icon = '🥞';
                if(stripos($cat->Name, 'Seafood') !== false || stripos($cat->Name, 'Fish') !== false) $icon = '🦐';
                if(stripos($cat->Name, 'Appetizer') !== false || stripos($cat->Name, 'Salad') !== false) $icon = '🥗';
                if(stripos($cat->Name, 'Vegan') !== false || stripos($cat->Name, 'Healthy') !== false) $icon = '🥑';
                if(stripos($cat->Name, 'BBQ') !== false || stripos($cat->Name, 'Grill') !== false) $icon = '🍖';
                if(stripos($cat->Name, 'Pasta') !== false) $icon = '🍝';
                if(stripos($cat->Name, 'Chicken') !== false) $icon = '🍗';
                if(stripos($cat->Name, 'Bakery') !== false) $icon = '🥐';
            @endphp
            <a href="{{ route('frontend.menu', array_merge(request()->query(), ['cat' => $cat->CategoryID])) }}" style="text-decoration:none; padding:10px 24px; border-radius:30px; border:1px solid {{ $catFilter == $cat->CategoryID ? 'var(--primary)' : 'var(--border-color)' }}; background:{{ $catFilter == $cat->CategoryID ? 'rgba(255,107,53,0.15)' : 'rgba(255,255,255,0.05)' }}; color:{{ $catFilter == $cat->CategoryID ? 'var(--primary)' : 'var(--text-primary)' }}; white-space:nowrap; font-weight:700; transition:all 0.3s ease; display:flex; align-items:center; gap:8px;" class="cat-btn">
                <span style="font-size:1.1rem">{{ $icon }}</span> {{ $cat->Name }}
            </a>
            @endforeach
        </div>

        <button class="btn btn-outline" style="background:var(--bg-card2); border-radius:50%; width:44px; height:44px; padding:0; flex-shrink:0; border-color:var(--border-color); color:var(--text-primary); display:flex; align-items:center; justify-content:center;" onclick="document.getElementById('catSlider').scrollBy({left:200, behavior:'smooth'})"><i class="fas fa-chevron-right"></i></button>
    </div>

    <style>
        .cat-slider::-webkit-scrollbar { display: none; }
        .cat-btn:hover { background: var(--bg-card) !important; border-color: var(--primary) !important; color: var(--primary) !important; transform: translateY(-2px); box-shadow: 0 4px 10px rgba(255,107,53,0.15); }
    </style>

    <!-- Most Ordered Items -->
    @if(isset($mostOrderedItems) && $mostOrderedItems->isNotEmpty())
    <div id="mostOrderedSection" class="reveal menu-special-section" style="margin-bottom: 50px;">
        <h2 style="font-size:1.8rem; margin-bottom: 24px; color:var(--text-primary); display:flex; align-items:center; gap:12px;">
            <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg, #ff6b35, #ff9f1c); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.2rem; box-shadow:0 10px 20px -5px rgba(255, 107, 53, 0.5);">🔥</div>
            Most Ordered Delicacies
        </h2>
        <div class="grid grid-4">
            @foreach($mostOrderedItems as $idx => $item)
            <div class="card menu-card reveal" data-cat="{{ $item->CategoryID ?? 0 }}">
                <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.1),rgba(255,167,38,0.05)); position:relative; overflow:hidden; height:180px">
                    @php
                        $itemImg = null;
                        if($item->images->count() > 0) {
                            $dbImg = $item->images->first()->Image;
                            $itemImg = str_starts_with($dbImg, 'http') ? $dbImg : url('upload/item_images/'.$dbImg);
                        } else {
                            $itemImg = url('upload/website_assets/grills.png');
                        }
                    @endphp
                    <img src="{{ $itemImg }}" style="width:100%; height:100%; object-fit:cover; transition:transform 0.5s ease" class="hover-zoom" alt="{{ $item->ItemName }}">
                    @if($item->CatName)
                    <span style="position:absolute; top:12px; left:12px; background:rgba(255,107,53,0.85); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:4px 14px; border-radius:20px; font-size:0.75rem; font-weight:700">{{ $item->CatName }}</span>
                    @endif
                    @if($item->KitchenName)
                    <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(0,0,0,0.8)); padding:10px 15px; text-align:left">
                        <a href="{{ route('frontend.kitchen', $item->KitchenOwnerID ?? 0) }}" style="text-decoration:none;">
                            <span style="font-size:0.75rem; color:rgba(255,255,255,0.9); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                                <i class="fas fa-store" style="color:var(--primary);"></i> {{ $item->KitchenName }}
                                @if(isset($item->distance))
                                    <span style="margin-left:4px; color:rgba(255,255,255,0.7); font-weight:500">
                                        • <i class="fas fa-map-marker-alt"></i> {{ $item->distance }} km
                                    </span>
                                @endif
                            </span>
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:12px">
                    <h3 class="card-title" style="font-size:1.1rem; font-weight:800; color:var(--text-primary)"><a href="{{ route('frontend.item', $item->MenuItemID) }}" style="color:inherit; text-decoration:none;">{{ $item->ItemName }}</a></h3>
                    <p class="card-text text-muted" style="font-size:0.85rem; line-height:1.5; margin:0">{{ Str::limit($item->Description ?? 'No description available.', 65) }}</p>
                    @if(isset($item->tags) && $item->tags->count() > 0)
                    <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:4px;">
                        @foreach($item->tags->take(3) as $tag)
                        <span style="background:rgba(255,107,53,0.1); color:var(--primary); font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:12px;"><i class="fas {{ $tag->icon }}"></i> {{ $tag->name }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="flex-between" style="margin-top:8px">
                        <div>
                            @if($item->DiscountPrice)
                                <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.8rem;display:block;line-height:1">{{ number_format($item->ItemPrice, 0) }} EGP</span>
                                <span style="font-size:1.3rem; font-weight:800; color:var(--success)">{{ number_format($item->DiscountPrice, 0) }} <small style="font-size:0.7rem; opacity:0.8">EGP</small></span>
                            @else
                                <span style="font-size:1.3rem; font-weight:800; color:var(--primary)">{{ number_format($item->ItemPrice, 0) }} <small style="font-size:0.7rem; opacity:0.8">EGP</small></span>
                            @endif
                        </div>
                        @php
                            $vStatus = ($item->kitchenOwner ?? $item->caterer)?->current_status ?? 'Open';
                        @endphp
                        <div style="display:flex; align-items:center; gap:8px; margin-top:10px;">
                            <button class="btn btn-outline btn-sm" onclick='showItemDetails(@json($item), "{{ $itemImg }}", "{{ $vStatus }}")' style="height:36px; border-radius:10px; padding:0 14px; font-size:0.8rem; border:1px solid var(--primary); color:var(--primary); font-weight:700; display:flex; align-items:center; gap:6px; transition:all 0.3s ease; background:transparent;" onmouseover="this.style.background='rgba(255,107,53,0.05)'" onmouseout="this.style.background='transparent'">
                                <i class="fas fa-eye" style="font-size:0.85rem"></i> Details
                            </button>
                            
                            @if($vStatus === 'Closed')
                                <button class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; border-radius:10px; opacity:0.4; cursor:not-allowed; display:flex; align-items:center; justify-content:center; border-color:var(--border-color); color:var(--text-muted)" disabled>
                                    <i class="fas fa-pen-nib" style="font-size:0.8rem"></i>
                                </button>
                                <button class="btn btn-outline btn-sm" style="height:36px; border-radius:10px; padding:0 14px; font-size:0.8rem; font-weight:700; opacity:0.5; cursor:not-allowed; border-color:var(--border-color); color:var(--text-muted); display:flex; align-items:center; gap:6px" disabled>
                                    <i class="fas fa-lock" style="font-size:0.8rem"></i> Closed
                                </button>
                            @else
                                <button class="btn btn-outline btn-sm" onclick="openMessengerChat({{ $item->MenuItemID }}, '{{ addslashes($item->ItemName) }}', {{ $item->DiscountPrice ?: $item->ItemPrice }}, '{{ $item->KitchenOwnerID ?? 0 }}')" style="width:36px; height:36px; padding:0; border-radius:10px; border:1px solid var(--primary); color:var(--primary); display:flex; align-items:center; justify-content:center; transition:all 0.3s ease; background:transparent;" onmouseover="this.style.background='rgba(255,107,53,0.05)'" onmouseout="this.style.background='transparent'" title="Customize Request">
                                    <i class="fas fa-pen-nib" style="font-size:0.85rem"></i>
                                </button>
                                <button class="btn btn-primary btn-sm" onclick="addToCart({{ $item->MenuItemID }},'{{ addslashes($item->ItemName) }}',{{ $item->DiscountPrice ?: $item->ItemPrice }},'{{ $itemImg ?? '' }}',{{ $item->KitchenOwnerID ?? 'null' }},{{ $item->CatererID ?? 'null' }})" style="height:36px; border-radius:10px; padding:0 18px; font-weight:700; display:flex; align-items:center; gap:8px; flex:1; justify-content:center; background:var(--primary); border:none; box-shadow: 0 4px 10px rgba(255,107,53,0.2)">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div id="mostOrderedDivider" class="menu-special-section" style="height:1px; background:var(--border-color); margin-bottom:40px; opacity:0.5;"></div>
    @endif

    <!-- Recommended For You -->
    @if(isset($recommendedItems) && $recommendedItems->isNotEmpty())
    <div id="recommendedSection" class="reveal menu-special-section" style="margin-bottom:50px;">
        <h2 style="font-size:1.8rem; margin-bottom:24px; color:var(--text-primary); display:flex; align-items:center; gap:12px;">
            <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,#a855f7,#6366f1); display:flex; align-items:center; justify-content:center; color:#fff; font-size:1.2rem; box-shadow:0 10px 20px -5px rgba(168,85,247,0.4);">✨</div>
            Recommended For You
            <span style="font-size:0.8rem; font-weight:500; color:var(--text-muted); margin-left:4px; background:rgba(168,85,247,0.1); border:1px solid rgba(168,85,247,0.2); padding:4px 12px; border-radius:20px;">Based on your orders</span>
        </h2>
        <div class="grid grid-4" id="recommendedGrid">
            @foreach($recommendedItems as $item)
            @php
                $recImg = null;
                if($item->images->count() > 0) {
                    $dbImg = $item->images->first()->Image;
                    $recImg = str_starts_with($dbImg, 'http') ? $dbImg : url('upload/item_images/'.$dbImg);
                } else {
                    $recImg = url('upload/website_assets/grills.png');
                }
            @endphp
            <div class="card menu-card reveal rec-card" data-cat="{{ $item->CategoryID ?? 0 }}" style="border-color:rgba(168,85,247,0.15);position:relative;overflow:hidden;">
                <div style="position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#a855f7,#6366f1);z-index:2;"></div>
                <div class="card-img" style="background:linear-gradient(135deg,rgba(168,85,247,0.1),rgba(99,102,241,0.05));position:relative;overflow:hidden;height:180px;">
                    <img src="{{ $recImg }}" style="width:100%;height:100%;object-fit:cover;transition:transform 0.5s ease" class="hover-zoom" alt="{{ $item->ItemName }}">
                    @if($item->CatName)
                    <span style="position:absolute;top:12px;left:12px;background:rgba(168,85,247,0.85);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);color:#fff;padding:4px 14px;border-radius:20px;font-size:0.75rem;font-weight:700;">{{ $item->CatName }}</span>
                    @endif
                    @if($item->KitchenName)
                    <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,0.8));padding:10px 15px;text-align:left;">
                        <a href="{{ route('frontend.kitchen', $item->KitchenOwnerID ?? 0) }}" style="text-decoration:none;">
                            <span style="font-size:0.75rem;color:rgba(255,255,255,0.9);font-weight:600;display:inline-flex;align-items:center;gap:6px;">
                                <i class="fas fa-store" style="color:#a855f7;"></i> {{ $item->KitchenName }}
                                @if(isset($item->distance))
                                    <span style="margin-left:4px; color:rgba(255,255,255,0.7); font-weight:500">
                                        • <i class="fas fa-map-marker-alt"></i> {{ $item->distance }} km
                                    </span>
                                @endif
                            </span>
                        </a>
                    </div>
                    @endif
                </div>
                <div class="card-body" style="padding:20px;display:flex;flex-direction:column;gap:12px;">
                    <h3 class="card-title" style="font-size:1.1rem;font-weight:800;color:var(--text-primary);"><a href="{{ route('frontend.item', $item->MenuItemID) }}" style="color:inherit;text-decoration:none;">{{ $item->ItemName }}</a></h3>
                    <p class="card-text text-muted" style="font-size:0.85rem;line-height:1.5;margin:0;">{{ Str::limit($item->Description ?? 'No description available.', 65) }}</p>
                    @if(isset($item->tags) && $item->tags->count() > 0)
                    <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:4px;">
                        @foreach($item->tags->take(3) as $tag)
                        <span style="background:rgba(168,85,247,0.1); color:#a855f7; font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:12px;"><i class="fas {{ $tag->icon }}"></i> {{ $tag->name }}</span>
                        @endforeach
                    </div>
                    @endif
                    <div class="flex-between" style="margin-top:8px;">
                        <div>
                            @if($item->DiscountPrice)
                                <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.8rem;display:block;line-height:1;">{{ number_format($item->ItemPrice,0) }} EGP</span>
                                <span style="font-size:1.3rem;font-weight:800;color:var(--success);">{{ number_format($item->DiscountPrice,0) }} <small style="font-size:0.7rem;opacity:0.8;">EGP</small></span>
                            @else
                                <span style="font-size:1.3rem;font-weight:800;color:#a855f7;">{{ number_format($item->ItemPrice,0) }} <small style="font-size:0.7rem;opacity:0.8;">EGP</small></span>
                            @endif
                        </div>
                        @php
                            $vStatus = ($item->kitchenOwner ?? $item->caterer)?->current_status ?? 'Open';
                        @endphp
                        <div style="display:flex; align-items:center; gap:8px; margin-top:10px;">
                            <button class="btn btn-outline btn-sm" onclick='showItemDetails(@json($item), "{{ $recImg }}", "{{ $vStatus }}")' style="height:36px; border-radius:10px; padding:0 14px; font-size:0.8rem; border:1px solid #a855f7; color:#a855f7; font-weight:700; display:flex; align-items:center; gap:6px; transition:all 0.3s ease; background:transparent;" onmouseover="this.style.background='rgba(168,85,247,0.05)'" onmouseout="this.style.background='transparent'">
                                <i class="fas fa-eye" style="font-size:0.85rem"></i> Details
                            </button>
                            
                            @if($vStatus === 'Closed')
                                <button class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; border-radius:10px; opacity:0.4; cursor:not-allowed; display:flex; align-items:center; justify-content:center; border-color:var(--border-color); color:var(--text-muted)" disabled>
                                    <i class="fas fa-pen-nib" style="font-size:0.8rem"></i>
                                </button>
                                <button class="btn btn-outline btn-sm" style="height:36px; border-radius:10px; padding:0 14px; font-size:0.8rem; font-weight:700; opacity:0.5; cursor:not-allowed; border-color:var(--border-color); color:var(--text-muted); display:flex; align-items:center; gap:6px" disabled>
                                    <i class="fas fa-lock" style="font-size:0.8rem"></i> Closed
                                </button>
                            @else
                                <button class="btn btn-outline btn-sm" onclick="openMessengerChat({{ $item->MenuItemID }},'{{ addslashes($item->ItemName) }}',{{ $item->DiscountPrice ?: $item->ItemPrice }},'{{ $item->KitchenOwnerID ?? 0 }}')" style="width:36px; height:36px; padding:0; border-radius:10px; border:1px solid #a855f7; color:#a855f7; display:flex; align-items:center; justify-content:center; transition:all 0.3s ease; background:transparent;" onmouseover="this.style.background='rgba(168,85,247,0.05)'" onmouseout="this.style.background='transparent'" title="Customize Request">
                                    <i class="fas fa-pen-nib" style="font-size:0.85rem;"></i>
                                </button>
                                <button class="btn btn-sm" onclick="addToCart({{ $item->MenuItemID }},'{{ addslashes($item->ItemName) }}',{{ $item->DiscountPrice ?: $item->ItemPrice }},'{{ $recImg }}',{{ $item->KitchenOwnerID ?? 'null' }},{{ $item->CatererID ?? 'null' }})" style="height:36px; border-radius:10px; padding:0 18px; font-weight:700; display:flex; align-items:center; gap:8px; flex:1; justify-content:center; background:linear-gradient(135deg,#a855f7,#6366f1); color:#fff; border:none; box-shadow: 0 4px 10px rgba(168,85,247,0.2)">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    <div id="recommendedDivider" class="menu-special-section" style="height:1px;background:var(--border-color);margin-bottom:40px;opacity:0.5;"></div>
    @endif

    <!-- Menu Grid -->
    <h3 id="exploreHeading" style="font-size:1.4rem; margin-bottom:20px; font-weight:700; display:flex; align-items:center; gap:12px">
        <div style="width:36px; height:36px; border-radius:10px; background:rgba(255,255,255,0.05); border:1px solid var(--border-color); display:flex; align-items:center; justify-content:center; color:var(--text-primary); font-size:1rem;">🍽️</div>
        Explore the Menu
    </h3>
    <div class="grid grid-4 menu-grid">
        @foreach($items as $idx => $item)
        <div class="card menu-card reveal" data-cat="{{ $item->CategoryID ?? 0 }}">
            <div class="card-img" style="background:linear-gradient(135deg,rgba(255,107,53,0.1),rgba(255,167,38,0.05)); position:relative; overflow:hidden; height:180px">
                @php
                    $itemImg = null;
                    if($item->images->count() > 0) {
                        $dbImg = $item->images->first()->Image;
                        $itemImg = str_starts_with($dbImg, 'http') ? $dbImg : url('upload/item_images/'.$dbImg);
                    } else {
                        $itemImg = url('upload/website_assets/grills.png');
                    }
                @endphp
                <img src="{{ $itemImg }}" style="width:100%; height:100%; object-fit:cover; transition:transform 0.5s ease" class="hover-zoom" alt="{{ $item->ItemName }}">
                @if($item->CatName)
                <span style="position:absolute; top:12px; left:12px; background:rgba(255,107,53,0.85); backdrop-filter:blur(8px); border:1px solid rgba(255,255,255,0.2); color:#fff; padding:4px 14px; border-radius:20px; font-size:0.75rem; font-weight:700">{{ $item->CatName }}</span>
                @endif
                @if($item->KitchenName)
                <div style="position:absolute; bottom:0; left:0; right:0; background:linear-gradient(transparent, rgba(0,0,0,0.8)); padding:10px 15px; text-align:left">
                    <a href="{{ route('frontend.kitchen', $item->KitchenOwnerID ?? 0) }}" style="text-decoration:none;">
                        <span style="font-size:0.75rem; color:rgba(255,255,255,0.9); font-weight:600; display:inline-flex; align-items:center; gap:6px;">
                            <i class="fas fa-store" style="color:var(--primary);"></i> {{ $item->KitchenName }}
                            @if(isset($item->distance))
                                <span style="margin-left:8px; background:rgba(255,167,38,0.25); color:#fff; padding:2px 8px; border-radius:10px; font-size:0.65rem; border:1px solid rgba(255,167,38,0.3)">
                                    <i class="fas fa-map-marker-alt" style="font-size:0.6rem"></i> {{ $item->distance }} km
                                </span>
                            @endif
                        </span>
                    </a>
                </div>
                @endif
            </div>
            <div class="card-body" style="padding:20px; display:flex; flex-direction:column; gap:12px">
                <h3 class="card-title" style="font-size:1.1rem; font-weight:800; color:var(--text-primary)"><a href="{{ route('frontend.item', $item->MenuItemID) }}" style="color:inherit; text-decoration:none;">{{ $item->ItemName }}</a></h3>
                <p class="card-text text-muted" style="font-size:0.85rem; line-height:1.5; margin:0">{{ Str::limit($item->Description ?? 'No description available.', 65) }}</p>
                @if(isset($item->tags) && $item->tags->count() > 0)
                <div style="display:flex; flex-wrap:wrap; gap:4px; margin-top:4px;">
                    @foreach($item->tags->take(3) as $tag)
                    <span style="background:rgba(255,107,53,0.1); color:var(--primary); font-size:0.7rem; font-weight:700; padding:2px 8px; border-radius:12px;"><i class="fas {{ $tag->icon }}"></i> {{ $tag->name }}</span>
                    @endforeach
                </div>
                @endif
                <div class="flex-between" style="margin-top:8px">
                    <div>
                        @if($item->DiscountPrice)
                            <span style="text-decoration:line-through;color:var(--text-muted);font-size:0.8rem;display:block;line-height:1">{{ number_format($item->ItemPrice, 0) }} EGP</span>
                            <span style="font-size:1.3rem; font-weight:800; color:var(--success)">{{ number_format($item->DiscountPrice, 0) }} <small style="font-size:0.7rem; opacity:0.8">EGP</small></span>
                        @else
                            <span style="font-size:1.3rem; font-weight:800; color:var(--primary)">{{ number_format($item->ItemPrice, 0) }} <small style="font-size:0.7rem; opacity:0.8">EGP</small></span>
                        @endif
                    </div>
                    @php
                        $vStatus = ($item->kitchenOwner ?? $item->caterer)?->current_status ?? 'Open';
                    @endphp
                    <div style="display:flex; align-items:center; gap:8px; margin-top:10px;">
                        <button class="btn btn-outline btn-sm" onclick='showItemDetails(@json($item), "{{ $itemImg }}", "{{ $vStatus }}")' style="height:36px; border-radius:10px; padding:0 14px; font-size:0.8rem; border:1px solid var(--primary); color:var(--primary); font-weight:700; display:flex; align-items:center; gap:6px; transition:all 0.3s ease; background:transparent;" onmouseover="this.style.background='rgba(255,107,53,0.05)'" onmouseout="this.style.background='transparent'">
                            <i class="fas fa-eye" style="font-size:0.85rem"></i> Details
                        </button>
                        
                        @if($vStatus === 'Closed')
                            <button class="btn btn-outline btn-sm" style="width:36px; height:36px; padding:0; border-radius:10px; opacity:0.4; cursor:not-allowed; display:flex; align-items:center; justify-content:center; border-color:var(--border-color); color:var(--text-muted)" disabled>
                                <i class="fas fa-pen-nib" style="font-size:0.8rem"></i>
                            </button>
                            <button class="btn btn-outline btn-sm" style="height:36px; border-radius:10px; padding:0 14px; font-size:0.8rem; font-weight:700; opacity:0.5; cursor:not-allowed; border-color:var(--border-color); color:var(--text-muted); display:flex; align-items:center; gap:6px" disabled>
                                <i class="fas fa-lock" style="font-size:0.8rem"></i> Closed
                            </button>
                        @else
                            <button class="btn btn-outline btn-sm" onclick="openMessengerChat({{ $item->MenuItemID }}, '{{ addslashes($item->ItemName) }}', {{ $item->DiscountPrice ?: $item->ItemPrice }}, '{{ $item->KitchenOwnerID ?? 0 }}')" style="width:36px; height:36px; padding:0; border-radius:10px; border:1px solid var(--primary); color:var(--primary); display:flex; align-items:center; justify-content:center; transition:all 0.3s ease; background:transparent;" onmouseover="this.style.background='rgba(255,107,53,0.05)'" onmouseout="this.style.background='transparent'" title="Customize Request">
                                <i class="fas fa-pen-nib" style="font-size:0.85rem"></i>
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="addToCart({{ $item->MenuItemID }},'{{ addslashes($item->ItemName) }}',{{ $item->DiscountPrice ?: $item->ItemPrice }},'{{ $itemImg ?? '' }}',{{ $item->KitchenOwnerID ?? 'null' }},{{ $item->CatererID ?? 'null' }})" style="height:36px; border-radius:10px; padding:0 18px; font-weight:700; display:flex; align-items:center; gap:8px; flex:1; justify-content:center; background:var(--primary); border:none; box-shadow: 0 4px 10px rgba(255,107,53,0.2)">
                                <i class="fas fa-plus"></i> Add
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($items->isEmpty())
    <div class="glass-card text-center reveal" style="padding:100px 20px; margin-top:40px; border:1px dashed rgba(255,255,255,0.1)">
        <div style="font-size:6rem; margin-bottom:24px; opacity:0.2">🍽️</div>
        <h2 style="margin-bottom:12px">{{ $area === 'nearby' ? 'No Delicacies Near You' : 'No Delicacies Found' }}</h2>
        <p style="color:var(--text-muted); max-width:400px; margin:0 auto 30px">We couldn't find any items matching your criteria. Try adjusting your filters or search keywords.</p>
        <a href="{{ route('frontend.menu', ['area' => '']) }}" class="btn btn-primary btn-lg">Browse All Dishes</a>
    </div>
    @endif

</div>
</section>

@push('scripts')
<script>
// ─── Store original DOM position of recommended section ────────────────
const _menuContainer = document.querySelector('.menu-grid')?.parentElement;
let   _recSection    = document.getElementById('recommendedSection');
let   _recDivider    = document.getElementById('recommendedDivider');
let   _recAtBottom   = false;

// ─── Recommended: filter by active category ────────────────────────────
const _activeCat = {{ $catFilter }};

function filterRecByCategory(catId) {
    const cards = document.querySelectorAll('.rec-card');
    cards.forEach(card => {
        if (!catId || catId == 0) {
            card.style.display = '';
        } else {
            card.style.display = (card.dataset.cat == catId) ? '' : 'none';
        }
    });

    // Hide the whole section if no visible cards
    if (_recSection) {
        const anyVisible = [...document.querySelectorAll('.rec-card')].some(c => c.style.display !== 'none');
        _recSection.style.display = anyVisible ? '' : 'none';
        if (_recDivider) _recDivider.style.display = anyVisible ? '' : 'none';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Initial category filter
    filterRecByCategory(_activeCat);
});

// ─── Search: hide special sections, move rec to bottom ────────────────
function handleMenuSearch(query) {
    const q = query.toLowerCase().trim();

    // Filter main menu cards
    document.querySelectorAll('.menu-grid .menu-card').forEach(card => {
        card.style.display = card.textContent.toLowerCase().includes(q) ? '' : 'none';
    });

    const moSection = document.getElementById('mostOrderedSection');
    const moDivider = document.getElementById('mostOrderedDivider');

    if (q) {
        // Hide Most Ordered when searching
        if (moSection) moSection.style.display = 'none';
        if (moDivider) moDivider.style.display = 'none';

        // Move recommended to bottom (after everything else)
        if (_recSection && !_recAtBottom) {
            if (_menuContainer) {
                if (_recDivider) _menuContainer.appendChild(_recDivider);
                _menuContainer.appendChild(_recSection);
            }
            _recAtBottom = true;
        }
    } else {
        // Restore Most Ordered
        if (moSection) moSection.style.display = '';
        if (moDivider) moDivider.style.display = '';

        // Restore recommended to its original position (before exploreHeading)
        if (_recSection && _recAtBottom) {
            const exploreH = document.getElementById('exploreHeading');
            if (exploreH) {
                if (_recDivider) exploreH.parentElement.insertBefore(_recDivider, exploreH);
                exploreH.parentElement.insertBefore(_recSection, exploreH);
            }
            _recAtBottom = false;
        }

        // Re-apply category filter
        filterRecByCategory(_activeCat);
    }
}
</script>

<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const userId = "{{ auth()->id() ?? 'guest' }}";
    if (!localStorage.getItem('bitehub_tour_menu_' + userId)) {
        const driver = window.driver.js.driver;
        const driverObj = driver({
            showProgress: true,
            steps: [
                { element: '#menuSearch', popover: { title: '🔍 Search Dishes', description: 'Type any dish name here to quickly find what you are craving.', side: 'bottom', align: 'start' }},
                { element: '#catSlider', popover: { title: '🍱 Filter by Category', description: 'Scroll through categories and click one to filter items by type.', side: 'bottom', align: 'start' }},
                { element: '#mostOrderedSection', popover: { title: '🔥 Most Ordered', description: 'These are the most popular dishes ordered by customers near you.', side: 'top', align: 'start' }},
                { element: '.menu-grid', popover: { title: '🍽️ Full Menu', description: 'Browse all available dishes below. Click Add to drop any item into your cart!', side: 'top', align: 'start' }}
            ],
            onDestroyStarted: () => { localStorage.setItem('bitehub_tour_menu_' + userId, 'true'); driverObj.destroy(); },
            onPopoverRendered: (popover) => {
                let footer = popover.wrapper.querySelector('.driver-popover-navigation-btns');
                if (footer && !footer.querySelector('.skip-tour-btn')) {
                    let btn = document.createElement('button');
                    btn.innerHTML = 'Skip Tour'; btn.className = 'driver-popover-prev-btn skip-tour-btn';
                    btn.style.color = '#ef4444'; btn.style.borderColor = 'transparent'; btn.style.fontWeight = 'bold';
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

                <div class="modal-stats" style="margin-top:-15px; margin-bottom:25px;" id="statMacros">
                    <div class="stat-item" style="border-color: rgba(139, 195, 74, 0.4); color: #8bc34a; display:none;" id="statProtein">
                        <i class="fas fa-drumstick-bite"></i> Protein: <span id="modalProtein">--</span>g
                    </div>
                    <div class="stat-item" style="border-color: rgba(255, 152, 0, 0.4); color: #ff9800; display:none;" id="statCarbs">
                        <i class="fas fa-bread-slice"></i> Carbs: <span id="modalCarbs">--</span>g
                    </div>
                    <div class="stat-item" style="border-color: rgba(244, 67, 54, 0.4); color: #f44336; display:none;" id="statFats">
                        <i class="fas fa-cheese"></i> Fats: <span id="modalFats">--</span>g
                    </div>
                </div>

                <div class="modal-section">
                    <div id="modalTagsContainer" style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 15px;"></div>
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
    background: rgba(0,0,0,0.8);
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
    background: rgba(255,255,255,0.1);
    border: none;
    color: white;
    cursor: pointer;
    z-index: 10;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.modal-close:hover { background: var(--primary); transform: rotate(90deg); }

.modal-layout { display: flex; height: 100%; min-height: 500px; }

.modal-image-side { flex: 1.1; position: relative; background: #000; display: flex; align-items: center; justify-content: center; overflow: hidden; }
.modal-image-side img { width: 100%; height: 100%; object-fit: cover; }
.cat-badge-float { position: absolute; top: 20px; left: 20px; background: var(--primary); color: white; padding: 6px 18px; border-radius: 20px; font-weight: 700; font-size: 0.8rem; box-shadow: 0 4px 12px rgba(255,107,53,0.3); z-index: 2; }

.modal-info-side { flex: 1; padding: 45px; display: flex; flex-direction: column; overflow-y: auto; background: #0f0f0f; color: #fff; }
.modal-title-text { font-size: 2.4rem; font-weight: 900; margin-bottom: 24px; color: #fff; letter-spacing: -1px; }

.modal-stats { display: flex; gap: 12px; margin-bottom: 30px; flex-wrap: wrap; }
.stat-item { background: rgba(255,255,255,0.05); padding: 10px 16px; border-radius: 16px; font-size: 0.85rem; font-weight: 600; color: #ccc; display: flex; align-items: center; gap: 8px; border: 1px solid rgba(255,255,255,0.1); }
.stat-item i { color: var(--primary); }

.modal-section { margin-bottom: 30px; }
.modal-section h3 { font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px; color: var(--primary); display: flex; align-items: center; gap: 10px; font-weight: 800; }
.modal-text { color: #aaa; line-height: 1.7; font-size: 1rem; }

.modal-footer-action { border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px; margin-top: auto; }
.price-display { margin-bottom: 25px; }
.old-price { text-decoration: line-through; color: #666; font-size: 1.1rem; margin-bottom: 4px; display: block; }
.current-price { font-size: 2.8rem; font-weight: 900; color: #fff; line-height: 1; }
.current-price small { font-size: 1rem; color: var(--primary); text-transform: uppercase; margin-left: 5px; }

.action-row { display: flex; gap: 20px; align-items: center; }
.qty-selector { display: flex; align-items: center; background: rgba(255,255,255,0.05); border-radius: 18px; padding: 6px; border: 1px solid rgba(255,255,255,0.15); }
.qty-selector button { width: 38px; height: 38px; border-radius: 12px; border: none; background: rgba(255,255,255,0.1); color: white; font-size: 1.2rem; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
.qty-selector button:hover { background: var(--primary); }
.qty-selector input { width: 50px; text-align: center; background: transparent; border: none; color: white !important; font-weight: 800; font-size: 1.2rem; outline: none; }
.modal-buy-btn { flex: 1; height: 50px; border-radius: 18px; font-weight: 800; font-size: 1.1rem; box-shadow: 0 10px 20px -5px rgba(255,107,53,0.4); text-transform: uppercase; letter-spacing: 0.5px; }

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

@media (max-width: 850px) {
    .modal-layout { flex-direction: column; }
    .modal-image-side { height: 250px; flex: none; }
    .modal-info-side { padding: 30px; }
    .modal-title-text { font-size: 1.8rem; }
}
</style>

<script>
let currentModalItem = null;
let currentModalImg = '';

function showItemDetails(item, imgUrl, kitchenStatus) {
    currentModalItem = item;
    currentModalImg = imgUrl;
    
    document.getElementById('modalItemImg').src = imgUrl;
    document.getElementById('modalCatBadge').textContent = item.CatName || 'Meal';
    document.getElementById('modalItemName').textContent = item.ItemName;
    document.getElementById('modalDescription').textContent = item.Description || 'No description available.';
    
    // Ingredients
    if(item.Ingredients) {
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
    
    // Macros
    document.getElementById('statProtein').style.display = item.Protein ? 'flex' : 'none';
    document.getElementById('modalProtein').textContent = item.Protein || '--';

    document.getElementById('statCarbs').style.display = item.Carbs ? 'flex' : 'none';
    document.getElementById('modalCarbs').textContent = item.Carbs || '--';

    document.getElementById('statFats').style.display = item.Fats ? 'flex' : 'none';
    document.getElementById('modalFats').textContent = item.Fats || '--';

    // Tags
    const tagsContainer = document.getElementById('modalTagsContainer');
    tagsContainer.innerHTML = '';
    if(item.tags && item.tags.length > 0) {
        item.tags.forEach(tag => {
            const span = document.createElement('span');
            span.style.background = 'rgba(255, 107, 53, 0.15)';
            span.style.color = '#fff';
            span.style.border = '1px solid rgba(255, 107, 53, 0.4)';
            span.style.padding = '4px 12px';
            span.style.borderRadius = '20px';
            span.style.fontSize = '0.75rem';
            span.style.fontWeight = '700';
            span.innerHTML = (tag.icon ? '<i class="fas ' + tag.icon + '"></i> ' : '') + tag.name;
            tagsContainer.appendChild(span);
        });
    }
    
    // Price
    if(item.DiscountPrice) {
        document.getElementById('modalOldPrice').textContent = Number(item.ItemPrice).toFixed(2) + ' EGP';
        document.getElementById('modalOldPrice').style.display = 'block';
        document.getElementById('modalCurrentPrice').innerHTML = Number(item.DiscountPrice).toFixed(2) + ' <small>EGP</small>';
    } else {
        document.getElementById('modalOldPrice').style.display = 'none';
        document.getElementById('modalCurrentPrice').innerHTML = Number(item.ItemPrice).toFixed(2) + ' <small>EGP</small>';
    }
    
    // Action Button
    const btn = document.getElementById('modalAddToCartBtn');
    if(kitchenStatus === 'Closed') {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-times"></i> Kitchen Closed';
        btn.style.opacity = '0.5';
    } else {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-cart-plus"></i> Add to Cart';
        btn.style.opacity = '1';
        btn.onclick = function() {
            const qty = parseInt(document.getElementById('modalQty').value);
            for(let i=0; i<qty; i++) {
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

function submitFilterForm() {
    document.querySelector('form[action*="menu"]').submit();
}

function updateModalQty(delta) {
    const input = document.getElementById('modalQty');
    let val = parseInt(input.value) + delta;
    if(val < 1) val = 1;
    input.value = val;
}

// Close on outside click
window.onclick = function(event) {
    const modal = document.getElementById('itemDetailModal');
    if (event.target == modal) {
        closeItemDetails();
    }
}
</script>
