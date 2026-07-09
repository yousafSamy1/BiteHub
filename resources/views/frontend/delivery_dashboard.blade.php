@extends('frontend.layouts.app')
@section('title', 'Delivery Dashboard')

@section('content')
<div style="padding:calc(var(--nav-h) + 36px) 0 60px">
<div class="container">

    <!-- Header -->
    <div class="glass-card reveal" style="padding:32px 36px;margin-bottom:28px;background:linear-gradient(160deg,rgba(96,165,250,0.07) 0%,rgba(74,222,128,0.03) 100%);border-color:rgba(96,165,250,0.18)">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#3b82f6,#06b6d4);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;flex-shrink:0;box-shadow:0 6px 24px rgba(59,130,246,0.35)">
                {{ strtoupper(substr(auth()->user()->FullName ?? 'D', 0, 1)) }}
            </div>
            <div>
                <h2 style="margin-bottom:4px;font-size:1.6rem;letter-spacing:-0.5px">{{ auth()->user()->FullName }}</h2>
                <p style="color:var(--text-muted);font-size:0.9rem;margin:0">{{ auth()->user()->Email }}</p>
                <span style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:5px 14px;background:rgba(96,165,250,0.12);border:1px solid rgba(96,165,250,0.25);border-radius:20px;font-size:0.78rem;font-weight:700;color:#60a5fa">
                    <i class="fas fa-motorcycle"></i> Delivery Agent
                </span>
            </div>
            <!-- Online status toggle -->
            <div style="margin-left:auto">
                <div style="display:flex;align-items:center;gap:10px;background:rgba(74,222,128,0.1);border:1px solid rgba(74,222,128,0.25);border-radius:30px;padding:10px 18px">
                    <span style="width:9px;height:9px;border-radius:50%;background:var(--success);box-shadow:0 0 8px rgba(74,222,128,0.6);animation:bounceDot 1.5s infinite"></span>
                    <span style="color:var(--success);font-weight:700;font-size:0.9rem">Online</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px">
        @foreach([
            ['value'=>'0','label'=>'Deliveries Today','icon'=>'fa-truck','color'=>'#60a5fa'],
            ['value'=>'0.00 EGP','label'=>"Today's Earnings",'icon'=>'fa-money-bill-wave','color'=>'var(--accent)'],
            ['value'=>'0','label'=>'Total Deliveries','icon'=>'fa-flag-checkered','color'=>'var(--success)'],
            ['value'=>'4.8','label'=>'Rating','icon'=>'fa-star','color'=>'#fbbf24'],
        ] as $stat)
        <div class="glass-card reveal" style="padding:20px;display:flex;align-items:center;gap:14px">
            <div style="width:46px;height:46px;border-radius:14px;background:{{ $stat['color'] }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};font-size:1.2rem"></i>
            </div>
            <div>
                <div style="font-size:1.4rem;font-weight:800;letter-spacing:-0.5px;color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div style="color:var(--text-muted);font-size:0.78rem">{{ $stat['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Action Cards -->
    <h3 style="margin-bottom:18px;font-size:1.1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600">Manage</h3>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:20px;margin-bottom:40px;">
        @foreach([
            ['icon'=>'🚲','label'=>'Active Deliveries','sub'=>'Current assignments','color'=>'#60a5fa', 'link'=>route('agent.deliveries')],
            ['icon'=>'📊','label'=>'Earnings','sub'=>"Today's revenue overview",'color'=>'var(--accent)', 'link'=>'#'],
            ['icon'=>'🏆','label'=>'History','sub'=>'Past deliveries & stats','color'=>'var(--success)', 'link'=>'#'],
        ] as $item)
        <a href="{{ $item['link'] }}" style="text-decoration:none; color:inherit;">
            <div class="glass-card action-card reveal" style="padding:28px 24px;cursor:pointer">
                <div class="action-icon" style="background:{{ $item['color'] }}18;border:1px solid {{ $item['color'] }}28;font-size:1.8rem;width:60px;height:60px">{{ $item['icon'] }}</div>
                <div style="font-weight:700;font-size:1rem;margin-top:4px">{{ $item['label'] }}</div>
                <div style="color:var(--text-muted);font-size:0.82rem;margin-top:4px">{{ $item['sub'] }}</div>
            </div>
        </a>
        @endforeach
    </div>

    <!-- Service Area Map -->
    <h3 style="margin-bottom:18px;font-size:1.1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600">My Service Area</h3>
    <div class="glass-card reveal" style="padding:24px;">
        <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:16px;">
            Set your primary location. Orders within a 25km radius will be assigned to you when available.
        </p>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
        <link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
        <div id="agentMap" style="height: 350px; width: 100%; border-radius: 12px; border: 1px solid var(--border-color); margin-bottom: 20px; z-index: 1;"></div>
        
        <form action="{{ route('agent.update.location') }}" method="POST">
            @csrf
            <input type="hidden" name="lat" id="agentLat" value="{{ auth()->user()->deliveryAgent->Latitude ?? '' }}">
            <input type="hidden" name="lng" id="agentLng" value="{{ auth()->user()->deliveryAgent->Longitude ?? '' }}">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save My Service Area</button>
        </form>
    </div>

</div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var latInput = document.getElementById('agentLat');
    var lngInput = document.getElementById('agentLng');
    
    var defaultLat = latInput.value ? parseFloat(latInput.value) : 30.0444; // Fallback Cairo
    var defaultLng = lngInput.value ? parseFloat(lngInput.value) : 31.2357;
    
    var map = L.map('agentMap').setView([defaultLat, defaultLng], 12);

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
        attribution: '&copy; OpenStreetMap contributors &copy; CARTO'
    }).addTo(map);

    var marker;
    var circle;

    // Add Search Control
    var geocoder = L.Control.geocoder({
        defaultMarkGeocode: false,
        placeholder: 'Search for your service area...',
    }).on('markgeocode', function(e) {
        var bbox = e.geocode.bbox;
        var center = e.geocode.center;
        
        map.fitBounds(bbox);
        
        if (marker) map.removeLayer(marker);
        if (circle) map.removeLayer(circle);
        
        marker = L.marker(center).addTo(map);
        circle = L.circle(center, {
            color: 'var(--primary)',
            fillColor: 'var(--primary)',
            fillOpacity: 0.1,
            radius: 25000 // 25km
        }).addTo(map);
        
        latInput.value = center.lat;
        lngInput.value = center.lng;
    }).addTo(map);

    // Put initial marker if exist
    if(latInput.value && lngInput.value) {
        marker = L.marker([defaultLat, defaultLng]).addTo(map);
        
        // Draw 25km radius circle
        circle = L.circle([defaultLat, defaultLng], {
            color: 'var(--primary)',
            fillColor: 'var(--primary)',
            fillOpacity: 0.1,
            radius: 25000 // 25km in meters
        }).addTo(map);
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            defaultLat = position.coords.latitude;
            defaultLng = position.coords.longitude;
            map.flyTo([defaultLat, defaultLng], 14);
        });
    }

    map.on('click', function(e) {
        if(marker) map.removeLayer(marker);
        if(circle) map.removeLayer(circle);
        
        marker = L.marker(e.latlng).addTo(map);
        circle = L.circle(e.latlng, {
            color: 'var(--primary)',
            fillColor: 'var(--primary)',
            fillOpacity: 0.1,
            radius: 25000 // 25km
        }).addTo(map);
        
        latInput.value = e.latlng.lat;
        lngInput.value = e.latlng.lng;
    });
});
</script>
@endsection
