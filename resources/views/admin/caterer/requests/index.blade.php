@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="clipboard" class="me-2"></i>My Catering Requests</h4>
</div>
<div class="card mb-3"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            @foreach(['Pending','Accepted','Rejected','Completed','Cancelled'] as $s)
            <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('caterer.requests') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div></div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Customer</th><th>Event</th><th>Date</th><th>Guests</th><th>Budget</th><th>Details</th><th>Status</th><th>Action</th></tr>
</thead>
<tbody>
@forelse($requests as $r)
<tr>
    <td>{{ $r->RequestID }}</td>
    <td>{{ $r->CustomerName ?? '—' }}</td>
    <td><strong>{{ $r->EventType ?? '—' }}</strong></td>
    <td>{{ $r->EventDate ?? '—' }}</td>
    <td>{{ $r->GuestCount ?? '—' }}</td>
    <td>{{ $r->Budget ? number_format($r->Budget, 2).' EGP' : '—' }}</td>
    <td style="font-size:.82rem">{{ Str::limit($r->Details, 40) }}</td>
    <td>
        @php $sc=['Pending'=>'warning','Accepted'=>'success','Rejected'=>'danger','Completed'=>'primary','Cancelled'=>'secondary']; @endphp
        <span class="badge bg-{{ $sc[$r->Status] ?? 'secondary' }}">{{ $r->Status }}</span>
    </td>
    <td>
        <form method="POST" action="{{ route('caterer.requests.update', $r->RequestID) }}" class="d-flex gap-1">
            @csrf
            <select name="status" class="form-select form-select-sm" style="width:130px">
                @foreach(['Accepted','Rejected','Completed','Cancelled'] as $s)
                <option value="{{ $s }}" @selected($r->Status==$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary"><i data-feather="save" style="width:13px"></i></button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="9" class="text-center text-muted py-4">No catering requests yet.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($requests->hasPages())
<div class="card-footer">{{ $requests->appends(request()->query())->links() }}</div>
@endif
</div>
</div>
@endsection
