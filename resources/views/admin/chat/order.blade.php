@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">

<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <div>
        <h4 class="mb-0"><i data-feather="message-circle" class="me-2"></i>Order Chat — #{{ $order->OrderID }} <small class="text-muted">(Admin View)</small></h4>
        <small class="text-muted">Customer: {{ $order->customer->user->FullName ?? 'Unknown' }} | Status: <strong>{{ $order->OrderStatus }}</strong></small>
    </div>
    <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" style="width:14px"></i> Back to Orders
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card d-flex flex-column" style="height: 750px;">
            <div class="card-header border-bottom py-3"><strong><i data-feather="message-circle" class="icon-sm me-2"></i>Chat Log</strong></div>
            <div class="card-body p-0 d-flex flex-column overflow-hidden">
                <div id="chatWindow" class="p-4 flex-grow-1" style="overflow-y: auto;">

                @forelse($messages as $msg)
                    @if($msg->Type === 'request')
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning bg-opacity-10 py-2 d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-warning">
                                <i data-feather="alert-circle" style="width:14px"></i>
                                Customer Request — {{ $msg->sender->FullName ?? 'Customer' }}
                            </span>
                            <small class="text-muted">{{ $msg->Timestamp }}</small>
                        </div>
                        <div class="card-body py-2">
                            <p class="mb-0">{{ $msg->Message }}</p>
                            <small class="text-muted">Status: Awaiting provider response</small>
                        </div>
                    </div>
                    @elseif($msg->Type === 'approved')
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success"><i data-feather="check" style="width:11px"></i> Request Approved</span>
                        <small class="text-muted">{{ $msg->Timestamp }}</small>
                    </div>
                    @elseif($msg->Type === 'rejected')
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-danger"><i data-feather="x" style="width:11px"></i> Request Rejected</span>
                        <small class="text-muted">{{ $msg->Timestamp }}</small>
                    </div>
                    @else
                    <div class="d-flex mb-3">
                        <div class="px-3 py-2 rounded-3 bg-light" style="max-width:75%">
                            <small class="d-block fw-bold mb-1">{{ $msg->sender->FullName ?? 'Unknown' }}</small>
                            {{ $msg->Message }}
                            <div class="text-end mt-1"><small style="font-size:0.7rem; opacity:0.7">{{ $msg->Timestamp }}</small></div>
                        </div>
                    </div>
                    @endif
                @empty
                <p class="text-center text-muted py-5">No messages in this order's chat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="col-lg-4">
        <div class="card">
            <div class="card-header"><strong>Order Summary</strong></div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-1">
                    <span class="small">Order #</span><strong class="small">{{ $order->OrderID }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small">Status</span>
                    <span class="badge bg-info" style="font-size:0.7rem">{{ $order->OrderStatus }}</span>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span class="small">Total</span>
                    <strong class="text-primary small">{{ number_format($order->TotalPrice, 2) }} EGP</strong>
                </div>
                
                <hr class="my-3">
                <div class="mb-2 fw-bold small"><i data-feather="shopping-cart" style="width:14px"></i> Items in Order:</div>
                <div class="list-group list-group-flush mb-3">
                    @foreach($order->menuItems as $item)
                        <div class="list-group-item px-0 py-2 border-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-0 small fw-bold">{{ $item->ItemName }}</h6>
                                    <small class="text-muted">Qty: {{ $item->pivot->Quantity }} × {{ number_format($item->ItemPrice, 2) }}</small>
                                </div>
                                <span class="fw-bold small">{{ number_format($item->ItemPrice * $item->pivot->Quantity, 2) }}</span>
                            </div>
                            
                            @php 
                                $itemCustomization = $messages->where('MenuItemID', $item->MenuItemID)
                                                             ->whereIn('Type', ['request', 'approved', 'added_to_cart'])
                                                             ->first();
                            @endphp
                            
                            @if($itemCustomization)
                            <div class="mt-2 p-2 bg-light rounded border-start border-primary border-3">
                                <div class="small fw-bold text-primary mb-1"><i data-feather="tool" style="width:12px"></i> Customization:</div>
                                <p class="small mb-0 text-dark">"{{ $itemCustomization->Message }}"</p>
                                @if($itemCustomization->ExtraCharge > 0)
                                    <div class="small text-success mt-1 fw-bold">+ {{ number_format($itemCustomization->ExtraCharge, 2) }} EGP Extra</div>
                                @endif
                            </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                @php $extraTotal = $messages->where('Type','approved')->sum('ExtraCharge'); @endphp
                @if($extraTotal > 0)
                <div class="d-flex justify-content-between mt-2 pt-2 border-top text-success">
                    <span class="small fw-bold">Total Extra Charges</span>
                    <strong class="small">+{{ number_format($extraTotal, 2) }} EGP</strong>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
<script>
const chatWindow = document.getElementById('chatWindow');
if (chatWindow) chatWindow.scrollTop = chatWindow.scrollHeight;
</script>
@endsection
