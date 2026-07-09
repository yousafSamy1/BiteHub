@extends('frontend.layouts.app')
@section('title', 'Notifications')

@section('content')
<section class="section dashboard-wrap" style="padding-top: calc(var(--nav-h) + 40px); min-height: 80vh;">
    <div class="container">
        <div class="section-header" style="text-align: left; margin-bottom: 30px;">
            <span class="subtitle">History</span>
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <h2 style="margin: 0;">My Notifications</h2>
                <a href="{{ route('notifications.clear') }}" class="btn btn-outline btn-sm">Mark all as read</a>
            </div>
        </div>

        <div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); overflow: hidden;">
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="modern-table" style="margin: 0;">
                        <thead>
                            <tr>
                                <th style="padding-left: 20px;">Type</th>
                                <th>Message</th>
                                <th>Received</th>
                                <th style="text-align: right; padding-right: 20px;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($notifications as $notification)
                            <tr style="{{ $notification->IsRead ? 'opacity: 0.7;' : '' }}">
                                <td style="padding-left: 20px;">
                                    <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(255,107,53,0.1); border: 1px solid rgba(255,107,53,0.2); display: flex; align-items: center; justify-content: center; color: var(--primary);">
                                        @php
                                            $icon = match($notification->Type) {
                                                'Order' => 'shopping-bag',
                                                'Promotion' => 'gift',
                                                'Chat' => 'comment-dots',
                                                default => 'bell',
                                            };
                                        @endphp
                                        <i class="fas fa-{{ $icon }}"></i>
                                    </div>
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">{{ $notification->Title }}</div>
                                    <div style="font-size: 0.85rem; color: var(--text-muted); line-height: 1.5;">{{ $notification->Message }}</div>
                                </td>
                                <td>
                                    <div style="font-size: 0.85rem; color: var(--text-secondary);">{{ \Carbon\Carbon::parse($notification->CreatedAt)->format('M d, Y h:i A') }}</div>
                                    <small style="font-size: 0.75rem; color: var(--text-muted);">{{ \Carbon\Carbon::parse($notification->CreatedAt)->diffForHumans() }}</small>
                                </td>
                                <td style="text-align: right; padding-right: 20px;">
                                    @if(!$notification->IsRead)
                                        <span class="badge" style="background: var(--primary); color: #fff; font-size: 0.7rem; padding: 4px 10px; border-radius: 20px;">New</span>
                                        <a href="{{ route('notifications.read', $notification->NotificationID) }}" style="color: var(--primary); margin-left: 10px;" title="Mark as read">
                                            <i class="fas fa-check-circle"></i>
                                        </a>
                                    @else
                                        <span style="font-size: 0.8rem; color: var(--text-muted);">Read</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 60px 20px;">
                                    <div style="opacity: 0.3; margin-bottom: 20px;">
                                        <i class="fas fa-bell-slash" style="font-size: 3rem;"></i>
                                    </div>
                                    <h4 style="color: var(--text-muted); font-weight: 500;">No notifications found</h4>
                                    <p style="color: var(--text-muted); font-size: 0.9rem;">We'll let you know when something important happens.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: center;">
            {{ $notifications->links() }}
        </div>
    </div>
</section>

<style>
    /* Styling for standard Laravel pagination to match theme */
    .pagination {
        display: flex;
        gap: 8px;
        list-style: none;
        padding: 0;
    }
    .page-item .page-link {
        background: var(--bg-card2);
        border: 1px solid var(--border-color);
        color: var(--text-primary);
        padding: 8px 16px;
        border-radius: 10px;
        text-decoration: none;
        transition: var(--transition-fast);
    }
    .page-item.active .page-link {
        background: var(--primary);
        border-color: var(--primary);
        color: #fff;
    }
    .page-item.disabled .page-link {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .page-item:not(.active):not(.disabled) .page-link:hover {
        background: var(--bg-card-hover);
        border-color: var(--primary);
    }
</style>
@endsection
