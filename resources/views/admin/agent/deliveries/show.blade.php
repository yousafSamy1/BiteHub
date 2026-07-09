@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="map-pin" class="me-2"></i>Delivery Details #{{ $order->OrderID }}</h4>
    <a href="{{ route('agent.deliveries') }}" class="btn btn-secondary btn-sm"><i data-feather="arrow-left" class="me-1" style="width:14px"></i> Back</a>
</div>

<div class="row">
    {{-- Info Panel --}}
    <div class="col-md-5 col-lg-4 grid-margin stretch-card">
        <div class="card w-100">
            <div class="card-body">
                @php
                    // Find address with coordinates if possible
                    $userAddresses = optional($order->customer->user)->addresses ?? collect();
                    $userAddress = $userAddresses->whereNotNull('Latitude')->whereNotNull('Longitude')->first() 
                                   ?? $userAddresses->where('IsPrimary', 1)->first() 
                                   ?? $userAddresses->first();
                    
                    $custLat = $userAddress->Latitude ?? null;
                    $custLng = $userAddress->Longitude ?? null;
                    $custAddrStr = $userAddress->Address ?? 'No Address Provided';
                @endphp

                <h6 class="card-title fw-bold">Customer Info</h6>
                <p class="mb-1 text-muted"><i data-feather="user" style="width:14px" class="me-1"></i> {{ optional($order->customer)->user->FullName ?? ($order->CustomerName ?? 'Customer') }}</p>
                <p class="mb-1 text-muted" style="font-size:0.85rem"><i data-feather="map-pin" style="width:14px" class="me-1"></i> {{ $custAddrStr }}</p>
                <p class="mb-2 text-muted"><i data-feather="phone" style="width:14px" class="me-1"></i> <a href="tel:{{ optional($order->customer)->user->phone->PhoneNumber ?? '' }}">{{ optional($order->customer)->user->phone->PhoneNumber ?? '—' }}</a></p>
                <div class="d-flex gap-2 mb-3">
                    @if($custLat && $custLng)
                        <button class="btn btn-xs btn-primary" onclick="focusOnLoc({{ $custLat }}, {{ $custLng }}, 'Customer Location')"><i data-feather="crosshair" style="width:12px" class="me-1"></i> Focus</button>
                        <button class="btn btn-xs btn-outline-danger" onclick="openInGmaps({{ $custLat }}, {{ $custLng }})"><i data-feather="map" style="width:12px" class="me-1"></i> Maps</button>
                    @else
                        <button class="btn btn-xs btn-secondary disabled" title="Exact location not set"><i data-feather="slash" style="width:12px" class="me-1"></i> No GPS Data</button>
                    @endif
                </div>

                <h6 class="card-title fw-bold border-top pt-3 mt-3">Pickup Information</h6>
                @php
                    $vendor = $order->kitchenOwner ?? $order->caterer;
                    $vendorName = $vendor ? ($vendor->KitchenName ?? $vendor->BusinessName) : 'Unknown Vendor';
                    $vendorPhone = $vendor && $vendor->user && $vendor->user->phone ? $vendor->user->phone->PhoneNumber : '—';
                    $vendorLocation = $vendor->Location ?? '—';
                    $vLat = $vendor->Latitude ?? null;
                    $vLng = $vendor->Longitude ?? null;
                @endphp
                <p class="mb-1 text-primary fw-bold"><i data-feather="home" style="width:14px" class="me-1"></i> {{ $vendorName }}</p>
                <p class="mb-1 text-muted"><i data-feather="map-pin" style="width:14px" class="me-1"></i> {{ $vendorLocation }}</p>
                <p class="mb-2 text-muted"><i data-feather="phone" style="width:14px" class="me-1"></i> <a href="tel:{{ $vendorPhone }}">{{ $vendorPhone }}</a></p>
                <div class="d-flex gap-2">
                    @if($vLat && $vLng)
                        <button class="btn btn-xs btn-primary" onclick="focusOnLoc({{ $vLat }}, {{ $vLng }}, 'Pickup: {{ addslashes($vendorName) }}')"><i data-feather="crosshair" style="width:12px" class="me-1"></i> Focus</button>
                        <button class="btn btn-xs btn-outline-danger" onclick="openInGmaps({{ $vLat }}, {{ $vLng }})"><i data-feather="map" style="width:12px" class="me-1"></i> Maps</button>
                    @else
                        <button class="btn btn-xs btn-secondary disabled"><i data-feather="slash" style="width:12px" class="me-1"></i> No Location</button>
                    @endif
                </div>
                
                <h6 class="card-title fw-bold border-top pt-3 mt-3">Order Specs</h6>
                <p class="mb-1 d-flex justify-content-between text-muted">
                    <span>Payment Method:</span> 
                    <span class="badge bg-secondary">{{ optional($order->payment)->Method ?? $order->PaymentMethod }}</span>
                </p>
                <p class="mb-1 d-flex justify-content-between text-muted">
                    <span>Total Amount:</span> 
                    <span class="fw-bold text-primary">{{ number_format($order->TotalPrice, 2) }} EGP</span>
                </p>
                <p class="mb-1 d-flex justify-content-between text-muted">
                    <span>Current Status:</span> 
                    @php $sc=['Pending'=>'warning','Confirmed'=>'info','Preparing'=>'primary','Ready'=>'success','Delivering'=>'secondary','Delivered'=>'success','Cancelled'=>'danger']; @endphp
                    <span class="badge bg-{{ $sc[$order->OrderStatus] ?? 'secondary' }}">{{ $order->OrderStatus }}</span>
                </p>

                <h6 class="card-title fw-bold border-top pt-3 mt-3">Items list</h6>
                <div class="table-responsive">
                    <table class="table table-sm text-muted">
                        <tbody>
                            @foreach($order->menuItems as $item)
                            <tr>
                                <td>{{ $item->ItemName }}</td>
                                <td class="text-end">x{{ $item->pivot->Quantity ?? 1 }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Map Panel --}}
    <div class="col-md-7 col-lg-8 grid-margin stretch-card">
        <div class="card w-100">
            <div class="card-header bg-transparent border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="card-title fw-bold mb-0">Live Navigation Route</h6>
                    <p class="text-muted" style="font-size:0.8rem" id="mapStatusText">Waiting for system...</p>
                </div>
                <button class="btn btn-xs btn-outline-secondary" onclick="startMapNow()"><i data-feather="refresh-cw" style="width:12px" class="me-1"></i> Refresh Map</button>
            </div>
            <div class="card-body p-2 p-md-3">
                <div id="deliveryMap" style="width: 100%; height: 550px; border-radius:10px; background:#e9ecef; z-index:1;"></div>
            </div>
        </div>
    </div>
</div>
</div>

{{-- Scripts and Styles --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.css" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@latest/dist/leaflet-routing-machine.js"></script>

<script>
    var customerLat = @json($custLat);
    var customerLng = @json($custLng);
    var pickupLat   = @json($vLat);
    var pickupLng   = @json($vLng);
    var pickupName  = @json($vendorName);
    window.deliveryMapObj = null;

    function startMapNow() {
        const st = document.getElementById('mapStatusText');
        if (st) st.innerText = "Initializing (v9)...";

        try {
            if (typeof L === 'undefined') {
                if (st) st.innerText = "Leaflet load failed.";
                return;
            }

            if (window.deliveryMapObj) {
                try { window.deliveryMapObj.remove(); } catch(e) {}
                window.deliveryMapObj = null;
            }

            window.deliveryMapObj = L.map('deliveryMap').setView([30.0444, 31.2357], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(window.deliveryMapObj);

            setTimeout(() => { 
                if (window.deliveryMapObj) window.deliveryMapObj.invalidateSize(); 
            }, 500);

            // Add Markers
            if (customerLat && customerLng) {
                const destIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41], iconAnchor: [12, 41]
                });
                L.marker([customerLat, customerLng], {icon: destIcon}).addTo(window.deliveryMapObj).bindPopup("Customer Location").openPopup();
            }

            if (pickupLat && pickupLng) {
                const pickupIcon = L.icon({
                    iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
                    shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                    iconSize: [25, 41], iconAnchor: [12, 41]
                });
                L.marker([pickupLat, pickupLng], {icon: pickupIcon}).addTo(window.deliveryMapObj).bindPopup("Pickup: " + pickupName);
            }

            let pts = [];
            if (customerLat && customerLng) pts.push([customerLat, customerLng]);
            if (pickupLat && pickupLng) pts.push([pickupLat, pickupLng]);
            if (pts.length > 0) window.deliveryMapObj.fitBounds(pts, {padding: [50, 50]});

            if (navigator.geolocation) {
                if (st) st.innerText = "Locating GPS...";
                navigator.geolocation.getCurrentPosition(function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    if (st) st.innerText = "GPS Fixed. Generating Route...";

                    if (customerLat && typeof L.Routing !== 'undefined') {
                        window.deliveryMapObj.eachLayer((l) => { if (l instanceof L.Marker) window.deliveryMapObj.removeLayer(l); });

                        let wps = [L.latLng(lat, lng)];
                        if (pickupLat && pickupLng) wps.push(L.latLng(pickupLat, pickupLng));
                        wps.push(L.latLng(customerLat, customerLng));

                        L.Routing.control({
                            waypoints: wps,
                            routeWhileDragging: false,
                            fitSelectedRoutes: true,
                            createMarker: function(i, wp, n) {
                                let col = i === 0 ? 'blue' : (i === n-1 ? 'red' : 'orange');
                                return L.marker(wp.latLng, {
                                    icon: L.icon({
                                        iconUrl: `https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-${col}.png`,
                                        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
                                        iconSize: [25, 41], iconAnchor: [12, 41]
                                    })
                                });
                            }
                        }).addTo(window.deliveryMapObj);
                        if (st) st.innerText = "Tracking Live.";
                    } else {
                        L.marker([lat, lng]).addTo(window.deliveryMapObj).bindPopup("Your Position").openPopup();
                        if (st) st.innerText = "GPS Active.";
                    }
                }, function() {
                    if (st) st.innerText = "GPS Access Denied.";
                });
            }
        } catch (e) {
            if (st) st.innerText = "Error: " + e.message;
        }
    }

    function focusOnLoc(lat, lng, label) {
        if (!lat || !lng || !window.deliveryMapObj) {
            alert("Map or location not ready.");
            return;
        }
        window.deliveryMapObj.setView([lat, lng], 18);
        window.deliveryMapObj.invalidateSize();
        L.popup().setLatLng([lat, lng]).setContent(label).openOn(window.deliveryMapObj);
    }

    function openInGmaps(lat, lng) {
        if (!lat || !lng) return;
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}`, '_blank');
    }

    window.addEventListener('load', startMapNow);
    if (document.readyState === 'complete') startMapNow();
</script>
@endsection
