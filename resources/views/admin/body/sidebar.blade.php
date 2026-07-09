  <!-- partial:partials/_sidebar.html -->
  <nav class="sidebar">
    <div class="sidebar-header">
        <a href="#" class="sidebar-brand">
            Bite<span>Hub</span>
        </a>
        <div class="sidebar-toggler active">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>
    <div class="sidebar-body">
        <ul class="nav">
            @php
                $adminCounts = [];
                $kitchenCounts = [];
                $catererCounts = [];
                $agentCounts = [];

                if (Auth::check()) {
                    $role = Auth::user()->Role;

                    if ($role === 'Admin' || $role === 'Owner') {
                        $adminCounts['users'] = \App\Models\User::whereDate('CreatedAt', \Carbon\Carbon::today())->count();
                        $adminCounts['kitchens'] = \App\Models\KitchenOwner::where('VerifyStatus', 'Pending')->count();
                        try { $adminCounts['caterers'] = \App\Models\Caterer::where('IsActive', 0)->count(); } catch(\Exception $e) { $adminCounts['caterers'] = 0; }
                        $adminCounts['orders'] = \App\Models\Order::where('OrderStatus', 'Pending')->count();
                        $adminCounts['catering'] = \App\Models\CateringRequest::where('Status', 'Pending')->count();
                        // Admin should see BOTH 'Pending' and 'PendingApproval' or just 'PendingApproval' depending on how it's defined.
                        // Based on KitchenOwnerController, 'PendingApproval' is the status for new requests.
                        try { $adminCounts['subscriptions'] = \App\Models\Subscription::where('Status', 'PendingApproval')->count(); } catch(\Exception $e) { $adminCounts['subscriptions'] = 0; }
                        try { $adminCounts['ads'] = \App\Models\Advertising::where('Status', 'Pending')->count(); } catch(\Exception $e) { $adminCounts['ads'] = 0; }
                        try { $adminCounts['withdrawals'] = \App\Models\WithdrawalRequest::where('Status', 'Pending')->count(); } catch(\Exception $e) { $adminCounts['withdrawals'] = 0; }
                        try { 
                            $adminCounts['customizations'] = \App\Models\LiveChat::whereNull('OrderID')->where('Type', 'request')->whereNotNull('SessionID')
                                ->whereNotIn('SessionID', function($q) { $q->select('SessionID')->from('live_chats')->whereNotNull('SessionID')->whereIn('Type', ['rejected', 'added_to_cart']); })
                                ->count(); 
                        } catch(\Exception $e) { $adminCounts['customizations'] = 0; }
                        $adminCounts['refunds'] = \App\Models\RefundRequest::where('Status', 'Pending')->count();
                    } elseif ($role === 'KitchenOwner') {
                        $ko = \App\Models\KitchenOwner::where('UserID', Auth::id())->first();
                        if ($ko) {
                            // Direct query on orders table is faster and handles split orders correctly
                            $kitchenCounts['standard_orders'] = \App\Models\Order::where('KitchenOwnerID', $ko->KitchenOwnerID)
                                                        ->whereNull('SubscriptionID')
                                                        ->where('OrderStatus', 'Pending')->count();
                            
                            $kitchenCounts['plan_orders'] = \App\Models\Order::where('KitchenOwnerID', $ko->KitchenOwnerID)
                                                        ->whereNotNull('SubscriptionID')
                                                        ->where('OrderStatus', 'Pending')->count();
                            
                            try { $kitchenCounts['subscriptions'] = \App\Models\Subscription::where('KitchenOwnerID', $ko->KitchenOwnerID)->where('Status', 'PendingApproval')->count(); } catch(\Exception $e) { $kitchenCounts['subscriptions'] = 0; }
                            try { 
                                $kitchenCounts['customizations'] = \App\Models\LiveChat::whereNull('OrderID')->where('ReceiverID', Auth::id())->where('Type', 'request')->whereNotNull('SessionID')
                                    ->whereNotIn('SessionID', function($q) { $q->select('SessionID')->from('live_chats')->whereNotNull('SessionID')->whereIn('Type', ['rejected', 'added_to_cart']); })
                                    ->count(); 
                            } catch(\Exception $e) { $kitchenCounts['customizations'] = 0; }

                            // NEW: Support & Refunds for Kitchen Owner
                            $kitchenCounts['support'] = \App\Models\SupportTicket::where('UserID', Auth::id())->whereIn('Status', ['Open', 'InProgress'])->count();
                            try {
                                $kitchenCounts['refunds'] = \App\Models\RefundRequest::where('Status', 'Pending')
                                    ->where(function($q) use ($ko) {
                                        $q->where(function($sq) use ($ko) {
                                            $sq->where('RefundableType', 'Order')
                                               ->whereHas('order', function($oq) use ($ko) { $oq->where('KitchenOwnerID', $ko->KitchenOwnerID); });
                                        })->orWhere(function($sq) use ($ko) {
                                            $sq->where('RefundableType', 'Subscription')
                                               ->whereHas('subscription', function($subq) use ($ko) { $subq->where('KitchenOwnerID', $ko->KitchenOwnerID); });
                                        });
                                    })->count();
                            } catch(\Exception $e) { $kitchenCounts['refunds'] = 0; }

                            // Active Subs & Ads for Kitchen Owner
                            $kitchenCounts['active_subs'] = \App\Models\Subscription::where('KitchenOwnerID', $ko->KitchenOwnerID)->where('Status', 'Active')->count();
                            try { $kitchenCounts['ads'] = \App\Models\Advertising::where('KitchenOwnerID', $ko->KitchenOwnerID)->where('Status', 'Active')->count(); } catch(\Exception $e) { $kitchenCounts['ads'] = 0; }
                        }
                    } elseif ($role === 'Caterer') {
                        $cat = \App\Models\Caterer::where('UserID', Auth::id())->first();
                        if ($cat) {
                            $catererCounts['orders'] = \App\Models\Order::where('CatererID', $cat->CatererID)
                                                        ->where('OrderStatus', 'Pending')->count();
                            
                            $catererCounts['catering'] = \App\Models\CateringRequest::where('CatererID', $cat->CatererID)->where('Status', 'Pending')->count();
                            try { 
                                $catererCounts['customizations'] = \App\Models\LiveChat::whereNull('OrderID')->where('ReceiverID', Auth::id())->where('Type', 'request')->whereNotNull('SessionID')
                                    ->whereNotIn('SessionID', function($q) { $q->select('SessionID')->from('live_chats')->whereNotNull('SessionID')->whereIn('Type', ['rejected', 'added_to_cart']); })
                                    ->count(); 
                            } catch(\Exception $e) { $catererCounts['customizations'] = 0; }

                            // NEW: Support & Refunds for Caterer
                            $catererCounts['support'] = \App\Models\SupportTicket::where('UserID', Auth::id())->whereIn('Status', ['Open', 'InProgress'])->count();
                            try {
                                $catererCounts['refunds'] = \App\Models\RefundRequest::where('Status', 'Pending')
                                    ->where('RefundableType', 'Order')
                                    ->whereHas('order', function($oq) use ($cat) { $oq->where('CatererID', $cat->CatererID); })
                                    ->count();
                            } catch(\Exception $e) { $catererCounts['refunds'] = 0; }
                            
                            try { $catererCounts['ads'] = \App\Models\Advertising::where('CatererID', $cat->CatererID)->where('Status', 'Active')->count(); } catch(\Exception $e) { $catererCounts['ads'] = 0; }
                        }
                    } elseif ($role === 'DeliveryAgent') {
                        $ag = \App\Models\DeliveryAgent::where('UserID', Auth::id())->first();
                        if ($ag) {
                            $agentCounts['deliveries'] = \App\Models\Order::where('DeliveryAgentID', $ag->DeliveryAgentID)
                                ->whereIn('OrderStatus', ['Pending', 'Confirmed', 'Preparing', 'Ready', 'Delivering'])
                                ->count();
                        }
                    }
                }
            @endphp


            {{-- ===================== ADMIN ===================== --}}
            @if(Auth::check() && in_array(Auth::user()->Role, ['Admin', 'Owner']))
                <li class="nav-item nav-category">Main</li>
                <li class="nav-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kpi') ? 'active' : '' }}">
                    <a href="{{ route('admin.kpi') }}" class="nav-link">
                        <i class="link-icon" data-feather="pie-chart"></i>
                        <span class="link-title">Performance (KPI)</span>
                    </a>
                </li>

                <li class="nav-item nav-category">People</li>
                <li class="nav-item {{ Request::is('admin/users*') ? 'active' : '' }}">
                    <a href="{{ route('admin.users') }}" class="nav-link">
                        <i class="link-icon" data-feather="users"></i>
                        <span class="link-title">All Users <span class="badge {{ ($adminCounts['users']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['users'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchens*') ? 'active' : '' }}">
                    <a href="{{ route('admin.kitchens') }}" class="nav-link">
                        <i class="link-icon" data-feather="home"></i>
                        <span class="link-title">Kitchens <span class="badge {{ ($adminCounts['kitchens']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['kitchens'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/caterers*') ? 'active' : '' }}">
                    <a href="{{ route('admin.caterers') }}" class="nav-link">
                        <i class="link-icon" data-feather="briefcase"></i>
                        <span class="link-title">Caterers <span class="badge {{ ($adminCounts['caterers']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['caterers'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/agents*') ? 'active' : '' }}">
                    <a href="{{ route('admin.agents') }}" class="nav-link">
                        <i class="link-icon" data-feather="truck"></i>
                        <span class="link-title">Delivery Agents</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/wallets*') ? 'active' : '' }}">
                    <a href="{{ route('admin.wallets') }}" class="nav-link">
                        <i class="link-icon" data-feather="credit-card"></i>
                        <span class="link-title">Wallets</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/withdrawals*') ? 'active' : '' }}">
                    <a href="{{ route('admin.withdrawals.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="external-link"></i>
                        <span class="link-title">Withdrawals <span class="badge {{ ($adminCounts['withdrawals']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['withdrawals'] ?? 0 }}</span></span>
                    </a>
                </li>

                @if(Auth::user()->Role === 'Owner')
                <li class="nav-item nav-category">Management</li>
                <li class="nav-item">
                    <a href="{{ route('admin.admins.list') }}" class="nav-link">
                        <i class="link-icon" data-feather="shield"></i>
                        <span class="link-title">Admins List</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.audit.logs') }}" class="nav-link">
                        <i class="link-icon" data-feather="activity"></i>
                        <span class="link-title">Audit Logs</span>
                    </a>
                </li>
                @endif

                <li class="nav-item nav-category">Operations</li>
                <li class="nav-item {{ Request::is('admin/orders*') ? 'active' : '' }}">
                    <a href="{{ route('admin.orders') }}" class="nav-link">
                        <i class="link-icon" data-feather="shopping-bag"></i>
                        <span class="link-title">Orders <span class="badge {{ ($adminCounts['orders']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['orders'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/customization-requests*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.customization.requests') }}" class="nav-link">
                        <i class="link-icon" data-feather="message-square"></i>
                        <span class="link-title">Customization Requests <span class="badge {{ ($adminCounts['customizations']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['customizations'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/catering*') ? 'active' : '' }}">
                    <a href="{{ route('admin.catering') }}" class="nav-link">
                        <i class="link-icon" data-feather="coffee"></i>
                        <span class="link-title">Catering Requests <span class="badge {{ ($adminCounts['catering']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['catering'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/subscriptions*') ? 'active' : '' }}">
                    <a href="{{ route('admin.subscriptions') }}" class="nav-link">
                        <i class="link-icon" data-feather="repeat"></i>
                        <span class="link-title">Subscriptions <span class="badge {{ ($adminCounts['subscriptions']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['subscriptions'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/refunds*') ? 'active' : '' }}">
                    <a href="{{ route('admin.refunds') }}" class="nav-link">
                        <i class="link-icon" data-feather="refresh-ccw"></i>
                        <span class="link-title">Refund Requests <span class="badge {{ ($adminCounts['refunds']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['refunds'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/categories*') ? 'active' : '' }}">
                    <a href="{{ route('admin.categories') }}" class="nav-link">
                        <i class="link-icon" data-feather="tag"></i>
                        <span class="link-title">Categories</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Marketing</li>
                <li class="nav-item {{ Request::is('admin/ads*') ? 'active' : '' }}">
                    <a href="{{ route('admin.ads') }}" class="nav-link">
                        <i class="link-icon" data-feather="speaker"></i>
                        <span class="link-title">Advertisements <span class="badge {{ ($adminCounts['ads']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $adminCounts['ads'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/loyalty*') ? 'active' : '' }}">
                    <a href="{{ route('admin.loyalty') }}" class="nav-link">
                        <i class="link-icon" data-feather="star"></i>
                        <span class="link-title">Loyalty Points</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/promo-codes*') ? 'active' : '' }}">
                    <a href="{{ route('admin.promo_codes') }}" class="nav-link">
                        <i class="link-icon" data-feather="tag"></i>
                        <span class="link-title">Promo Codes</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Support</li>
                <li class="nav-item {{ Request::is('admin/reports*') ? 'active' : '' }}">
                    <a href="{{ route('admin.reports') }}" class="nav-link">
                        <i class="link-icon" data-feather="life-buoy"></i>
                        <span class="link-title">Reports & Support @php $openTickets = \App\Models\SupportTicket::where('Status','Open')->count(); @endphp <span class="badge {{ $openTickets > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $openTickets }}</span></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.inquiries') }}" class="nav-link">
                        <i class="link-icon" data-feather="message-circle"></i>
                        <span class="link-title">Support Inquiries @php $openInq = \App\Models\SupportInquiry::where('Status','Escalated')->count(); @endphp <span class="badge {{ $openInq > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $openInq }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/error-reports*') ? 'active' : '' }}">
                    <a href="{{ route('admin.error-reports') }}" class="nav-link">
                        <i class="link-icon" data-feather="alert-triangle"></i>
                        <span class="link-title">Error Reports @php $pendingErrors = \App\Models\ErrorReport::where('Status','Pending')->count(); @endphp <span class="badge {{ $pendingErrors > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $pendingErrors }}</span></span>
                    </a>
                </li>

                <li class="nav-item nav-category">Account</li>
                <li class="nav-item">
                    <a href="{{ route('notifications.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="bell"></i>
                        <span class="link-title">Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.profile') }}" class="nav-link">
                        <i class="link-icon" data-feather="user"></i>
                        <span class="link-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.change.password') }}" class="nav-link">
                        <i class="link-icon" data-feather="lock"></i>
                        <span class="link-title">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('admin.logout') }}" class="nav-link">
                        <i class="link-icon" data-feather="log-out"></i>
                        <span class="link-title">Logout</span>
                    </a>
                </li>

            {{-- ===================== KITCHEN OWNER ===================== --}}
            @elseif(Auth::check() && Auth::user()->Role === 'KitchenOwner')
                <li class="nav-item nav-category">Main Dashboard</li>
                <li class="nav-item {{ Request::is('admin/kitchen/dashboard') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.dashboard') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/kpi') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.kpi') }}" class="nav-link">
                        <i class="link-icon" data-feather="pie-chart"></i>
                        <span class="link-title">Performance (KPI)</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Menu & Products</li>
                <li class="nav-item {{ Request::is('admin/kitchen/menu*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.menu') }}" class="nav-link">
                        <i class="link-icon" data-feather="book-open"></i>
                        <span class="link-title">My Menu Items</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/categories*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.categories') }}" class="nav-link">
                        <i class="link-icon" data-feather="tag"></i>
                        <span class="link-title">Categories</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Orders & Sales</li>
                <li class="nav-item {{ Request::is('admin/kitchen/orders*') && request()->get('type') == 'standard' ? 'active' : '' }}">
                    <a href="{{ route('kitchen.orders', ['type' => 'standard']) }}" class="nav-link">
                        <i class="link-icon" data-feather="shopping-cart"></i>
                        <span class="link-title">Standard Orders <span class="badge {{ ($kitchenCounts['standard_orders']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['standard_orders'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/orders*') && request()->get('type') == 'plan' ? 'active' : '' }}">
                    <a href="{{ route('kitchen.orders', ['type' => 'plan']) }}" class="nav-link">
                        <i class="link-icon" data-feather="truck"></i>
                        <span class="link-title">Plan Deliveries <span class="badge {{ ($kitchenCounts['plan_orders']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['plan_orders'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/customization-requests*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.customization.requests') }}" class="nav-link">
                        <i class="link-icon" data-feather="message-square"></i>
                        <span class="link-title">Customization Requests <span class="badge {{ ($kitchenCounts['customizations']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['customizations'] ?? 0 }}</span></span>
                    </a>
                </li>

                <li class="nav-item nav-category">Subscriptions</li>
                <li class="nav-item {{ Request::is('admin/kitchen/subscriptions') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.subscriptions') }}" class="nav-link">
                        <i class="link-icon" data-feather="users"></i>
                        <span class="link-title">Active Subscriptions <span class="badge {{ ($kitchenCounts['active_subs']??0) > 0 ? 'bg-success' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['active_subs'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/subscription-requests*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.subscriptions.requests') }}" class="nav-link">
                        <i class="link-icon" data-feather="bell"></i>
                        <span class="link-title">Subscription Requests <span class="badge {{ ($kitchenCounts['subscriptions']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['subscriptions'] ?? 0 }}</span></span>
                    </a>
                </li>

                <li class="nav-item nav-category">Finance & Growth</li>
                <li class="nav-item {{ Request::is('admin/kitchen/withdraw*') ? 'active' : '' }}">
                    <a href="{{ route('withdraw.methods.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="dollar-sign"></i>
                        <span class="link-title">Withdrawal Methods</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/refunds*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.refunds') }}" class="nav-link">
                        <i class="link-icon" data-feather="refresh-ccw"></i>
                        <span class="link-title">Deducted Refunds <span class="badge {{ ($kitchenCounts['refunds']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['refunds'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/ads*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.ads') }}" class="nav-link">
                        <i class="link-icon" data-feather="speaker"></i>
                        <span class="link-title">Advertisements <span class="badge {{ ($kitchenCounts['ads']??0) > 0 ? 'bg-primary' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['ads'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/promo-codes*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.promo_codes') }}" class="nav-link">
                        <i class="link-icon" data-feather="tag"></i>
                        <span class="link-title">Promo Codes</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Support Center</li>
                <li class="nav-item {{ Request::is('admin/kitchen/support*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.support') }}" class="nav-link">
                        <i class="link-icon" data-feather="life-buoy"></i>
                        <span class="link-title">Support <span class="badge {{ ($kitchenCounts['support']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $kitchenCounts['support'] ?? 0 }}</span></span>
                    </a>
                </li>

                <li class="nav-item nav-category">Account</li>
                <li class="nav-item">
                    <a href="{{ route('notifications.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="bell"></i>
                        <span class="link-title">Notifications</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/profile*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.profile') }}" class="nav-link">
                        <i class="link-icon" data-feather="user"></i>
                        <span class="link-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/kitchen/change-password*') ? 'active' : '' }}">
                    <a href="{{ route('kitchen.change.password') }}" class="nav-link">
                        <i class="link-icon" data-feather="lock"></i>
                        <span class="link-title">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('kitchen.logout') }}" class="nav-link">
                        <i class="link-icon" data-feather="log-out"></i>
                        <span class="link-title">Logout</span>
                    </a>
                </li>

            {{-- ===================== CATERER ===================== --}}
            @elseif(Auth::check() && Auth::user()->Role === 'Caterer')
                <li class="nav-item nav-category">Main Dashboard</li>
                <li class="nav-item">
                    <a href="{{ route('caterer.dashboard') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.kpi') }}" class="nav-link">
                        <i class="link-icon" data-feather="pie-chart"></i>
                        <span class="link-title">Performance (KPI)</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Orders & Requests</li>
                <li class="nav-item">
                    <a href="{{ route('caterer.orders') }}" class="nav-link">
                        <i class="link-icon" data-feather="shopping-cart"></i>
                        <span class="link-title">Incoming Orders <span class="badge {{ ($catererCounts['orders']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $catererCounts['orders'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.customization.requests') }}" class="nav-link">
                        <i class="link-icon" data-feather="message-circle"></i>
                        <span class="link-title">Customizations <span class="badge {{ ($catererCounts['customizations']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $catererCounts['customizations'] ?? 0 }}</span></span>
                    </a>
                </li>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.requests') }}" class="nav-link">
                        <i class="link-icon" data-feather="clipboard"></i>
                        <span class="link-title">Catering Requests <span class="badge {{ ($catererCounts['catering']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $catererCounts['catering'] ?? 0 }}</span></span>
                    </a>
                </li>

                <li class="nav-item nav-category">Menu & Products</li>
                <li class="nav-item">
                    <a href="{{ route('caterer.menu') }}" class="nav-link">
                        <i class="link-icon" data-feather="list"></i>
                        <span class="link-title">Menu Items</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.categories') }}" class="nav-link">
                        <i class="link-icon" data-feather="tag"></i>
                        <span class="link-title">Categories</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Marketing</li>
                <li class="nav-item">
                    <a href="{{ route('caterer.ads') }}" class="nav-link">
                        <i class="link-icon" data-feather="speaker"></i>
                        <span class="link-title">Advertisements <span class="badge {{ ($catererCounts['ads']??0) > 0 ? 'bg-primary' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $catererCounts['ads'] ?? 0 }}</span></span>
                    </a>
                </li>
                </li>

                <li class="nav-item nav-category">Finance & Growth</li>
                <li class="nav-item">
                    <a href="{{ route('withdraw.methods.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="dollar-sign"></i>
                        <span class="link-title">Withdrawal Methods</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.refunds') }}" class="nav-link">
                        <i class="link-icon" data-feather="refresh-ccw"></i>
                        <span class="link-title">Deducted Refunds <span class="badge {{ ($catererCounts['refunds']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $catererCounts['refunds'] ?? 0 }}</span></span>
                    </a>
                </li>
                <li class="nav-item {{ Request::is('admin/caterer/promo-codes*') ? 'active' : '' }}">
                    <a href="{{ route('caterer.promo_codes') }}" class="nav-link">
                        <i class="link-icon" data-feather="tag"></i>
                        <span class="link-title">Promo Codes</span>
                    </a>
                </li>
                <li class="nav-item nav-category">Support</li>
                <li class="nav-item">
                    <a href="{{ route('caterer.support') }}" class="nav-link">
                        <i class="link-icon" data-feather="life-buoy"></i>
                        <span class="link-title">Support <span class="badge {{ ($catererCounts['support']??0) > 0 ? 'bg-danger' : 'bg-secondary' }} ms-2" style="font-size:0.7rem;">{{ $catererCounts['support'] ?? 0 }}</span></span>
                    </a>
                </li>

                <li class="nav-item nav-category">Account</li>
                <li class="nav-item">
                    <a href="{{ route('notifications.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="bell"></i>
                        <span class="link-title">Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.profile') }}" class="nav-link">
                        <i class="link-icon" data-feather="user"></i>
                        <span class="link-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.change.password') }}" class="nav-link">
                        <i class="link-icon" data-feather="lock"></i>
                        <span class="link-title">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('caterer.logout') }}" class="nav-link">
                        <i class="link-icon" data-feather="log-out"></i>
                        <span class="link-title">Logout</span>
                    </a>
                </li>

            {{-- ===================== DELIVERY AGENT ===================== --}}
            @elseif(Auth::check() && Auth::user()->Role === 'DeliveryAgent')
                <li class="nav-item nav-category">Deliveries</li>
                <li class="nav-item">
                    <a href="{{ route('agent.dashboard') }}" class="nav-link">
                        <i class="link-icon" data-feather="box"></i>
                        <span class="link-title">Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.deliveries') }}" class="nav-link">
                        <i class="link-icon" data-feather="truck"></i>
                        <span class="link-title">My Deliveries @if(($agentCounts['deliveries']??0) > 0)<span class="badge bg-danger ms-2" style="font-size:0.7rem;">{{ $agentCounts['deliveries'] }}</span>@endif</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('withdraw.methods.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="dollar-sign"></i>
                        <span class="link-title">Withdrawal Methods</span>
                    </a>
                </li>

                <li class="nav-item nav-category">Account</li>
                <li class="nav-item">
                    <a href="{{ route('notifications.index') }}" class="nav-link">
                        <i class="link-icon" data-feather="bell"></i>
                        <span class="link-title">Notifications</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.profile') }}" class="nav-link">
                        <i class="link-icon" data-feather="user"></i>
                        <span class="link-title">Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.change.password') }}" class="nav-link">
                        <i class="link-icon" data-feather="lock"></i>
                        <span class="link-title">Change Password</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('agent.logout') }}" class="nav-link">
                        <i class="link-icon" data-feather="log-out"></i>
                        <span class="link-title">Logout</span>
                    </a>
                </li>
            @endif

            {{-- ===================== CUSTOMER & GUESTS (FRONTEND UI) ===================== --}}
            @if(!Auth::check() || Auth::user()->Role === 'Customer')
                <li class="nav-item nav-category">BiteHub</li>
                <li class="nav-item">
                    <a href="{{ route('frontend.home') }}" class="nav-link">
                        <i class="link-icon" data-feather="home"></i>
                        <span class="link-title">Home</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('frontend.browse') }}" class="nav-link">
                        <i class="link-icon" data-feather="search"></i>
                        <span class="link-title">Kitchens & Caterers</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('frontend.menu') }}" class="nav-link">
                        <i class="link-icon" data-feather="grid"></i>
                        <span class="link-title">Full Menu</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('frontend.top') }}" class="nav-link">
                        <i class="link-icon" data-feather="star"></i>
                        <span class="link-title">Top 10 Kitchens</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('frontend.subscriptions') }}" class="nav-link">
                        <i class="link-icon" data-feather="calendar"></i>
                        <span class="link-title">Meal Plans</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('frontend.catering') }}" class="nav-link">
                        <i class="link-icon" data-feather="coffee"></i>
                        <span class="link-title">Book Catering</span>
                    </a>
                </li>

                <li class="nav-item nav-category">My Account</li>
                @if(Auth::check() && Auth::user()->Role === 'Customer')
                    <li class="nav-item">
                        <a href="{{ route('dashboard.customer') }}" class="nav-link">
                            <i class="link-icon" data-feather="user"></i>
                            <span class="link-title">My Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('frontend.cart') }}" class="nav-link">
                            <i class="link-icon" data-feather="shopping-cart"></i>
                            <span class="link-title">My Cart</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('customer.support') }}" class="nav-link">
                            <i class="link-icon" data-feather="life-buoy"></i>
                            <span class="link-title">Support</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}" id="logoutFormSidebar" style="display:none">@csrf</form>
                        <a href="#" class="nav-link" onclick="document.getElementById('logoutFormSidebar').submit()">
                            <i class="link-icon" data-feather="log-out"></i>
                            <span class="link-title">Logout</span>
                        </a>
                    </li>
                @else
                    <li class="nav-item">
                        <a href="{{ route('login') }}" class="nav-link">
                            <i class="link-icon" data-feather="log-in"></i>
                            <span class="link-title">Login</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('register') }}" class="nav-link">
                            <i class="link-icon" data-feather="user-plus"></i>
                            <span class="link-title">Sign Up</span>
                        </a>
                    </li>
                @endif
            @endif

        </ul>
    </div>
</nav>
