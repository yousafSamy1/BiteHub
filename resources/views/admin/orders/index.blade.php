@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="shopping-bag" class="me-2"></i>Orders Management</h4>
</div>

<div class="card mb-3"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            @foreach(['Pending','Confirmed','Preparing','Ready','Delivering','Delivered','Cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('admin.orders') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div></div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Type</th><th>Customer</th><th>Address</th><th>Total</th><th>Payment</th><th>Agent</th><th>Status</th><th>Date</th><th>Actions</th></tr>
</thead>
<tbody id="orders-table-body">
    @include('admin.orders.table_body')
</tbody>
</table>
</div>
</div>
@if($orders->hasPages())
<div class="card-footer">{{ $orders->appends(request()->query())->links() }}</div>
@endif
</div>
</div>

@push('custom-scripts')
<script>
    let lastOrderCount = {{ $orders->total() }};
    
    function refreshOrdersTable() {
        const status = new URLSearchParams(window.location.search).get('status') || '';
        
        fetch(`{{ route('admin.orders.fragment') }}?status=${status}`)
            .then(res => res.text())
            .then(html => {
                const tbody = document.getElementById('orders-table-body');
                if (tbody) {
                    tbody.innerHTML = html;
                    if (window.feather) feather.replace(); // Refresh icons
                }
            })
            .catch(err => console.error('Failed to refresh orders table', err));
    }

    setInterval(() => {
        fetch(`{{ route('admin.realtime.stats') }}`)
            .then(res => res.json())
            .then(data => {
                if (data.kpis && data.kpis.totalOrders !== lastOrderCount) {
                    refreshOrdersTable();
                    lastOrderCount = data.kpis.totalOrders;
                }
            });
    }, 5000);
</script>
@endpush
@endsection
