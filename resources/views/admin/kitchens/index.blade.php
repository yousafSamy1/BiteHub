@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="home" class="me-2"></i>Kitchens Management</h4>
</div>

{{-- Filter --}}
<div class="card mb-3"><div class="card-body py-2">
<form method="GET" class="row g-2 align-items-end">
    <div class="col-md-3">
        <select name="status" class="form-select form-select-sm">
            <option value="">All Status</option>
            @foreach(['Active','Inactive','Suspended'] as $s)
            <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3">
        <select name="verify" class="form-select form-select-sm">
            <option value="">All Verification</option>
            @foreach(['Pending','Verified','Rejected'] as $v)
            <option value="{{ $v }}" @selected(request('verify')==$v)>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
    <div class="col-auto"><a href="{{ route('admin.kitchens') }}" class="btn btn-secondary btn-sm">Reset</a></div>
</form>
</div></div>

<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Kitchen</th><th>Owner</th><th>Status</th><th>Verified</th><th>Joined</th><th>Actions</th></tr>
</thead>
<tbody>
@forelse($kitchens as $k)
<tr>
    <td>{{ $k->KitchenOwnerID }}</td>
    <td><strong>{{ $k->KitchenName ?? '—' }}</strong></td>
    <td>
        <div>{{ $k->FullName }}</div>
        <small class="text-muted">{{ $k->Email }}</small>
    </td>
    <td>
        @php $sc=['Active'=>'success','Inactive'=>'secondary','Suspended'=>'danger']; @endphp
        <span class="badge bg-{{ $sc[$k->Status] ?? 'secondary' }}">{{ $k->Status }}</span>
    </td>
    <td>
        @php $vc=['Verified'=>'success','Pending'=>'warning','Rejected'=>'danger']; @endphp
        <span class="badge bg-{{ $vc[$k->VerifyStatus] ?? 'secondary' }}">{{ $k->VerifyStatus }}</span>
    </td>
    <td>{{ \Carbon\Carbon::parse($k->JoinedAt)->format('d M Y') }}</td>
    <td>
        <div class="d-flex gap-1 flex-wrap">
        @if(!empty($k->Attachment))
        <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#attachmentModal{{ $k->KitchenOwnerID }}" title="View Attachments">
            <i data-feather="paperclip" style="width:13px"></i> Attachments
        </button>
        @endif
        @if($k->VerifyStatus !== 'Verified')
        <form method="POST" action="{{ route('admin.kitchens.verify', $k->KitchenOwnerID) }}" class="d-inline">@csrf
            <button type="button" class="btn btn-success btn-sm confirm-submit" 
                    data-message="Approve and verify this kitchen? They will be allowed to start selling." 
                    data-icon="question">
                <i data-feather="check" style="width:13px"></i> Verify
            </button>
        </form>
        @endif
        @if($k->VerifyStatus === 'Pending')
        <form method="POST" action="{{ route('admin.kitchens.reject', $k->KitchenOwnerID) }}" class="d-inline">@csrf
            <button type="button" class="btn btn-warning btn-sm confirm-submit" 
                    data-message="Reject this kitchen application?" 
                    data-icon="warning">
                <i data-feather="x" style="width:13px"></i> Reject
            </button>
        </form>
        @endif
        @if($k->Status !== 'Suspended')
        <form method="POST" action="{{ route('admin.kitchens.suspend', $k->KitchenOwnerID) }}" class="d-inline">@csrf
            <button type="button" class="btn btn-outline-danger btn-sm confirm-submit" 
                    data-message="Suspend this kitchen? All their items will be hidden from customers." 
                    data-icon="warning">Suspend</button>
        </form>
        @else
        <form method="POST" action="{{ route('admin.kitchens.activate', $k->KitchenOwnerID) }}" class="d-inline">@csrf
            <button type="button" class="btn btn-outline-success btn-sm confirm-submit" 
                    data-message="Re-activate this kitchen?" 
                    data-icon="info">Activate</button>
        </form>
        @endif
        </div>
    </td>
</tr>
@empty
<tr><td colspan="7" class="text-center text-muted py-4">No kitchens found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($kitchens->hasPages())
<div class="card-footer">{{ $kitchens->appends(request()->query())->links() }}</div>
@endif
</div>
</div>

@foreach($kitchens as $k)
    @if(!empty($k->Attachment))
    <div class="modal fade" id="attachmentModal{{ $k->KitchenOwnerID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Attachments for {{ $k->KitchenName ?? $k->FullName }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
                @foreach($k->Attachment as $file)
                    <div class="col-md-4 mb-3 text-center">
                        @if(Str::endsWith(strtolower($file), ['.pdf', '.doc', '.docx']))
                            <a href="{{ asset('upload/kitchen_attachments/' . $file) }}" target="_blank" class="btn btn-outline-primary d-inline-block p-3 w-100">
                                <i data-feather="file-text" class="mb-2" style="width:30px;height:30px"></i><br>
                                View Document
                            </a>
                        @else
                            <a href="{{ asset('upload/kitchen_attachments/' . $file) }}" target="_blank">
                                <img src="{{ asset('upload/kitchen_attachments/' . $file) }}" class="img-fluid rounded border" alt="Attachment" style="max-height: 200px; object-fit: cover;">
                            </a>
                        @endif
                    </div>
                @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
@endforeach

@endsection
