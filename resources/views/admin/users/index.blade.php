@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="users" class="me-2"></i>Users Management</h4>
</div>

{{-- Search / Filter --}}
<div class="card mb-3">
<div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-4">
        <input name="search" class="form-control form-control-sm" placeholder="Search name or email..." value="{{ request('search') }}">
    </div>
    <div class="col-md-3">
        <select name="role" class="form-select form-select-sm">
            <option value="">All Roles</option>
            @foreach(['Admin','Customer','KitchenOwner','Caterer','DeliveryAgent'] as $r)
            <option value="{{ $r }}" @selected(request('role')==$r)>{{ $r }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('admin.users') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div>
</div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr>
        <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Strikes</th><th>Joined</th><th>Actions</th>
    </tr>
</thead>
<tbody>
@forelse($users as $u)
<tr>
    <td>{{ $u->UserID }}</td>
    <td>
        <div class="d-flex align-items-center gap-2">
            @if($u->Image && file_exists(public_path('upload/admin_images/'.$u->Image)))
            <img src="{{ asset('upload/admin_images/'.$u->Image) }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
            @else
            <img src="{{ asset('upload/no_image.jpg') }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
            @endif
            {{ $u->FullName }}
        </div>
    </td>
    <td>{{ $u->Email }}</td>
    <td>
        @php $colors=['Admin'=>'danger','Customer'=>'primary','KitchenOwner'=>'warning','Caterer'=>'info','DeliveryAgent'=>'success']; @endphp
        <span class="badge bg-{{ $colors[$u->Role] ?? 'secondary' }}">{{ $u->Role }}</span>
    </td>
    <td>
        <span class="badge {{ $u->Status === 'Suspended' ? 'bg-danger' : 'bg-success' }}">
            {{ $u->Status ?? 'Active' }}
        </span>
    </td>
    <td>
        <span class="fw-bold {{ $u->ProfanityStrikes > 0 ? 'text-danger' : 'text-muted' }}">
            {{ $u->ProfanityStrikes }} / 3
        </span>
    </td>
    <td>{{ \Carbon\Carbon::parse($u->CreatedAt)->format('d M Y') }}</td>
    <td>
        <div class="d-flex gap-1">
        @if($u->Role !== 'Admin')
            @if($u->Status === 'Suspended')
            <form method="POST" action="{{ route('admin.users.activate', $u->UserID) }}">
                @csrf
                <button class="btn btn-outline-success btn-xs" title="Activate Account">
                    <i data-feather="unlock" style="width:13px"></i>
                </button>
            </form>
            @else
            <form method="POST" action="{{ route('admin.users.suspend', $u->UserID) }}">
                @csrf
                <button type="button" class="btn btn-outline-warning btn-xs confirm-submit" 
                        data-message="Suspend this user account? They will no longer be able to log in."
                        data-icon="warning"
                        title="Suspend Account">
                    <i data-feather="lock" style="width:13px"></i>
                </button>
            </form>
            @endif

            <form method="POST" action="{{ route('admin.users.delete', $u->UserID) }}">
                @csrf @method('DELETE')
                <button type="button" class="btn btn-outline-danger btn-xs confirm-submit" 
                        data-message="Permanently delete this user? This action cannot be undone."
                        data-icon="error"
                        title="Delete User">
                    <i data-feather="trash-2" style="width:13px"></i>
                </button>
            </form>
        @endif
        </div>
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
