@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="star" class="me-2"></i>Loyalty Points</h4>
</div>

<div class="row mb-4">
{{-- Add Points Form --}}
<div class="col-md-5">
<div class="card h-100">
<div class="card-header"><strong>Add / Adjust Points</strong></div>
<div class="card-body">
<form method="POST" action="{{ route('admin.loyalty.add') }}">
@csrf
<div class="mb-3">
    <label class="form-label">Customer</label>
    <select name="customer_id" class="form-select" required>
        <option value="">Select customer...</option>
        @foreach($customers as $c)
        <option value="{{ $c->CustomerID }}">{{ $c->FullName }}</option>
        @endforeach
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Points</label>
    <input type="number" name="points" class="form-control" min="1" required>
</div>
<div class="mb-3">
    <label class="form-label">Type</label>
    <select name="type" class="form-select">
        <option value="Bonus">Bonus</option>
        <option value="Earned">Earned</option>
        <option value="Referral">Referral</option>
        <option value="Redeemed">Redeemed (deduct)</option>
    </select>
</div>
<div class="mb-3">
    <label class="form-label">Note</label>
    <input name="description" class="form-control" placeholder="Reason...">
</div>
<button class="btn btn-primary w-100"><i data-feather="plus" style="width:14px"></i> Add Points</button>
</form>
</div>
</div>
</div>

{{-- Recent Transactions --}}
<div class="col-md-7">
<div class="card h-100">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Customer</th><th>Points</th><th>Type</th><th>Description</th><th>Date</th></tr>
</thead>
<tbody>
@forelse($transactions as $t)
<tr>
    <td>{{ $t->TransactionID }}</td>
    <td>{{ $t->CustomerName ?? '—' }}</td>
    <td>
        <span class="fw-bold text-{{ in_array($t->Type, ['Earned','Bonus','Referral']) ? 'success' : 'danger' }}">
            {{ in_array($t->Type, ['Earned','Bonus','Referral']) ? '+' : '-' }}{{ $t->Points }}
        </span>
    </td>
    <td><span class="badge bg-{{ $t->Type === 'Redeemed' ? 'danger' : 'success' }}">{{ $t->Type }}</span></td>
    <td style="font-size:.82rem">{{ Str::limit($t->Description, 40) }}</td>
    <td style="font-size:.82rem">{{ \Carbon\Carbon::parse($t->CreatedAt)->format('d M Y') }}</td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-4">No transactions yet.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($transactions->hasPages())
<div class="card-footer">{{ $transactions->links() }}</div>
@endif
</div>
</div>
</div>

</div>
@endsection
