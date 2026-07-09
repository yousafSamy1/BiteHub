@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="briefcase" class="me-2"></i>Caterers Management</h4>
</div>
<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Business</th><th>Owner</th><th>Status</th><th>Actions</th></tr>
</thead>
<tbody>
@forelse($caterers as $c)
<tr>
    <td>{{ $c->CatererID }}</td>
    <td><strong>{{ $c->BusinessName ?? '—' }}</strong></td>
    <td>
        <div>{{ $c->FullName }}</div>
        <small class="text-muted">{{ $c->Email }}</small>
    </td>
    <td>
        <span class="badge bg-{{ $c->IsActive ? 'success' : 'danger' }}">{{ $c->IsActive ? 'Active' : 'Inactive' }}</span>
    </td>
    <td>
        <div class="d-flex gap-1 flex-wrap">
            @if(!empty($c->Attachment))
            <button type="button" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#attachmentModal{{ $c->CatererID }}" title="View Attachments">
                <i data-feather="paperclip" style="width:13px"></i> Attachments
            </button>
            @endif
            <form method="POST" action="{{ route('admin.caterers.toggle', $c->CatererID) }}" class="d-inline">@csrf
                <button class="btn btn-sm btn-{{ $c->IsActive ? 'outline-danger' : 'outline-success' }}">
                    {{ $c->IsActive ? 'Deactivate' : 'Activate' }}
                </button>
            </form>
        </div>
    </td>
</tr>
@empty
<tr><td colspan="5" class="text-center text-muted py-4">No caterers found.</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($caterers->hasPages())
<div class="card-footer">{{ $caterers->links() }}</div>
@endif
</div>
</div>

@foreach($caterers as $c)
    @if(!empty($c->Attachment))
    <div class="modal fade" id="attachmentModal{{ $c->CatererID }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Attachments for {{ $c->BusinessName ?? $c->FullName }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="row">
                @foreach($c->Attachment as $file)
                    <div class="col-md-4 mb-3 text-center">
                        @if(Str::endsWith(strtolower($file), ['.pdf', '.doc', '.docx']))
                            <a href="{{ asset('upload/caterer_attachments/' . $file) }}" target="_blank" class="btn btn-outline-primary d-inline-block p-3 w-100">
                                <i data-feather="file-text" class="mb-2" style="width:30px;height:30px"></i><br>
                                View Document
                            </a>
                        @else
                            <a href="{{ asset('upload/caterer_attachments/' . $file) }}" target="_blank">
                                <img src="{{ asset('upload/caterer_attachments/' . $file) }}" class="img-fluid rounded border" alt="Attachment" style="max-height: 200px; object-fit: cover;">
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
