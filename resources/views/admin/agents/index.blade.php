@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="truck" class="me-2"></i>Delivery Agents</h4>
</div>
<div class="card mb-3"><div class="card-body py-2">
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-auto">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Status</option>
                @foreach(['Available','Offline'] as $s)
                <option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto"><button class="btn btn-primary btn-sm">Filter</button></div>
        <div class="col-auto"><a href="{{ route('admin.agents') }}" class="btn btn-secondary btn-sm">Reset</a></div>
    </form>
    <div>
        <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addAgentModal">
            <i data-feather="plus" style="width:13px"></i> Add Agent
        </button>
    </div>
</div>
</div></div>
<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Agent</th><th>Vehicle</th><th>Approval Status</th><th>Attachments</th><th>Change Status</th></tr>
</thead>
<tbody>
@forelse($agents as $a)
<tr>
    <td>{{ $a->DeliveryAgentID }}</td>
    <td>
        <div>{{ $a->FullName }}</div>
        <small class="text-muted">{{ $a->Email }}</small>
    </td>
    <td>{{ $a->VehicleType ?? '—' }}</td>
    <td>
        @if($a->AdminVerified)
            <span class="badge bg-success">Approved</span>
        @elseif($a->IsVerified && !$a->AdminVerified)
            <span class="badge bg-warning text-dark">Pending Review</span>
        @else
            <span class="badge bg-secondary">No Uploads</span>
        @endif
    </td>
    <td>
        @if($a->Attachment && is_array($a->Attachment))
            <div class="d-flex gap-1 align-items-center">
                <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewAttachmentsModal{{ $a->DeliveryAgentID }}">
                    <i data-feather="image" style="width:13px"></i> View
                </button>
                @if(!$a->AdminVerified)
                    <form method="POST" action="{{ route('admin.agents.approve', $a->DeliveryAgentID) }}">
                        @csrf
                        <button class="btn btn-sm btn-success"><i data-feather="check-circle" style="width:13px"></i> Approve</button>
                    </form>
                @endif
            </div>
        @else
            <span class="text-muted small">No files</span>
        @endif
    </td>
    <td>
        @php $sc=['Available'=>'success','Offline'=>'secondary']; @endphp
        <span class="badge bg-{{ $sc[$a->Status] ?? 'secondary' }} d-block mb-1">{{ $a->Status }}</span>
        <form method="POST" action="{{ route('admin.agents.status', $a->DeliveryAgentID) }}" class="d-flex gap-1">
            @csrf
            <select name="status" class="form-select form-select-sm" style="width:130px">
                @foreach(['Available','Offline'] as $s)
                <option value="{{ $s }}" @selected($a->Status==$s)>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-sm btn-primary"><i data-feather="save" style="width:13px"></i></button>
        </form>
    </td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-4">No agents found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($agents->hasPages())
<div class="card-footer">{{ $agents->links() }}</div>
@endif
</div>

<!-- View Attachments Modals -->
@foreach($agents as $a)
    @if($a->Attachment && is_array($a->Attachment))
    <div class="modal fade" id="viewAttachmentsModal{{ $a->DeliveryAgentID }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Attachments for {{ $a->FullName }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="row g-3">
                        @foreach($a->Attachment as $path)
                            <div class="col-md-6">
                                <a href="{{ asset($path) }}" target="_blank">
                                    <img src="{{ asset($path) }}" alt="Attachment" class="img-fluid rounded border" style="max-height:300px; object-fit:contain;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

<!-- Add Agent Modal -->
<div class="modal fade" id="addAgentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Delivery Agent</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="{{ route('admin.agents.store') }}" method="POST">
        @csrf
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Vehicle Type</label>
                <select name="vehicle_type" class="form-select" required>
                    <option value="Bike">Bike</option>
                    <option value="Car">Car</option>
                    <option value="Motorcycle">Motorcycle</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-success">Save & Send Credentials</button>
        </div>
      </form>
    </div>
  </div>
</div>

</div>
@endsection
