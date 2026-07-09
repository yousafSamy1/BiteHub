@extends('frontend.layouts.app')
@section('title', 'Browse Kitchens')
@section('nav-browse', 'active')

@section('content')
<div class="page-header">
    <h1>Discover <span class="highlight">Home Kitchens</span></h1>
    <p>Find amazing home kitchens near you — fresh, authentic food delivered to your door</p>
</div>

<section class="section" style="padding-top:0">
<div class="container">

    <!-- Premium Filter Bar -->
    <style>
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

    <form method="GET" action="{{ route('frontend.browse') }}" class="reveal" style="margin-bottom:30px;">
        <div class="glass-card responsive-filter-grid" style="padding:10px; border-radius:24px; border:1px solid rgba(255,255,255,0.4); backdrop-filter:blur(20px); box-shadow:0 25px 50px -12px rgba(0,0,0,0.15); background:rgba(255,255,255,0.8)">
            
            <!-- Search Field -->
            <div style="position:relative; flex:1">
                <i class="fas fa-search" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); color:var(--primary); font-size:1.1rem; pointer-events:none; z-index:2"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search for kitchens..." style="padding-left:52px; height:58px; border-radius:18px; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); font-size:1rem; width:100%; transition:0.3s; color:#1e1e2d; font-weight:500" onfocus="this.style.borderColor='var(--primary)'; this.style.background='#fff'; this.style.boxShadow='0 0 0 4px rgba(255,107,53,0.1)'" onblur="this.style.borderColor='rgba(0,0,0,0.05)'; this.style.background='rgba(0,0,0,0.03)'; this.style.boxShadow='none'">
            </div>

            <!-- Area Dropdown -->
            <div style="position:relative; width:180px;">
                <i class="fas fa-map-marker-alt" style="position:absolute; left:18px; top:50%; transform:translateY(-50%); color:var(--primary); pointer-events:none; z-index:2"></i>
                <select name="area" onchange="this.form.submit()" class="form-control" style="padding-left:46px; padding-right:40px; height:58px; border-radius:18px; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); font-weight:700; cursor:pointer; width:100%; transition:0.3s; color:#1e1e2d; appearance:none; outline:none;" onmouseover="this.style.background='#fff'; this.style.borderColor='var(--primary)'" onmouseout="if(document.activeElement !== this) { this.style.background='rgba(0,0,0,0.03)'; this.style.borderColor='rgba(0,0,0,0.05)' }">
                    <option value="nearby" @selected($area == 'nearby')>Near Me</option>
                    <option value="" @selected($area == '')>All Areas</option>
                    <option value="Cairo" @selected($area == 'Cairo')>Cairo</option>
                    <option value="Giza" @selected($area == 'Giza')>Giza</option>
                    <option value="Alexandria" @selected($area == 'Alexandria')>Alexandria</option>
                </select>
                <i class="fas fa-chevron-down" style="position:absolute; right:18px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:rgba(0,0,0,0.3); pointer-events:none"></i>
            </div>

            <!-- Rating Filter -->
            <div style="position:relative; width:160px;">
                <i class="fas fa-star" style="position:absolute; left:18px; top:50%; transform:translateY(-50%); color:var(--primary); pointer-events:none; z-index:2"></i>
                <select name="rating" onchange="this.form.submit()" class="form-control" style="padding-left:46px; padding-right:40px; height:58px; border-radius:18px; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); font-weight:700; cursor:pointer; width:100%; transition:0.3s; color:#1e1e2d; appearance:none; outline:none;" onmouseover="this.style.background='#fff'; this.style.borderColor='var(--primary)'" onmouseout="if(document.activeElement !== this) { this.style.background='rgba(0,0,0,0.03)'; this.style.borderColor='rgba(0,0,0,0.05)' }">
                    <option value="">Rating</option>
                    <option value="5" @selected(request('rating') == '5')>5 Stars</option>
                    <option value="4" @selected(request('rating') == '4')>4+ Stars</option>
                    <option value="3" @selected(request('rating') == '3')>3+ Stars</option>
                </select>
                <i class="fas fa-chevron-down" style="position:absolute; right:18px; top:50%; transform:translateY(-50%); font-size:0.8rem; color:rgba(0,0,0,0.3); pointer-events:none"></i>
            </div>

            <!-- Filter Button -->
            <button type="submit" class="btn btn-primary" style="height:58px; border-radius:18px; padding:0 30px; font-weight:800; display:flex; align-items:center; gap:10px; box-shadow:0 10px 25px -5px rgba(255, 107, 53, 0.4); border:none; background:var(--primary)">
                <i class="fas fa-sliders-h" style="font-size:1.1rem"></i> 
                <span>Filter</span>
            </button>
        </div>
    </form>
    
    @if($noAddress ?? false)
    <div class="info-box info-orange reveal" style="margin-bottom:20px; border-radius:14px; background:rgba(255,167,38,0.1); border:1px solid rgba(255,167,38,0.2); color:#ff9f1c">
        <i class="fas fa-exclamation-triangle"></i>
        <span>To show kitchens near you, please <a href="{{ route('frontend.addresses') }}" style="color:#ff9f1c;font-weight:700;text-decoration:underline">set your primary address</a> first. Showing all kitchens for now.</span>
    </div>
    @elseif($isProximityFiltered ?? false)
    <div class="info-box info-blue reveal" style="margin-bottom:20px; border-radius:14px">
        <i class="fas fa-location-dot"></i>
        <span>Showing kitchens within <strong>20 km</strong> of your saved address. <a href="{{ route('frontend.addresses') }}" style="color:var(--primary);font-weight:700">Manage addresses</a></span>
    </div>
    @endif

    <script>
    function toggleNearby() {
        const input = document.getElementById('nearbyInput');
        const btn = document.getElementById('nearbyBtn');
        if (input.value == '1') {
            input.value = '0';
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline');
        } else {
            @if(!auth()->check())
                window.location.href = "{{ route('login') }}?redirect={{ url()->current() }}";
                return;
            @endif
            input.value = '1';
            btn.classList.add('btn-primary');
            btn.classList.remove('btn-outline');
        }
        btn.closest('form').submit();
    }
    </script>

    {{-- Kitchen Ads Banner --}}
    @if(isset($kitchenAds) && $kitchenAds->isNotEmpty())
    
    @push('styles')
    <style>
    .ad-nav-btn {
        flex-shrink:0; background:var(--bg-card); border:1px solid var(--border-color); color:var(--text-primary);
        width:36px; height:36px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
    }
    .ad-nav-btn:hover { background:var(--primary); color:#fff; border-color:var(--primary); }
    
    @media (max-width: 768px) {
        .kitchen-ad-slide { flex-direction: column !important; text-align: center !important; justify-content: center !important; padding: 25px 15px !important; }
        .kitchen-ad-slide h4 { font-size: 1.3rem !important; white-space: normal !important; text-overflow: unset !important; margin-bottom: 10px !important; line-height: 1.3 !important; }
        .kitchen-ad-slide p { display: none !important; }
        .kitchen-ad-slide > div { align-items: center !important; justify-content: center !important; text-align: center !important; }
        .kitchen-ad-slide .btn { width: 100% !important; margin-top: 10px !important; }
        .kitchen-ad-slide span { margin: 0 auto 10px auto !important; display: inline-block !important; }
    }
    </style>
    @endpush

    <div class="reveal" style="display:flex;align-items:center;gap:12px;margin-bottom:36px">
        @if($kitchenAds->count() > 1)
        <button class="ad-nav-btn" onclick="moveAd(-1, 'kitchen')"><i class="fas fa-chevron-left"></i></button>
        @endif

        <div style="flex:1;position:relative;overflow:hidden;border-radius:16px;">
            @foreach($kitchenAds as $adIdx => $ad)
            @php
                $bgUrl = $ad->BackgroundImage ? asset('upload/ad_images/'.$ad->BackgroundImage) : asset('upload/website_assets/hero.png');
            @endphp
            <div class="ad-slide kitchen-ad-slide" style="display:{{ $adIdx === 0 ? 'flex' : 'none' }};position:relative;border-radius:16px;min-height:220px;padding:30px;align-items:center;gap:20px;background-image:url('{{ $bgUrl }}');background-size:cover;background-position:center;box-shadow:0 8px 24px rgba(0,0,0,0.15);overflow:hidden;">
                <!-- Dark Overlay for Readability -->
                <div style="position:absolute;inset:0;background:linear-gradient(to right, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0.2) 100%);z-index:1;"></div>
                
                <div style="flex-shrink:0;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:1.6rem;box-shadow:0 6px 18px rgba(255,107,53,0.4);border:2px solid rgba(255,255,255,0.2);z-index:2;">👨‍🍳</div>
                
                <div style="flex:1;min-width:0;z-index:2;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                        <span style="background:var(--primary);color:#fff;font-size:0.7rem;font-weight:800;padding:4px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:1px;box-shadow:0 2px 8px rgba(255,107,53,0.3)">Kitchen Ads</span>
                        @if($ad->kitchenOwner)<span style="color:#e2e8f0;font-size:0.85rem;font-weight:600;text-shadow:0 1px 2px rgba(0,0,0,0.5);">by {{ $ad->kitchenOwner->KitchenName }}</span>@endif
                    </div>
                    <h4 style="font-size:1.6rem;font-weight:900;margin:0 0 6px;color:#fff;text-shadow:0 2px 4px rgba(0,0,0,0.6);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $ad->Title }}</h4>
                    @if($ad->Description)<p style="color:#cbd5e1;margin:0;font-size:1rem;line-height:1.5;max-width:80%;max-height:3em;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;text-shadow:0 1px 3px rgba(0,0,0,0.8);">{{ $ad->Description }}</p>@endif
                </div>
                
                @if($ad->kitchenOwner)
                <a href="{{ route('frontend.kitchen', $ad->kitchenOwner->KitchenOwnerID) }}" class="btn btn-primary" style="flex-shrink:0;border-radius:30px;font-size:1rem;font-weight:700;padding:12px 24px;white-space:nowrap;z-index:2;box-shadow:0 4px 12px rgba(255,107,53,0.4)">Visit Kitchen <i class="fas fa-arrow-right ms-2"></i></a>
                @endif
            </div>
            @endforeach
            
            @if($kitchenAds->count() > 1)
            <div style="position:absolute;bottom:8px;left:0;right:0;display:flex;justify-content:center;gap:6px;">
                @foreach($kitchenAds as $adIdx => $ad)
                <button onclick="showAd({{ $adIdx }}, 'kitchen')" id="kitchenAdDot-{{ $adIdx }}" style="width:6px;height:6px;border-radius:50%;border:none;cursor:pointer;background:{{ $adIdx === 0 ? 'var(--primary)' : 'var(--border-color)' }};transition:all 0.3s;padding:0;"></button>
                @endforeach
            </div>
            @endif
        </div>

        @if($kitchenAds->count() > 1)
        <button class="ad-nav-btn" onclick="moveAd(1, 'kitchen')"><i class="fas fa-chevron-right"></i></button>
        @endif
    </div>
    @endif

    {{-- Home Kitchens --}}
    <div class="section-header reveal" style="text-align:left;margin-bottom:28px">
        <h2 style="font-size:1.6rem;display:flex;align-items:center;gap:12px">
            <span style="width:42px;height:42px;border-radius:14px;background:linear-gradient(135deg,var(--primary),var(--accent));display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0"><i class="fas fa-store" style="color:#fff"></i></span>
            Home Kitchens
        </h2>
    </div>
    <div class="grid grid-3 kitchen-grid mb-3">
        @foreach($kitchens as $k)
        @php $rating = $k->average_rating; $reviews = $k->review_count; @endphp
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
            <div class="card-img" style="background:linear-gradient(135deg,rgba(17,17,17,0.4),rgba(17,17,17,0.1));display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;height:220px;position:relative;overflow:hidden">
                <img src="{{ $kProfileUrl }}" style="position:absolute;top:0;left:0;width:100%;height:100%;object-fit:cover;opacity:0.25;z-index:0;filter:blur(3px)">
                <img src="{{ $kProfileUrl }}" style="width:84px;height:84px;border-radius:50%;border:4px solid var(--primary);object-fit:cover;box-shadow:0 8px 25px rgba(255,107,53,0.4);z-index:1" alt="{{ $k->KitchenName }}">
                <h3 style="color:var(--text-primary);font-size:1.1rem;z-index:1;font-weight:800">{{ $k->KitchenName }}</h3>
                @if($k->VerifyStatus === 'Verified')
                <span class="kitchen-badge" style="z-index:1;background:rgba(25,135,84,0.9);padding:4px 12px;border-radius:20px;color:#fff;font-size:0.7rem"><i class="fas fa-check"></i> Verified</span>
                @endif
            </div>
            <div class="card-body">
                <div class="kitchen-rating">
                    <i class="fas fa-star"></i> {{ $rating }}
                    <span style="color:var(--text-muted);font-weight:400">({{ $reviews }} reviews)</span>
                </div>
                <p class="card-text">{{ Str::limit($k->Description ?? 'Delicious homemade food made with love.', 80) }}</p>
                <div class="kitchen-meta">
                    @php $cStatus = $k->current_status; @endphp
                    <span class="badge-status badge-{{ strtolower($cStatus) }}">{{ $cStatus }}</span>
                    @if(isset($k->distance))
                        <span style="background:rgba(255,167,38,0.15); color:#e67e22; border:1px solid rgba(255,167,38,0.3); padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:700">
                            <i class="fas fa-map-marker-alt"></i> {{ $k->distance }} km
                        </span>
                    @endif
                    <span><i class="fas fa-location-dot"></i> {{ $k->Area ?? $k->Location ?? 'Egypt' }}</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    @if($kitchens->isEmpty())
    <div class="glass-card text-center reveal" style="padding:100px 20px; margin-top:40px; border:1px dashed rgba(255,255,255,0.1)">
        <div style="font-size:6rem; margin-bottom:24px; opacity:0.3">🏪</div>
        <h2 style="margin-bottom:12px; font-weight:800">{{ $area === 'nearby' ? 'No Kitchens Near You' : 'No Kitchens Found' }}</h2>
        <p style="color:var(--text-muted); max-width:400px; margin:0 auto 30px; font-size:1.05rem">We couldn't find any kitchens matching your criteria. Try adjusting your filters or area.</p>
        <a href="{{ route('frontend.browse', ['area' => '']) }}" class="btn btn-primary btn-lg" style="border-radius:20px; padding:12px 32px">Clear Filters</a>
    </div>
    @endif


</div>
</section>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const userId = "{{ auth()->id() ?? 'guest' }}";
    if (!localStorage.getItem('bitehub_tour_browse_' + userId)) {
        const driver = window.driver.js.driver;
        const driverObj = driver({
            showProgress: true,
            steps: [
                { element: '.filter-bar', popover: { title: '🔍 Find Kitchens', description: 'Search by name, filter by area, or minimum star rating to narrow down your results.', side: 'bottom', align: 'start' }},
                { element: '.kitchen-grid', popover: { title: '🏠 Kitchen Listings', description: 'Each card shows a kitchen\'s name, status (Open/Busy/Closed), and working hours. Click any to explore their menu.', side: 'top', align: 'start' }}
            ],
            onDestroyStarted: () => { localStorage.setItem('bitehub_tour_browse_' + userId, 'true'); driverObj.destroy(); },
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

@push('scripts')
<script>
var adStates = {
    kitchen: { current: 0, slides: document.querySelectorAll('.kitchen-ad-slide') }
};
function showAd(idx, type) {
    var state = adStates[type];
    if (!state || state.slides.length === 0) return;
    if (idx < 0) idx = state.slides.length - 1;
    if (idx >= state.slides.length) idx = 0;
    state.slides.forEach(function(s, i) {
        s.style.display = i === idx ? 'flex' : 'none';
        var dot = document.getElementById(type + 'AdDot-' + i);
        if (dot) dot.style.background = i === idx ? 'var(--primary)' : 'var(--border-color)';
    });
    state.current = idx;
}
function moveAd(step, type) {
    var state = adStates[type];
    if (!state) return;
    showAd(state.current + step, type);
}
Object.keys(adStates).forEach(function(type) {
    var state = adStates[type];
    if (state.slides.length > 1) setInterval(function() { moveAd(1, type); }, 5000);
});
</script>
@endpush
