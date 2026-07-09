@forelse($orders as $o)
<tr>
    <td>{{ $o->OrderID }}</td>
    <td>
        @php
            $typeColors = [
                'Meal Plan' => '#8b5cf6',
                'Catering'  => '#9B0F06',
                'Order'     => '#3b82f6'
            ];
            $type = $o->OrderType ?? ($o->SubscriptionID ? 'Meal Plan' : 'Order');
        @endphp
        <span class="badge" style="background:{{ $typeColors[$type] ?? '#6c757d' }}; font-size:0.65rem; border-radius:8px; display:inline-block; min-width:65px; text-align:center;">
            {{ $type }}
        </span>
    </td>
    <td>{{ $o->CustomerName ?? '—' }}</td>
    <td style="max-width:180px; line-height:1.2;">
        @php
            $addr = null;
            if(str_contains($o->SpecialRequests, 'Delivery:')) {
                $addr = trim(explode("\n", explode('Delivery:', $o->SpecialRequests)[1])[0]);
            }
            if(!$addr) $addr = $o->CustomerAddress;
            if(!$addr) $addr = "—";
            
            $addrParts = explode(',', $addr);
            $mainAddr = trim($addrParts[0]);
            $subAddr = count($addrParts) > 1 ? implode(', ', array_slice($addrParts, 1)) : '';
        @endphp
        <div class="small fw-bold text-white" style="font-size:0.75rem">{{ $mainAddr }}</div>
        @if($subAddr)
            <div class="text-white-50" style="font-size:0.65rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;" title="{{ $addr }}">{{ $subAddr }}</div>
        @endif
    </td>
    <td class="fw-bold text-primary">
        <div>{{ number_format($o->TotalPrice - ($o->PointsDiscount ?? 0) - ($o->PromoDiscount ?? 0), 2) }} EGP</div>
        @if(($o->PointsDiscount ?? 0) > 0 || ($o->PromoDiscount ?? 0) > 0)
            <div class="text-muted" style="font-size:0.7rem; text-decoration: line-through; font-weight:normal;">{{ number_format($o->TotalPrice, 2) }} EGP</div>
            @if(($o->PromoDiscount ?? 0) > 0)
                <div class="text-success" style="font-size:0.65rem; font-weight: normal;" title="Promo Discount">
                    🎟️ -{{ number_format($o->PromoDiscount, 2) }}
                </div>
            @endif
            @if(($o->PointsDiscount ?? 0) > 0)
                <div class="text-warning" style="font-size:0.65rem; font-weight: normal;" title="Points Discount">
                    ⭐ -{{ number_format($o->PointsDiscount, 2) }}
                </div>
            @endif
        @endif
    </td>
    <td><span class="badge bg-secondary">{{ $o->PaymentMethod ?? '—' }}</span></td>
    <td>
        <form method="POST" action="{{ route('admin.orders.assign', $o->OrderID) }}" class="d-flex gap-1 align-items-center">
            @csrf
            <div class="flex-grow-1">
                <select name="agent_id" class="form-select form-select-sm" style="width:140px; font-size:0.7rem; height:30px; padding:2px 5px; background-color: #2a3038; border-color: #3b424b; color: #fff;">
                    <option value="">Select Agent</option>
                    @php
                        $lowAddr = strtolower($addr);
                        $cities = ['cairo', 'alexandria', 'giza', 'mansoura', 'tanta', 'sohag'];
                        $orderCity = 'cairo';
                        foreach($cities as $c) if(str_contains($lowAddr, $c)) $orderCity = $c;
                    @endphp
                    @foreach($agents as $agent)
                        @php
                            $agentArea = strtolower($agent->ServiceArea ?? '');
                            $isMatch = str_contains($agentArea, $orderCity);
                            $isCurrent = ($o->DeliveryAgentID == $agent->DeliveryAgentID);
                        @endphp
                        @if($isMatch || !$agent->ServiceArea || $isCurrent)
                            <option value="{{ $agent->DeliveryAgentID }}" @selected($isCurrent)>
                                {{ $agent->FullName }} ({{ $isCurrent && !$isMatch ? 'Reassign?' : ucwords($orderCity) }})
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button class="btn btn-sm btn-primary p-1" style="height:30px; width:30px;" title="Assign Agent">
                <i data-feather="user-plus" style="width:14px"></i>
            </button>
        </form>
    </td>
    <td>
        @php $sc=['Pending'=>'warning','Confirmed'=>'info','Preparing'=>'purple','Ready'=>'success','Delivering'=>'primary','Delivered'=>'success','Cancelled'=>'danger']; @endphp
        <span class="badge bg-{{ $sc[$o->OrderStatus] ?? 'secondary' }}">{{ $o->OrderStatus }}</span>
    </td>
    <td style="font-size:.82rem">{{ \Carbon\Carbon::parse($o->CreatedAt)->format('d M Y') }}</td>
    <td>
        <div class="d-flex gap-1 flex-wrap">
            <form method="POST" action="{{ route('admin.orders.status', $o->OrderID) }}" class="d-flex flex-column gap-1">
                @csrf
                <div class="d-flex gap-1">
                    <select name="status" class="form-select form-select-sm admin-status-select" style="width:90px; font-size:0.7rem; height:30px; background-color: #2a3038; border-color: #3b424b; color: #fff;">
                        @foreach(['Pending','Confirmed','Preparing','Ready','Delivering','Delivered','Cancelled'] as $s)
                        <option value="{{ $s }}" @selected($o->OrderStatus==$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-outline-secondary p-1" style="height:30px; width:30px;"><i data-feather="save" style="width:14px"></i></button>
                </div>
            </form>
            <a href="{{ route('admin.orders.chat', $o->OrderID) }}" class="btn btn-sm btn-outline-info p-1" style="height:30px; width:30px;" title="Details & Chat">
                <i data-feather="message-circle" style="width:14px"></i>
            </a>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="10" class="text-center text-muted py-4">No orders found.</td></tr>
@endforelse
