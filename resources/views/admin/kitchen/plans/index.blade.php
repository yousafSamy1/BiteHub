@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">

    <div class="d-flex justify-content-between align-items-center flex-wrap mb-4">
        <div>
            <h3 class="fw-bold mb-1" style="color: #f8fafc;">Manage Subscription Plans</h3>
            <p class="text-muted">Direct control over your kitchen's recurring offerings.</p>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('kitchen.plans.create') }}" class="btn btn-primary d-flex align-items-center gap-2 px-4 py-2 rounded-pill shadow">
                <i data-feather="plus-circle" style="width:18px"></i> Create New Plan
            </a>
        </div>
    </div>

    @if(session('message'))
    <div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : 'danger' }} alert-dismissible fade show border-0 shadow-sm" role="alert">
        <i data-feather="{{ session('alert-type') === 'success' ? 'check-circle' : 'alert-circle' }}" class="me-2" style="width:18px"></i>
        {{ session('message') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <!-- Stats Summary -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card bg-dark border-0 shadow-sm p-4 text-center" style="background: rgba(30, 41, 59, 1);">
                <div class="text-muted mb-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Total Plans</div>
                <h2 class="fw-black mb-0 text-white">{{ $plans->count() }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-0 shadow-sm p-4 text-center" style="background: rgba(30, 41, 59, 1);">
                <div class="text-success mb-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Active</div>
                <h2 class="fw-black mb-0 text-white">{{ $plans->where('Status', 'Active')->count() }}</h2>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-dark border-0 shadow-sm p-4 text-center" style="background: rgba(30, 41, 59, 1);">
                <div class="text-danger mb-2 text-uppercase fw-bold" style="font-size: 0.75rem; letter-spacing: 1px;">Inactive</div>
                <h2 class="fw-black mb-0 text-white">{{ $plans->where('Status', 'Inactive')->count() }}</h2>
            </div>
        </div>
    </div>

    <!-- Plans Table -->
    <div class="card bg-dark border-0 shadow-sm" style="background: #0f172a; border-radius: 16px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle" style="color: #cbd5e1;">
                <thead style="background: rgba(255,255,255,0.02);">
                    <tr>
                        <th class="ps-4 py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Plan Details</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Period</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Frequency</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Price</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Subscribers</th>
                        <th class="py-4 text-uppercase fw-bold" style="font-size: 0.75rem; color: #94a3b8;">Status</th>
                        <th class="pe-4 py-4 text-uppercase fw-bold text-end" style="font-size: 0.75rem; color: #94a3b8;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($plans as $plan)
                    @php 
                        $subCount = \App\Models\Subscription::where('KitchenPlanID', $plan->KitchenPlanID)->where('Status', 'Active')->count();
                    @endphp
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td class="ps-4 py-4">
                            <div class="fw-bold fs-5 text-white">{{ $plan->Title }}</div>
                            <div class="text-muted text-truncate" style="max-width: 300px; font-size: 0.85rem;">{{ $plan->Description ?? 'No description provided.' }}</div>
                        </td>
                        <td class="py-4">
                            <span class="badge bg-soft-info text-info p-2 px-3" style="background: rgba(6, 182, 212, 0.1); border: 1px solid rgba(6, 182, 212, 0.2);">{{ $plan->PlanTime }}</span>
                        </td>
                        <td class="py-4">
                            <span class="badge bg-soft-warning text-warning p-2 px-3" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2);">{{ $plan->MealsPerDay }} Meals / Day</span>
                        </td>
                        <td class="py-4">
                            <div class="fw-bold text-success fs-5">{{ number_format($plan->Price, 2) }} <small class="fw-normal fs-6">EGP</small></div>
                        </td>
                        <td class="py-4">
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold text-white">{{ $subCount }}</span>
                                <span class="text-muted small">Active</span>
                            </div>
                        </td>
                        <td class="py-4">
                            @if($plan->Status === 'Active')
                                <span class="badge bg-success rounded-pill px-3">Active</span>
                            @else
                                <span class="badge bg-danger rounded-pill px-3">Inactive</span>
                            @endif
                        </td>
                        <td class="pe-4 py-4 text-end">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('kitchen.plans.subscribers', $plan->KitchenPlanID) }}" class="btn btn-outline-primary btn-sm btn-icon rounded-circle" title="View Subscribers">
                                    <i data-feather="users"></i>
                                </a>
                                <a href="{{ route('kitchen.plans.edit', $plan->KitchenPlanID) }}" class="btn btn-outline-info btn-sm btn-icon rounded-circle" title="Edit">
                                    <i data-feather="edit-2"></i>
                                </a>
                                <form action="{{ route('kitchen.plans.delete', $plan->KitchenPlanID) }}" method="POST" onsubmit="return confirm('Delete this plan forever? Users with this plan will still be active but no new users can join.')" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger btn-sm btn-icon rounded-circle" title="Delete">
                                        <i data-feather="trash-2"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-5 text-center">
                            <div class="text-muted">
                                <i data-feather="box" style="width: 48px; height: 48px; opacity: 0.2;" class="mb-3"></i>
                                <h4>No Plans Yet</h4>
                                <p>Start by creating your first subscription plan.</p>
                                <a href="{{ route('kitchen.plans.create') }}" class="btn btn-primary mt-3 px-4">Get Started</a>
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
