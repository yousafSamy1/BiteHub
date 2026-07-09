@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Support Inquiries</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title">User Inquiries (BiteBot Escalations)</h6>
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>User</th>
                                    <th>Last Message</th>
                                    <th>Status</th>
                                    <th>Last Activity</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inquiries as $key => $item)
                                <tr>
                                    <td>{{ $key+1 }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="ms-2">
                                                <p class="font-weight-bold mb-0">{{ $item->user->FullName }}</p>
                                                <small class="text-muted">{{ $item->user->Email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 250px;">
                                            {{ $item->messages->last()->Message ?? 'No messages' }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($item->Status == 'Bot')
                                            <span class="badge bg-secondary">Bot handled</span>
                                        @elseif($item->Status == 'Escalated')
                                            <span class="badge bg-danger">Requires Admin</span>
                                        @else
                                            <span class="badge bg-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->updated_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('admin.inquiry.chat', $item->InquiryID) }}" class="btn btn-primary btn-icon-text">
                                            <i class="btn-icon-prepend" data-feather="message-square"></i> Open Chat
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
