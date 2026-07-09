@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="speaker" class="me-2"></i>My Advertisements</h4>
</div>

@if(session('message'))
<div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : (session('alert-type') === 'error' ? 'danger' : 'warning') }} alert-dismissible fade show">
    {{ session('message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Kitchen Identity Banner --}}
<div class="alert alert-info d-flex align-items-center gap-3 mb-4">
    <i data-feather="home" style="width:28px;height:28px;flex-shrink:0"></i>
    <div>
        <strong style="font-size:1.1rem">{{ $kitchen->KitchenName }}</strong>
        @if($kitchen->Description)
            <div class="text-muted small mt-1">{{ $kitchen->Description }}</div>
        @endif
        <div class="mt-1">
            <span class="badge bg-{{ $kitchen->Status === 'Active' ? 'success' : 'warning' }} me-1">{{ $kitchen->Status }}</span>
            <span class="badge bg-{{ $kitchen->VerifyStatus === 'Verified' ? 'primary' : 'secondary' }}">{{ $kitchen->VerifyStatus }}</span>
        </div>
    </div>
</div>

{{-- Add New Ad Form --}}
<div class="card mb-4">
<div class="card-header">
    <strong><i data-feather="speaker" style="width:16px" class="me-1"></i>Submit New Advertisement for <span class="text-primary">{{ $kitchen->KitchenName }}</span></strong>
</div>
<div class="card-body">
<form method="POST" action="{{ route('kitchen.ads.store') }}" class="row g-3" id="adForm" enctype="multipart/form-data">
@csrf
<div class="col-md-3">
    <label class="form-label fw-semibold">Ad Title *</label>
    <input name="title" class="form-control" placeholder="e.g. Summer Special Offer" required>
</div>
<div class="col-md-3">
    <label class="form-label fw-semibold">Start Date *</label>
    <input type="date" name="start_date" id="start_date" class="form-control" required
           min="{{ date('Y-m-d') }}" onchange="calcTotal()">
</div>
<div class="col-md-3">
    <label class="form-label fw-semibold">End Date *</label>
    <input type="date" name="end_date" id="end_date" class="form-control" required onchange="calcTotal()">
</div>
<div class="col-md-3">
    <label class="form-label fw-semibold">Background Image</label>
    <input type="file" name="image" class="form-control" accept="image/*">
</div>
<div class="col-12">
    <label class="form-label fw-semibold">Description</label>
    <textarea name="description" class="form-control" rows="2" placeholder="Optional description of your ad campaign..."></textarea>
</div>

<div class="col-12 mt-4">
    <div class="d-flex align-items-center justify-content-between p-3" style="background: rgba(0,0,0,0.15); border-radius: 12px; border: 1px solid var(--border-color);">
        <div class="d-flex align-items-center gap-3">
            <div style="width: 45px; height: 45px; border-radius: 10px; background: rgba(74, 222, 128, 0.1); color: #4ade80; display: flex; align-items: center; justify-content: center;">
                <i data-feather="dollar-sign"></i>
            </div>
            <div>
                <div class="text-muted small fw-bold text-uppercase tracking-wider">Current Wallet Balance</div>
                <div class="fs-5 fw-bolder text-white" id="currentWalletBalance" data-balance="{{ Auth::user()->Wallet_balance ?? 0 }}">{{ number_format(Auth::user()->Wallet_balance ?? 0, 2) }} EGP</div>
            </div>
        </div>
    </div>
</div>

{{-- Pricing Summary --}}
<div class="col-12 mt-3">
    <div class="alert alert-warning mb-0 d-flex align-items-center gap-3" id="priceSummary" style="display:none!important">
        <i data-feather="info" style="width:20px;flex-shrink:0"></i>
        <span>
            <strong>Price per day:</strong> 50.00 EGP &nbsp;×&nbsp;
            <strong>Days:</strong> <span id="daysCount">0</span>
            &nbsp;=&nbsp;
            <strong class="text-success fs-6"><span id="totalAmount">0.00</span> EGP Total</strong>
        </span>
    </div>
    
    {{-- Insufficient Balance Warning --}}
    <div class="alert alert-danger mt-3 mb-0 d-flex align-items-center gap-3" id="balanceWarning" style="display:none!important">
        <i data-feather="alert-circle" style="width:20px;flex-shrink:0"></i>
        <span>
            <strong>Insufficient Balance!</strong> Your wallet balance is lower than the required ad cost. 
            <a href="#" class="alert-link ms-2 text-decoration-underline">Recharge Wallet</a>
        </span>
    </div>
</div>

<div class="col-auto mt-4">
    <button class="btn btn-primary px-4 py-2" id="submitBtn" disabled>
        <i data-feather="check" style="width:16px" class="me-1"></i>
        Confirm & Pay <span id="btnTotal"></span>
    </button>
    <small class="text-muted d-block mt-2">Your ad will be submitted for admin approval after payment.</small>
</div>
</form>
</div>
</div>

{{-- Ads List --}}
<div class="card">
<div class="card-header d-flex align-items-center justify-content-between">
    <strong>Submitted Advertisements — {{ $kitchen->KitchenName }}</strong>
    <small class="text-muted">{{ $ads->total() }} ad(s) total</small>
</div>
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr>
        <th>#</th>
        <th>Kitchen</th>
        <th>Title</th>
        <th>Period</th>
        <th>Days</th>
        <th>Price/Day</th>
        <th>Total Paid</th>
        <th>Status</th>
    </tr>
</thead>
<tbody>
@forelse($ads as $ad)
<tr>
    <td class="text-muted">{{ $ad->AdvertisingID }}</td>
    <td>
        <span class="fw-semibold text-primary">{{ $kitchen->KitchenName }}</span>
    </td>
    <td>{{ $ad->Title }}</td>
    <td style="font-size:0.85rem">
        <span class="text-success">{{ \Carbon\Carbon::parse($ad->StartDate)->format('M d, Y') }}</span><br>
        <span class="text-danger">{{ \Carbon\Carbon::parse($ad->EndDate)->format('M d, Y') }}</span>
    </td>
    <td>{{ \Carbon\Carbon::parse($ad->StartDate)->diffInDays($ad->EndDate) }}</td>
    <td>{{ number_format($ad->PricePerDay ?? 50, 2) }} EGP</td>
    <td class="fw-bold">
        {{ number_format($ad->TotalAmount ?? 0, 2) }} EGP
        @if($ad->PaidAt)
            <br><span class="badge bg-success" style="font-size:0.7rem"><i data-feather="check" style="width:10px"></i> Paid</span>
        @else
            <br><span class="badge bg-danger" style="font-size:0.7rem">Unpaid</span>
        @endif
    </td>
    <td>
        @if($ad->Status === 'Pending')
            <span class="badge bg-warning text-dark">⏳ Pending</span>
        @elseif(in_array($ad->Status, ['Approved', 'Active']))
            <span class="badge bg-success">✅ {{ $ad->Status }}</span>
        @elseif(in_array($ad->Status, ['Rejected', 'Inactive']))
            <span class="badge bg-danger">❌ {{ $ad->Status }}</span>
        @else
            <span class="badge bg-secondary">{{ $ad->Status }}</span>
        @endif
    </td>
</tr>
@empty
<tr><td colspan="8" class="text-center text-muted py-5">
    <i data-feather="speaker" style="width:30px;opacity:0.3" class="d-block mx-auto mb-2"></i>
    No advertisements submitted yet for {{ $kitchen->KitchenName }}.
</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($ads->hasPages())
<div class="card-footer">{{ $ads->links() }}</div>
@endif
</div>
</div>

<script>
const PRICE_PER_DAY = 50.00;

function calcTotal() {
    const start = document.getElementById('start_date').value;
    const end   = document.getElementById('end_date').value;
    const summary = document.getElementById('priceSummary');
    const submitBtn = document.getElementById('submitBtn');
    if (!start || !end) return;

    const startDate = new Date(start);
    const endDate   = new Date(end);
    const diffTime  = endDate - startDate;

    if (diffTime <= 0) {
        summary.style.display = 'none';
        submitBtn.disabled = true;
        return;
    }

    const days  = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    const total = (days * PRICE_PER_DAY).toFixed(2);

    document.getElementById('daysCount').textContent   = days;
    document.getElementById('totalAmount').textContent  = total;
    document.getElementById('btnTotal').textContent     = `(${total} EGP)`;

    summary.style.cssText = '';
    
    // Check Wallet Balance
    const balanceEl = document.getElementById('currentWalletBalance');
    const currentBalance = parseFloat(balanceEl.getAttribute('data-balance'));
    const warningEl = document.getElementById('balanceWarning');
    
    if (currentBalance < total) {
        warningEl.style.cssText = '';
        submitBtn.disabled = true;
    } else {
        warningEl.style.display = 'none';
        submitBtn.disabled = false;
    }

    if (window.feather) feather.replace();
}
</script>
@endsection
