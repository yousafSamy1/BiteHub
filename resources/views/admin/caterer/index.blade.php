@extends('admin.admin_dashboard')

@section('admin')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<div class="page-content">

    <style>
        /* Premium Dark Theme Dashboard */
        /* Premium Dark Theme Dashboard */
        .dark-card { 
            background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%); 
            border-radius: 20px; 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5); 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            color: #f8fafc; 
            overflow: hidden;
        }
        .dark-card:hover { 
            transform: translateY(-5px); 
            border-color: rgba(245, 158, 11, 0.3);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6); 
        }
        .heading-main { color: #f8fafc; font-weight: 800; letter-spacing: -0.025em; }
        .text-custom-muted { color: #94a3b8; font-weight: 500; }
        .banner-card { 
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); 
            border-radius: 24px; 
            padding: 25px 35px; 
            color: #ffffff; 
            position: relative; 
            overflow: hidden; 
            border: none;
            box-shadow: 0 20px 50px rgba(245, 158, 11, 0.25); 
        }
        .banner-card::before { 
            content: ''; 
            position: absolute; 
            top: -20%; 
            left: -10%; 
            width: 400px; 
            height: 400px; 
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%); 
            border-radius: 50%; 
        }
        .icon-box-dark { 
            width: 54px; 
            height: 54px; 
            border-radius: 16px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            box-shadow: inset 0 2px 4px rgba(255,255,255,0.05);
        }
        .icon-primary { background: rgba(59, 130, 246, 0.12); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.2); }
        .icon-success { background: rgba(16, 185, 129, 0.12); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.2); }
        .icon-warning { background: rgba(245, 158, 11, 0.12); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.2); }
        .icon-danger  { background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.2); }
        .icon-info    { background: rgba(6, 182, 212, 0.12); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.2); }
        .stat-title { color: #64748b; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 8px; }
        .stat-value { color: #f8fafc; font-weight: 800; font-size: 2rem; letter-spacing: -0.04em; margin-bottom: 0; }
        .btn-custom-primary { 
            background: linear-gradient(135deg, #f59e0b, #d87706); 
            color: #000000 !important; 
            border: none; 
            border-radius: 10px; 
            font-weight: 700; 
            padding: 8px 16px; 
            transition: all 0.3s; 
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
            font-size: 0.8rem;
        }
        .btn-custom-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(245, 158, 11, 0.4); }
        .btn-custom-light { 
            background: rgba(255, 255, 255, 0.05); 
            color: #e2e8f0; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 10px; 
            font-weight: 600; 
            padding: 8px 16px; 
            transition: all 0.3s;
            font-size: 0.8rem;
        }
        .btn-custom-light:hover { background: rgba(255, 255, 255, 0.1); color: #ffffff; border-color: rgba(255, 255, 255, 0.2); }
    </style>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-3">
        <div>
            <h3 class="heading-main mb-1">Caterer Dashboard</h3>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap gap-3">
            <span class="badge bg-warning text-dark px-3 py-2 fs-6">Caterer</span>
        </div>
    </div>
 
    <!-- Quick Working Hours -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="dark-card card p-3" style="background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.08);">
                <form action="{{ route('caterer.update_hours') }}" method="POST" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    @csrf
                    <div class="d-flex align-items-center">
                        <div class="icon-box-dark icon-warning me-3" style="width:45px; height:45px; background: rgba(245, 158, 11, 0.1);"><i data-feather="clock" style="width:20px"></i></div>
                        <div>
                            <h5 class="heading-main mb-0 fs-6">Operating Hours</h5>
                            @php
                                $status = $caterer->current_status ?? 'Closed';
                                $statusColor = ($status == 'Open') ? 'text-success' : (($status == 'Busy') ? 'text-warning' : 'text-danger');
                                $statusBg = ($status == 'Open') ? 'rgba(16, 185, 129, 0.1)' : (($status == 'Busy') ? 'rgba(245, 158, 11, 0.1)' : 'rgba(239, 68, 68, 0.1)');
                            @endphp
                            <span class="badge border-0 py-1 px-2 mt-1" style="background: {{ $statusBg }}; {{ $statusColor }}; font-size: 0.7rem; letter-spacing: 0.5px;">
                                <span class="d-inline-block rounded-circle me-1" style="width: 6px; height: 6px; background: currentColor;"></span>
                                {{ strtoupper($status) }} NOW
                            </span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-4 flex-grow-1 justify-content-center">
                        <div class="text-center">
                            <label class="text-custom-muted small fw-bold mb-1 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">OPENING TIME</label>
                            <div class="position-relative">
                                <input type="time" name="opening_time" class="form-control bg-dark text-white border-secondary py-2 px-3" 
                                    style="width:150px; border-radius:12px; border: 1px solid rgba(255,255,255,0.1); font-weight: 600;" 
                                    value="{{ \Carbon\Carbon::parse($caterer->OpeningTime ?? '09:00')->format('H:i') }}" required>
                            </div>
                        </div>
                        <div class="text-white-50 mt-3"><i data-feather="arrow-right" style="width: 16px;"></i></div>
                        <div class="text-center">
                            <label class="text-custom-muted small fw-bold mb-1 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">CLOSING TIME</label>
                            <div class="position-relative">
                                <input type="time" name="closing_time" class="form-control bg-dark text-white border-secondary py-2 px-3" 
                                    style="width:150px; border-radius:12px; border: 1px solid rgba(255,255,255,0.1); font-weight: 600;" 
                                    value="{{ \Carbon\Carbon::parse($caterer->ClosingTime ?? '22:00')->format('H:i') }}" required>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-warning px-4 py-2 fw-bold text-dark" style="border-radius:12px; box-shadow:0 4px 15px rgba(245,158,11,0.3); height: 45px;">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Banner -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="banner-card d-flex justify-content-between align-items-center">
                <div style="z-index: 1;">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-lg bg-white bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 60px; height: 60px;">
                            <span class="fs-2">🍽️</span>
                        </div>
                        <h1 class="fw-bold mb-0 text-white" style="letter-spacing: -0.03em;">Welcome, {{ Auth::user()->name }}</h1>
                    </div>
                    <p class="mb-0 text-white-50" style="font-size: 1.1rem; max-width: 600px;">
                        You have <span class="badge bg-white text-warning px-2 py-1 rounded-pill fw-bold mx-1">{{ $openRequests }} open requests</span> waiting for your proposals. Success starts here!
                    </p>
                </div>
                <div class="d-none d-md-block" style="z-index: 1;">
                    <a href="{{ route('caterer.requests') }}" class="btn btn-white rounded-pill px-5 py-3 fw-bold text-warning shadow-lg border-0" style="background: #ffffff; transition: all 0.3s; box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;">
                        <i data-feather="briefcase" class="me-2" style="width:18px"></i>Manage Requests
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card h-100 p-4">
                <div class="d-flex align-items-start justify-content-between mb-4">
                    <div>
                        <p class="stat-title">My Wallet</p>
                        <h4 class="stat-value">{{ number_format(Auth::user()->Wallet_balance ?? 0, 2) }} <span class="text-custom-muted fw-normal fs-6">EGP</span></h4>
                        @if(Auth::user()->pending_balance > 0)
                            <p class="mb-0 mt-1 d-flex align-items-center" style="font-size: 0.85rem; color: #fbbf24; font-weight: 600;">
                                <i class="fas fa-clock me-1 opacity-75" style="font-size: 0.75rem;"></i>
                                Pending: {{ number_format(Auth::user()->pending_balance, 2) }} EGP
                            </p>
                        @endif
                        <p class="text-custom-muted small mt-2" style="font-size: 0.7rem;"><i data-feather="info" style="width:12px" class="me-1"></i> Platform commission: 15% per order.</p>
                    </div>
                    <div class="icon-box-dark icon-success"><i data-feather="dollar-sign" style="width:20px"></i></div>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button type="button" class="btn btn-custom-light flex-grow-1" data-bs-toggle="modal" data-bs-target="#topupModal" style="background: #635bff; color: white !important; border: none;">
                        Top Up
                    </button>
                    <button type="button" class="btn btn-custom-light flex-grow-1" data-bs-toggle="modal" data-bs-target="#withdrawModal">
                        Withdraw
                    </button>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card h-100 p-4 border-0" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(30, 41, 59, 1) 100%); border: 1px solid rgba(59, 130, 246, 0.1) !important;">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <p class="stat-title text-primary">Total Requests</p>
                        <h4 class="stat-value">{{ $totalRequests }}</h4>
                    </div>
                    <div class="icon-box-dark icon-primary"><i data-feather="file-text" style="width:20px"></i></div>
                </div>
                <div class="mt-auto pt-2">
                    <span class="text-primary small fw-bold">All Time History</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card h-100 p-4 border-0" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(30, 41, 59, 1) 100%); border: 1px solid rgba(245, 158, 11, 0.1) !important;">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <p class="stat-title text-warning">Open Requests</p>
                        <h4 class="stat-value text-warning">{{ $openRequests }}</h4>
                    </div>
                    <div class="icon-box-dark icon-warning"><i data-feather="bell" style="width:20px"></i></div>
                </div>
                <div class="mt-auto pt-2">
                    <a href="{{ route('caterer.requests') }}" class="text-warning text-decoration-none small fw-bold d-flex align-items-center">Action Needed <i data-feather="arrow-right" class="ms-1" style="width:12px"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card h-100 p-4 border-0" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(30, 41, 59, 1) 100%); border: 1px solid rgba(16, 185, 129, 0.1) !important;">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <p class="stat-title text-success">Completed Jobs</p>
                        <h4 class="stat-value text-success">{{ $completedRequests }}</h4>
                    </div>
                    <div class="icon-box-dark icon-success"><i data-feather="award" style="width:20px"></i></div>
                </div>
                <div class="mt-auto pt-2">
                    <span class="text-success small fw-bold d-flex align-items-center"><i data-feather="shield" class="me-1" style="width:12px"></i> High Rating</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ACTION REQUIRED -->
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="heading-main mb-3 fs-5 d-flex align-items-center">
                <i data-feather="alert-circle" class="text-danger me-2" style="width:20px;"></i>
                Action Required
            </h5>
            <div class="row g-3">
                <!-- Support Tickets Action -->
                <div class="col-md-6">
                    <div class="dark-card card h-100 p-3" style="border-left: 4px solid #f59e0b;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-box-dark icon-warning me-3" style="width: 45px; height: 45px;">
                                    <i data-feather="life-buoy" style="width: 20px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-bold">Support Tickets</h6>
                                    <p class="mb-0 text-white-50 small">{{ $openSupportTickets }} open tickets require your attention.</p>
                                </div>
                            </div>
                            <a href="{{ route('caterer.support') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold">View</a>
                        </div>
                    </div>
                </div>
                <!-- Pending Refunds Action -->
                <div class="col-md-6">
                    <div class="dark-card card h-100 p-3" style="border-left: 4px solid #ef4444;">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="icon-box-dark icon-danger me-3" style="width: 45px; height: 45px;">
                                    <i data-feather="refresh-ccw" style="width: 20px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-1 text-white fw-bold">Pending Refunds</h6>
                                    <p class="mb-0 text-white-50 small">{{ $pendingRefunds ?? 0 }} refund requests await review.</p>
                                </div>
                            </div>
                            <button class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold" onclick="alert('Please contact Admin to resolve refunds for catering requests.')">Review</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SYSTEM ACTIVITY FEED -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="heading-main mb-0 fs-5 d-flex align-items-center">
                        <span class="icon-box-dark icon-info me-3" style="width:40px; height:40px;"><i data-feather="activity" style="width:18px"></i></span>
                        System Activity Feed
                    </h5>
                </div>
                
                <div class="d-flex flex-column gap-3">
                    @forelse($recentTickets as $ticket)
                    <div class="d-flex align-items-start p-3 rounded" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05);">
                        <div class="me-3 mt-1">
                            <div class="bg-primary bg-opacity-25 text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                <i data-feather="message-square" style="width: 16px;"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 text-white fw-bold">New Ticket: {{ \Illuminate\Support\Str::limit($ticket->Subject, 40) }}</h6>
                                <span class="badge {{ $ticket->Status == 'Open' ? 'bg-danger' : ($ticket->Status == 'InProgress' ? 'bg-warning' : 'bg-secondary') }} rounded-pill" style="font-size: 0.65rem;">{{ $ticket->Status }}</span>
                            </div>
                            <p class="mb-1 text-white-50 small">{{ \Illuminate\Support\Str::limit($ticket->Description, 80) }}</p>
                            <small class="text-muted"><i data-feather="clock" style="width: 12px; margin-right: 4px;"></i>{{ $ticket->created_at->diffForHumans() }}</small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center py-4">
                        <i data-feather="check-circle" class="text-success mb-2" style="width: 40px; height: 40px; opacity: 0.5;"></i>
                        <p class="text-white-50 mb-0">You're all caught up! No recent support activity.</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <div class="col-lg-12">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5 d-flex align-items-center">
                        <span class="icon-box-dark icon-warning me-3" style="width:40px; height:40px;"><i data-feather="bar-chart-2" style="width:18px"></i></span>
                        Analytics Summary
                    </h4>
                    <button class="btn btn-custom-light btn-sm py-2 px-4 shadow-sm border-0"><i data-feather="download" class="me-2" style="width:14px"></i> Export Data</button>
                </div>
                <div id="realMonthlySalesChart" style="height: 320px; width: 100%;"></div>
            </div>
        </div>
    </div>
    @include('partials.withdraw-modal')
    @include('partials.topup-modal')
</div>

@push('custom-scripts')
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<script>
    window.addEventListener("load", function() {
        var colors = { primary: "#6571ff", success: "#05a34a", warning: "#fbbc06", danger: "#ff3366", gridBorder: "rgba(77, 138, 240, .15)", bodyColor: "#b8c3d9", cardBg: "#0c1427", muted: "#7987a1" };
        var fontFamily = "'Inter', sans-serif";
        var monthlySalesData = {!! json_encode(array_values($monthlySalesData ?? array_fill(0,12,0))) !!};
        
        var options = {
            chart: { 
                type: 'area', 
                height: 320,
                width: '100%',
                parentHeightOffset: 0, 
                foreColor: colors.bodyColor, 
                background: 'transparent', 
                toolbar: { show: false },
                redrawOnParentResize: true,
                redrawOnWindowResize: true
            },
            theme: { mode: 'dark' },
            colors: [colors.primary],
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.05, stops: [0, 100] } },
            stroke: { curve: 'smooth', width: 2 },
            grid: { padding: { bottom: -4, left: 10, right: 10 }, borderColor: 'rgba(255,255,255,0.05)', xaxis: { lines: { show: true } } },
            series: [{ name: 'Catering Requests', data: monthlySalesData }],
            xaxis: {
                categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                axisBorder: { color: 'rgba(255,255,255,0.1)' },
                axisTicks: { color: 'rgba(255,255,255,0.1)' },
            },
            yaxis: { title: { text: 'Requests', style: { size: 9, color: colors.muted } } },
            legend: { show: true, position: "top", horizontalAlign: 'center', fontFamily: fontFamily },
            dataLabels: { enabled: false },
        };
        var el = document.querySelector("#realMonthlySalesChart");
        if(el) {
            var chart = new ApexCharts(el, options);
            chart.render().then(function() {
                setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 100);
            });
        }

        // Onboarding Tour
        const userId = "{{ auth()->id() }}";
        const hasSeenTour = localStorage.getItem('bitehub_tour_caterer_' + userId);
        
        if (!hasSeenTour) {
            const driver = window.driver.js.driver;
            const driverObj = driver({
                showProgress: true,
                steps: [
                    { element: '.banner-card', popover: { title: 'Caterer Overview', description: 'Welcome to your Catering Dashboard! This banner highlights your open requests that require immediate response.', side: "bottom", align: 'start' }},
                    { element: '.banner-card a.btn-light', popover: { title: 'Manage Requests', description: 'Click here to review and accept/decline customer requests for event catering.', side: "left", align: 'center' }}
                ],
                onDestroyStarted: () => {
                    localStorage.setItem('bitehub_tour_caterer_' + userId, 'true');
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
