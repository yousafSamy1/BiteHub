@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">

    <style>
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
        
        .icon-box-dark {
            width: 42px; height: 42px;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
        }
        .icon-primary { background: rgba(59, 130, 246, 0.15); color: #60a5fa; }
        .icon-success { background: rgba(16, 185, 129, 0.15); color: #34d399; }
        .icon-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; }
        .icon-danger  { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .icon-info    { background: rgba(6, 182, 212, 0.15); color: #22d3ee; }
        .icon-purple  { background: rgba(139, 92, 246, 0.15); color: #a78bfa; }

        .stat-title { color: #94a3b8; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
        .stat-value { color: #f8fafc; font-weight: 800; font-size: 1.4rem; letter-spacing: -0.025em; margin-bottom: 0; }
        
        .btn-custom-light { background-color: rgba(255, 255, 255, 0.05); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; font-weight: 600; padding: 8px 16px; transition: all 0.2s; }
        .btn-custom-light:hover { background-color: rgba(255, 255, 255, 0.1); color: #ffffff; }
    </style>

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="heading-main mb-1">Platform Performance (KPI)</h3>
            <p class="text-custom-muted mb-0">System-wide Analytics & Financial Overview</p>
        </div>
        <div class="d-flex align-items-center gap-3">
             <div class="dropdown">
                <button class="btn-custom-light dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                    <i data-feather="calendar" style="width:16px"></i> 
                    {{ ucfirst($range) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg" style="background: #1e293b; border:1px solid rgba(255,255,255,0.05);">
                    <li><a class="dropdown-item" href="?range=all">All Time</a></li>
                    <li><a class="dropdown-item" href="?range=today">Today</a></li>
                    <li><a class="dropdown-item" href="?range=week">This Week</a></li>
                    <li><a class="dropdown-item" href="?range=month">This Month</a></li>
                    <li><a class="dropdown-item" href="?range=year">This Year</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Row 1: Revenue & Financials -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Platform Gross Revenue</p>
                        <div class="icon-box-dark icon-success"><i data-feather="dollar-sign" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($totalPlatformRevenue, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Today's Total</p>
                        <div class="icon-box-dark icon-primary"><i data-feather="activity" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($todayPlatformRevenue, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">This Month Total</p>
                        <div class="icon-box-dark icon-info"><i data-feather="calendar" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($monthlyPlatformRevenueTotal, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="border-left: 4px solid #a78bfa; cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">System Commission</p>
                        <div class="icon-box-dark icon-purple"><i data-feather="percent" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($siteCommission, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 2: Order & Activity -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Total Orders ({{ $range }})</p>
                        <div class="icon-box-dark icon-primary"><i data-feather="shopping-bag" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $totalOrdersCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Pending Orders</p>
                        <div class="icon-box-dark icon-warning"><i data-feather="clock" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $pendingOrdersCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.subscriptions') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Active Subscriptions</p>
                        <div class="icon-box-dark icon-success"><i data-feather="refresh-cw" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $activeSubscriptionsCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.ads') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Active Advertisements</p>
                        <div class="icon-box-dark icon-info"><i data-feather="tv" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $activeAdsCount }}</h4>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 3: Users & Providers -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('admin.users') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Total Users</p>
                        <div class="icon-box-dark icon-primary"><i data-feather="users" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $totalUsersCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.kitchens') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Active Kitchens</p>
                        <div class="icon-box-dark icon-warning"><i data-feather="home" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $totalKitchensCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.caterers') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Active Caterers</p>
                        <div class="icon-box-dark icon-info"><i data-feather="truck" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $totalCaterersCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('admin.agents') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Delivery Agents</p>
                        <div class="icon-box-dark icon-success"><i data-feather="navigation" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $totalAgentsCount }}</h4>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 4: Support & Critical -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('admin.reports') }}" class="text-decoration-none">
                <div class="dark-card p-4" style="border-left: 5px solid #fbbf24; cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="stat-title">Open Support Tickets</p>
                            <h2 class="stat-value text-warning">{{ $openTicketsCount }}</h2>
                        </div>
                        <div class="icon-box-dark icon-warning" style="width: 55px; height: 55px;"><i data-feather="help-circle" style="width:28px"></i></div>
                    </div>
                    <p class="text-custom-muted mb-0">Active inquiries requiring administrator attention.</p>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('admin.refunds') }}" class="text-decoration-none">
                <div class="dark-card p-4" style="border-left: 5px solid #f87171; cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="stat-title">Pending Refund Requests</p>
                            <h2 class="stat-value text-danger">{{ $pendingRefundsCount }}</h2>
                        </div>
                        <div class="icon-box-dark icon-danger" style="width: 55px; height: 55px;"><i data-feather="alert-triangle" style="width:28px"></i></div>
                    </div>
                    <p class="text-custom-muted mb-0">Refund claims awaiting verification and processing.</p>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 5: System Trend -->
    <div class="row mb-5">
        <div class="col-12">
            <a href="{{ route('admin.orders') }}" class="text-decoration-none">
                <div class="dark-card p-4" style="cursor: pointer;">
                    <h5 class="heading-main mb-4">Platform Revenue Trend (Current Year)</h5>
                    <div id="adminRevenueChart" style="height: 350px;"></div>
                </div>
            </a>
        </div>
    </div>

</div>

@push('custom-scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var options = {
            chart: {
                type: 'area',
                height: 350,
                parentHeightOffset: 0,
                foreColor: '#94a3b8',
                toolbar: { show: false },
                background: 'transparent'
            },
            theme: { mode: 'dark' },
            colors: ['#3b82f6'],
            fill: {
                type: 'gradient',
                gradient: {
                    shadeIntensity: 1,
                    opacityFrom: 0.45,
                    opacityTo: 0.05,
                    stops: [20, 100, 100, 100]
                }
            },
            stroke: { curve: 'smooth', width: 3 },
            grid: { borderColor: 'rgba(255, 255, 255, 0.05)', padding: { bottom: -10 } },
            series: [{
                name: 'Revenue',
                data: {!! json_encode($chartData) !!}
            }],
            xaxis: {
                categories: {!! json_encode($chartLabels) !!},
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    formatter: function (val) { return val.toFixed(0) + " EGP"; }
                }
            },
            dataLabels: { enabled: false },
            tooltip: { theme: 'dark', x: { show: true } }
        };

        if(document.querySelector("#adminRevenueChart")) {
            new ApexCharts(document.querySelector("#adminRevenueChart"), options).render();
        }
    });
</script>
@endpush

@endsection
