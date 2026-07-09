@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="text-white fw-bold"><i class="fas fa-hand-holding-usd me-2 text-warning"></i> Withdrawal Requests</h3>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-sm {{ !request('status') ? 'btn-primary' : 'btn-outline-primary' }}" style="border-radius: 10px; padding: 5px 16px;">All</a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'Pending']) }}" class="btn btn-sm {{ request('status') === 'Pending' ? 'btn-warning text-dark' : 'btn-outline-warning' }}" style="border-radius: 10px; padding: 5px 16px;">Pending</a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'Approved']) }}" class="btn btn-sm {{ request('status') === 'Approved' ? 'btn-success' : 'btn-outline-success' }}" style="border-radius: 10px; padding: 5px 16px;">Approved</a>
            <a href="{{ route('admin.withdrawals.index', ['status' => 'Rejected']) }}" class="btn btn-sm {{ request('status') === 'Rejected' ? 'btn-danger' : 'btn-outline-danger' }}" style="border-radius: 10px; padding: 5px 16px;">Rejected</a>
        </div>
    </div>

    <!-- Dashboard Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <a href="{{ route('admin.withdrawals.index') }}" class="text-decoration-none h-100 d-block">
                <div class="dark-card p-4 h-100 d-flex justify-content-between align-items-center {{ !request('status') ? 'active-filter' : '' }}" style="background: linear-gradient(135deg, #111827 0%, #1e1b4b 100%); border: 1px solid rgba(79, 70, 229, 0.2) !important;">
                    <div>
                        <h6 class="text-white-50 small text-uppercase fw-bold mb-1">Platform Earnings</h6>
                        <h3 class="text-white fw-bold mb-0">{{ number_format($ownerBalance, 2) }} <span class="small opacity-50">EGP</span></h3>
                    </div>
                    <div class="rounded-3 bg-primary bg-opacity-10 p-3">
                        <i class="fas fa-university fa-2x text-primary"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.withdrawals.index', ['status' => 'Pending']) }}" class="text-decoration-none h-100 d-block">
                <div class="dark-card p-4 h-100 d-flex justify-content-between align-items-center {{ request('status') === 'Pending' ? 'active-filter-warning' : '' }}" style="background: linear-gradient(135deg, #111827 0%, #312e81 100%); border: 1px solid rgba(99, 102, 241, 0.2) !important;">
                    <div>
                        <h6 class="text-white-50 small text-uppercase fw-bold mb-1">Total Pending</h6>
                        <h3 class="text-white fw-bold mb-0 text-warning">{{ number_format($totalPending, 2) }} <span class="small opacity-50">EGP</span></h3>
                    </div>
                    <div class="rounded-3 bg-warning bg-opacity-10 p-3">
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="{{ route('admin.withdrawals.index') }}" class="text-decoration-none h-100 d-block">
                <div class="dark-card p-4 h-100 d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #111827 0%, #064e3b 100%); border: 1px solid rgba(16, 185, 129, 0.2) !important;">
                    <div>
                        <h6 class="text-white-50 small text-uppercase fw-bold mb-1">Net Flow</h6>
                        <h3 class="text-white fw-bold mb-0 text-success">{{ number_format($ownerBalance - $totalPending, 2) }} <span class="small opacity-50">EGP</span></h3>
                    </div>
                    <div class="rounded-3 bg-success bg-opacity-10 p-3">
                        <i class="fas fa-chart-line fa-2x text-success"></i>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <div class="dark-card card border-0 shadow-sm overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-white">
                <thead class="bg-dark text-white-50 small text-uppercase">
                    <tr>
                        <th class="px-4 py-3">User</th>
                        <th class="py-3">Wallet</th>
                        <th class="py-3">Amount</th>
                        <th class="py-3">Earnings (1%)</th>
                        <th class="py-3">Net to Pay</th>
                        <th class="py-3">Method</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-end px-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $wr)
                    <tr>
                        <td class="px-4">
                            <div class="d-flex align-items-center">
                                @php
                                    $img = $wr->user->Image;
                                    $imgUrl = empty($img) 
                                        ? 'https://ui-avatars.com/api/?name='.urlencode($wr->user->FullName).'&background=random&color=fff' 
                                        : (str_starts_with($img, 'http') ? $img : asset('upload/admin_images/'.$img));
                                @endphp
                                <img src="{{ $imgUrl }}" class="rounded-circle me-3" width="36" style="object-fit: cover; height: 36px; border: 1px solid rgba(255,255,255,0.1);">
                                <div>
                                    <div class="fw-bold">{{ $wr->user->FullName }}</div>
                                    <div class="small text-white-50">{{ $wr->user->Role }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="fw-bold text-info">
                            <i class="fas fa-wallet small me-1 opacity-50"></i>
                            {{ number_format($wr->user->Wallet_balance, 2) }}
                        </td>
                        <td class="fw-bold text-white-50">{{ number_format($wr->Amount, 2) }}</td>
                        <td class="fw-bold text-warning">{{ number_format($wr->Commission, 2) }}</td>
                        <td class="fw-bold text-success">{{ number_format($wr->NetAmount, 2) }} EGP</td>
                        <td>
                            <div class="small fw-bold">{{ $wr->Method }}</div>
                            <div class="x-small text-white-50">
                                @if($wr->Method === 'Bank')
                                    {{ $wr->MethodDetails['bank_name'] }} ({{ substr($wr->MethodDetails['account_number'], -4) }})
                                @else
                                    {{ $wr->MethodDetails['phone'] ?? $wr->MethodDetails['address'] }}
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            @if($wr->Status === 'Pending') <span class="badge bg-warning text-dark" style="border-radius: 8px;">Pending</span>
                            @elseif($wr->Status === 'Approved') <span class="badge bg-success" style="border-radius: 8px;">Approved</span>
                            @else <span class="badge bg-danger" style="border-radius: 8px;">Rejected</span> @endif
                            <div class="x-small text-white-50 mt-1">{{ $wr->created_at->format('M d, H:i') }}</div>
                        </td>
                        <td class="text-end px-4">
                            @if($wr->Status === 'Pending')
                            <div class="d-flex gap-2 justify-content-end">
                                <form action="{{ route('admin.withdrawals.update', $wr->RequestID) }}" method="POST" class="approve-form">
                                    @csrf
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="button" class="btn btn-sm btn-success approve-btn" 
                                        data-amount="{{ number_format($wr->NetAmount, 2) }}" 
                                        data-user="{{ $wr->user->FullName }}"
                                        style="border-radius: 10px; padding: 6px 14px;">Approve</button>
                                </form>

                                <form action="{{ route('admin.withdrawals.update', $wr->RequestID) }}" method="POST" class="reject-form">
                                    @csrf
                                    <input type="hidden" name="status" value="Rejected">
                                    <input type="hidden" name="notes" class="reject-notes">
                                    <button type="button" class="btn btn-sm btn-outline-danger reject-btn" 
                                        data-user="{{ $wr->user->FullName }}"
                                        style="border-radius: 10px; padding: 6px 14px;">Reject</button>
                                </form>
                            </div>
                            @else
                                <span class="text-white-50 small">Processed</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-white-50">No withdrawal requests found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-3">
        {{ $requests->links() }}
    </div>
</div>

<style>
.dark-card { background: #111827; border-radius: 20px; border: 1px solid rgba(255,255,255,0.05) !important; transition: all 0.2s; }
.dark-card:hover { transform: translateY(-5px); border-color: rgba(255,255,255,0.2) !important; }
.active-filter { border-color: #4f46e5 !important; box-shadow: 0 0 15px rgba(79, 70, 229, 0.2); }
.active-filter-warning { border-color: #f59e0b !important; box-shadow: 0 0 15px rgba(245, 158, 11, 0.2); }
.table { color: #f3f4f6; }
.table-hover tbody tr:hover { background: rgba(255, 255, 255, 0.03); }
.table thead th { border-bottom: 1px solid rgba(255,255,255,0.05); }
.table td { border-bottom: 1px solid rgba(255,255,255,0.05); }
.x-small { font-size: 0.7rem; }
</style>

@push('custom-scripts')
<script>
$(document).ready(function() {
    // Use delegation to ensure events are bound even if table reloads
    $(document).on('click', '.approve-btn', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const amount = $(this).data('amount');
        const user = $(this).data('user');

        Swal.fire({
            title: 'Confirm Payment',
            html: `You are confirming that <b class="text-success">${amount} EGP</b> has been sent to <b>${user}</b>.`,
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#30353d',
            confirmButtonText: 'Yes, Mark as Paid',
            background: '#111827',
            color: '#fff',
            customClass: {
                popup: 'rounded-4 border border-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                form.submit();
            }
        });
    });

    $(document).on('click', '.reject-btn', function(e) {
        e.preventDefault();
        const form = $(this).closest('form');
        const user = $(this).data('user');

        Swal.fire({
            title: 'Reject Request',
            text: `Please enter the reason for rejecting ${user}'s request:`,
            input: 'textarea',
            inputPlaceholder: 'Reason for rejection...',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#30353d',
            confirmButtonText: 'Confirm Rejection',
            background: '#111827',
            color: '#fff',
            inputAttributes: {
                style: 'background: #000; color: #fff; border: 1px solid #333; border-radius: 10px;'
            },
            customClass: {
                popup: 'rounded-4 border border-secondary'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value) {
                    form.find('.reject-notes').val(result.value);
                }
                $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection
