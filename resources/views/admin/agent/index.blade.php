@extends('admin.admin_dashboard')

@section('admin')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<div class="page-content">

    <style>
        /* Premium Dark Theme Dashboard - Consolidated */
        .dark-card {
            background-color: #1e293b;
            border-radius: 16px;
            border: 1px solid rgba(255, 255, 255, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2), 0 2px 4px -1px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            color: #f8fafc;
        }
        .dark-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
        }

        .heading-main { color: #f8fafc; font-weight: 700; letter-spacing: -0.025em; }
        .text-custom-muted { color: #94a3b8; font-weight: 500; }

        .banner-card {
            background: linear-gradient(135deg, #1d4ed8 0%, #312e81 100%);
            border-radius: 16px;
            padding: 30px 40px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 25px rgba(29, 78, 216, 0.3);
        }
        .banner-card::after {
            content: ''; position: absolute; top: -50%; right: -10%; width: 300px; height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);
            border-radius: 50%; pointer-events: none;
        }

        .icon-box-dark {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .icon-primary { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .icon-success { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .icon-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .icon-info    { background: rgba(6, 182, 212, 0.15); color: #22d3ee; }

        .stat-title { color: #94a3b8; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .stat-value { color: #f8fafc; font-weight: 800; font-size: 1.7rem; letter-spacing: -0.025em; margin-bottom: 0; }

        .btn-custom-primary { background-color: #3b82f6; color: #ffffff; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.2s; }
        .btn-custom-primary:hover { background-color: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        .btn-custom-light { background-color: rgba(255, 255, 255, 0.05); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.2s; }
        .btn-custom-light:hover { background-color: rgba(255, 255, 255, 0.1); color: #ffffff; border-color: rgba(255, 255, 255, 0.2); }
    </style>

    <!-- Header & Actions -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4" style="gap: 15px;">
        <div>
            <h3 class="heading-main mb-1">Agent Dashboard</h3>
            <p class="text-custom-muted mb-0">Rider Overview & Task Analytics</p>
        </div>
        <div class="d-flex align-items-center gap-3 mt-3 mt-md-0">
            <span class="badge border border-primary text-primary px-3 py-2" style="font-weight: 700; background: rgba(59, 130, 246, 0.05); border-radius: 8px;">DELIVERY AGENT</span>
            
            @if($agent)
                <div class="d-flex align-items-center bg-dark-subtle px-3 py-2" style="border-radius: 12px; border: 1px solid rgba(255,255,255,0.05);">
                    <div class="form-check form-switch p-0 m-0 d-flex align-items-center gap-2">
                        <label class="form-check-label fw-bold me-2" for="statusToggle" id="statusLabel" style="color: {{ $agent->Status === 'Available' ? '#34d399' : '#94a3b8' }}; font-size: 0.85rem;">
                            {{ $agent->Status === 'Available' ? 'ONLINE' : 'OFFLINE' }}
                        </label>
                        <input class="form-check-input ms-0" type="checkbox" id="statusToggle" style="width: 40px; height: 20px; cursor: pointer;" {{ $agent->Status === 'Available' ? 'checked' : '' }} onchange="toggleAgentStatus()">
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="banner-card d-flex justify-content-between align-items-center">
                <div style="z-index: 1;">
                    <h2 class="fw-bold mb-2 text-white">Welcome, {{ Auth::user()->FullName }} 👋🚴‍♂️</h2>
                    <p class="mb-0 text-white-50" style="font-size: 1.05rem;">You have <strong class="text-white">{{ $pendingDeliveries }} pending tasks</strong>. Drive safely!</p>
                </div>
                <div class="d-none d-md-block" style="z-index: 1;">
                    <button class="btn btn-light rounded-pill px-4 py-2 fw-bolder text-primary shadow" onclick="window.location.href='{{ route('agent.deliveries') }}'">View Deliveries</button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title">Online Profit</p>
                        <h4 class="stat-value text-success">{{ number_format(Auth::user()->Wallet_balance ?? 0, 2) }} <small class="text-custom-muted fw-normal fs-6">EGP</small></h4>
                        <div class="mt-1 d-flex align-items-center" style="font-size: 0.8rem; color: #34d399; font-weight: 600;">
                            <i class="fas fa-coins me-1 opacity-75" style="font-size: 0.7rem;"></i>
                            Today: {{ number_format($todayEarnings, 2) }} EGP
                        </div>
                    </div>
                    <div class="icon-box-dark icon-success"><i data-feather="dollar-sign" style="width: 22px;"></i></div>
                </div>
                <div class="mt-auto d-flex justify-content-between align-items-center gap-1">
                    <span class="text-custom-muted d-none d-sm-inline" style="font-size:0.75rem"><i data-feather="trending-up" class="me-1" style="width:12px"></i> Earnings (Card)</span>
                    <div class="d-flex gap-1">
                        @if((Auth::user()->cash_to_settle ?? 0) > 0)
                            <button type="button" class="btn btn-xs btn-outline-secondary py-1 px-2" disabled data-bs-toggle="tooltip" title="Pay debt first" style="border-radius: 6px; font-size: 0.7rem; font-weight: 700; cursor: not-allowed;">
                                Withdraw
                            </button>
                        @else
                            <button type="button" class="btn btn-xs btn-outline-success py-1 px-2" data-bs-toggle="modal" data-bs-target="#withdrawModal" style="border-radius: 6px; font-size: 0.7rem; font-weight: 700;">
                                Withdraw
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3 mb-xl-0">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title">Cash to Settle</p>
                        <h4 class="stat-value text-warning">{{ number_format(Auth::user()->cash_to_settle ?? 0, 2) }} <small class="text-custom-muted fw-normal fs-6">EGP</small></h4>
                    </div>
                    <div class="icon-box-dark icon-warning"><i data-feather="briefcase" style="width: 22px;"></i></div>
                </div>
                <div class="mt-auto d-flex justify-content-between align-items-center">
                    <span class="text-custom-muted" style="font-size:0.85rem"><i class="fas fa-exclamation-triangle me-1 text-warning"></i> Due to Company</span>
                    <button type="button" class="btn btn-xs btn-warning py-1 px-2 border-0 shadow-sm" data-bs-toggle="modal" data-bs-target="#settleDebtModal" style="border-radius: 6px; font-size: 0.7rem; font-weight: 700; color: #000;">
                        Settle Debt
                    </button>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3 mb-md-0">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div>
                        <p class="stat-title">Daily Milestone</p>
                        <h4 class="stat-value text-primary">{{ $todayCompleted }} / 11</h4>
                    </div>
                    <div class="icon-box-dark icon-primary"><i data-feather="target" style="width: 22px;"></i></div>
                </div>
                
                 @php 
                    $percent = min(($todayCompleted / 11) * 100, 100);
                    $color = $todayCompleted >= 11 ? 'success' : 'primary';
                @endphp
                <div class="progress mb-2" style="height: 6px; background-color: rgba(255,255,255,0.05);">
                    <div class="progress-bar bg-{{ $color }}" role="progressbar" style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-auto">
                    <span class="text-custom-muted" style="font-size:0.75rem">
                        @if($todayCompleted >= 11)
                            <span class="text-success"><i class="fas fa-gift me-1"></i> Bonus Earned!</span>
                        @else
                            {{ 11 - $todayCompleted }} more for bonus
                        @endif
                    </span>
                    <span class="text-custom-muted" style="font-size:0.75rem">Total: {{ $completedDeliveries }}</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title">Pending Tasks</p>
                        <h4 class="stat-value">{{ $pendingDeliveries }}</h4>
                    </div>
                    <div class="icon-box-dark icon-warning"><i data-feather="clock" style="width: 22px;"></i></div>
                </div>
                <div class="mt-auto">
                    <span class="badge bg-warning text-dark px-2 py-1" style="font-size:0.65rem">Action Required</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Section -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5">Delivery Performance</h4>
                    <button class="btn-custom-light btn-sm py-1 px-3 fs-6"><i data-feather="bar-chart-2" style="width:14px"></i> Monthly Summary</button>
                </div>
                <div id="realMonthlySalesChart" style="height: 320px; width: 100%;"></div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="dark-card card p-4">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-secondary text-muted"><i data-feather="search" style="width:14px"></i></span>
                        <input type="text" id="mapSearchInput" class="form-control bg-transparent text-white border-secondary fs-6" placeholder="Search for area (e.g. Maadi)..." onkeypress="if(event.key==='Enter') searchAddress()">
                        <button class="btn btn-outline-secondary" type="button" onclick="searchAddress()" title="Search location"><i class="fas fa-search"></i></button>
                    </div>
                </div>

                <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
                <div style="position: relative;">
                    <div id="agentMap" style="height: 250px; width: 100%; border-radius: 12px; border: 1px solid rgba(255,255,255,0.05); margin-bottom: 20px; z-index: 1; overflow: hidden;"></div>
                    <button type="button" onclick="locateMe()" class="btn btn-primary btn-sm rounded-circle d-flex align-items-center justify-content-center" style="position: absolute; bottom: 35px; right: 15px; width: 40px; height: 40px; z-index: 1000; box-shadow: 0 4px 12px rgba(0,0,0,0.3); border: 2px solid rgba(255,255,255,0.2);" title="Find my location">
                        <i class="fas fa-location-crosshairs"></i>
                    </button>
                </div>
                
                <form action="{{ route('agent.update.location') }}" method="POST">
                    @csrf
                    <div class="input-group mb-2">
                        <span class="input-group-text bg-transparent border-secondary text-muted"><i data-feather="map-pin" style="width:14px"></i></span>
                        <input type="text" id="agentAreaInput" name="service_area" class="form-control bg-transparent text-white border-secondary fs-6" value="{{ auth()->user()->deliveryAgent->ServiceArea ?? '' }}" placeholder="Detected address..." required readonly>
                        <span id="mapLoading" class="input-group-text bg-transparent border-secondary text-muted d-none"><i class="fas fa-spinner fa-spin"></i></span>
                    </div>
                    <input type="hidden" name="lat" id="agentLat" value="{{ auth()->user()->deliveryAgent->Latitude ?? '' }}">
                    <input type="hidden" name="lng" id="agentLng" value="{{ auth()->user()->deliveryAgent->Longitude ?? '' }}">
                    <button type="submit" class="btn-custom-primary btn-block w-100 mt-2">
                        Update Location
                    </button>
                </form>
            </div>
        </div>
    <!-- Recent Deliveries Table -->
    <div class="row">
        <div class="col-12">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h4 class="heading-main mb-1 fs-5">Recent Deliveries</h4>
                        <p class="text-custom-muted mb-0 small">Your latest activities and order status</p>
                    </div>
                    <a href="{{ route('agent.deliveries') }}" class="btn btn-link text-primary p-0 fw-bold text-decoration-none">View All <i class="fas fa-arrow-right ms-1"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="color: #f8fafc;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                <th class="border-0 px-2 py-3 text-custom-muted small fw-bold">ORDER ID</th>
                                <th class="border-0 px-2 py-3 text-custom-muted small fw-bold">CUSTOMER</th>
                                <th class="border-0 px-2 py-3 text-custom-muted small fw-bold">METHOD</th>
                                <th class="border-0 px-2 py-3 text-custom-muted small fw-bold">TOTAL</th>
                                <th class="border-0 px-2 py-3 text-custom-muted small fw-bold">STATUS</th>
                                <th class="border-0 px-2 py-3 text-custom-muted small fw-bold">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $order)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;">
                                    <td class="px-2 py-3 align-middle">
                                        <span class="fw-bold">#{{ $order->OrderID }}</span>
                                        <br>
                                        <small class="text-custom-muted">{{ \Carbon\Carbon::parse($order->CreatedAt)->format('h:i A') }}</small>
                                    </td>
                                    <td class="px-2 py-3 align-middle">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; color: #60a5fa;">
                                                {{ strtoupper(substr($order->customer->user->FullName ?? 'C', 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold small">{{ $order->customer->user->FullName ?? 'Guest' }}</div>
                                                <small class="text-custom-muted">{{ $order->customer->user->phone->PhoneNumber ?? 'No Phone' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3 align-middle">
                                        <span class="badge bg-{{ strtolower($order->payment->Method ?? 'cash') === 'cash' ? 'warning' : 'info' }}-subtle text-{{ strtolower($order->payment->Method ?? 'cash') === 'cash' ? 'warning' : 'info' }} rounded-pill px-2">
                                            {{ $order->payment->Method ?? 'Cash' }}
                                        </span>
                                    </td>
                                    <td class="px-2 py-3 align-middle">
                                        <span class="fw-bold text-success">{{ number_format($order->TotalPrice, 2) }}</span>
                                        <small class="text-custom-muted">EGP</small>
                                    </td>
                                    <td class="px-2 py-3 align-middle">
                                        @php
                                            $statusColors = [
                                                'Pending' => 'secondary',
                                                'Confirmed' => 'primary',
                                                'Preparing' => 'info',
                                                'Ready' => 'warning',
                                                'Delivering' => 'info',
                                                'Delivered' => 'success',
                                                'Cancelled' => 'danger'
                                            ];
                                            $color = $statusColors[$order->OrderStatus] ?? 'secondary';
                                        @endphp
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="spinner-grow spinner-grow-sm text-{{ $color }}" role="status" style="width: 8px; height: 8px; {{ $order->OrderStatus === 'Delivered' || $order->OrderStatus === 'Cancelled' ? 'display: none;' : '' }}"></span>
                                            <span class="fw-bold text-{{ $color }} small text-uppercase">{{ $order->OrderStatus }}</span>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3 align-middle text-end">
                                        <a href="{{ route('agent.delivery.details', $order->OrderID) }}" class="btn btn-sm btn-icon btn-custom-light rounded-circle">
                                            <i data-feather="eye" style="width: 14px;"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-custom-muted">
                                            <i data-feather="package" style="width: 48px; height: 48px; opacity: 0.2;" class="mb-3"></i>
                                            <p>No recent deliveries found</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @include('partials.withdraw-modal')
    @include('partials.topup-modal')
</div>

@push('custom-scripts')
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    function toggleAgentStatus() {
        const toggle = document.getElementById('statusToggle');
        const label = document.getElementById('statusLabel');
        
        fetch("{{ route('agent.update.status') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const isOnline = data.new_status === 'Available';
                label.innerText = isOnline ? 'ONLINE' : 'OFFLINE';
                label.style.color = isOnline ? '#34d399' : '#94a3b8';
                
                if (typeof showToast === 'function') {
                    showToast('Status updated to ' + data.new_status, 'success');
                } else if (window.toastr) {
                     toastr.success('Status updated to ' + data.new_status);
                }
            } else {
                toggle.checked = !toggle.checked; // Revert
                alert(data.message || 'Error updating status');
            }
        })
        .catch(err => {
            toggle.checked = !toggle.checked; // Revert
            console.error('Status Update Error:', err);
            alert('Connection error. Please try again.');
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        var el = document.querySelector("#realMonthlySalesChart");
        if(!el) return;

        var colors = { primary: "#6571ff", success: "#05a34a", warning: "#fbbc06", danger: "#ff3366", gridBorder: "rgba(77, 138, 240, .15)", bodyColor: "#b8c3d9", cardBg: "#0c1427", muted: "#7987a1" };
        var fontFamily = "'Roboto', sans-serif";
        var monthlySalesData = {!! json_encode(array_values($monthlySalesData ?? array_fill(0,12,0))) !!};

        var options = {
            chart: { 
                type: 'area', 
                height: 320,
                parentHeightOffset: 0, 
                foreColor: colors.bodyColor, 
                background: 'transparent', 
                toolbar: { show: false },
            },
            theme: { mode: 'dark' },
            colors: [colors.primary],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            stroke: { curve: 'smooth', width: 3 },
            grid: { borderColor: 'rgba(255,255,255,0.05)' },
            series: [{ name: 'Completed Deliveries', data: monthlySalesData }],
            xaxis: {
                categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                axisBorder: { show: false },
                axisTicks: { show: false },
            },
            yaxis: { labels: { show: true } },
            legend: { show: false },
            dataLabels: { enabled: false },
        };

        new ApexCharts(el, options).render();

        // Enhanced Leaflet Map Logic
        var latInput = document.getElementById('agentLat');
        var lngInput = document.getElementById('agentLng');
        var areaInput = document.getElementById('agentAreaInput');
        var loading = document.getElementById('mapLoading');
        
        var defaultLat = latInput.value ? parseFloat(latInput.value) : 30.0444; 
        var defaultLng = lngInput.value ? parseFloat(lngInput.value) : 31.2357;
        
        var map = L.map('agentMap', { zoomControl: false }).setView([defaultLat, defaultLng], 12);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png').addTo(map);

        // Map layout fix for NobleUI theme
        setTimeout(() => { map.invalidateSize(); }, 500);

        var marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
        var circle = L.circle([defaultLat, defaultLng], { color: '#6571ff', radius: 10000 }).addTo(map);

        // Map Click Interaction
        map.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });

        marker.on('dragend', function(e) {
            var pos = e.target.getLatLng();
            updateMarker(pos.lat, pos.lng, false);
        });

        function updateMarker(lat, lng, moveMap = true) {
            latInput.value = lat;
            lngInput.value = lng;
            var newPos = [lat, lng];
            marker.setLatLng(newPos);
            circle.setLatLng(newPos);
            if(moveMap) map.panTo(newPos);
            getAddress(lat, lng);
        }

        window.locateMe = function() {
            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser.");
                return;
            }
            if (typeof showToast === 'function') showToast('Locating...', 'info');
            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    updateMarker(pos.coords.latitude, pos.coords.longitude);
                    map.setZoom(14);
                },
                (err) => {
                    console.error(err);
                    alert("Could not detect your location. Please check browser permissions.");
                }
            );
        };

        window.searchAddress = function() {
            const query = document.getElementById('mapSearchInput').value;
            if (!query.trim()) return;
            
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=1`)
                .then(r => r.json())
                .then(data => {
                    if (data && data.length > 0) {
                        const res = data[0];
                        updateMarker(parseFloat(res.lat), parseFloat(res.lon));
                        map.setZoom(14);
                    } else {
                        alert("Area not found. Try a more general name.");
                    }
                })
                .catch(err => console.error(err));
        };

        function getAddress(lat, lng) {
            loading.classList.remove('d-none');
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                .then(r => r.json())
                .then(d => { 
                    areaInput.value = d.display_name || "Unknown Location"; 
                    loading.classList.add('d-none');
                })
                .catch(err => {
                    console.error(err);
                    loading.classList.add('d-none');
                });
        }

        // Onboarding Tour
        const userId = "{{ auth()->id() }}";
        const hasSeenTour = localStorage.getItem('bitehub_tour_agent_' + userId);
        
        if (!hasSeenTour) {
            const driver = window.driver.js.driver;
            const driverObj = driver({
                showProgress: true,
                steps: [
                    { element: 'form[action*="status"]', popover: { title: 'Availability Status', description: 'Toggle your status to Online when you are ready to start receiving delivery tasks. You cannot receive tasks while Offline.', side: "bottom", align: 'start' }},
                    { element: '#agentMap', popover: { title: 'Service Radius', description: 'Drag the pin to set your current location. We use this to only match you with deliveries within a 10km radius.', side: "top", align: 'center' }},
                    { element: '.banner-card button', popover: { title: 'Manage Deliveries', description: 'Click here to view your active delivery queues, accept packages, and complete journeys.', side: "left", align: 'center' }}
                ],
                onDestroyStarted: () => {
                    localStorage.setItem('bitehub_tour_agent_' + userId, 'true');
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
    <!-- Settle Debt Modal -->
    <div class="modal fade" id="settleDebtModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content bg-dark border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-bottom border-white-5 opacity-75">
                    <h5 class="modal-title fw-bold text-white">Settle Cash Debt</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="text-center mb-4">
                        <div class="icon-box-dark icon-warning mx-auto mb-3" style="width: 60px; height: 60px;">
                            <i data-feather="briefcase" style="width: 30px;"></i>
                        </div>
                        <h4 class="fw-bold text-white mb-1">{{ number_format(Auth::user()->cash_to_settle ?? 0, 2) }} <small>EGP</small></h4>
                        <p class="text-custom-muted small">Outstanding balance collected from cash orders</p>
                    </div>

                    <div class="d-grid gap-3">
                        <!-- Wallet Option -->
                        <div class="p-3 bg-dark-subtle border border-white-5 rounded-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-success-subtle p-2 rounded-circle"><i class="fas fa-wallet text-success"></i></div>
                                <div>
                                    <p class="mb-0 fw-bold text-white" style="font-size: 0.9rem;">From Online Profit</p>
                                    <p class="mb-0 text-custom-muted" style="font-size: 0.75rem;">Balance: {{ number_format(Auth::user()->Wallet_balance ?? 0, 2) }} EGP</p>
                                </div>
                            </div>
                            @if(Auth::user()->Wallet_balance >= Auth::user()->cash_to_settle && Auth::user()->cash_to_settle > 0)
                                <form action="{{ route('agent.settle_debt') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success px-3 fw-bold rounded-pill">Settle Now</button>
                                </form>
                            @else
                                <span class="badge bg-secondary">Low Balance</span>
                            @endif
                        </div>

                        <!-- Paymob Option -->
                        <div class="p-3 bg-dark-subtle border border-white-5 rounded-3 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-primary-subtle p-2 rounded-circle"><i class="fas fa-credit-card text-primary"></i></div>
                                <div>
                                    <p class="mb-0 fw-bold text-white" style="font-size: 0.9rem;">Pay via Card/Visa</p>
                                    <p class="mb-0 text-custom-muted" style="font-size: 0.75rem;">Instant settlement via Paymob</p>
                                </div>
                            </div>
                            @if(Auth::user()->cash_to_settle > 0)
                                <form action="{{ route('agent.paymob.settle') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary px-3 fw-bold rounded-pill">Pay Now</button>
                                </form>
                            @else
                                <span class="badge bg-secondary">No Debt</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

