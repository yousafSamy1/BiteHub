@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="repeat" class="me-2"></i>Subscriptions</h4>
</div>
<div class="card mb-3"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            @foreach(['Active','Expired','Cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('admin.subscriptions') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div></div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Customer</th><th>Plan</th><th>Price</th><th>Period</th><th>Status</th></tr>
</thead>
<tbody>
@forelse($subscriptions as $s)
<tr>
    <td>{{ $s->SubscriptionID }}</td>
    <td>
        <div>{{ $s->CustomerName ?? '—' }}</div>
        <small class="text-muted">{{ $s->Email }}</small>
    </td>
    <td>{{ $s->PlanTime ?? '—' }}</td>
    <td class="fw-bold">{{ number_format($s->Price ?? 0, 2) }} EGP</td>
    <td style="font-size:.82rem">{{ $s->StartDate }} → {{ $s->EndDate }}</td>
    <td>
        <span class="badge bg-{{ $s->Status === 'Active' ? 'success' : ($s->Status === 'Expired' ? 'warning' : 'danger') }}">
            {{ $s->Status }}
        </span>
    </td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted py-4">No subscriptions found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($subscriptions->hasPages())
<div class="card-footer">{{ $subscriptions->appends(request()->query())->links() }}</div>
@endif
</div>
</div>
@endsection
