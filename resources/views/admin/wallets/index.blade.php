@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">

{{-- Header --}}
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="credit-card" class="me-2"></i>Wallet Management</h4>
    <div class="d-flex gap-3 align-items-center">
        <div class="bg-success bg-opacity-10 border border-success rounded px-3 py-2 text-success fw-bold">
            <i data-feather="trending-up" style="width:16px"></i>
            Total Wallets: {{ number_format($totalWallets, 2) }} EGP
        </div>
    </div>
</div>

@if(session('message'))
<div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : (session('alert-type') === 'error' ? 'danger' : 'warning') }} alert-dismissible fade show">
    {{ session('message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

{{-- Filter --}}
<div class="card mb-3">
<div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-4">
        <input name="search" class="form-control form-control-sm" placeholder="Search name or email..."
               value="{{ request('search') }}">
    </div>
    <div class="col-md-3">
        <select name="role" class="form-select form-select-sm">
            <option value="">All Roles</option>
            @foreach(['Customer','KitchenOwner','Caterer','DeliveryAgent'] as $r)
            <option value="{{ $r }}" @selected(request('role')==$r)>{{ $r }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('admin.wallets') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div>
</div>

{{-- Wallets Table --}}
<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr>
        <th>#</th>
        <th>User</th>
        <th>Role</th>
        <th>Email</th>
        <th>Wallet Balance</th>
    </tr>
</thead>
<tbody>
@forelse($users as $u)
<tr>
    <td class="text-muted">{{ $u->UserID }}</td>
    <td>
        <div class="d-flex align-items-center gap-2">
            @if($u->Image)
            <img src="{{ asset('upload/admin_images/'.$u->Image) }}" style="width:30px;height:30px;border-radius:50%;object-fit:cover">
            @else
            <div style="width:30px;height:30px;border-radius:50%;background:#6c757d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.75rem;font-weight:700">{{ strtoupper(substr($u->FullName,0,1)) }}</div>
            @endif
            <span class="fw-semibold">{{ $u->FullName }}</span>
        </div>
    </td>
    <td>
        @php $colors=['Admin'=>'danger','Customer'=>'primary','KitchenOwner'=>'warning','Caterer'=>'info','DeliveryAgent'=>'success']; @endphp
        <span class="badge bg-{{ $colors[$u->Role] ?? 'secondary' }}">{{ $u->Role }}</span>
    </td>
    <td class="text-muted" style="font-size:.85rem">{{ $u->Email }}</td>
    <td>
        <span class="fw-bold fs-6 {{ $u->Wallet_balance > 0 ? 'text-success' : 'text-muted' }}">
            {{ number_format($u->Wallet_balance ?? 0, 2) }} EGP
        </span>
    </td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-4">No users found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($users->hasPages())
<div class="card-footer">{{ $users->appends(request()->query())->links() }}</div>
@endif
</div>
</div>
@endsection
