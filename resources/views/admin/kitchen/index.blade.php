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
            border-color: rgba(59, 130, 246, 0.3);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.6); 
        }
        .heading-main { color: #f8fafc; font-weight: 800; letter-spacing: -0.025em; }
        .text-custom-muted { color: #94a3b8; font-weight: 500; }
        .banner-card { 
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
            border-radius: 24px; 
            padding: 25px 35px; 
            color: #ffffff; 
            position: relative; 
            overflow: hidden; 
            border: none;
            box-shadow: 0 20px 50px rgba(29, 78, 216, 0.25); 
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
            background: linear-gradient(135deg, #3b82f6, #2563eb); 
            color: #ffffff !important; 
            border: none; 
            border-radius: 10px; 
            font-weight: 700; 
            padding: 8px 16px; 
            transition: all 0.3s; 
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
            font-size: 0.8rem;
        }
        .btn-custom-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(59, 130, 246, 0.4); }
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
            <h3 class="heading-main mb-1">Kitchen Dashboard</h3>
        </div>
        <div class="d-flex align-items-center flex-wrap text-nowrap gap-3">
            <form action="{{ route('kitchen.toggle_plan_requests') }}" method="POST" id="planRequestsForm" class="m-0">
                @csrf
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="acceptsPlanRequests" name="accepts_plan_requests" style="cursor: pointer;"
                        onchange="document.getElementById('planRequestsForm').submit()"
                        {{ ($kitchen && $kitchen->AcceptsPlanRequests) ? 'checked' : '' }}>
                    <label class="form-check-label text-white fw-bold mb-0 ms-1" for="acceptsPlanRequests" style="cursor: pointer;">Accepts Custom Plans</label>
                </div>
            </form>
            <span class="badge bg-primary px-3 py-2 fs-6">Kitchen Owner</span>
        </div>
    </div>
 
    <!-- Quick Working Hours -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="dark-card card p-3" style="background: linear-gradient(90deg, #1e293b 0%, #0f172a 100%); border: 1px solid rgba(255,255,255,0.08);">
                <form action="{{ route('kitchen.update_hours') }}" method="POST" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    @csrf
                    <div class="d-flex align-items-center">
                        <div class="icon-box-dark icon-primary me-3" style="width:45px; height:45px; background: rgba(59, 130, 246, 0.1);"><i data-feather="clock" style="width:20px"></i></div>
                        <div>
                            <h5 class="heading-main mb-0 fs-6">Operating Hours</h5>
                            @php
                                $status = $kitchen->current_status ?? 'Closed';
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
                                    value="{{ \Carbon\Carbon::parse($kitchen->OpeningTime ?? '09:00')->format('H:i') }}" required>
                            </div>
                        </div>
                        <div class="text-white-50 mt-3"><i data-feather="arrow-right" style="width: 16px;"></i></div>
                        <div class="text-center">
                            <label class="text-custom-muted small fw-bold mb-1 d-block" style="font-size: 0.65rem; letter-spacing: 1px;">CLOSING TIME</label>
                            <div class="position-relative">
                                <input type="time" name="closing_time" class="form-control bg-dark text-white border-secondary py-2 px-3" 
                                    style="width:150px; border-radius:12px; border: 1px solid rgba(255,255,255,0.1); font-weight: 600;" 
                                    value="{{ \Carbon\Carbon::parse($kitchen->ClosingTime ?? '22:00')->format('H:i') }}" required>
                            </div>
                        </div>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary px-4 py-2 fw-bold" style="border-radius:12px; background: linear-gradient(135deg, #3b82f6, #2563eb); border:none; box-shadow:0 4px 15px rgba(59,130,246,0.4); height: 45px;">
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
                            <span class="fs-2">👨‍🍳</span>
                        </div>
                        <h1 class="fw-bold mb-0 text-white" style="letter-spacing: -0.03em;">Welcome, {{ Auth::user()->name }}</h1>
                    </div>
                    <p class="mb-0 text-white-50" style="font-size: 1.1rem; max-width: 600px;">
                        You have <span class="badge bg-white text-primary px-2 py-1 rounded-pill fw-bold mx-1">{{ $pendingOrders }} pending orders</span> waiting for your magic in the kitchen. Let's get cooking!
                    </p>
                </div>
                <div class="d-none d-md-block" style="z-index: 1;">
                    <a href="{{ route('kitchen.dashboard') }}" class="btn btn-white rounded-pill px-5 py-3 fw-bold text-primary shadow-lg border-0" style="background: #ffffff; transition: all 0.3s; box-shadow: 0 15px 35px rgba(0,0,0,0.2) !important;">
                        <i data-feather="settings" class="me-2" style="width:18px"></i>Manage Kitchen
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
                        <p class="text-custom-muted small mt-2" style="font-size: 0.7rem;"><i data-feather="info" style="width:12px" class="me-1"></i> Commission included</p>
                    </div>
                    <div class="icon-box-dark icon-success"><i data-feather="dollar-sign" style="width:20px"></i></div>
                </div>
                <div class="d-flex gap-2 mt-auto">
                    <button type="button" class="btn btn-custom-primary flex-grow-1" data-bs-toggle="modal" data-bs-target="#topupModal">Top Up</button>
                    <button type="button" class="btn btn-custom-light flex-grow-1" data-bs-toggle="modal" data-bs-target="#withdrawModal">Withdraw</button>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card h-100 p-4 border-0" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(30, 41, 59, 1) 100%); border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <p class="stat-title text-success">Monthly Revenue</p>
                        <h4 class="stat-value text-success">{{ number_format($monthlyRevenue ?? 0, 2) }} <span class="fw-normal fs-6">EGP</span></h4>
                        <p class="text-custom-muted small mt-1" style="font-size: 0.7rem;">From delivered orders this month</p>
                    </div>
                    <div class="icon-box-dark icon-success"><i data-feather="trending-up" style="width:20px"></i></div>
                </div>
                <div class="mt-auto pt-2">
                    <span class="text-success small fw-bold d-flex align-items-center"><i data-feather="check-circle" class="me-1" style="width:12px"></i> Financial Success</span>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card h-100 p-4 border-0" style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.15) 0%, rgba(30, 41, 59, 1) 100%); border: 1px solid rgba(239, 68, 68, 0.2) !important;">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <p class="stat-title text-danger">Open Tickets</p>
                        <h4 class="stat-value text-danger">{{ $openSupportTickets ?? 0 }}</h4>
                        <p class="text-custom-muted small mt-1" style="font-size: 0.7rem;">Needs your attention</p>
                    </div>
                    <div class="icon-box-dark icon-danger"><i data-feather="life-buoy" style="width:20px"></i></div>
                </div>
                <div class="mt-auto pt-2">
                    <a href="{{ route('kitchen.support') }}" class="text-danger text-decoration-none small fw-bold d-flex align-items-center">Support Center <i data-feather="arrow-right" class="ms-1" style="width:12px"></i></a>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="dark-card card h-100 p-4 border-0" style="background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(30, 41, 59, 1) 100%); border: 1px solid rgba(245, 158, 11, 0.1) !important;">
                <div class="d-flex align-items-start justify-content-between mb-3">
                    <div>
                        <p class="stat-title text-warning">Pending Orders</p>
                        <h4 class="stat-value text-warning">{{ $pendingOrders }}</h4>
                        <p class="text-custom-muted small mt-1" style="font-size: 0.7rem;">Standard orders to prepare</p>
                    </div>
                    <div class="icon-box-dark icon-warning"><i data-feather="activity" style="width:20px"></i></div>
                </div>
                <div class="mt-auto pt-2">
                    <a href="{{ route('kitchen.orders') }}?type=standard&status=Pending" class="text-warning text-decoration-none small fw-bold d-flex align-items-center">Process Orders <i data-feather="arrow-right" class="ms-1" style="width:12px"></i></a>
                </div>
            </div>
        </div>
    </div>

    <!-- Subscription KPI Row -->
    <div class="row g-4 mb-4">
        <div class="col-xl-6 col-md-6">
            <div class="dark-card card p-4" style="background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(30, 41, 59, 1) 100%); height: 130px;">
                <div class="d-flex align-items-center justify-content-between h-100">
                    <div>
                        <p class="stat-title mb-1">Active Subscribers</p>
                        <h4 class="stat-value" style="font-size: 1.8rem;">{{ $activeSubscribers }} <span class="text-custom-muted fw-normal fs-6">Users</span></h4>
                        <p class="text-custom-muted mb-0 fs-7">Currently active recurring plans</p>
                    </div>
                    <div class="icon-box-dark icon-primary" style="width: 56px; height: 56px; border-radius: 50%;"><i data-feather="users" style="width: 24px;"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="dark-card card p-4" style="background: linear-gradient(135deg, rgba(251, 191, 36, 0.08) 0%, rgba(30, 41, 59, 1) 100%); height: 130px;">
                <div class="d-flex align-items-center justify-content-between h-100">
                    <div>
                        <p class="stat-title mb-1">Today's Meals</p>
                        <h4 class="stat-value" style="font-size: 1.8rem;">{{ $todaySubscribedMeals }} <span class="text-custom-muted fw-normal fs-6">Portions</span></h4>
                        <p class="text-custom-muted mb-0 fs-7">Total scheduled for today</p>
                    </div>
                    <div class="icon-box-dark icon-warning" style="width: 56px; height: 56px; border-radius: 50%;"><i data-feather="package" style="width: 24px;"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-5">
        <!-- Recent Orders -->
        <div class="col-lg-8">
            <div class="dark-card card h-100 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="heading-main mb-0 fs-5 d-flex align-items-center">
                        <span class="icon-box-dark icon-primary me-3" style="width:40px; height:40px;"><i data-feather="clock" style="width:18px"></i></span>
                        Recent Order Activity
                    </h4>
                    <a href="{{ route('kitchen.orders') }}?type=standard" class="btn btn-link text-primary text-decoration-none fw-bold p-0">All Orders <i data-feather="chevron-right" style="width:16px"></i></a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="color: #e2e8f0;">
                        <thead>
                            <tr style="border-bottom: 2px solid rgba(255,255,255,0.03);">
                                <th class="ps-0 py-3 text-custom-muted fs-7">ORDER ID</th>
                                <th class="py-3 text-custom-muted fs-7">CUSTOMER</th>
                                <th class="py-3 text-custom-muted fs-7">TOTAL</th>
                                <th class="py-3 text-custom-muted fs-7">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentOrders as $ro)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03);">
                                    <td class="ps-0 py-3 fw-bold text-primary">#{{ $ro->OrderID }}</td>
                                    <td class="py-3">{{ $ro->customer->user->FullName ?? 'N/A' }}</td>
                                    <td class="py-3 fw-bold">{{ number_format($ro->TotalPrice, 2) }} EGP</td>
                                    <td class="py-3">
                                        @php
                                            $badgeClass = match($ro->OrderStatus) {
                                                'Pending' => 'bg-warning',
                                                'Confirmed', 'Preparing', 'Ready' => 'bg-info',
                                                'Delivered' => 'bg-success',
                                                'Cancelled' => 'bg-danger',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} bg-opacity-10 text-{{ str_replace('bg-', '', $badgeClass) }} border border-{{ str_replace('bg-', '', $badgeClass) }} border-opacity-20 px-3 py-1">{{ $ro->OrderStatus }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-5 text-custom-muted">No recent orders yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Today's Stats Breakdown -->
        <div class="col-lg-4 mt-4 mt-lg-0">
            <div class="dark-card card h-100 p-4">
                <h4 class="heading-main mb-4 fs-5">Quick Stats</h4>
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 rounded bg-white bg-opacity-5 border border-white border-opacity-5">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-custom-muted fs-7">TODAY'S ORDERS</span>
                            <span class="text-white fw-bold">{{ $todayOrders }}</span>
                        </div>
                        <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar bg-primary" style="width: 45%;"></div>
                        </div>
                    </div>
                    <div class="p-3 rounded bg-white bg-opacity-5 border border-white border-opacity-5">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-custom-muted fs-7">MENU VISIBILITY</span>
                            <span class="text-white fw-bold">High</span>
                        </div>
                        <div class="progress" style="height: 4px; background: rgba(255,255,255,0.05);">
                            <div class="progress-bar bg-success" style="width: 85%;"></div>
                        </div>
                    </div>
                    
                    <div class="mt-auto">
                         <h6 class="text-white small mb-3 text-uppercase opacity-50" style="letter-spacing: 1px;">Top Action</h6>
                         <a href="{{ route('kitchen.menu') }}" class="btn btn-outline-primary w-100 py-2 fw-bold d-flex align-items-center justify-content-center">
                            <i data-feather="plus-circle" class="me-2" style="width:16px;"></i> Add New Dish
                         </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Grouped Quick Actions -->
    <div class="row mb-4 g-4">
        <div class="col-md-4">
            <div class="dark-card card p-4">
                <h6 class="text-custom-muted small text-uppercase mb-3 fw-bold" style="letter-spacing: 1px;">Management</h6>
                <div class="list-group list-group-flush bg-transparent">
                    <a href="{{ route('kitchen.menu') }}" class="list-group-item bg-transparent text-white border-white border-opacity-5 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="book-open" class="me-2 text-primary" style="width:16px;"></i> Edit Menu</span>
                        <i data-feather="chevron-right" style="width:14px;"></i>
                    </a>
                    <a href="{{ route('kitchen.categories') }}" class="list-group-item bg-transparent text-white border-0 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="tag" class="me-2 text-info" style="width:16px;"></i> Item Categories</span>
                        <i data-feather="chevron-right" style="width:14px;"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dark-card card p-4">
                <h6 class="text-custom-muted small text-uppercase mb-3 fw-bold" style="letter-spacing: 1px;">Financials</h6>
                <div class="list-group list-group-flush bg-transparent">
                    <a href="{{ route('withdraw.methods.index') }}" class="list-group-item bg-transparent text-white border-white border-opacity-5 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="credit-card" class="me-2 text-success" style="width:16px;"></i> Payout Methods</span>
                        <i data-feather="chevron-right" style="width:14px;"></i>
                    </a>
                    <a href="{{ route('kitchen.refunds') }}" class="list-group-item bg-transparent text-white border-white border-opacity-5 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="refresh-ccw" class="me-2 text-danger" style="width:16px;"></i> Refund History</span>
                        <i data-feather="chevron-right" style="width:14px;"></i>
                    </a>
                    <a href="{{ route('kitchen.ads') }}" class="list-group-item bg-transparent text-white border-0 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="speaker" class="me-2 text-primary" style="width:16px;"></i> Run Ad Campaign</span>
                        <i data-feather="chevron-right" style="width:14px;"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="dark-card card p-4">
                <h6 class="text-custom-muted small text-uppercase mb-3 fw-bold" style="letter-spacing: 1px;">Communication</h6>
                <div class="list-group list-group-flush bg-transparent">
                    <a href="{{ route('kitchen.support') }}" class="list-group-item bg-transparent text-white border-white border-opacity-5 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="life-buoy" class="me-2 text-info" style="width:16px;"></i> Support Center</span>
                        <span class="badge bg-danger rounded-pill">{{ $openSupportTickets }}</span>
                    </a>
                    <a href="{{ route('kitchen.customization.requests') }}" class="list-group-item bg-transparent text-white border-white border-opacity-5 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="message-square" class="me-2 text-primary" style="width:16px;"></i> User Chat</span>
                        <i data-feather="chevron-right" style="width:14px;"></i>
                    </a>
                    <a href="{{ route('kitchen.profile') }}" class="list-group-item bg-transparent text-white border-0 px-0 d-flex justify-content-between align-items-center">
                        <span><i data-feather="user" class="me-2 text-warning" style="width:16px;"></i> Profile Settings</span>
                        <i data-feather="chevron-right" style="width:14px;"></i>
                    </a>
                </div>
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
            series: [{ name: 'Kitchen Orders', data: monthlySalesData }],
            xaxis: {
                categories: ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
                axisBorder: { color: 'rgba(255,255,255,0.1)' },
                axisTicks: { color: 'rgba(255,255,255,0.1)' },
            },
            yaxis: { title: { text: 'Orders', style: { size: 9, color: colors.muted } } },
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
        const hasSeenTour = localStorage.getItem('bitehub_tour_kitchen_' + userId);
        
        if (!hasSeenTour) {
            const driver = window.driver.js.driver;
            const driverObj = driver({
                showProgress: true,
                steps: [
                    { element: '.form-check.form-switch', popover: { title: 'Accept Custom Plans', description: 'Enable this toggle to allow users to request custom multi-day meal plans from your kitchen.', side: "bottom", align: 'start' }},
                    { element: '.banner-card', popover: { title: 'Kitchen Overview', description: 'At a glance, see how many pending orders you have waiting for preparation.', side: "bottom", align: 'start' }},
                    { element: 'a[href*="kitchen/menu"] .dark-card', popover: { title: 'Manage Menu', description: 'Click here to add, edit, or remove dishes from your active menu.', side: "top", align: 'start' }},
                    { element: 'a[href*="kitchen/orders"] .dark-card', popover: { title: 'Process Orders', description: 'Here is where you process sales and update the statuses of all incoming orders.', side: "top", align: 'start' }}
                ],
                onDestroyStarted: () => {
                    localStorage.setItem('bitehub_tour_kitchen_' + userId, 'true');
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
