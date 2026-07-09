@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <div>
        <h4 class="mb-0"><i data-feather="message-circle" class="me-2"></i>Order Chat — #{{ $order->OrderID }}</h4>
        <small class="text-muted">
            Customer: {{ $order->customer->user->FullName ?? 'Unknown' }}
            &nbsp;|&nbsp; Status: <strong>{{ $order->OrderStatus }}</strong>
            &nbsp;|&nbsp; Total: <strong class="text-primary">{{ number_format($order->TotalPrice, 2) }} EGP</strong>
        </small>
    </div>
    <a href="{{ route('kitchen.orders') }}" class="btn btn-sm btn-outline-secondary">
        <i data-feather="arrow-left" style="width:14px"></i> Back to Orders
    </a>
</div>

@if(session('message'))
<div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : (session('alert-type') === 'error' ? 'danger' : 'warning') }} alert-dismissible fade show">
    {{ session('message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">
    {{-- Chat Window --}}
    <div class="col-lg-8">
        <div class="card d-flex flex-column" style="height: 750px;">
            <div class="card-header border-bottom py-3"><strong><i data-feather="message-circle" class="icon-sm me-2"></i>Messages</strong></div>
            <div class="card-body p-0 d-flex flex-column overflow-hidden">
                <div id="chatWindow" class="p-4 flex-grow-1" style="overflow-y: auto;">

                @forelse($messages as $msg)
                    @php $isMine = $msg->SenderID === auth()->user()->UserID; @endphp

                    @if($msg->Type === 'request')
                    {{-- Customer request pending --}}
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning bg-opacity-10 py-2 d-flex align-items-center justify-content-between">
                            <span class="fw-semibold text-warning">
                                <i data-feather="alert-circle" style="width:14px"></i>
                                Modification Request — {{ $msg->sender->FullName ?? 'Customer' }}
                            </span>
                            <small class="text-muted">{{ $msg->Timestamp }}</small>
                        </div>
                        <div class="card-body py-2">
                            <p class="mb-3">{{ $msg->Message }}</p>
                            <div class="d-flex gap-2 align-items-end flex-wrap">
                                {{-- Approve with optional extra charge --}}
                                <form method="POST" action="{{ route('kitchen.chat.approve', $msg->LiveChatID) }}" class="d-flex gap-2 align-items-end">
                                    @csrf
                                    <div>
                                        <label class="form-label form-label-sm mb-0">Extra Charge (EGP)</label>
                                        <input type="number" name="extra_charge" class="form-control form-control-sm"
                                               style="width:120px" placeholder="0.00" min="0" step="0.01" value="0">
                                    </div>
                                    <button class="btn btn-sm btn-success">
                                        <i data-feather="check" style="width:12px"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('kitchen.chat.reject', $msg->LiveChatID) }}">@csrf
                                    <button class="btn btn-sm btn-danger">
                                        <i data-feather="x" style="width:12px"></i> Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    @elseif($msg->Type === 'approved')
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success">
                            <i data-feather="check" style="width:11px"></i> Request Approved
                            @if($msg->ExtraCharge > 0)
                                — +{{ number_format($msg->ExtraCharge, 2) }} EGP charged
                            @endif
                        </span>
                        <small class="text-muted">{{ $msg->Timestamp }}</small>
                    </div>

                    @elseif($msg->Type === 'rejected')
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-danger"><i data-feather="x" style="width:11px"></i> Request Rejected</span>
                        <small class="text-muted">{{ $msg->Timestamp }}</small>
                    </div>

                    @else
                    {{-- Normal message bubble --}}
                    <div class="d-flex mb-3 {{ $isMine ? 'flex-row-reverse' : '' }}">
                        <div class="px-3 py-2 rounded-3 {{ $isMine ? 'bg-primary text-white' : 'bg-light text-dark' }}" style="max-width:70%">
                            <small class="d-block fw-bold mb-1" style="{{ $isMine ? 'color:rgba(255,255,255,0.8);' : 'color:var(--primary);' }}">
                                {{ $msg->sender->FullName ?? 'Unknown' }}
                            </small>
                            <div style="line-height: 1.4;">{{ $msg->Message }}</div>
                            <div class="text-end mt-1"><small style="font-size:0.65rem; opacity:0.8">{{ \Carbon\Carbon::parse($msg->Timestamp)->format('H:i') }}</small></div>
                        </div>
                    </div>
                    @endif
                @empty
                <p class="text-center text-muted py-5">No messages yet. Start the conversation!</p>
                @endforelse
            </div>
            </div>

            {{-- Send Message --}}
            <div class="card-footer">
                <div class="mb-2">
                    <small class="text-muted"><i data-feather="info" style="width:13px"></i> To end this customization chat, type <strong>"Bitehub"</strong>. The Admin also monitors this chat.</small>
                </div>
                <form method="POST" action="{{ route('kitchen.orders.chat.send', $order->OrderID) }}" class="d-flex gap-2">
                    @csrf
                    <input type="hidden" name="type" value="message">
                    <input type="text" name="message" class="form-control" placeholder="Type a message..." required>
                    <button class="btn btn-primary px-3"><i data-feather="send" style="width:14px"></i></button>
                </form>
            </div>
        </div>
    </div>

    {{-- Send Request Sidebar --}}
    <div class="col-lg-4">
        <div class="card border-warning mb-3">
            <div class="card-header bg-warning bg-opacity-10">
                <strong><i data-feather="alert-circle" style="width:14px"></i> Send Request to Customer</strong>
            </div>
            <div class="card-body">
                <p class="text-muted small">Ask the customer about a modification to their order.</p>
                <form method="POST" action="{{ route('kitchen.orders.chat.send', $order->OrderID) }}">
                    @csrf
                    <input type="hidden" name="type" value="request">
                    <textarea name="message" class="form-control mb-2" rows="3"
                              placeholder="e.g. Can we substitute the chicken with beef?" required></textarea>
                    <button class="btn btn-warning w-100">
                        <i data-feather="send" style="width:14px"></i> Send Request
                    </button>
                </form>
            </div>
        </div>

        {{-- Order Summary --}}
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
