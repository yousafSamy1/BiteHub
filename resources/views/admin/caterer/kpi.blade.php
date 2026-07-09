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

        .stat-title { color: #94a3b8; font-weight: 600; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px; }
        .stat-value { color: #f8fafc; font-weight: 800; font-size: 1.4rem; letter-spacing: -0.025em; margin-bottom: 0; }
        
        .btn-custom-light { background-color: rgba(255, 255, 255, 0.05); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; font-weight: 600; padding: 8px 16px; transition: all 0.2s; }
        .btn-custom-light:hover { background-color: rgba(255, 255, 255, 0.1); color: #ffffff; }

        .top-item-row { display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .top-item-row:last-child { border-bottom: none; }
    </style>

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="heading-main mb-1">{{ $caterer->BusinessName }} Performance</h3>
            <p class="text-custom-muted mb-0">Analytics & Key Performance Indicators</p>
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

    <!-- Row 1: Revenue Core -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Total Revenue</p>
                        <div class="icon-box-dark icon-success"><i data-feather="dollar-sign" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($totalRevenue, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Today's Revenue</p>
                        <div class="icon-box-dark icon-primary"><i data-feather="activity" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($todayRevenue, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Monthly Revenue</p>
                        <div class="icon-box-dark icon-info"><i data-feather="calendar" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($monthlyRevenueTotal, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Avg Order Value</p>
                        <div class="icon-box-dark icon-warning"><i data-feather="trending-up" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($aov, 2) }} <small class="text-custom-muted fs-6">EGP</small></h4>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 2: Order Lifecycle -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
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
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="border-left: 4px solid #fbbf24; cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Pending Orders</p>
                        <div class="icon-box-dark icon-warning"><i data-feather="clock" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $pendingOrdersCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="border-left: 4px solid #34d399; cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Delivered Orders</p>
                        <div class="icon-box-dark icon-success"><i data-feather="check-circle" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $completedOrdersCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.refunds') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="border-left: 4px solid #f87171; cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Cancellation Rate</p>
                        <div class="icon-box-dark icon-danger"><i data-feather="x-circle" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ number_format($cancellationRate, 1) }}%</h4>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 3: Catering & Marketing -->
    <div class="row mb-4">
        <div class="col-md-3">
            <a href="{{ route('caterer.requests') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Catering Requests</p>
                        <div class="icon-box-dark icon-info"><i data-feather="clipboard" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $totalCateringRequests }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.requests') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Approved Requests</p>
                        <div class="icon-box-dark icon-success"><i data-feather="check-square" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $approvedCateringRequests }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.menu') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Menu Items</p>
                        <div class="icon-box-dark icon-primary"><i data-feather="book-open" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $totalMenuItemsCount }}</h4>
                </div>
            </a>
        </div>
        <div class="col-md-3">
            <a href="{{ route('caterer.ads') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between mb-2">
                        <p class="stat-title">Active Ads</p>
                        <div class="icon-box-dark icon-warning"><i data-feather="tv" style="width:18px"></i></div>
                    </div>
                    <h4 class="stat-value">{{ $activeAdsCount }}</h4>
                </div>
            </a>
        </div>
    </div>

    <!-- Row 4: Customer Feedback & Top Selling -->
    <div class="row mb-4">
        <div class="col-md-6">
            <a href="{{ route('caterer.support') }}" class="text-decoration-none">
                <div class="dark-card p-3" style="cursor: pointer;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <p class="stat-title">Average Rating</p>
                            <div class="d-flex align-items-center gap-2">
                                <h2 class="stat-value text-warning">{{ number_format($avgRating, 1) }}</h2>
                                <div class="text-warning">
                                    @for($i=1; $i<=5; $i++)
                                        <i data-feather="star" style="width:20px; fill: {{ $i <= $avgRating ? '#fbbf24' : 'none' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <div class="icon-box-dark icon-warning" style="width: 50px; height: 50px;"><i data-feather="smile" style="width:24px"></i></div>
                    </div>
                    <p class="text-custom-muted mb-0">Based on <strong>{{ $totalReviewsCount }}</strong> customer reviews.</p>
                    
                    <div class="mt-4">
                        <h6 class="stat-title mb-3">Recent Feedback</h6>
                        @forelse($recentReviews as $review)
                            <div class="mb-3 p-2 rounded-3" style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.03);">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold" style="font-size: 0.85rem;">{{ $review->customer->user->FullName ?? 'Customer' }}</span>
                                    <div class="text-warning" style="font-size: 0.7rem;">
                                        @for($i=1; $i<=5; $i++)
                                            <i data-feather="star" style="width:10px; height:10px; fill: {{ $i <= $review->Rating ? '#fbbf24' : 'none' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="mb-0 text-custom-muted" style="font-size: 0.8rem; line-height: 1.4;">{{ Str::limit($review->Comment, 80) }}</p>
                            </div>
                        @empty
                            <p class="text-custom-muted small py-2">No reviews yet.</p>
                        @endforelse
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6">
            <a href="{{ route('caterer.menu') }}" class="text-decoration-none">
                <div class="dark-card p-4" style="cursor: pointer;">
                    <h5 class="heading-main mb-3"><i data-feather="award" class="me-2 text-primary"></i> Top Selling Items</h5>
                    @forelse($topItems as $item)
                        <div class="top-item-row">
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ (!empty($item->Image)) ? url($item->Image) : url('upload/no_image.jpg') }}" class="rounded-3" style="width:45px; height:45px; object-fit:cover;">
                                <div>
                                    <h6 class="mb-0 fw-bold">{{ $item->ItemName }}</h6>
                                    <small class="text-custom-muted">{{ $item->category->Name ?? 'Category' }}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary rounded-pill">{{ $item->sales_count }} Sold</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-center text-custom-muted py-3">No sales recorded yet.</p>
                    @endforelse
                </div>
            </a>
        </div>
    </div>

    <!-- Row 5: Chart -->
    <div class="row mb-5">
        <div class="col-12">
            <a href="{{ route('caterer.orders') }}" class="text-decoration-none">
                <div class="dark-card p-4" style="cursor: pointer;">
                    <h5 class="heading-main mb-4">Revenue Trend (Current Year)</h5>
                    <div id="catererRevenueChart" style="height: 350px;"></div>
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

        if(document.querySelector("#catererRevenueChart")) {
            new ApexCharts(document.querySelector("#catererRevenueChart"), options).render();
        }
    });
</script>
@endpush

@endsection
