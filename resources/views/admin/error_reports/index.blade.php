@extends('admin.admin_dashboard')
@section('admin')

<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Error Reports</li>
        </ol>
    </nav>

    {{-- Stats Row --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card dark-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="icon-box-dark icon-danger" style="width:48px;height:48px;font-size:1.2rem;">
                        <i data-feather="alert-triangle"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold" style="color:#f8fafc;">{{ $reports->where('Status','Pending')->count() }}</h4>
                        <small class="text-custom-muted">Pending Reports</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dark-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="icon-box-dark icon-success" style="width:48px;height:48px;font-size:1.2rem;">
                        <i data-feather="check-circle"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold" style="color:#f8fafc;">{{ $reports->where('Status','Fixed')->count() }}</h4>
                        <small class="text-custom-muted">Fixed</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dark-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="icon-box-dark icon-warning" style="width:48px;height:48px;font-size:1.2rem;">
                        <i data-feather="eye-off"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold" style="color:#f8fafc;">{{ $reports->where('Status','Ignored')->count() }}</h4>
                        <small class="text-custom-muted">Ignored</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card dark-card">
                <div class="card-body d-flex align-items-center gap-3 py-3">
                    <div class="icon-box-dark icon-primary" style="width:48px;height:48px;font-size:1.2rem;">
                        <i data-feather="list"></i>
                    </div>
                    <div>
                        <h4 class="mb-0 fw-bold" style="color:#f8fafc;">{{ $reports->count() }}</h4>
                        <small class="text-custom-muted">Total Reports</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card dark-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title mb-0" style="font-size: 1.2rem; font-weight: 800; color: #f8fafc;">
                            <i data-feather="alert-triangle" class="me-2 text-danger"></i> Error Reports from Users
                        </h6>
                    </div>
                    
                    <div class="table-responsive">
                        <table id="dataTableExample" class="table table-dark-custom">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Reporter</th>
                                    <th>Error Code</th>
                                    <th>URL</th>
                                    <th>Browser</th>
                                    <th>Reported At</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reports as $report)
                                <tr>
                                    <td>{{ $report->id }}</td>
                                    <td>
                                        @if($report->user)
                                        <div class="d-flex align-items-center">
                                            <div class="icon-box-dark icon-primary me-2" style="width:32px;height:32px;font-size:0.8rem;font-weight:bold;">
                                                {{ substr($report->user->FullName, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $report->user->FullName }}</div>
                                                <small class="text-custom-muted">{{ $report->user->Email }}</small>
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-custom-muted"><i data-feather="user-x" style="width:14px;height:14px;"></i> Guest</span>
                                        @endif
                                    </td>
                                    <td>
                                        @php
                                            $codeColor = match($report->ErrorCode) {
                                                '404' => 'warning',
                                                '403' => 'info',
                                                '500' => 'danger',
                                                '503' => 'danger',
                                                '419' => 'primary',
                                                '429' => 'warning',
                                                default => 'secondary',
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $codeColor }} bg-opacity-10 text-{{ $codeColor }} border-{{ $codeColor }} border" style="font-size:0.85rem;font-weight:700;">
                                            {{ $report->ErrorCode }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ $report->URL }}" target="_blank" class="text-info text-decoration-none" title="{{ $report->URL }}" style="max-width:200px;display:inline-block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                            {{ $report->URL }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $ua = $report->UserAgent ?? '';
                                            $browser = 'Unknown';
                                            if (str_contains($ua, 'Chrome')) $browser = 'Chrome';
                                            elseif (str_contains($ua, 'Firefox')) $browser = 'Firefox';
                                            elseif (str_contains($ua, 'Safari')) $browser = 'Safari';
                                            elseif (str_contains($ua, 'Edge')) $browser = 'Edge';
                                        @endphp
                                        <small class="text-custom-muted">{{ $browser }}</small>
                                    </td>
                                    <td>
                                        <small class="text-custom-muted">{{ $report->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $statusBadge = match($report->Status) {
                                                'Pending' => 'badge-soft-warning',
                                                'Fixed' => 'badge-soft-success',
                                                'Ignored' => 'badge-soft-danger',
                                                default => 'badge-soft-info',
                                            };
                                        @endphp
                                        <span class="{{ $statusBadge }}">{{ $report->Status }}</span>
                                    </td>
                                    <td>
                                        @if($report->Status === 'Pending')
                                        <div class="d-flex gap-1">
                                            <form method="POST" action="{{ route('admin.error-reports.update', $report->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Fixed">
                                                <button type="submit" class="btn btn-success btn-icon btn-sm" title="Mark as Fixed">
                                                    <i data-feather="check"></i>
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.error-reports.update', $report->id) }}" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="status" value="Ignored">
                                                <button type="submit" class="btn btn-secondary btn-icon btn-sm" title="Ignore">
                                                    <i data-feather="eye-off"></i>
                                                </button>
                                            </form>
                                        </div>
                                        @else
                                        <small class="text-custom-muted">Processed</small>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center text-custom-muted py-5">
                                        <i data-feather="check-circle" style="width:40px;height:40px;opacity:.3;" class="mb-2 d-block mx-auto"></i>
                                        No error reports yet. Your platform is running smoothly! 🎉
                                    </td>
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

@push('custom-scripts')
<script>
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
    .icon-success { color: #10b981; background: rgba(16,184,129,0.1); }
    .icon-danger { color: #ef4444; background: rgba(239,68,68,0.1); }
    .icon-warning { color: #f59e0b; background: rgba(245,158,11,0.1); }
    .text-custom-muted { color: #94a3b8; }
</style>
@endpush

@endsection
