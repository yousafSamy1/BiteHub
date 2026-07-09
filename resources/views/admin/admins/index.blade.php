@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
        <h4 class="mb-0"><i data-feather="shield" class="me-2"></i>Administration Management</h4>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAdminModal">
            <i data-feather="plus-circle" class="me-1" style="width:16px"></i> Add New Admin
        </button>
    </div>

    {{-- Add Admin Modal --}}
    <div class="modal fade" id="addAdminModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Administrator</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="{{ route('admin.admins.store') }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. John Doe" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email Address <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" placeholder="e.g. john@example.com" required>
                            <small class="text-muted">A secure password will be automatically generated and emailed to this address.</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Admin Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Administrator</th>
                            <th>Email</th>
                            <th>Current Role</th>
                            <th>Status</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $u)
                        <tr>
                            <td>{{ $u->UserID }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    @if($u->Image)
                                        @php
                                            $imgPath = $u->Role === 'Admin' || $u->Role === 'Owner' 
                                                ? asset('upload/admin_images/'.$u->Image) 
                                                : asset('upload/no_image.jpg');
                                            // Handle UI avatars
                                            if(str_contains($u->Image, 'http')) $imgPath = $u->Image;
                                        @endphp
                                        <img src="{{ $imgPath }}" style="width:32px;height:32px;border-radius:50%;object-fit:cover">
                                    @else
                                        <div style="width:32px;height:32px;border-radius:50%;background:#6c757d;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.8rem;font-weight:700">{{ strtoupper(substr($u->FullName,0,1)) }}</div>
                                    @endif
                                    {{ $u->FullName }}
                                    @if(in_array($u->Email, ['wezo8123@gmail.com', 'matf4866@gmail.com', 'yousafsamy50@gmail.com']))
                                        <span class="badge bg-soft-info text-info border-0 ms-1" style="font-size: 0.6rem;">CORE OWNER</span>
                                    @endif
                                </div>
                            </td>
                            <td>{{ $u->Email }}</td>
                            <td>
                                <span class="badge bg-{{ $u->Role === 'Owner' ? 'info' : 'danger' }}">{{ $u->Role }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $u->Status === 'Suspended' ? 'bg-danger' : 'bg-success' }}">
                                    {{ $u->Status ?? 'Active' }}
                                </span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($u->CreatedAt)->format('d M Y') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <form method="POST" action="{{ route('admin.admins.toggle-role', $u->UserID) }}">
                                        @csrf
                                        <button type="button" class="btn btn-{{ $u->Role === 'Admin' ? 'info' : 'danger' }} btn-xs confirm-submit" 
                                                data-message="Change this users role to {{ $u->Role === 'Admin' ? 'Owner' : 'Admin' }}?"
                                                data-icon="question"
                                                title="Switch Role">
                                            <i data-feather="refresh-cw" style="width:13px"></i> Switch to {{ $u->Role === 'Admin' ? 'Owner' : 'Admin' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('admin.users.delete', $u->UserID) }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="btn btn-outline-danger btn-xs confirm-submit" 
                                                data-message="Are you sure you want to permanently delete this administrator account?"
                                                data-icon="warning"
                                                title="Delete Account">
                                            <i data-feather="trash-2" style="width:13px"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
