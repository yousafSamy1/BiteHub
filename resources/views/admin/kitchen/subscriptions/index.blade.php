@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="calendar" class="me-2"></i>My Subscriptions</h4>
</div>

@if(session('message'))
<div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : (session('alert-type') === 'error' ? 'danger' : 'warning') }} alert-dismissible fade show">
    {{ session('message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>All Subscribed Customers</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr style="background: rgba(255,255,255,0.03); border-bottom: 2px solid rgba(255,255,255,0.05);">
                                <th style="width:60px; color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px 20px;">ID</th>
                                <th style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px 20px;">Customer Name</th>
                                <th style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px 20px;">Plan / Items</th>
                                <th style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px 20px;">Duration</th>
                                <th style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px 20px;">Payment Status</th>
                                <th style="color: #94a3b8; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; padding: 15px 20px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($subscriptions as $sub)
                            <tr style="background: {{ in_array($sub->Status, ['Cancelled','Refunded']) ? 'rgba(248,113,113,0.08)' : ($sub->Status === 'Paused' ? 'rgba(255,167,38,0.08)' : 'rgba(255,107,53,0.02)') }}; {{ in_array($sub->Status, ['Cancelled','Refunded']) ? 'opacity:0.85;' : '' }}">
                                <td class="text-muted fw-bold">#{{ $sub->SubscriptionID }}</td>
                                <td>
                                    <strong>{{ $sub->customer->user->FullName ?? 'Guest Customer' }}</strong><br>
                                    <div class="mt-1 d-flex flex-column gap-1">
                                        <small class="text-muted d-flex align-items-center gap-1">
                                            <i data-feather="mail" style="width:11px"></i> {{ $sub->customer->user->Email ?? '' }}
                                        </small>
                                        @if($sub->customer->user->phone)
                                            <small class="text-info d-flex align-items-center gap-1">
                                                <i data-feather="phone" style="width:11px"></i> 
                                                <a href="tel:{{ $sub->customer->user->phone->PhoneNumber }}" class="text-info" style="text-decoration:none;">{{ $sub->customer->user->phone->PhoneNumber }}</a>
                                            </small>
                                        @endif
                                        @if($sub->customer->user->addresses->count() > 0)
                                            <small class="text-muted d-flex align-items-start gap-1 mt-1">
                                                <i data-feather="map-pin" style="width:11px; margin-top:3px;"></i> 
                                                <span style="line-height:1.2;">{{ $sub->customer->user->addresses->first()->Address }}</span>
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($sub->kitchenPlan)
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:10px;height:10px;border-radius:50%;background:var(--primary)"></div>
                                            <strong class="text-primary">{{ $sub->kitchenPlan->Title }}</strong>
                                        </div>
                                        <span class="badge bg-soft-info text-info mt-1" style="font-size: 0.7rem;">{{ $sub->PlanTime }} Plan</span>
                                    @else
                                        <div class="d-flex align-items-center gap-2">
                                            <div style="width:10px;height:10px;border-radius:50%;background:var(--warning)"></div>
                                            <strong class="text-warning">Custom Plan</strong>
                                        </div>
                                        <span class="badge bg-soft-warning text-warning mt-1" style="font-size: 0.7rem;">{{ $sub->MealsPerDay }} Meals/Day</span>
                                    @endif
                                    
                                    @if($sub->menuItems->count() > 0)
                                        <div class="mt-2 pt-2 border-top border-light">
                                            @if($sub->PreferredTimes)
                                                <div class="mb-2">
                                                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.65rem;">Delivery Schedule:</small>
                                                    @foreach($sub->PreferredTimes as $time)
                                                        <span class="badge bg-soft-secondary text-secondary me-1" style="font-size: 0.7rem;">{{ $time }}</span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 0.65rem;">Selected Items:</small>
                                            @foreach($sub->menuItems as $item)
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span style="font-size: 0.85rem;">• {{ $item->ItemName }}</span>
                                                    @if($item->pivot->Status === 'Approved')
                                                        <i data-feather="check-circle" class="text-success" style="width:12px"></i>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column" style="font-size: 0.85rem;">
                                        <span class="text-success fw-bold"><i data-feather="calendar" style="width:12px"></i> Start: {{ \Carbon\Carbon::parse($sub->StartDate)->format('M d') }}</span>
                                        <span class="text-danger fw-bold"><i data-feather="clock" style="width:12px"></i> End: {{ \Carbon\Carbon::parse($sub->EndDate)->format('M d') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <div class="p-2 rounded bg-dark border border-secondary" style="font-size: 0.75rem;">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Plan Price:</span>
                                                <span class="fw-bold">{{ number_format($sub->Price, 2) }} EGP</span>
                                            </div>
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="text-muted">Delivery:</span>
                                                <span class="fw-bold text-info">+ {{ number_format($sub->DeliveryCharge ?? 0, 2) }} EGP</span>
                                            </div>
                                            <div class="d-flex justify-content-between pt-1 border-top border-secondary">
                                                <span class="fw-bold text-primary">Total:</span>
                                                <span class="fw-bold text-primary">{{ number_format($sub->Price + ($sub->DeliveryCharge ?? 0), 2) }} EGP</span>
                                            </div>
                                        </div>
                                        
                                        <span class="badge bg-soft-success text-success" style="font-size: 0.75rem;">Paid: {{ number_format($sub->PaidAmount ?? 0, 2) }} EGP</span>
                                        @php $totalSubPrice = ($sub->Price ?? 0) + ($sub->DeliveryCharge ?? 0); @endphp
                                        @if(($sub->PaidAmount ?? 0) < $totalSubPrice && !in_array($sub->Status, ['Cancelled','Refunded']))
                                            <span class="text-danger fw-bold text-center mt-1" style="font-size: 0.8rem; border: 1px dashed #f87171; border-radius:4px; padding:2px;">
                                                Due: {{ number_format($totalSubPrice - ($sub->PaidAmount ?? 0), 2) }} EGP
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if(in_array($sub->Status, ['Cancelled','Refunded']))
                                        <span class="badge bg-danger" style="font-size: 0.8rem;"><i class="fas fa-times-circle me-1"></i>CANCELLED</span>
                                    @elseif($sub->Status === 'Paused')
                                        <span class="badge bg-warning text-dark" style="font-size: 0.8rem;"><i class="fas fa-pause-circle me-1"></i>PAUSED</span>
                                    @else
                                        @php
                                            $pendingCount = $sub->menuItems->where('pivot.Status', 'Pending')->count();
                                        @endphp
                                        @if($pendingCount > 0)
                                            <span class="badge bg-warning text-dark">{{ $pendingCount }} Items Pending</span>
                                        @else
                                            <span class="badge bg-success">All Items Approved</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    @if(in_array($sub->Status, ['Cancelled','Refunded']))
                                        <span class="text-muted" style="font-size:0.75rem;">No actions</span>
                                    @else
                                    <div class="dropdown">
                                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                            Manage
                                        </button>
                                        <ul class="dropdown-menu">
                                            @foreach($sub->menuItems as $item)
                                                @if($item->pivot->Status === 'Pending')
                                                    <li>
                                                        <form method="POST" action="{{ route('kitchen.subscriptions.update_item', [$sub->SubscriptionID, $item->MenuItemID]) }}" class="px-3 py-1">
                                                            @csrf
                                                            <input type="hidden" name="action" value="approve">
                                                            <button type="submit" class="btn btn-xs btn-success w-100 mb-1">Approve {{ $item->ItemName }}</button>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endforeach
                                            <li><a class="dropdown-item text-info" href="#"><i data-feather="message-circle" style="width:14px"></i> Chat Customer</a></li>
                                        </ul>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-5">No active subscribers found for your kitchen plans yet.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if($subscriptions->hasPages())
                    <div class="px-4 py-3 border-top">
                        {{ $subscriptions->links() }}
                    </div>
                @endif
            </div>
        </div>
    <div class="col-12 mt-3">
        <div class="alert alert-soft-warning border-dashed d-flex align-items-center">
            <i data-feather="info" class="me-2 text-warning"></i>
            <small class="text-muted"><strong>Policy:</strong> For subscription deposits, users are required to fulfill the remaining balance at least <strong>one day before</strong> the plan end date. Otherwise, the subscription can be halted.</small>
        </div>
    </div>
</div>
</div>
@endsection
