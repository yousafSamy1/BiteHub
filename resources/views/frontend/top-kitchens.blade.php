@extends('frontend.layouts.app')
@section('title', 'Top 10 Near You')
@section('nav-top', 'active')

@section('content')
<div class="page-header">
    <h1>Top 10 Near <span class="highlight">You</span></h1>
    <p>The best-rated local kitchens within 20km of your location</p>
</div>

<section class="section" style="padding-top:0">
<div class="container" style="max-width:800px">

    @foreach($topKitchens as $i => $k)
    @php
        $rankClass = $i===0?'#FFD700':($i===1?'#C0C0C0':($i===2?'#CD7F32':'var(--text-muted)'));
        $rankBg    = $i===0?'rgba(255,215,0,0.12)':($i===1?'rgba(192,192,192,0.10)':($i===2?'rgba(205,127,50,0.10)':'rgba(255,255,255,0.03)'));
        $medal     = $i===0?'🥇':($i===1?'🥈':($i===2?'🥉':''));
        $rating = $k->average_rating;
    @endphp
    <a href="{{ route('frontend.kitchen', $k->KitchenOwnerID) }}" class="glass-card reveal top-rank-card" style="margin-bottom:14px;display:flex;align-items:center;gap:24px;text-decoration:none;color:inherit;padding:24px;background:{{ $rankBg }};border-color:{{ $i<3 ? $rankClass.'44' : 'var(--border-color)' }};transition:var(--transition);position:relative;overflow:hidden">
        <!-- Background Thematic -->
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
            
            $rawProfile = $k->photo ?? $k->Image ?? null;
            $profileUrl = (!empty($rawProfile) && !str_contains($rawProfile, 'no_image') && file_exists(public_path('upload/admin_images/'.$rawProfile))) ? asset('upload/admin_images/'.$rawProfile) : asset('upload/website_assets/'.$kImg);
        @endphp
        <img src="{{ $profileUrl }}" style="position:absolute;top:0;right:0;width:40%;height:100%;object-fit:cover;opacity:0.15;z-index:0;filter:blur(10px);pointer-events:none">
        
        <!-- Rank -->
        <div style="font-size:{{ $i<3?'2.2rem':'1.4rem' }};width:60px;text-align:center;flex-shrink:0;z-index:1">
            @if($i < 3){{ $medal }}@else
            <span style="font-family:var(--font-heading);font-weight:900;color:{{ $rankClass }};font-size:1.6rem">#{{ $i+1 }}</span>
            @endif
        </div>
        <!-- Avatar -->
        <img src="{{ $profileUrl }}" style="width:72px;height:72px;border-radius:50%;border:3px solid {{ $rankClass }};object-fit:cover;box-shadow:0 8px 24px rgba(0,0,0,0.4);flex-shrink:0;z-index:1" alt="{{ $k->KitchenName }}">
        <!-- Info -->
        <div style="flex:1;min-width:0;z-index:1">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                <h3 style="font-size:1.2rem;margin:0;font-weight:800">{{ $k->KitchenName }}</h3>
                @if($k->VerifyStatus === 'Verified')
                <i class="fas fa-circle-check" style="color:var(--success);font-size:1rem"></i>
                @endif
            </div>
            <p style="color:var(--text-muted);font-size:0.9rem;margin:6px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;opacity:0.8">{{ Str::limit($k->Description ?? 'Amazing homemade food', 75) }}</p>
        </div>
        <!-- Score -->
        <div style="text-align:right;flex-shrink:0;z-index:1">
            <div style="font-family:var(--font-heading);font-weight:900;font-size:1.2rem;color:var(--accent);display:flex;align-items:center;gap:6px;justify-content:flex-end">
                <i class="fas fa-star" style="font-size:0.9rem;color:#FFD700"></i> {{ $rating }}
            </div>
            <div style="color:var(--text-muted);font-size:0.85rem;margin-top:4px"><i class="fas fa-bag-shopping" style="font-size:0.8rem"></i> {{ $k->totalOrders ?? '100+' }} orders</div>
        </div>
        <i class="fas fa-chevron-right" style="color:var(--text-muted);flex-shrink:0;z-index:1;opacity:0.5"></i>
    </a>
    @endforeach

    @if($noAddress)
    <div class="glass-card text-center reveal" style="padding:60px">
        <div style="font-size:4rem;margin-bottom:16px">📍</div>
        <h3>Where are you?</h3>
        <p style="color:var(--text-muted);margin-top:8px">Please set your primary address to see the best kitchens in your area.</p>
        <a href="{{ route('frontend.profile') }}#addresses" class="btn btn-primary" style="margin-top:24px">Set My Location</a>
    </div>
    @elseif(empty($topKitchens) || (is_countable($topKitchens) && count($topKitchens) === 0))
    <div class="glass-card text-center reveal" style="padding:60px">
        <div style="font-size:4rem;margin-bottom:16px">🍽️</div>
        <h3>No local rankings yet</h3>
        <p style="color:var(--text-muted);margin-top:8px">We couldn't find any kitchens within 20km with enough orders to rank. Try browsing all kitchens!</p>
        <a href="{{ route('frontend.browse') }}" class="btn btn-primary" style="margin-top:24px">Explore More Kitchens</a>
    </div>
    @endif

</div>
</section>
@endsection
