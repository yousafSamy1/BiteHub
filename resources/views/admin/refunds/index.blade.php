@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Refund Requests</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card dark-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title mb-0" style="font-size: 1.2rem; font-weight: 800; color: #f8fafc;">
                            <i data-feather="refresh-ccw" class="me-2 text-primary"></i> Refund Management
                        </h6>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-dark-custom">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Original Price</th>
                                    <th>Consumed/Used</th>
                                    <th>Refund Amount</th>
                                    <th>Reason</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($requests as $request)
                                <tr>
                                    <td>#{{ $request->RequestID }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box-dark icon-primary me-2" style="width: 32px; height: 32px; font-size: 0.8rem; font-weight: bold;">
                                                {{ substr($request->customer->user->FullName, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $request->customer->user->FullName }}</div>
                                                <small class="text-custom-muted">{{ $request->customer->user->Email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge {{ $request->RefundableType === 'Order' ? 'bg-info' : 'bg-primary' }} bg-opacity-10 text-{{ $request->RefundableType === 'Order' ? 'info' : 'primary' }} border-{{ $request->RefundableType === 'Order' ? 'info' : 'primary' }} border">
                                            {{ $request->RefundableType }} #{{ $request->RefundableID }}
                                        </span>
                                    </td>
                                    <td class="text-custom-muted">{{ number_format($request->OriginalAmount, 2) }} EGP</td>
                                    <td class="text-danger">-{{ number_format($request->ConsumedAmount, 2) }} EGP</td>
                                    <td class="fw-bold text-success">{{ number_format($request->Amount, 2) }} EGP</td>
                                    <td>
                                        <span title="{{ $request->Reason }}" data-bs-toggle="tooltip">
                                            {{ Str::limit($request->Reason, 30) }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $badge = [
                                                'Pending'  => 'badge-soft-warning',
                                                'Approved' => 'badge-soft-success',
                                                'Rejected' => 'badge-soft-danger',
                                            ][$request->Status] ?? 'badge-soft-info';
                                        @endphp
                                        <span class="{{ $badge }}">{{ $request->Status }}</span>
                                    </td>
                                    <td>
                                        @if($request->Status === 'Pending')
                                            <div class="d-flex gap-2">
                                                <button onclick="confirmApprove({{ $request->RequestID }}, {{ $request->Amount }}, {{ $request->OriginalAmount }}, {{ $request->ConsumedAmount }})" class="btn btn-success btn-icon btn-sm" title="Approve">
                                                    <i data-feather="check"></i>
                                                </button>
                                                <button onclick="openRejectModal({{ $request->RequestID }})" class="btn btn-danger btn-icon btn-sm" title="Reject">
                                                    <i data-feather="x"></i>
                                                </button>
                                            </div>
                                        @else
                                            <small class="text-custom-muted">Processed</small>
                                        @endif
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

{{-- Approve Confirmation Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1e293b; color: #f8fafc; border-radius: 16px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Approve Refund</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <div class="icon-box-dark icon-success mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                    <i data-feather="check-circle"></i>
                </div>
                <h4 class="mb-2">Are you sure?</h4>
                <p class="text-custom-muted mb-0">Original Total: <span id="approveOriginal" class="text-white"></span> EGP</p>
                <p class="text-custom-muted mb-1">Deduction (Used): <span id="approveConsumed" class="text-danger"></span> EGP</p>
                <p class="text-custom-muted mb-0">Final Refund: <span id="approveAmount" class="fw-bold text-success"></span> EGP.</p>
                <p class="text-custom-muted mt-3" style="font-size: 0.85rem;">This will automatically deduct funds from the Kitchen and credit the Customer's wallet.</p>
            </div>
            <div class="modal-footer border-0">
                <form id="approveForm" method="POST" class="w-100 d-flex gap-2">
                    @csrf
                    <button type="button" class="btn btn-custom-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success flex-grow-1">Yes, Approve</button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background-color: #1e293b; color: #f8fafc; border-radius: 16px;">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Reject Refund</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label text-custom-muted fw-bold">Reason for Rejection</label>
                        <textarea name="admin_notes" class="form-control" rows="4" style="background: rgba(255,255,255,0.05); color: #fff; border: 1px solid rgba(255,255,255,0.1);" placeholder="Explain why this request is being rejected..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-custom-light flex-grow-1" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger flex-grow-1">Reject Request</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('custom-scripts')
<script>
    function confirmApprove(id, amount, original, consumed) {
        document.getElementById('approveAmount').textContent = amount.toLocaleString();
        document.getElementById('approveOriginal').textContent = original.toLocaleString();
        document.getElementById('approveConsumed').textContent = consumed.toLocaleString();
        document.getElementById('approveForm').action = "/admin/refunds/" + id + "/approve";
        new bootstrap.Modal(document.getElementById('approveModal')).show();
    }

    function openRejectModal(id) {
        document.getElementById('rejectForm').action = "/admin/refunds/" + id + "/reject";
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }

    document.addEventListener("DOMContentLoaded", function() {
        if (window.feather) { feather.replace(); }
    });
</script>

<style>
    .table-dark-custom td {
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }
    .icon-box-dark {
        background-color: rgba(255,255,255,0.05);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .icon-primary { color: #3b82f6; background: rgba(59,130,246,0.1); }
    .icon-success { color: #10b881; background: rgba(16,184,129,0.1); }
    .text-custom-muted { color: #94a3b8; }
</style>
@endpush

@endsection
