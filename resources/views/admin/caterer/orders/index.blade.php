@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="shopping-cart" class="me-2"></i>Incoming Orders</h4>
</div>

<div class="card mb-3"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            @foreach(['Pending','Confirmed','Preparing','Ready','Cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('caterer.orders') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div></div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Customer</th><th>Total</th><th>Notes</th><th>Status</th><th>Date</th><th>Action</th></tr>
</thead>
<tbody>
@forelse($orders as $o)
<tr>
    <td><span class="fw-bold">#{{ $o->KitchenOrderNumber ?? $o->OrderID }}</span></td>
    <td>{{ $o->CustomerName ?? '—' }}</td>
    <td class="fw-bold text-primary">{{ number_format($o->TotalPrice, 2) }} EGP</td>
    <td style="font-size:.82rem">{{ Str::limit($o->SpecialRequests, 40) }}</td>
    <td>
        @php $sc=['Pending'=>'warning','Confirmed'=>'info','Preparing'=>'primary','Ready'=>'success','Delivering'=>'secondary','Delivered'=>'success','Cancelled'=>'danger']; @endphp
        <span class="badge bg-{{ $sc[$o->OrderStatus] ?? 'secondary' }}">{{ $o->OrderStatus }}</span>
    </td>
    <td style="font-size:.82rem">{{ \Carbon\Carbon::parse($o->CreatedAt)->format('d M Y, H:i') }}</td>
    <td>
        <div class="d-flex gap-1 flex-wrap">
        <form method="POST" action="{{ route('caterer.orders.status', $o->OrderID) }}" class="d-flex flex-column gap-1">
            @csrf
            <div class="d-flex gap-1">
                <select name="status" class="form-select form-select-sm caterer-status-select" style="width:130px">
                    @foreach(['Pending','Confirmed','Preparing','Ready','Cancelled'] as $s)
                    <option value="{{ $s }}" @selected($o->OrderStatus==$s)>{{ $s }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary"><i data-feather="save" style="width:13px"></i></button>
            </div>
        </form>
        <a href="{{ route('caterer.orders.chat', $o->OrderID) }}" class="btn btn-sm btn-outline-info" title="Open Chat">
            <i data-feather="message-circle" style="width:13px"></i>
        </a>
        <button class="btn btn-sm btn-outline-danger" title="Report a problem with this order"
            onclick="openCatererReportModal({{ $o->OrderID }}, '{{ addslashes($o->CustomerName ?? '') }}')">
            <i data-feather="alert-triangle" style="width:13px"></i>
        </button>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($orders->hasPages())
<div class="card-footer">{{ $orders->appends(request()->query())->links() }}</div>
@endif
</div>
</div>

{{-- ══ Report Order Modal ══ --}}
<div class="modal fade" id="catererReportModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="background:#1e293b;border:1px solid rgba(255,255,255,0.08);border-radius:16px;">
      <div class="modal-header border-0 pb-1">
        <h5 class="modal-title fw-bold" style="color:#f1f5f9;">
          <span style="color:#f87171">⚠️</span> Report Issue — Order <span id="catererModalOrderId" style="color:#f87171;"></span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('caterer.support.store') }}">
        @csrf
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="color:#94a3b8;font-size:.82rem;">Problem Category *</label>
            <select name="category" class="form-select" style="background:#0f172a;border-color:rgba(255,255,255,0.1);color:#f1f5f9;" required>
              <option value="" style="background:#0f172a; color:#f1f5f9;">— Select a category —</option>
              <option value="Order Problem" style="background:#0f172a; color:#f1f5f9;">Order Problem</option>
              <option value="Payment / Billing Issue" style="background:#0f172a; color:#f1f5f9;">Payment / Billing Issue</option>
              <option value="Customer Misconduct" style="background:#0f172a; color:#f1f5f9;">Customer Misconduct</option>
              <option value="Contract / Agreement Dispute" style="background:#0f172a; color:#f1f5f9;">Contract / Agreement Dispute</option>
              <option value="Technical Issue on Platform" style="background:#0f172a; color:#f1f5f9;">Technical Issue on Platform</option>
              <option value="Platform Policy Concern" style="background:#0f172a; color:#f1f5f9;">Platform Policy Concern</option>
              <option value="Account / Profile Issue" style="background:#0f172a; color:#f1f5f9;">Account / Profile Issue</option>
              <option value="Other" style="background:#0f172a; color:#f1f5f9;">Other</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="color:#94a3b8;font-size:.82rem;">Subject *</label>
            <input type="text" name="subject" id="catererModalSubject" class="form-control"
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
function openCatererReportModal(orderId, customerName) {
    document.getElementById('catererModalOrderId').textContent = '#' + orderId;
    document.getElementById('catererModalSubject').value =
        'Issue with Order #' + orderId + (customerName ? ' — Customer: ' + customerName : '');
    new bootstrap.Modal(document.getElementById('catererReportModal')).show();
    if (window.feather) setTimeout(() => feather.replace(), 100);
}
// OTP and status logic updated for Caterer as per new requirements
</script>
@endpush
@endsection
