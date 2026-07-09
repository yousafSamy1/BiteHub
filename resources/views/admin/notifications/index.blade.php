@extends('admin.admin_dashboard')

@section('admin')
<div class="page-content">
    <nav class="page-breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Notifications</li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-12 grid-margin stretch-card">
            <div class="card bg-dark text-white shadow-lg border-0" style="border-radius: 15px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h6 class="card-title mb-0" style="font-size: 1.25rem; font-weight: 700;">All Notifications</h6>
                        <a href="{{ route('notifications.clear') }}" class="btn btn-outline-primary btn-sm px-3 rounded-pill">Mark all as read</a>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 text-white">
                            <thead>
                                <tr class="text-muted" style="border-bottom: 2px solid rgba(255,255,255,0.05);">
                                    <th class="ps-0 py-3">Type</th>
                                    <th class="py-3">Message</th>
                                    <th class="py-3">Received</th>
                                    <th class="py-3 text-end pe-0">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notifications as $notification)
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); transition: background 0.2s;">
                                    <td class="ps-0 py-3">
                                        <div class="wd-35 ht-35 d-flex align-items-center justify-content-center bg-primary rounded-circle">
                                            @php
                                                $icon = match($notification->Type) {
                                                    'Order' => 'shopping-cart',
                                                    'Promotion' => 'gift',
                                                    'Chat' => 'message-square',
                                                    default => 'bell',
                                                };
                                            @endphp
                                            <i data-feather="{{ $icon }}" class="icon-sm text-white"></i>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold mb-1" style="font-size: 1rem;">{{ $notification->Title }}</span>
                                            <span class="text-muted small">{{ $notification->Message }}</span>
                                        </div>
                                    </td>
                                    <td class="py-3 text-muted">
                                        {{ \Carbon\Carbon::parse($notification->CreatedAt)->format('M d, Y h:i A') }}
                                        <br>
                                        <small>{{ \Carbon\Carbon::parse($notification->CreatedAt)->diffForHumans() }}</small>
                                    </td>
                                    <td class="py-3 text-end pe-0">
                                        @if(!$notification->IsRead)
                                            <span class="badge bg-primary rounded-pill px-2 py-1">New</span>
                                            <a href="{{ route('notifications.read', $notification->NotificationID) }}" class="btn btn-link text-primary p-0 ms-2" title="Mark as read">
                                                <i data-feather="check-circle" class="icon-sm"></i>
                                            </a>
                                        @else
                                            <span class="text-muted small">Read</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5 text-muted">
                                        <i data-feather="bell-off" class="icon-lg mb-3 d-block mx-auto" style="opacity: 0.3;"></i>
                                        No notifications found.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 d-flex justify-content-center">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
