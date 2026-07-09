@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="truck" class="me-2"></i>My Assigned Deliveries</h4>
</div>

<div class="card mb-3"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            @foreach(['Ready','Delivering','Delivered'] as $s)
            <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('agent.deliveries') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div></div>

{{-- Active Deliveries Section --}}
<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i data-feather="play-circle" class="icon-sm me-1"></i>Active Deliveries (Today)</h6>
        <span class="badge bg-white text-primary rounded-pill">{{ $todayOrders->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive d-none d-md-block">
            <table class="table table-hover mb-0" style="font-size:0.85rem; width:100%;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50px">#</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Phone</th>
                        <th>Address</th>
                        <th>Pay</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th style="width: 150px">Update Status</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($todayOrders as $o)
                    <tr>
                        <td><span class="fw-bold">#{{ $o->KitchenOrderNumber ?? $o->OrderID }}</span></td>
                        <td>
                            @php
                                $typeColors = ['Meal Plan' => '#8b5cf6', 'Catering' => '#9B0F06', 'Order' => '#3b82f6'];
                                $type = $o->OrderType ?? ($o->SubscriptionID ? 'Meal Plan' : 'Order');
                            @endphp
                            <span class="badge" style="background:{{ $typeColors[$type] ?? '#6c757d' }}; font-size:0.65rem; border-radius:8px; display:inline-block; min-width:65px; text-align:center;">{{ $type }}</span>
                        </td>
                        <td class="text-truncate" style="max-width: 120px;">{{ optional($o->customer?->user)->FullName ?? '—' }}</td>
                        <td>{{ optional($o->customer?->user?->phone)->PhoneNumber ?? '—' }}</td>
                        <td class="text-wrap" style="min-width: 200px;">
                            @php
                                $addr = null;
                                if(str_contains($o->SpecialRequests, 'Delivery:')) {
                                    $addr = trim(explode("\n", explode('Delivery:', $o->SpecialRequests)[1])[0]);
                                }
                                if(!$addr) $addr = optional($o->customer?->user?->address)->Address;
                                if(!$addr) $addr = "—";
                                
                                $addrParts = explode(',', $addr);
                                $mainAddr = trim($addrParts[0]);
                                $subAddr = count($addrParts) > 1 ? implode(', ', array_slice($addrParts, 1)) : '';
                            @endphp
                            <div class="fw-bold text-white" style="font-size:0.8rem">{{ $mainAddr }}</div>
                            @if($subAddr)
                                <div class="text-white-50" style="font-size:0.7rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $addr }}">{{ $subAddr }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $o->payment->Method ?? '—' }}</span></td>
                        <td>
                            <div style="font-size:0.8rem; line-height: 1.2;">
                                @if($o->OrderType === 'Meal Plan' || $o->SubscriptionID)
                                    <div class="text-primary fw-bold">{{ \Carbon\Carbon::parse($o->ScheduledDate ?? $o->CreatedAt)->format('d M y') }}</div>
                                    <div class="text-muted"><i data-feather="clock" style="width:11px;"></i> {{ $o->DeliveryTime ?? \Carbon\Carbon::parse($o->CreatedAt)->format('H:i') }}</div>
                                @else
                                    <div class="text-muted">{{ \Carbon\Carbon::parse($o->CreatedAt)->format('d M y, H:i') }}</div>
                                @endif
                            </div>
                        </td>
                        <td>
                            @php $sc=['Ready'=>'info', 'Delivering'=>'primary', 'Delivered'=>'success', 'Cancelled'=>'danger']; @endphp
                            <span class="badge bg-{{ $sc[$o->OrderStatus] ?? 'secondary' }}">{{ $o->OrderStatus }}</span>
                        </td>
                        <td>
                            @if(!in_array($o->OrderStatus, ['Delivered', 'Cancelled']))
                                @if(!in_array($o->OrderStatus, ['Ready', 'Delivering']))
                                    <div class="d-flex align-items-center gap-1">
                                        <span class="badge py-2 px-2" style="font-size:0.7rem; background: rgba(251, 191, 36, 0.1); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2);">
                                            <i data-feather="loader" style="width:12px; animation: spin 2s linear infinite;"></i> Awaiting Kitchen
                                        </span>
                                        <a href="{{ route('agent.delivery.details', $o->OrderID) }}" class="btn btn-sm btn-outline-info" title="Map & Details"><i data-feather="map" style="width:13px"></i></a>
                                    </div>
                                    <style>@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }</style>
                                @else
                                    <form method="POST" action="{{ route('agent.deliveries.update', $o->OrderID) }}" class="d-flex flex-column gap-1">
                                        @csrf
                                        <div class="d-flex gap-1">
                                            <select name="status" class="form-select form-select-sm status-dropdown" style="width:110px">
                                                @if($o->OrderStatus == 'Ready')
                                                    <option value="Delivering" selected>Delivering</option>
                                                @elseif($o->OrderStatus == 'Delivering')
                                                    <option value="Delivered" selected>Delivered</option>
                                                @endif
                                            </select>
                                            <button class="btn btn-sm btn-primary" title="Update Status"><i data-feather="save" style="width:13px"></i></button>
                                            <a href="{{ route('agent.delivery.details', $o->OrderID) }}" class="btn btn-sm btn-outline-info" title="Map & Details"><i data-feather="map" style="width:13px"></i></a>
                                        </div>
                                        <input type="text" name="delivery_code" class="form-control form-control-sm otp-input" placeholder="OTP" maxlength="4" 
                                            style="width:110px; display: {{ $o->OrderStatus == 'Delivering' ? 'block' : 'none' }};" 
                                            title="Ask the customer for their delivery code">
                                        
                                        @if(optional($o->payment)->Method === 'Cash' || $o->OrderType === 'Meal Plan' || $o->SubscriptionID)
                                        <input type="number" step="0.01" name="wallet_change" class="form-control form-control-sm wallet-change-input mt-1" 
                                            placeholder="Wallet ?" min="0" 
                                            style="width:110px; display: {{ $o->OrderStatus == 'Delivering' ? 'block' : 'none' }};" 
                                            title="Add change to customer wallet if needed">
                                        @endif

                                        @if($o->OrderType === 'Meal Plan' || $o->SubscriptionID)
                                        <input type="number" step="0.01" name="plan_cash_paid" class="form-control form-control-sm plan-cash-input mt-1" 
                                            placeholder="Paid ?" min="0" 
                                            style="width:110px; display: {{ $o->OrderStatus == 'Delivering' ? 'block' : 'none' }};" 
                                            title="Enter cash collected for this meal">
                                        @endif
                                    </form>
                                @endif
                            @else
                            <span class="text-muted"><i data-feather="check" style="width:14px"></i> Done</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="text-center text-muted py-4">No active deliveries for today.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View for Active Deliveries --}}
        <div class="d-md-none p-2">
            @forelse($todayOrders as $o)
                <div class="card mb-3 border shadow-sm" style="background: #1a1a1b;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <span class="fw-bold text-primary">#{{ $o->KitchenOrderNumber ?? $o->OrderID }}</span>
                                @php
                                    $typeColors = ['Meal Plan' => '#8b5cf6', 'Catering' => '#9B0F06', 'Order' => '#3b82f6'];
                                    $type = $o->OrderType ?? ($o->SubscriptionID ? 'Meal Plan' : 'Order');
                                @endphp
                                <span class="badge ms-1" style="background:{{ $typeColors[$type] ?? '#6c757d' }}; font-size:0.6rem;">{{ $type }}</span>
                            </div>
                            @php $sc=['Ready'=>'info', 'Delivering'=>'primary', 'Delivered'=>'success', 'Cancelled'=>'danger']; @endphp
                            <span class="badge bg-{{ $sc[$o->OrderStatus] ?? 'secondary' }}">{{ $o->OrderStatus }}</span>
                        </div>
                        
                        <div class="mb-3">
                            <div class="mb-1"><i data-feather="user" class="icon-sm me-1 text-muted"></i> <strong>{{ optional($o->customer?->user)->FullName ?? '—' }}</strong></div>
                            <div class="mb-1"><i data-feather="phone" class="icon-sm me-1 text-muted"></i> <a href="tel:{{ optional($o->customer?->user?->phone)->PhoneNumber }}" class="text-decoration-none">{{ optional($o->customer?->user?->phone)->PhoneNumber ?? '—' }}</a></div>
                            <div class="text-wrap small">
                                <i data-feather="map-pin" class="icon-sm me-1 text-muted"></i> 
                                <span class="fw-bold text-white">{{ $mainAddr }}</span>
                                <div class="text-white-50 ps-4" style="font-size:0.65rem">{{ $subAddr }}</div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light text-dark border"><i data-feather="credit-card" class="icon-xs me-1"></i> {{ $o->payment->Method ?? '—' }}</span>
                            <div class="text-end">
                                @if($o->OrderType === 'Meal Plan' || $o->SubscriptionID)
                                    <div class="text-primary fw-bold small">{{ \Carbon\Carbon::parse($o->ScheduledDate ?? $o->CreatedAt)->format('d M y') }}</div>
                                    <div class="text-muted extra-small"><i data-feather="clock" style="width:10px;"></i> {{ $o->DeliveryTime ?? \Carbon\Carbon::parse($o->CreatedAt)->format('H:i') }}</div>
                                @else
                                    <div class="text-muted small">{{ \Carbon\Carbon::parse($o->CreatedAt)->format('d M y, H:i') }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="mt-2 pt-3 border-top">
                            @if(!in_array($o->OrderStatus, ['Delivered', 'Cancelled']))
                                @if(!in_array($o->OrderStatus, ['Ready', 'Delivering']))
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="small fw-bold" style="background: rgba(251, 191, 36, 0.1); color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.2); padding: 4px 8px; border-radius: 4px;">
                                            <i data-feather="loader" class="icon-sm me-1" style="animation: spin 2s linear infinite;"></i> Awaiting Kitchen Ready...
                                        </span>
                                        <a href="{{ route('agent.delivery.details', $o->OrderID) }}" class="btn btn-sm btn-outline-info"><i data-feather="map" class="icon-sm"></i> Map</a>
                                    </div>
                                @else
                                    <form method="POST" action="{{ route('agent.deliveries.update', $o->OrderID) }}">
                                        @csrf
                                        <div class="row g-2 align-items-center">
                                            <div class="col-8">
                                                <select name="status" class="form-select form-select-sm status-dropdown bg-dark text-white">
                                                    @if($o->OrderStatus == 'Ready')
                                                        <option value="Delivering" selected>Delivering</option>
                                                    @elseif($o->OrderStatus == 'Delivering')
                                                        <option value="Delivered" selected>Delivered</option>
                                                    @endif
                                                </select>
                                            </div>
                                            <div class="col-4 d-flex gap-1">
                                                <button class="btn btn-sm btn-primary flex-grow-1"><i data-feather="save" class="icon-sm"></i></button>
                                                <a href="{{ route('agent.delivery.details', $o->OrderID) }}" class="btn btn-sm btn-outline-info"><i data-feather="map" class="icon-sm"></i></a>
                                            </div>
                                        </div>

                                        <div class="mt-2">
                                            <input type="text" name="delivery_code" class="form-control form-control-sm otp-input mb-1 bg-dark text-white border-secondary" placeholder="Enter 4-Digit OTP" maxlength="4" 
                                                style="display: {{ $o->OrderStatus == 'Delivering' ? 'block' : 'none' }};">
                                            
                                            @if(optional($o->payment)->Method === 'Cash' || $o->OrderType === 'Meal Plan' || $o->SubscriptionID)
                                            <input type="number" step="0.01" name="wallet_change" class="form-control form-control-sm wallet-change-input mb-1 bg-dark text-white border-secondary" 
                                                placeholder="Change to Wallet ?" min="0" 
                                                style="display: {{ $o->OrderStatus == 'Delivering' ? 'block' : 'none' }};">
                                            @endif

                                            @if($o->OrderType === 'Meal Plan' || $o->SubscriptionID)
                                            <input type="number" step="0.01" name="plan_cash_paid" class="form-control form-control-sm plan-cash-input bg-dark text-white border-secondary" 
                                                placeholder="Cash Paid ?" min="0" 
                                                style="display: {{ $o->OrderStatus == 'Delivering' ? 'block' : 'none' }};">
                                            @endif
                                        </div>
                                    </form>
                                @endif
                            @else
                                <div class="text-center text-success fw-bold py-1"><i data-feather="check-circle" class="icon-sm me-1"></i> Delivery Completed</div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">No active deliveries for today.</div>
            @endforelse
        </div>
    </div>

</div>

{{-- Scheduled Deliveries Section --}}
<div class="card">
    <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i data-feather="calendar" class="icon-sm me-1"></i>Scheduled Deliveries (Upcoming)</h6>
        <span class="badge bg-white text-secondary rounded-pill">{{ $scheduledOrders->count() }}</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive d-none d-md-block">
            <table class="table mb-0" style="font-size:0.85rem; width:100%;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 150px">Date</th>
                        <th>Type</th>
                        <th>Customer</th>
                        <th>Address</th>
                        <th>Slot</th>
                        <th>Note</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($scheduledOrders as $o)
                    <tr>
                        <td class="fw-bold text-primary">{{ \Carbon\Carbon::parse($o->ScheduledDate)->format('D, d M') }}</td>
                        <td><span class="badge" style="background:#8b5cf6; font-size:0.65rem; border-radius:8px;">Meal Plan</span></td>
                        <td>{{ optional($o->customer?->user)->FullName ?? '—' }}</td>
                        <td class="text-wrap" style="min-width: 250px;">
                            @php
                                $addr = null;
                                if(str_contains($o->SpecialRequests, 'Delivery:')) {
                                    $addr = trim(explode("\n", explode('Delivery:', $o->SpecialRequests)[1])[0]);
                                }
                                if(!$addr) $addr = optional($o->customer?->user?->address)->Address;
                                if(!$addr) $addr = "—";
                                
                                $addrParts = explode(',', $addr);
                                $mainAddr = trim($addrParts[0]);
                                $subAddr = count($addrParts) > 1 ? implode(', ', array_slice($addrParts, 1)) : '';
                            @endphp
                            <div class="fw-bold text-white" style="font-size:0.8rem">{{ $mainAddr }}</div>
                            @if($subAddr)
                                <div class="text-white-50" style="font-size:0.7rem;">{{ $subAddr }}</div>
                            @endif
                        </td>
                        <td><span class="badge bg-light text-dark border"><i data-feather="clock" style="width:11px;"></i> {{ $o->DeliveryTime }}</span></td>
                        <td class="text-muted italic" style="font-size:0.75rem">Upcoming Delivery</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No future scheduled deliveries yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View for Scheduled Deliveries --}}
        <div class="d-md-none p-2">
            @forelse($scheduledOrders as $o)
                <div class="card mb-2 border shadow-sm" style="background: #1a1a1b;">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold text-primary">{{ \Carbon\Carbon::parse($o->ScheduledDate)->format('D, d M') }}</span>
                            <span class="badge" style="background:#8b5cf6; font-size:0.6rem;">Meal Plan</span>
                        </div>
                        <div class="small mb-1"><strong>{{ optional($o->customer?->user)->FullName ?? '—' }}</strong></div>
                        <div class="small mb-2">
                            <i data-feather="map-pin" class="icon-xs me-1 text-muted"></i> 
                            <span class="fw-bold text-white">{{ $mainAddr }}</span>
                            <div class="text-white-50 ps-3" style="font-size:0.6rem">{{ $subAddr }}</div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-light text-dark border"><i data-feather="clock" class="icon-xs me-1"></i> {{ $o->DeliveryTime }}</span>
                            <span class="text-muted small italic">Scheduled</span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-muted py-4">No future scheduled deliveries.</div>
            @endforelse
        </div>
    </div>
</div>
</div>
</div>
</div>{{-- /.page-content --}}
@endsection

@push('scripts')
<script>
    // Live Location Tracking for Agent
    document.addEventListener('DOMContentLoaded', function() {
        // Dropdown toggle logic
        document.querySelectorAll('.status-dropdown').forEach(dropdown => {
            // Function to handle visibility and required attribute
            const updateUI = (el) => {
                const form = el.closest('form');
                const otpInput = form.querySelector('.otp-input');
                const walletInput = form.querySelector('.wallet-change-input');
                const planCashInput = form.querySelector('.plan-cash-input');
                
                if(otpInput) {
                    if(el.value === 'Delivered') {
                        otpInput.style.display = 'block';
                        otpInput.setAttribute('required', 'required');
                        if (walletInput) walletInput.style.display = 'block';
                        if (planCashInput) planCashInput.style.display = 'block';
                    } else {
                        otpInput.style.display = 'none';
                        otpInput.removeAttribute('required');
                        if (walletInput) { walletInput.style.display = 'none'; walletInput.value = ''; }
                        if (planCashInput) { planCashInput.style.display = 'none'; planCashInput.value = ''; }
                    }
                }
            };

            // Run on change
            dropdown.addEventListener('change', function() { updateUI(this); });
            // Run on load for already selected values
            updateUI(dropdown);
        });

        // Find if they have any active "Delivering" orders
        const activeOrders = @json($todayOrders->filter(fn($o) => $o->OrderStatus === 'Delivering')->pluck('OrderID'));
        
        if (activeOrders.length > 0) {
            if ("geolocation" in navigator) {
                console.log("Location tracking initialized for orders:", activeOrders);

                // Check every 15 seconds
                setInterval(() => {
                    navigator.geolocation.getCurrentPosition(
                        function(position) {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            // Send location for each active order
                            activeOrders.forEach(orderId => {
                                fetch(`/admin/agent/deliveries/${orderId}/location`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ lat, lng })
                                }).catch(err => console.error('Tracking Error:', err));
                            });
                        },
                        function(error) {
                            console.warn("Geolocation Error:", error.message);
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
                    );
                }, 15000);
            } else {
                console.warn("Geolocation API not supported in this browser.");
            }
        }
    });
</script>
@endpush
