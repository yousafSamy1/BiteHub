@extends('admin.admin_dashboard')

@section('admin')


<div class="page-content">

    <style>
        /* Premium Dark Theme Dashboard */
        /* Premium Dark Theme Dashboard */
        .dark-card {
            background-color: #1e293b; /* Sleek dark slate */
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
        .icon-danger  { background: rgba(239, 68, 68, 0.15); color: #f87171; }
        .icon-info    { background: rgba(6, 182, 212, 0.15); color: #22d3ee; }

        .stat-title { color: #94a3b8; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .stat-value { color: #f8fafc; font-weight: 800; font-size: 1.7rem; letter-spacing: -0.025em; margin-bottom: 0; }

        .btn-custom-primary { background-color: #3b82f6; color: #ffffff; border: none; border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.2s; }
        .btn-custom-primary:hover { background-color: #2563eb; color: #ffffff; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); }
        .btn-custom-light { background-color: rgba(255, 255, 255, 0.05); color: #e2e8f0; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 10px; font-weight: 600; padding: 10px 20px; transition: all 0.2s; }
        .btn-custom-light:hover { background-color: rgba(255, 255, 255, 0.1); color: #ffffff; border-color: rgba(255, 255, 255, 0.2); }

        .table-dark-custom { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-dark-custom th { color: #94a3b8; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; padding: 16px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .table-dark-custom td { color: #e2e8f0; font-weight: 500; font-size: 0.95rem; padding: 16px; vertical-align: middle; border-bottom: 1px solid rgba(255,255,255,0.02); }
        .table-dark-custom tbody tr { transition: background-color 0.2s; }
        .table-dark-custom tbody tr:hover { background-color: rgba(255, 255, 255, 0.02); }

        .badge-soft-success { background: rgba(16, 185, 129, 0.15); color: #34d399; font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-soft-warning { background: rgba(245, 158, 11, 0.15); color: #fbbf24; font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-soft-danger  { background: rgba(239, 68, 68, 0.15); color: #f87171; font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge-soft-primary { background: rgba(59, 130, 246, 0.15); color: #60a5fa; font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: 1px solid rgba(59, 130, 246, 0.2); }
        .badge-soft-info    { background: rgba(6, 182, 212, 0.15); color: #22d3ee; font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: 1px solid rgba(6, 182, 212, 0.2); }
        .badge-soft-secondary { background: rgba(148, 163, 184, 0.15); color: #94a3b8; font-weight: 600; padding: 6px 12px; border-radius: 8px; font-size: 0.8rem; border: 1px solid rgba(148, 163, 184, 0.2); }

        .recent-users-item { display: flex; align-items: center; padding: 12px; border-radius: 12px; transition: background 0.2s; border: 1px solid transparent; }
        .recent-users-item:hover { background: rgba(255, 255, 255, 0.03); border-color: rgba(255, 255, 255, 0.05); }
    </style>

    <!-- Header & Actions -->
    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="heading-main mb-1">Dashboard</h3>
            <p class="text-custom-muted mb-0">System Overview & Analytics</p>
        </div>
        <div class="d-flex align-items-center gap-3 mt-3 mt-md-0">
            <!-- Time Filter Dropdown -->
            <div class="dropdown">
                <button class="btn-custom-light dropdown-toggle d-flex align-items-center gap-2" type="button" id="rangeDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i data-feather="calendar" style="width:16px"></i> 
                    @php
                        $rangeLabels = ['today' => 'Today', 'week' => 'This Week', 'month' => 'This Month', 'year' => 'This Year', 'all' => 'All Time'];
                        echo $rangeLabels[$range] ?? 'All Time';
                    @endphp
                </button>
                <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end p-2 border-0 shadow-lg" style="background: #1e293b; border-radius: 12px; border:1px solid rgba(255,255,255,0.05);" aria-labelledby="rangeDropdown">
                    <li><a class="dropdown-item rounded-3 {{ $range == 'today' ? 'active bg-primary' : '' }}" href="?range=today">Today</a></li>
                    <li><a class="dropdown-item rounded-3 {{ $range == 'week' ? 'active bg-primary' : '' }}" href="?range=week">This Week</a></li>
                    <li><a class="dropdown-item rounded-3 {{ $range == 'month' ? 'active bg-primary' : '' }}" href="?range=month">This Month</a></li>
                    <li><a class="dropdown-item rounded-3 {{ $range == 'year' ? 'active bg-primary' : '' }}" href="?range=year">This Year</a></li>
                    <li><hr class="dropdown-divider opacity-10"></li>
                    <li><a class="dropdown-item rounded-3 {{ $range == 'all' ? 'active bg-primary' : '' }}" href="?range=all">All Time</a></li>
                </ul>
            </div>

            <button class="btn-custom-light d-flex align-items-center gap-2" onclick="window.print()">
                <i data-feather="printer" style="width:16px"></i> Print
            </button>
            <a href="{{ route('admin.report.download') }}" class="btn-custom-primary d-flex align-items-center gap-2 text-decoration-none" onclick="this.innerHTML='<span class=\'spinner-border spinner-border-sm me-2\' role=\'status\' aria-hidden=\'true\'></span> Generating...'; setTimeout(() => { this.innerHTML='<i data-feather=\'download\' style=\'width:16px\'></i> Export Report'; if(window.feather) feather.replace(); }, 4000);">
                <i data-feather="download" style="width:16px"></i> Export Report
            </a>
        </div>
    </div>

    <!-- Banner -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="banner-card d-flex justify-content-between align-items-center">
                <div style="z-index: 1;">
                    <h2 class="fw-bold mb-2 text-white">Welcome, {{ Auth::user()->name }} 👋</h2>
                    <p class="mb-0 text-white-50" style="font-size: 1.05rem;">You have <strong class="text-white">{{ $pendingOrders }} pending orders</strong> waiting for attention.</p>
                </div>
                <div class="d-none d-md-block" style="z-index: 1;">
                    <button class="btn btn-light rounded-pill px-4 py-2 fw-bolder text-primary shadow" onclick="window.location.href='{{ route('admin.orders') }}'">Manage Orders</button>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Row 1 -->
    <div class="row mb-4">
        <div class="col-xl col-md-6 mb-3 mb-xl-0">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title">Total Customers</p>
                        <h4 class="stat-value">{{ number_format($totalCustomers) }}</h4>
                    </div>
                    <div class="icon-box-dark icon-primary"><i data-feather="users" style="width: 22px;"></i></div>
                </div>
                <div class="d-flex align-items-center mt-auto">
                    <span class="badge-soft-success me-2"><i data-feather="trending-up" style="width:12px; margin-right:4px;"></i>Growing</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-3 mb-xl-0">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title">Orders ({{ $rangeLabels[$range] ?? 'All' }})</p>
                        <h4 class="stat-value" id="kpi-orders">{{ number_format($totalOrders) }}</h4>
                    </div>
                    <div class="icon-box-dark icon-success"><i data-feather="shopping-bag" style="width: 22px;"></i></div>
                </div>
                <div class="d-flex align-items-center mt-auto">
                    <span class="badge-soft-primary me-2">+{{ $todayOrders }}</span> <span class="text-custom-muted" style="font-size:0.85rem">Today</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-3 mb-md-0">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title">Revenue ({{ $rangeLabels[$range] ?? 'All' }})</p>
                        <h4 class="stat-value"><span id="kpi-revenue">{{ number_format($totalRevenue) }}</span> <span class="text-custom-muted fw-normal fs-6">EGP</span></h4>
                    </div>
                    <div class="icon-box-dark icon-warning"><i data-feather="dollar-sign" style="width: 22px;"></i></div>
                </div>
                <div class="d-flex align-items-center mt-auto">
                    <span class="badge-soft-warning me-2"><i data-feather="clock" style="width:12px; margin-right:4px;"></i> {{ $pendingOrders }} Pending</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-6 mb-3 mb-md-0">
            <div class="dark-card card p-4" style="border: 1px solid rgba(16, 185, 129, 0.15);">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title" style="color: #34d399;">Commission ({{ $rangeLabels[$range] ?? 'All' }})</p>
                        <h4 class="stat-value" style="color: #34d399;"><span id="kpi-commission">{{ number_format($siteCommission, 2) }}</span> <span class="fw-normal fs-6" style="color: #6ee7b7;">EGP</span></h4>
                    </div>
                    <div class="icon-box-dark" style="background: rgba(16, 185, 129, 0.2); color: #34d399;"><i data-feather="percent" style="width: 22px;"></i></div>
                </div>
                <div class="d-flex align-items-center mt-auto">
                    <span class="badge-soft-success me-2"><i data-feather="trending-up" style="width:12px; margin-right:4px;"></i>Platform Profit</span>
                </div>
            </div>
        </div>
        <div class="col-xl col-md-6">
            <div class="dark-card card p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <p class="stat-title">Total Wallets</p>
                        <h4 class="stat-value"><span id="kpi-wallets">{{ number_format($totalWalletsSum) }}</span> <span class="text-custom-muted fw-normal fs-6">EGP</span></h4>
                    </div>
                    <div class="icon-box-dark icon-info"><i data-feather="credit-card" style="width: 22px;"></i></div>
                </div>
                <div class="d-flex align-items-center mt-auto">
                    <span class="text-custom-muted" style="font-size:0.85rem"><i data-feather="activity" style="width:14px" class="me-1"></i>Platform Balance</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Required Row -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3 mb-md-0">
            <div class="dark-card card p-4 border-start border-warning border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="heading-main mb-1">Open Support Tickets</h5>
                        <p class="text-custom-muted mb-3">Customers and kitchens waiting for help.</p>
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="stat-value text-warning mb-0">{{ $openTicketsCount }}</h2>
                            <a href="{{ route('admin.reports') }}" class="btn btn-warning btn-sm fw-bold px-3 py-2 rounded-3">View All Tickets</a>
                        </div>
                    </div>
                    <div class="icon-box-dark bg-warning bg-opacity-10 text-warning" style="width: 60px; height: 60px;">
                        <i data-feather="help-circle" style="width: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="dark-card card p-4 border-start border-danger border-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="heading-main mb-1">Pending Refund Requests</h5>
                        <p class="text-custom-muted mb-3">Approved claims waiting for final processing.</p>
                        <div class="d-flex align-items-center gap-3">
                            <h2 class="stat-value text-danger mb-0">{{ $pendingRefundsCount }}</h2>
                            <a href="{{ route('admin.refunds') }}" class="btn btn-danger btn-sm fw-bold px-3 py-2 rounded-3">Process Refunds</a>
                        </div>
                    </div>
                    <div class="icon-box-dark bg-danger bg-opacity-10 text-danger" style="width: 60px; height: 60px;">
                        <i data-feather="refresh-cw" style="width: 28px;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mini Stats Grid -->
    <div class="row mb-4">
        @php
            $miniStats = [
                ['title' => 'Kitchens', 'value' => $totalKitchens, 'icon' => 'home', 'color' => 'icon-danger'],
                ['title' => 'Caterers', 'value' => $totalCaterers, 'icon' => 'briefcase', 'color' => 'icon-warning'],
                ['title' => 'Delivery Agents', 'value' => $totalDeliveryAgents, 'icon' => 'truck', 'color' => 'icon-info'],
                ['title' => 'Subscriptions', 'value' => $totalSubscriptions, 'icon' => 'repeat', 'color' => 'icon-success'],
                ['title' => 'Active Ads', 'value' => $totalAdvertisements, 'icon' => 'radio', 'color' => 'icon-primary'],
                ['title' => 'Catering Req', 'value' => $totalCateringReqs, 'icon' => 'layers', 'color' => 'icon-info']
            ];
        @endphp
        
        @foreach($miniStats as $stat)
        <div class="col-md-2 col-6 mb-3 mb-md-0">
            <div class="dark-card card p-3 d-flex flex-row align-items-center justify-content-between">
                <div>
                    <p class="text-custom-muted mb-1" style="font-size: 0.7rem; font-weight:700; text-transform:uppercase;">{{ $stat['title'] }}</p>
                    <h5 class="heading-main mb-0 fs-5">{{ $stat['value'] }}</h5>
                </div>
                <div class="icon-box-dark {{ $stat['color'] }}" style="width: 36px; height: 36px;"><i data-feather="{{ $stat['icon'] }}" style="width: 14px;"></i></div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Charts and Users Row -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5">Revenue Analytics ({{ $rangeLabels[$range] ?? 'All' }})</h4>
                    <button class="btn-custom-light btn-sm py-1 px-3 fs-6" data-bs-toggle="modal" data-bs-target="#revenueDetailsModal"><i data-feather="bar-chart-2" style="width:14px"></i> Details</button>
                </div>
                <div id="realMonthlySalesChart" style="height: 320px; width: 100%;"></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5">Recent Signups</h4>
                    <a href="{{ route('admin.users') }}" class="text-primary fw-bold text-decoration-none fs-6">View All</a>
                </div>
                <div class="d-flex flex-column gap-2">
                    @forelse($recentUsers as $user)
                    <div class="recent-users-item">
                        <img src="{{ (!empty($user->Image) && file_exists(public_path('upload/admin_images/'.$user->Image))) ? url('upload/admin_images/'.$user->Image) : url('upload/no_image.jpg') }}" class="rounded-circle me-3" style="width:40px; height:40px; object-fit:cover; border: 2px solid rgba(255,255,255,0.1);" alt="user">
                        <div class="flex-grow-1">
                            <h6 class="heading-main mb-1" style="font-size: 0.95rem;">{{ $user->FullName }}</h6>
                            <p class="text-custom-muted mb-0" style="font-size: 0.75rem;"><span class="badge border border-secondary text-secondary me-1 py-0 px-1">{{ $user->Role }}</span> {{ \Carbon\Carbon::parse($user->CreatedAt)->diffForHumans(null, true, true) }}</p>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-custom-muted py-4">No recent users.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Dark Table Row -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5">Recent Orders</h4>
                    <a href="{{ route('admin.orders') }}" class="btn-custom-light py-1 px-3 fs-6">Manage Orders</a>
                </div>
                <div class="table-responsive">
                    <table class="table-dark-custom text-nowrap w-100">
                        <thead>
                            <tr>
                                <th>Order Ref</th>
                                <th>Customer</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td class="fw-bold text-primary">#{{ $order->OrderID }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="icon-box-dark icon-info" style="width:32px; height:32px; font-size:13px; font-weight:bold;">
                                            {{ substr($order->CustomerName, 0, 1) }}
                                        </div>
                                        <span class="fw-semibold">{{ $order->CustomerName }}</span>
                                    </div>
                                </td>
                                <td class="text-custom-muted"><i data-feather="calendar" style="width:12px" class="me-1"></i> {{ date('d M Y', strtotime($order->CreatedAt)) }}</td>
                                <td class="fw-bold">{{ number_format($order->TotalPrice) }} <span class="fs-6 text-custom-muted fw-normal">EGP</span></td>
                                <td>
                                    @php
                                        $badgeClass = 'badge-soft-primary';
                                        if ($order->OrderStatus == 'Pending') $badgeClass = 'badge-soft-warning';
                                        if ($order->OrderStatus == 'Confirmed') $badgeClass = 'badge-soft-info';
                                        if ($order->OrderStatus == 'Preparing') $badgeClass = 'badge-soft-primary';
                                        if ($order->OrderStatus == 'Out for Delivery') $badgeClass = 'badge-soft-primary';
                                        if ($order->OrderStatus == 'Delivered') $badgeClass = 'badge-soft-success';
                                        if ($order->OrderStatus == 'Cancelled') $badgeClass = 'badge-soft-danger';
                                    @endphp
                                    <span class="{{ $badgeClass }}">{{ $order->OrderStatus }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- System Activity Feed -->
    <div class="row mb-5">
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5"><i data-feather="message-circle" style="width:18px;color:#fbbf24"></i> Recent Support Activity</h4>
                    <a href="{{ route('admin.reports') }}" class="text-warning fw-bold text-decoration-none fs-6">View All</a>
                </div>
                <div class="d-flex flex-column gap-3">
                    @forelse($recentTickets as $ticket)
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: rgba(255,255,255,0.02);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box-dark bg-warning bg-opacity-10 text-warning" style="width: 40px; height: 40px;">
                                <i data-feather="tag" style="width: 18px;"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">{{ $ticket->Subject }}</h6>
                                <small class="text-custom-muted">{{ $ticket->user->FullName }} • {{ \Carbon\Carbon::parse($ticket->created_at)->diffForHumans() }}</small>
                            </div>
                        </div>
                        <span class="badge-soft-{{ $ticket->status_badge }}">{{ $ticket->Status }}</span>
                    </div>
                    @empty
                    <div class="text-center text-custom-muted py-3">No recent support activity.</div>
                    @endforelse
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="dark-card card p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5"><i data-feather="refresh-ccw" style="width:18px;color:#f87171"></i> Recent Refund Requests</h4>
                    <a href="{{ route('admin.refunds') }}" class="text-danger fw-bold text-decoration-none fs-6">Manage</a>
                </div>
                <div class="d-flex flex-column gap-3">
                    @forelse($recentRefunds as $refund)
                    <div class="d-flex align-items-center justify-content-between p-2 rounded-3" style="background: rgba(255,255,255,0.02);">
                        <div class="d-flex align-items-center gap-3">
                            <div class="icon-box-dark bg-danger bg-opacity-10 text-danger" style="width: 40px; height: 40px;">
                                <i data-feather="dollar-sign" style="width: 18px;"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-1" style="font-size: 0.9rem;">Refund #{{ $refund->RequestID }} - {{ number_format($refund->Amount, 2) }} EGP</h6>
                                <small class="text-custom-muted">{{ $refund->customer->user->FullName }} • {{ $refund->Status }}</small>
                            </div>
                        </div>
                        <a href="{{ route('admin.refunds') }}" class="btn btn-sm btn-outline-light border-0"><i data-feather="eye" style="width: 16px;"></i></a>
                    </div>
                    @empty
                    <div class="text-center text-custom-muted py-3">No recent refund requests.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Top Providers Row -->
    <div class="row mb-5">
        <div class="col-md-6 mb-4 mb-md-0">
            <div class="dark-card card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5"><i data-feather="star" style="width:18px;color:#fbbc06"></i> Top Kitchens ({{ $rangeLabels[$range] ?? 'All' }})</h4>
                </div>
                <div class="d-flex flex-column gap-3">
                    @if(count($topKitchens) > 0)
                        @foreach($topKitchens as $index => $kitchen)
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2" style="border-color: rgba(255,255,255,0.05)!important;">
                            <div class="d-flex align-items-center gap-3">
                                @if($index == 0)
                                    <div class="icon-box-dark bg-warning text-dark shadow-sm" style="width:36px; height:36px; border-radius:50%; font-weight:bold; font-size:1.1rem;">1</div>
                                @elseif($index == 1)
                                    <div class="icon-box-dark text-dark shadow-sm" style="background:#e2e8f0; width:36px; height:36px; border-radius:50%; font-weight:bold; font-size:1rem;">2</div>
                                @elseif($index == 2)
                                    <div class="icon-box-dark text-white shadow-sm" style="background:#cd7f32; width:36px; height:36px; border-radius:50%; font-weight:bold; font-size:1rem;">3</div>
                                @else
                                    <div class="icon-box-dark text-secondary" style="background:rgba(255,255,255,0.05); width:36px; height:36px; border-radius:50%; font-weight:bold;">{{ $index + 1 }}</div>
                                @endif
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $kitchen->KitchenName }}</h6>
                                    <small class="text-custom-muted">{{ $kitchen->FullName }}</small>
                                </div>
                            </div>
                            <span class="badge-soft-success">{{ $kitchen->order_count }} Orders</span>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-custom-muted py-2">No kitchens found.</div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="dark-card card p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5"><i data-feather="award" style="width:18px;color:#3b82f6"></i> Top Caterers ({{ $rangeLabels[$range] ?? 'All' }})</h4>
                </div>
                <div class="d-flex flex-column gap-3">
                    @if(count($topCaterers) > 0)
                        @foreach($topCaterers as $index => $caterer)
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2" style="border-color: rgba(255,255,255,0.05)!important;">
                            <div class="d-flex align-items-center gap-3">
                                @if($index == 0)
                                    <div class="icon-box-dark shadow-sm" style="background:#fbbf24; color:#fff; width:36px; height:36px; border-radius:50%; font-weight:bold; font-size:1.1rem;">1</div>
                                @elseif($index == 1)
                                    <div class="icon-box-dark shadow-sm" style="background:#94a3b8; color:#fff; width:36px; height:36px; border-radius:50%; font-weight:bold; font-size:1rem;">2</div>
                                @elseif($index == 2)
                                    <div class="icon-box-dark text-white shadow-sm" style="background:#b45309; width:36px; height:36px; border-radius:50%; font-weight:bold; font-size:1rem;">3</div>
                                @else
                                    <div class="icon-box-dark text-secondary" style="background:rgba(255,255,255,0.05); width:36px; height:36px; border-radius:50%; font-weight:bold;">{{ $index + 1 }}</div>
                                @endif
                                <div>
                                    <h6 class="fw-bold mb-1">{{ $caterer->CatererName }}</h6>
                                    <small class="text-custom-muted">{{ $caterer->FullName }}</small>
                                </div>
                            </div>
                            <span class="badge-soft-primary">{{ $caterer->request_count }} Requests</span>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center text-custom-muted py-2">No caterers found.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Details Modal -->
    <div class="modal fade" id="revenueDetailsModal" tabindex="-1" aria-labelledby="revenueDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content" style="background-color: #1e293b; color: #f8fafc; border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px;">
                <div class="modal-header border-bottom-0 pb-0">
                    <h5 class="modal-title heading-main fs-4" id="revenueDetailsModalLabel"><i data-feather="pie-chart" class="me-2 text-primary"></i> Revenue Analytics Breakdown</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3 mb-md-0">
                            <div class="p-3" style="background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid rgba(255,255,255,0.03);">
                                <h6 class="text-custom-muted mb-2 text-uppercase fs-6">Orders By Status</h6>
                                <div class="d-flex flex-column gap-2 mt-3">
                                    @foreach($ordersByStatus as $status => $count)
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="fw-medium">{{ $status }}</span>
                                        <span class="badge border border-secondary text-secondary">{{ $count }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 h-100" style="background: rgba(255,255,255,0.02); border-radius: 12px; border: 1px solid rgba(255,255,255,0.03);">
                                <h6 class="text-custom-muted mb-2 text-uppercase fs-6">Key Performance</h6>
                                <ul class="list-unstyled mt-3 mb-0 d-flex flex-column gap-3">
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span class="text-secondary"><i data-feather="check-circle" class="me-2" style="width:16px"></i> Total Orders</span>
                                        <strong class="fs-5">{{ number_format($totalOrders) }}</strong>
                                    </li>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span class="text-secondary"><i data-feather="dollar-sign" class="me-2 text-warning" style="width:16px"></i> Total Revenue</span>
                                        <strong class="fs-5 text-warning">{{ number_format($totalRevenue) }} <span class="fs-6 fw-normal">EGP</span></strong>
                                    </li>
                                    <li class="d-flex align-items-center justify-content-between">
                                        <span class="text-secondary"><i data-feather="briefcase" class="me-2 text-info" style="width:16px"></i> Total Wallets Balance</span>
                                        <strong class="fs-5 text-info">{{ number_format($totalWalletsSum) }} <span class="fs-6 fw-normal">EGP</span></strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('custom-scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function downloadReport(btn) {
        if (typeof html2pdf === 'undefined') {
            alert('PDF library is still loading or failed to load. Please wait a moment and try again.');
            return;
        }

        var originalText = btn.innerHTML;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Generating...';
        btn.disabled = true;

        var element = document.querySelector('.page-content');
        
        // Hide the top header temporarily so it doesn't appear in the PDF
        var headerDiv = document.querySelector('.d-flex.justify-content-between.align-items-center.flex-wrap.grid-margin');
        var originalDisplay = headerDiv ? headerDiv.style.display : '';
        if(headerDiv) headerDiv.style.display = 'none';

        var opt = {
            margin:       0.5,
            filename:     'Dashboard_Report_{{ date("Y-m-d") }}.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'in', format: 'a3', orientation: 'landscape' }
        };

        try {
            html2pdf().set(opt).from(element).save().then(function() {
                if(headerDiv) headerDiv.style.display = originalDisplay;
                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(function(err) {
                console.error(err);
                if(headerDiv) headerDiv.style.display = originalDisplay;
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('An error occurred while generating the PDF.');
            });
        } catch (e) {
            console.error(e);
            if(headerDiv) headerDiv.style.display = originalDisplay;
            btn.innerHTML = originalText;
            btn.disabled = false;
            alert('An error occurred starting the PDF generation.');
        }
    }

    // Dynamic Chart Initialization
    document.addEventListener("DOMContentLoaded", function() {
        var colors = { primary: "#6571ff", success: "#05a34a", warning: "#fbbc06", danger: "#ff3366", gridBorder: "rgba(77, 138, 240, .15)", bodyColor: "#b8c3d9", cardBg: "#0c1427", muted: "#7987a1" };
        var fontFamily = "'Roboto', Helvetica, sans-serif";

        // Main Revenue Chart (Dynamic from Backend)
        var chartData   = {!! json_encode($chartData) !!};
        var chartLabels = {!! json_encode($chartLabels) !!};

        var options4 = {
            chart: { type: 'bar', height: '318', parentHeightOffset: 0, foreColor: colors.bodyColor, background: colors.cardBg, toolbar: { show: false } },
            theme: { mode: 'light' },
            colors: [colors.primary],
            fill: { opacity: .9 },
            grid: { padding: { bottom: -4 }, borderColor: colors.gridBorder, xaxis: { lines: { show: true } } },
            series: [{ name: 'Revenue', data: chartData }],
            xaxis: {
                categories: chartLabels,
                axisBorder: { color: colors.gridBorder },
                axisTicks: { color: colors.gridBorder },
            },
            yaxis: { title: { text: 'Revenue (EGP)', style: { size: 9, color: colors.muted } }, labels: { formatter: function (val) { return val.toFixed(0); } } },
            legend: { show: true, position: "top", horizontalAlign: 'center', fontFamily: fontFamily },
            dataLabels: { enabled: true, style: { fontSize: '10px', fontFamily: fontFamily }, offsetY: -27 },
            plotOptions: { bar: { columnWidth: "50%", borderRadius: 4, dataLabels: { position: 'top', orientation: 'vertical' } } }
        };
        if(document.querySelector("#realMonthlySalesChart")) {
            new ApexCharts(document.querySelector("#realMonthlySalesChart"), options4).render();
        }

        // Mini Sparklines (Simplified logic)
        function renderSpark(id, data, color) {
            var opts = {
                chart: { type: "line", height: 60, sparkline: { enabled: true } },
                series: [{ data: data }],
                stroke: { width: 2, curve: "smooth" },
                markers: { size: 0 },
                colors: [color],
            };
            if(document.querySelector(id)) new ApexCharts(document.querySelector(id), opts).render();
        }

        renderSpark("#realCustomersChart", [3, 5, 4, 8, 2, 7, 6, 8, 3, 5, {{ $totalCustomers }}], colors.primary);
        renderSpark("#realOrdersChart", [10, 2, 4, 1, 8, 3, 5, 2, 8, 0, {{ $totalOrders }}], colors.success);
        renderSpark("#realGrowthChart", [5, 10, 8, 15, 12, 18, 20, 22, 25, 28, 30], colors.warning);
    });
</script>
@endpush

<style>
    @media print {
        body { margin: 0; padding: 0; background: #fff; }
        .sidebar, .navbar, .page-header { display: none !important; }
        .page-content { margin-top: 0 !important; margin-left: 0 !important; width: 100% !important; padding: 0 !important; }
        .d-flex.justify-content-between.align-items-center.flex-wrap.grid-margin { display: none !important; }
        .card { border: none !important; box-shadow: none !important; }
    }
</style>
@endsection
