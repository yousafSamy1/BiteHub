@extends('admin.admin_dashboard')
@section('admin')
    <div class="page-content">
        <div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
            <h4 class="mb-0"><i data-feather="bell" class="me-2"></i>Subscription Requests</h4>
        </div>

        @if(session('message'))
            <div
                class="alert alert-{{ session('alert-type') === 'success' ? 'success' : (session('alert-type') === 'error' ? 'danger' : 'warning') }} alert-dismissible fade show">
                {{ session('message') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <strong>Pending Approval</strong>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Customer</th>
                                        <th>Item Requested</th>
                                        <th>Schedule</th>
                                        <th>Duration</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($requests as $req)
                                        <tr>
                                            <td>
                                                <strong>{{ $req->customer->user->FullName }}</strong><br>
                                                <small class="text-muted">{{ $req->customer->user->Email }}</small>
                                            </td>
                                            <td>
                                                @foreach($req->menuItems as $item)
                                                    <div class="badge bg-soft-primary text-primary mb-1">{{ $item->ItemName }}</div>
                                                @endforeach
                                                @if($req->Price > 0)
                                                    <div class="mt-2">
                                                        <span class="text-muted small">Customer's Target:</span>
                                                        <span
                                                            class="badge bg-soft-success text-success">{{ number_format($req->Price, 2) }}
                                                            EGP</span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="fw-bold text-info"><i data-feather="coffee"
                                                        class="icon-sm me-1"></i>{{ $req->MealsPerDay }} Meals / Day</div>
                                                @if($req->PreferredTimes)
                                                    <div class="mt-2">
                                                        <div class="text-muted small mb-1">Preferred Slots:</div>
                                                        @foreach($req->PreferredTimes as $time)
                                                            <span class="badge bg-light text-dark border me-1">{{ $time }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                                <small class="text-muted d-block mt-2">
                                                    <i data-feather="calendar" class="icon-sm me-1"></i>
                                                    Starts: {{ \Carbon\Carbon::parse($req->StartDate)->format('d M Y') }}
                                                </small>
                                            </td>
                                            <td>
                                                <div class="fw-bold">{{ $req->DurationDays }} Days</div>
                                                <small class="text-muted">Ends:
                                                    {{ \Carbon\Carbon::parse($req->EndDate)->format('d M Y') }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-2">
                                                    <button class="btn btn-sm btn-success w-100"
                                                        onclick="openApproveModal({{ $req->SubscriptionID }}, '{{ $req->customer->user->FullName }}')">
                                                        Approve & Quote
                                                    </button>
                                                    <form
                                                        action="{{ route('kitchen.subscriptions.reject', $req->SubscriptionID) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100"
                                                            onclick="return confirm('Are you sure you want to reject this request?')">
                                                            Reject
                                                        </button>
                                                    </form>
                                                    @php
                                                        $hasMessages = \App\Models\LiveChat::where('SubscriptionID', $req->SubscriptionID)->exists();
                                                    @endphp
                                                    <a href="{{ route('kitchen.subscriptions.chat', $req->SubscriptionID) }}"
                                                        class="btn btn-sm {{ $hasMessages ? 'btn-primary' : 'btn-outline-primary' }} w-100">
                                                        <i data-feather="message-circle" class="icon-sm me-1"></i>
                                                        Chat {{ $hasMessages ? '💬' : '' }}
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">No pending subscription requests
                                                found.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approval Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="approveForm" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">Approve Subscription Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-4 text-muted">Provide a price quote and optional deposit for <strong
                                id="modalCustomerName" class="text-dark"></strong>.</p>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Total Subscription Price (EGP) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">EGP</span>
                                <input type="number" name="price" class="form-control"
                                    placeholder="Total amount for the full duration" required step="0.01" min="0">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Upfront Deposit Required (Optional)</label>
                            <div class="input-group">
                                <span class="input-group-text">EGP</span>
                                <input type="number" name="deposit_amount" class="form-control"
                                    placeholder="Amount to be paid now to start" step="0.01" min="0">
                            </div>
                            <div class="form-text mt-1" style="font-size: 0.75rem;">If set, the subscription will start only
                                after this deposit is paid.</div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success px-4">Send Approval & Quote</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApproveModal(id, name) {
            document.getElementById('modalCustomerName').innerText = name;
            document.getElementById('approveForm').action = "/admin/kitchen/subscription-requests/" + id + "/approve";
            var myModal = new bootstrap.Modal(document.getElementById('approveModal'));
            myModal.show();
        }
    </script>
@endsection