@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0">
        <i data-feather="shopping-cart" class="me-2"></i>
        @if(request('type') === 'plan') Plan Deliveries @elseif(request('type') === 'standard') Standard Orders @else Incoming Orders @endif
    </h4>
</div>

<div class="card mb-3"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-2">
        <label class="form-label form-label-sm mb-1 text-muted" style="font-size:.75rem">Status</label>
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            @foreach(['Pending','Confirmed','Preparing','Ready','Cancelled','Delivering','Delivered'] as $s)
            <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label form-label-sm mb-1 text-muted" style="font-size:.75rem">Date From</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
    </div>
    <div class="col-md-2">
        <label class="form-label form-label-sm mb-1 text-muted" style="font-size:.75rem">Date To</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
    </div>
    <div class="col-auto d-flex gap-1 align-items-end">
        <input type="hidden" name="type" value="{{ request('type') }}">
        <button class="btn btn-primary btn-sm"><i data-feather="filter" style="width:13px"></i> Filter</button>
        <a href="{{ route('kitchen.orders', ['type' => request('type')]) }}" class="btn btn-secondary btn-sm">Reset</a>
        <a href="{{ route('kitchen.orders', ['type' => request('type'), 'date_from' => now()->toDateString(), 'date_to' => now()->toDateString()]) }}" 
           class="btn btn-sm btn-outline-warning" title="Show today only">
           <i data-feather="sun" style="width:13px"></i> Today
        </a>
    </div>
</form>
<ul class="nav nav-tabs nav-tabs-line" id="myTab" role="tablist">
  <li class="nav-item">
    <a class="nav-link active" id="deliveries-tab" data-bs-toggle="tab" href="#deliveries" role="tab" aria-controls="deliveries" aria-selected="true" style="font-weight:700;">
        {{ request('type') === 'plan' ? 'Subscription Deliveries' : 'Standard Deliveries' }}
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link" id="contracts-tab" data-bs-toggle="tab" href="#contracts" role="tab" aria-controls="contracts" aria-selected="false" style="font-weight:700;">Meal Plan Contracts</a>
  </li>
</ul>

<div class="tab-content border border-top-0 p-3 mb-4" id="myTabContent" style="background:#1e293b; border-radius: 0 0 12px 12px;">
  <div class="tab-pane fade show active" id="deliveries" role="tabpanel" aria-labelledby="deliveries-tab">
    <div class="table-responsive">
    <table class="table table-hover mb-0" style="background:var(--bg-card);">
    <thead class="table-light">
        <tr><th>ID</th><th>Customer</th><th>Total</th><th>Notes / Slot</th><th>Status</th><th>Date</th><th>Action</th></tr>
    </thead>
    <tbody>
    @forelse($regularOrders as $o)
    <tr>
        <td><span class="fw-bold">#{{ $o->KitchenOrderNumber ?? $o->OrderID }}</span></td>
        <td>{{ $o->CustomerName ?? '—' }}</td>
        <td class="fw-bold text-primary">{{ number_format($o->TotalPrice, 2) }} EGP</td>
        <td style="font-size:.82rem">
            @if($o->OrderType === 'Meal Plan')
                <span class="badge" style="background:#8b5cf6;font-size:0.65rem;border-radius:4px;"><i data-feather="box" style="width:10px;"></i> Meal Plan #{{ $o->SubscriptionID }}</span>
                <div class="mt-1">
                    @if($o->ScheduledDate)
                        <span class="text-warning fw-bold"><i data-feather="calendar" style="width:11px"></i> {{ \Carbon\Carbon::parse($o->ScheduledDate)->format('d M Y') }}</span>
                    @endif
                    @if($o->DeliveryTime)
                        &nbsp;<span class="text-info"><i data-feather="clock" style="width:11px"></i> {{ $o->DeliveryTime }}</span>
                    @endif
                </div>
            @else
                {{ Str::limit($o->SpecialRequests, 40) }}
            @endif
        </td>
        <td>
            @php $sc=['Pending'=>'warning','Confirmed'=>'info','Preparing'=>'primary','Ready'=>'success','Delivering'=>'secondary','Delivered'=>'success','Cancelled'=>'danger']; @endphp
            <span class="badge bg-{{ $sc[$o->OrderStatus] ?? 'secondary' }}">{{ $o->OrderStatus }}</span>
        </td>
        <td style="font-size:.82rem">{{ \Carbon\Carbon::parse($o->CreatedAt)->format('d M y, H:i') }}</td>
        <td>
            <div class="d-flex gap-1 flex-wrap">
            <form method="POST" action="{{ route('kitchen.orders.status', $o->OrderID) }}" class="d-flex flex-column gap-1">
                @csrf
                <div class="d-flex gap-1">
                    @php
                        $editableStatuses = ['Pending','Confirmed','Preparing','Ready','Cancelled'];
                        $isEditable = in_array($o->OrderStatus, $editableStatuses);
                    @endphp
                    @if(!$isEditable)
                        {{-- Show a locked status badge instead of a disabled/invisible select --}}
                        @php
                            $lockColors = ['Delivering'=>'#6c757d','Delivered'=>'#198754'];
                            $lockColor = $lockColors[$o->OrderStatus] ?? '#6c757d';
                        @endphp
                        <span style="display:inline-flex;align-items:center;gap:4px;padding:4px 10px;background:{{ $lockColor }};color:#fff;border-radius:6px;font-size:0.75rem;font-weight:600;width:110px;justify-content:center;">
                            <i data-feather="lock" style="width:11px;height:11px;"></i> {{ $o->OrderStatus }}
                        </span>
                        <button class="btn btn-sm btn-secondary px-2" disabled><i data-feather="save" style="width:13px"></i></button>
                    @else
                        <select name="status" class="form-select form-select-sm kitchen-status-select" style="width:110px">
                            @foreach($editableStatuses as $s)
                            <option value="{{ $s }}" @selected($o->OrderStatus==$s)>{{ $s }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary px-2"><i data-feather="save" style="width:13px"></i></button>
                    @endif
                </div>
            </form>
            <a href="{{ route('kitchen.orders.chat', $o->OrderID) }}" class="btn btn-sm btn-outline-info px-2" title="Chat">
                <i data-feather="message-circle" style="width:13px"></i>
            </a>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center text-muted py-4">No standard deliveries found.</td></tr>
    @endforelse
    </tbody>
    </table>
    </div>
    @if($regularOrders->hasPages())
    <div class="mt-3">{{ $regularOrders->appends(request()->query())->links() }}</div>
    @endif
  </div>

  <div class="tab-pane fade" id="contracts" role="tabpanel" aria-labelledby="contracts-tab">
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('kitchen.subscriptions') }}" class="badge bg-soft-info text-info p-2 text-decoration-none">View All Active Subscriptions</a>
    </div>
    <div class="table-responsive">
    <table class="table table-hover mb-0" style="background:var(--bg-card);">
    <thead class="table-light">
        <tr><th>ID</th><th>Type</th><th>Customer</th><th>Total</th><th>Duration</th><th>Status</th><th>Added</th><th>Action</th></tr>
    </thead>
    <tbody>
    @forelse($mealPlanOrders as $sub)
    <tr>
        <td><span class="fw-bold text-muted">#{{ $sub->SubscriptionID }}</span></td>
        <td>
            @if($sub->kitchenPlan)
                <span class="badge" style="background:#8b5cf6; font-size:0.65rem; border-radius:8px;">Standard Plan</span>
            @else
                <span class="badge" style="background:#f59e0b; font-size:0.65rem; border-radius:8px;">Custom Plan</span>
            @endif
        </td>
        <td>{{ $sub->customer->user->FullName ?? '—' }}</td>
        <td class="fw-bold text-primary">{{ number_format($sub->Price, 2) }} EGP</td>
        <td style="font-size:.82rem">
            @if($sub->PlanTime) {{ $sub->PlanTime }} @else {{ $sub->DurationDays }} Days @endif
        </td>
        <td>
            @php
                $subClass = ['PendingApproval'=>'secondary', 'AwaitingPayment'=>'warning', 'Active'=>'success', 'Expired'=>'danger', 'Cancelled'=>'danger'];
            @endphp
            <span class="badge bg-{{ $subClass[$sub->Status] ?? 'secondary' }}">{{ $sub->Status }}</span>
        </td>
        <td style="font-size:.82rem">{{ \Carbon\Carbon::parse($sub->CreatedAt)->format('d M Y, H:i') }}</td>
        <td>
            <div class="d-flex gap-1">
                <a href="{{ route('kitchen.subscriptions') }}" class="btn btn-sm btn-primary" title="Manage Subscription">
                    <i data-feather="edit" style="width:13px"></i>
                </a>
                <a href="#" class="btn btn-sm btn-outline-info" title="Open Chat">
                    <i data-feather="message-circle" style="width:13px"></i>
                </a>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center text-muted py-4">No meal plan contracts found.</td></tr>
    @endforelse
    </tbody>
    </table>
    </div>
    @if($mealPlanOrders->hasPages())
    <div class="mt-3">{{ $mealPlanOrders->appends(request()->query())->links() }}</div>
    @endif
  </div>
</div>
</div>

{{-- ══ Report Order Modal ══ --}}
<div class="modal fade" id="kitchenReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:#1e293b;border:1px solid rgba(255,255,255,0.08);border-radius:16px;">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-bold" style="color:#f1f5f9;">
          <span style="color:#f87171">⚠️</span> Report Issue — Order <span id="kitchenModalOrderId" style="color:#f87171;"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('kitchen.support.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="color:#94a3b8;font-size:.82rem;">Problem Category *</label>
            <select name="category" class="form-select" style="background:#0f172a;border-color:rgba(255,255,255,0.1);color:#f1f5f9;" required>
              <option value="" style="background:#0f172a; color:#f1f5f9;">— Select a category —</option>
              <option value="Order Problem" style="background:#0f172a; color:#f1f5f9;">Order Problem</option>
              <option value="Payment / Billing Issue" style="background:#0f172a; color:#f1f5f9;">Payment / Billing Issue</option>
              <option value="Customer Misconduct" style="background:#0f172a; color:#f1f5f9;">Customer Misconduct</option>
              <option value="Technical Issue on Platform" style="background:#0f172a; color:#f1f5f9;">Technical Issue on Platform</option>
              <option value="Platform Policy Concern" style="background:#0f172a; color:#f1f5f9;">Platform Policy Concern</option>
              <option value="Subscription Dispute" style="background:#0f172a; color:#f1f5f9;">Subscription Dispute</option>
              <option value="Account / Profile Issue" style="background:#0f172a; color:#f1f5f9;">Account / Profile Issue</option>
              <option value="Other" style="background:#0f172a; color:#f1f5f9;">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="color:#94a3b8;font-size:.82rem;">Subject *</label>
            <input type="text" name="subject" id="kitchenModalSubject" class="form-control"
              style="background:#0f172a;border-color:rgba(255,255,255,0.1);color:#f1f5f9;" required>
          </div>
          <div class="mb-1">
            <label class="form-label fw-semibold" style="color:#94a3b8;font-size:.82rem;">Description *</label>
            <textarea name="description" class="form-control" rows="4"
              style="background:#0f172a;border-color:rgba(255,255,255,0.1);color:#f1f5f9;"
              placeholder="Describe the problem in detail..." required></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger btn-sm fw-bold px-4">
            <i data-feather="send" style="width:13px" class="me-1"></i> Submit Report
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

@push('custom-scripts')
<script>
function openKitchenReportModal(orderId, customerName) {
    document.getElementById('kitchenModalOrderId').textContent = '#' + orderId;
    document.getElementById('kitchenModalSubject').value =
        'Issue with Order #' + orderId + (customerName ? ' — Customer: ' + customerName : '');
    new bootstrap.Modal(document.getElementById('kitchenReportModal')).show();
    if (window.feather) setTimeout(() => feather.replace(), 100);
}
// OTP and status logic updated for Kitchen as per new requirements
</script>
@endpush
@endsection
