@extends('frontend.layouts.app')
@section('title', 'Caterer Dashboard')

@section('content')
<div style="padding:calc(var(--nav-h) + 36px) 0 60px">
<div class="container">

    <!-- Header -->
    <div class="glass-card reveal" style="padding:32px 36px;margin-bottom:28px;background:linear-gradient(160deg,rgba(171,71,188,0.07) 0%,rgba(255,167,38,0.03) 100%);border-color:rgba(171,71,188,0.2)">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#ab47bc,#7b1fa2);display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;flex-shrink:0;box-shadow:0 6px 24px rgba(171,71,188,0.35)">
                {{ strtoupper(substr(auth()->user()->FullName ?? 'C', 0, 1)) }}
            </div>
            <div>
                <h2 style="margin-bottom:4px;font-size:1.6rem;letter-spacing:-0.5px">{{ auth()->user()->FullName }}</h2>
                <p style="color:var(--text-muted);font-size:0.9rem;margin:0">{{ auth()->user()->Email }}</p>
                <span style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:5px 14px;background:rgba(171,71,188,0.12);border:1px solid rgba(171,71,188,0.25);border-radius:20px;font-size:0.78rem;font-weight:700;color:#ab47bc">
                    <i class="fas fa-concierge-bell"></i> Caterer
                </span>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <h3 style="margin-bottom:18px;font-size:1.1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600">Manage</h3>
    <div class="grid grid-4" style="gap:20px;margin-bottom:32px">
        @foreach([
            ['icon'=>'🎪','label'=>'My Profile','sub'=>'Update catering profile','color'=>'#ab47bc'],
            ['icon'=>'📅','label'=>'Booking Requests','sub'=>'Incoming catering bookings','color'=>'var(--primary)'],
            ['icon'=>'💰','label'=>'Earnings','sub'=>'Revenue & payments','color'=>'var(--accent)'],
            ['icon'=>'🍽️','label'=>'My Packages','sub'=>'Manage catering packages','color'=>'var(--success)'],
        ] as $item)
        <div class="glass-card action-card reveal" style="padding:28px 24px">
            <div class="action-icon" style="background:{{ $item['color'] }}18;border:1px solid {{ $item['color'] }}28;font-size:1.8rem;width:60px;height:60px">{{ $item['icon'] }}</div>
            <div style="font-weight:700;font-size:1rem;margin-top:4px">{{ $item['label'] }}</div>
            <div style="color:var(--text-muted);font-size:0.82rem;margin-top:4px">{{ $item['sub'] }}</div>
        </div>
        @endforeach
    </div>

    <!-- Empty state -->
    <div class="glass-card reveal" style="padding:48px;text-align:center;border-style:dashed;border-color:rgba(171,71,188,0.3)">
        <div style="font-size:3.5rem;margin-bottom:16px">📋</div>
        <h3>No bookings yet</h3>
        <p style="color:var(--text-muted);font-size:0.9rem;margin-top:8px;max-width:400px;margin-left:auto;margin-right:auto">When customers book your catering services, their requests will appear here for your review.</p>
    </div>

</div>
</div>
@endsection
