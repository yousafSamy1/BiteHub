@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="speaker" class="me-2"></i>Advertisements</h4>
</div>

{{-- Add New Ad Form --}}
<div class="card mb-4">
<div class="card-header"><strong>Add New Advertisement</strong></div>
<div class="card-body">
<form method="POST" action="{{ route('admin.ads.store') }}" class="row g-3" enctype="multipart/form-data">
@csrf
<div class="col-md-3">
    <label class="form-label">Title *</label>
    <input name="title" class="form-control" required>
</div>
<div class="col-md-3">
    <label class="form-label">Start Date *</label>
    <input type="date" name="start_date" class="form-control" required>
</div>
<div class="col-md-3">
    <label class="form-label">End Date *</label>
    <input type="date" name="end_date" class="form-control" required>
</div>
<div class="col-md-3">
    <label class="form-label">Background Image</label>
    <input type="file" name="image" class="form-control" accept="image/*">
</div>
<div class="col-12">
    <label class="form-label">Description</label>
    <textarea name="description" class="form-control" rows="2"></textarea>
</div>
<div class="col-auto">
    <button class="btn btn-primary"><i data-feather="plus" style="width:14px"></i> Create Ad</button>
</div>
</form>
</div>
</div>

{{-- Ads List --}}
<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Title</th><th>Kitchen/Caterer</th><th>Period</th><th>Status</th><th>Actions</th></tr>
</thead>
<tbody>
@forelse($ads as $ad)
<tr>
    <td>{{ $ad->AdvertisingID }}</td>
    <td>{{ $ad->Title }}</td>
    <td>{{ $ad->KitchenName ?? $ad->BusinessName ?? '(Admin)' }}</td>
    <td>{{ $ad->StartDate }} → {{ $ad->EndDate }}</td>
    <td>
        @if($ad->Status === 'Pending')
            <span class="badge bg-warning">Pending</span>
        @elseif(in_array($ad->Status, ['Approved', 'Active']))
            <span class="badge bg-success">{{ $ad->Status }}</span>
        @elseif(in_array($ad->Status, ['Rejected', 'Inactive']))
            <span class="badge bg-danger">{{ $ad->Status }}</span>
        @else
            <span class="badge bg-secondary">{{ $ad->Status }}</span>
        @endif
    </td>
    <td>
        @if($ad->Status === 'Pending')
            <form method="POST" action="{{ route('admin.ads.approve', $ad->AdvertisingID) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-outline-success" title="Approve">
                    <i data-feather="check" style="width:14px"></i>
                </button>
            </form>
            <form method="POST" action="{{ route('admin.ads.reject', $ad->AdvertisingID) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-outline-warning" title="Reject">
                    <i data-feather="x" style="width:14px"></i>
                </button>
            </form>
        @else
            <form method="POST" action="{{ route('admin.ads.toggle', $ad->AdvertisingID) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-outline-{{ $ad->Status === 'Active' || $ad->Status === 'Approved' ? 'warning' : 'success' }}" title="Toggle Active/Inactive">
                    <i data-feather="power" style="width:14px"></i>
                </button>
            </form>
        @endif
        <form method="POST" action="{{ route('admin.ads.delete', $ad->AdvertisingID) }}" class="d-inline">
            @csrf @method('DELETE')
            <button type="button" class="btn btn-sm btn-outline-danger confirm-submit" 
                    data-message="Permanently delete this advertisement?" 
                    data-icon="error">
                <i data-feather="trash-2" style="width:13px"></i>
            </button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="6" class="text-center text-muted py-4">No advertisements yet.</td></tr>
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
@endsection
