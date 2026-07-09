@extends('frontend.layouts.app')
@section('title', 'Caterers')
@section('nav-caterers', 'active')

@section('content')
<div class="page-header">
    <h1>Professional <span class="highlight">Caterers</span></h1>
    <p>Find expert catering services for your events, weddings, and corporate gatherings</p>
</div>

<section class="section" style="padding-top:0">
<div class="container">

    <!-- Premium Filter Bar -->
    <style>
    .responsive-filter-grid {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    @media(max-width: 991px) {
        .responsive-filter-grid {
            flex-direction: column;
            padding: 20px !important;
            gap: 16px;
        }
        .responsive-filter-grid > div {
            width: 100% !important;
        }
        .responsive-filter-grid button {
            width: 100% !important;
            justify-content: center;
        }
    }
    </style>

    <form method="GET" action="{{ route('frontend.caterers') }}" class="reveal" style="margin-bottom:30px;">
        <div class="glass-card responsive-filter-grid" style="padding:10px; border-radius:24px; border:1px solid rgba(255,255,255,0.4); backdrop-filter:blur(20px); box-shadow:0 25px 50px -12px rgba(0,0,0,0.15); background:rgba(255,255,255,0.8)">
            
            <!-- Search Field -->
            <div style="position:relative; flex:1">
                <i class="fas fa-search" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); color:var(--primary); font-size:1.1rem; pointer-events:none; z-index:2"></i>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search for professional caterers..." style="padding-left:52px; height:58px; border-radius:18px; background:rgba(0,0,0,0.03); border:1px solid rgba(0,0,0,0.05); font-size:1rem; width:100%; transition:0.3s; color:#1e1e2d; font-weight:500" onfocus="this.style.borderColor='var(--primary)'; this.style.background='#fff'; this.style.boxShadow='0 0 0 4px rgba(255,107,53,0.1)'" onblur="this.style.borderColor='rgba(0,0,0,0.05)'; this.style.background='rgba(0,0,0,0.03)'; this.style.boxShadow='none'">
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

            <!-- Search Button -->
            <button type="submit" class="btn btn-primary" style="height:58px; border-radius:18px; padding:0 30px; font-weight:800; display:flex; align-items:center; gap:10px; box-shadow:0 10px 25px -5px rgba(255, 107, 53, 0.4); border:none; background:var(--primary)">
                <i class="fas fa-sliders-h" style="font-size:1.1rem"></i> 
                <span>Filter</span>
            </button>
        </div>
    </form>
    
    @if($noAddress ?? false)
    <div class="info-box info-orange reveal" style="margin-bottom:20px; border-radius:14px; background:rgba(255,167,38,0.1); border:1px solid rgba(255,167,38,0.2); color:#ff9f1c">
        <i class="fas fa-exclamation-triangle"></i>
        <span>To show caterers near you, please <a href="{{ route('frontend.addresses') }}" style="color:#ff9f1c;font-weight:700;text-decoration:underline">set your primary address</a> first. Showing all caterers for now.</span>
    </div>
    @elseif($isProximityFiltered ?? false)
    <div class="info-box info-blue reveal" style="margin-bottom:20px; border-radius:14px">
        <i class="fas fa-location-dot"></i>
        <span>Showing caterers within <strong>20 km</strong> of your saved address. <a href="{{ route('frontend.addresses') }}" style="color:var(--primary);font-weight:700">Manage addresses</a></span>
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

    {{-- Caterer Ads Banner --}}
    @if(isset($catererAds) && $catererAds->isNotEmpty())
    
    @push('styles')
    <style>
    .ad-nav-btn-caterer {
        flex-shrink:0; background:var(--bg-card); border:1px solid var(--border-color); color:var(--text-primary);
        width:36px; height:36px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:0.2s;
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
    }
    .ad-nav-btn-caterer:hover { background:#9B0F06; color:#fff; border-color:#9B0F06; }
    </style>
    @endpush

    <div class="reveal" style="display:flex;align-items:center;gap:12px;margin-bottom:36px">
        @if($catererAds->count() > 1)
        <button class="ad-nav-btn-caterer" onclick="moveAd(-1, 'caterer')"><i class="fas fa-chevron-left"></i></button>
        @endif

        <div style="flex:1;position:relative;overflow:hidden;border-radius:16px;">
            @foreach($catererAds as $adIdx => $ad)
            @php
                $bgUrl = $ad->BackgroundImage ? asset('upload/ad_images/'.$ad->BackgroundImage) : asset('upload/website_assets/hero.png');
            @endphp
            <div class="ad-slide caterer-ad-slide" style="display:{{ $adIdx === 0 ? 'flex' : 'none' }};position:relative;border-radius:16px;min-height:220px;padding:30px;align-items:center;gap:20px;background-image:url('{{ $bgUrl }}');background-size:cover;background-position:center;box-shadow:0 8px 24px rgba(0,0,0,0.15);overflow:hidden;">
                <!-- Dark Overlay for Readability -->
                <div style="position:absolute;inset:0;background:linear-gradient(to right, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0.2) 100%);z-index:1;"></div>
                
                <div style="flex-shrink:0;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#9B0F06,var(--accent));display:flex;align-items:center;justify-content:center;font-size:1.6rem;box-shadow:0 6px 18px rgba(155,15,6,0.4);border:2px solid rgba(255,255,255,0.2);z-index:2;">🎪</div>
                
                <div style="flex:1;min-width:0;z-index:2;">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
                        <span style="background:#9B0F06;color:#fff;font-size:0.7rem;font-weight:800;padding:4px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:1px;box-shadow:0 2px 8px rgba(155,15,6,0.3)">Caterer Ads</span>
                        @if($ad->caterer)<span style="color:#e2e8f0;font-size:0.85rem;font-weight:600;text-shadow:0 1px 2px rgba(0,0,0,0.5);">by {{ $ad->caterer->FullName ?? $ad->caterer->BusinessName }}</span>@endif
                    </div>
                    <h4 style="font-size:1.6rem;font-weight:900;margin:0 0 6px;color:#fff;text-shadow:0 2px 4px rgba(0,0,0,0.6);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $ad->Title }}</h4>
                    @if($ad->Description)<p style="color:#cbd5e1;margin:0;font-size:1rem;line-height:1.5;max-width:80%;max-height:3em;overflow:hidden;text-overflow:ellipsis;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;text-shadow:0 1px 3px rgba(0,0,0,0.8);">{{ $ad->Description }}</p>@endif
                </div>
                
                @if($ad->caterer)
                <a href="{{ route('frontend.caterer', $ad->caterer->CatererID) }}" class="btn btn-primary" style="flex-shrink:0;border-radius:30px;font-size:1rem;font-weight:700;padding:12px 24px;white-space:nowrap;background:#9B0F06;border-color:#9B0F06;z-index:2;box-shadow:0 4px 12px rgba(155,15,6,0.4)">Visit Caterer <i class="fas fa-arrow-right ms-2"></i></a>
                @endif
            </div>
            @endforeach
            
            @if($catererAds->count() > 1)
            <div style="position:absolute;bottom:8px;left:0;right:0;display:flex;justify-content:center;gap:6px;">
                @foreach($catererAds as $adIdx => $ad)
                <button onclick="showAd({{ $adIdx }}, 'caterer')" id="catererAdDot-{{ $adIdx }}" style="width:6px;height:6px;border-radius:50%;border:none;cursor:pointer;background:{{ $adIdx === 0 ? '#9B0F06' : 'var(--border-color)' }};transition:all 0.3s;padding:0;"></button>
                @endforeach
            </div>
            @endif
        </div>

        @if($catererAds->count() > 1)
        <button class="ad-nav-btn-caterer" onclick="moveAd(1, 'caterer')"><i class="fas fa-chevron-right"></i></button>
        @endif
    </div>
    @endif

    {{-- Section Header --}}
    <div class="section-header reveal" style="text-align:left;margin-bottom:28px">
        <h2 style="font-size:1.6rem;display:flex;align-items:center;gap:12px">
            <span style="width:42px;height:42px;border-radius:14px;background:linear-gradient(135deg,#9B0F06,var(--accent));display:inline-flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0">
                <i class="fas fa-concierge-bell" style="color:#fff"></i>
            </span>
            Catering Providers
            <span style="font-size:0.85rem;font-weight:500;color:var(--text-muted);margin-left:4px">({{ count($caterers) }})</span>
        </h2>
    </div>

    @if(count($caterers) === 0)
        <div class="reveal" style="text-align:center;padding:80px 20px">
            <div style="font-size:4rem;margin-bottom:16px">🎪</div>
            <h3 style="color:var(--text-primary);margin-bottom:8px">{{ $area === 'nearby' ? 'No caterers near you' : 'No caterers found' }}</h3>
            <p style="color:var(--text-muted)">Try a different search term or <a href="{{ route('frontend.caterers', ['area' => '']) }}" style="color:var(--primary)">browse all caterers</a>.</p>
        </div>
    @else
        <div class="grid grid-3">
            @foreach($caterers as $c)
            @php
                $cImg = ($c->Image && file_exists(public_path('upload/admin_images/'.$c->Image)))
                    ? url('upload/admin_images/'.$c->Image)
                    : 'https://ui-avatars.com/api/?name='.urlencode($c->FullName).'&background=9b0f06&color=fff&bold=true';
            @endphp
            <div class="card reveal">
                <a href="{{ route('frontend.caterer', $c->CatererID) }}" style="text-decoration:none;">
                    <div class="card-img" style="background:linear-gradient(135deg,rgba(155,15,6,0.12),rgba(255,167,38,0.06));display:flex;align-items:center;justify-content:center;flex-direction:column;gap:10px;height:200px;transition:var(--transition-norm);"
                         onmouseover="this.style.background='linear-gradient(135deg,rgba(155,15,6,0.2),rgba(255,167,38,0.1))'"
                         onmouseout="this.style.background='linear-gradient(135deg,rgba(155,15,6,0.12),rgba(255,167,38,0.06))'">
                        <img src="{{ $cImg }}" style="width:80px;height:80px;border-radius:50%;border:3px solid #9B0F06;object-fit:cover;box-shadow:0 4px 20px rgba(155,15,6,0.35)" alt="{{ $c->FullName }}">
                        <h3 style="color:var(--text-primary);font-size:1rem;font-weight:700">{{ $c->BusinessName ?? $c->FullName }}</h3>
                        <span style="background:rgba(155,15,6,0.15);border:1px solid rgba(155,15,6,0.3);color:#9B0F06;padding:4px 12px;border-radius:20px;font-size:0.72rem;font-weight:700">
                            <i class="fas fa-concierge-bell" style="margin-right:4px"></i>Caterer
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="kitchen-rating" style="color:#9B0F06">
                            <i class="fas fa-star" style="color:var(--accent)"></i> {{ $c->average_rating }}
                            <span style="color:var(--text-muted);font-weight:400">({{ $c->review_count }} reviews)</span>
                        </div>
                        <p class="card-text">{{ $c->Description ?? 'Professional catering services for events, weddings, and corporate gatherings.' }}</p>
                        <div class="kitchen-meta">
                            @php $cStatus = $c->current_status; @endphp
                            <span class="badge-status badge-{{ strtolower($cStatus) }}">{{ $cStatus }}</span>
                            @if(isset($c->distance))
                                <span style="background:rgba(255,167,38,0.15); color:#e67e22; border:1px solid rgba(255,167,38,0.3); padding:4px 10px; border-radius:12px; font-size:0.75rem; font-weight:700">
                                    <i class="fas fa-map-marker-alt"></i> {{ $c->distance }} km
                                </span>
                            @endif
                            <span><i class="fas fa-location-dot"></i> {{ $c->Location ?? 'Egypt' }}</span>
                        </div>
                    </div>
                </a>
            </div>
            @endforeach
        </div>

        {{-- CTA Section --}}
        <div class="reveal" style="text-align:center;margin-top:60px;padding:40px 20px;background:var(--bg-card);border-radius:24px;border:1px solid var(--border-color)">
            <div style="font-size:2.5rem;margin-bottom:12px">🎉</div>
            <h3 style="color:var(--text-primary);margin-bottom:8px">Need a caterer for your event?</h3>
            <p style="color:var(--text-muted);margin-bottom:20px">Submit a catering request and let us match you with the perfect provider.</p>
            <a href="{{ route('frontend.catering') }}" class="btn btn-primary">
                <i class="fas fa-paper-plane" style="margin-right:8px"></i>Book a Caterer
            </a>
        </div>
    @endif

</div>
</section>
@endsection

@push('scripts')
<script>
var adStates = {
    caterer: { current: 0, slides: document.querySelectorAll('.caterer-ad-slide') }
};
function showAd(idx, type) {
    var state = adStates[type];
    if (!state || state.slides.length === 0) return;
    if (idx < 0) idx = state.slides.length - 1;
    if (idx >= state.slides.length) idx = 0;
    state.slides.forEach(function(s, i) {
        s.style.display = i === idx ? 'flex' : 'none';
        var dot = document.getElementById(type + 'AdDot-' + i);
        if (dot) dot.style.background = i === idx ? '#9B0F06' : 'var(--border-color)';
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

<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const userId = "{{ auth()->id() ?? 'guest' }}";
    if (!localStorage.getItem('bitehub_tour_caterers_' + userId)) {
        const driver = window.driver.js.driver;
        const driverObj = driver({
            showProgress: true,
            steps: [
                { element: '.filter-bar', popover: { title: '🔎 Search Caterers', description: 'Search for a caterer by name. Caterers specialise in events like weddings, parties, and corporate gatherings.', side: 'bottom', align: 'start' }},
                { element: '.grid.grid-3', popover: { title: '🍴 Browse Caterers', description: 'Each card shows a caterer profile. Click View Profile to see their menu and request a booking.', side: 'top', align: 'start' }}
            ],
            onDestroyStarted: () => { localStorage.setItem('bitehub_tour_caterers_' + userId, 'true'); driverObj.destroy(); },
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
