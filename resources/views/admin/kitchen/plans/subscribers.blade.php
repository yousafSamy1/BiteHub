@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">

    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('kitchen.plans') }}">My Plans</a></li>
            <li class="breadcrumb-item active" aria-current="page">Plan Subscribers</li>
        </ol>
    </nav>

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: #f8fafc;">Subscribers: {{ $plan->Title }}</h3>
            <p class="text-muted">Manage customers currently enrolled in this specific plan.</p>
        </div>
    </div>

    @if(session('message'))
    <div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i data-feather="{{ session('alert-type') === 'success' ? 'check-circle' : 'alert-circle' }}" class="me-2" style="width:18px"></i>
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="card bg-dark border-0 shadow-sm" style="background: #0f172a; border-radius: 16px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="color: #cbd5e1;">
                <thead style="background: rgba(255,255,255,0.02);">
                    <tr>
                        <th class="ps-4 py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Customer</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Duration</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Selected Items</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Status</th>
                        <th class="pe-4 py-4 text-uppercase fw-bold text-end" style="font-size: 0.75rem; color: #94a3b8;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($subscribers as $sub)
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="ps-4 py-4">
                            <div class="d-flex align-items-center gap-3">
                                <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #3b82f6, #8b5cf6); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 1.1rem;">
                                    {{ substr($sub->customer->user->FullName, 0, 1) }}
                                </div>
                                <div>
                                    <div class="fw-bold text-white">{{ $sub->customer->user->FullName }}</div>
                                    <div class="text-muted small">{{ $sub->customer->user->Email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="py-4">
                            <div class="text-white small">
                                <div><span class="text-muted">Start:</span> {{ \Carbon\Carbon::parse($sub->StartDate)->format('M d, Y') }}</div>
                                <div><span class="text-muted">End:</span> {{ \Carbon\Carbon::parse($sub->EndDate)->format('M d, Y') }}</div>
                            </div>
                        </td>
                        <td class="py-4">
                            @foreach($sub->menuItems as $item)
                                <span class="badge bg-soft-light text-light border border-secondary mb-1" style="font-size: 0.7rem;">{{ $item->ItemName }}</span>
                            @endforeach
                            @if($sub->menuItems->isEmpty())
                                <span class="text-muted italic small">No items picked</span>
                            @endif
                        </td>
                        <td class="py-4">
                            @if($sub->Status === 'Active')
                                <span class="badge bg-success rounded-pill px-3">Active</span>
                            @elseif($sub->Status === 'Cancelled')
                                <span class="badge bg-danger rounded-pill px-3">Cancelled</span>
                            @else
                                <span class="badge bg-warning rounded-pill px-3">{{ $sub->Status }}</span>
                            @endif
                        </td>
                        <td class="pe-4 py-4 text-end">
                            @if($sub->Status === 'Active')
                            <form action="{{ route('kitchen.subscriptions.cancel', $sub->SubscriptionID) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this user\'s subscription?')">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm px-3 rounded-pill">
                                    <i data-feather="x-circle" class="me-1" style="width:14px"></i> Cancel
                                </button>
                            </form>
                            @else
                                <span class="text-muted small">No actions</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-5 text-center">
                            <div class="text-muted">
                                <i data-feather="users" style="width: 48px; height: 48px; opacity: 0.2;" class="mb-3"></i>
                                <h4>No Subscribers Yet</h4>
                                <p>This plan doesn't have any active subscribers at the moment.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
