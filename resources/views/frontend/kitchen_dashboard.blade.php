@extends('frontend.layouts.app')
@section('title', 'Kitchen Dashboard')

@section('content')
<div style="padding:calc(var(--nav-h) + 36px) 0 60px">
<div class="container">

    <!-- Header -->
    <div class="glass-card reveal" style="padding:32px 36px;margin-bottom:28px;background:linear-gradient(160deg,rgba(255,107,53,0.07) 0%,rgba(255,167,38,0.03) 100%);border-color:rgba(255,107,53,0.18)">
        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap">
            <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:2rem;color:#fff;flex-shrink:0;box-shadow:0 6px 24px rgba(255,107,53,0.35)">
                {{ strtoupper(substr(auth()->user()->FullName ?? 'K', 0, 1)) }}
            </div>
            <div>
                <h2 style="margin-bottom:4px;font-size:1.6rem;letter-spacing:-0.5px">{{ auth()->user()->FullName }}</h2>
                <p style="color:var(--text-muted);font-size:0.9rem;margin:0">{{ auth()->user()->Email }}</p>
                <span style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;padding:5px 14px;background:rgba(255,107,53,0.12);border:1px solid rgba(255,107,53,0.25);border-radius:20px;font-size:0.78rem;font-weight:700;color:var(--primary)">
                    <i class="fas fa-store"></i> Kitchen Owner
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Stats Row -->
    <div class="grid grid-4" style="gap:16px;margin-bottom:28px">
        @foreach([
            ['value'=>'0','label'=>'Total Orders','icon'=>'fa-receipt','color'=>'var(--primary)'],
            ['value'=>'0.00 EGP','label'=>'Revenue','icon'=>'fa-circle-dollar-to-slot','color'=>'var(--accent)'],
            ['value'=>'0','label'=>'Menu Items','icon'=>'fa-utensils','color'=>'#60a5fa'],
            ['value'=>'4.9','label'=>'Avg Rating','icon'=>'fa-star','color'=>'#fbbf24'],
        ] as $stat)
        <div class="glass-card reveal" style="padding:20px;display:flex;align-items:center;gap:14px">
            <div style="width:46px;height:46px;border-radius:14px;background:{{ $stat['color'] }}18;display:flex;align-items:center;justify-content:center;flex-shrink:0">
                <i class="fas {{ $stat['icon'] }}" style="color:{{ $stat['color'] }};font-size:1.2rem"></i>
            </div>
            <div>
                <div style="font-size:1.4rem;font-weight:800;letter-spacing:-0.5px;color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div style="color:var(--text-muted);font-size:0.78rem">{{ $stat['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Action Cards -->
    <h3 style="margin-bottom:18px;font-size:1.1rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-weight:600">Manage</h3>
    <div class="grid grid-4" style="gap:20px">
        @foreach([
            ['icon'=>'👨‍🍳','label'=>'My Kitchen','sub'=>'Update kitchen profile & settings','color'=>'var(--primary)'],
            ['icon'=>'🍴','label'=>'Menu Items','sub'=>'Add and edit your menu dishes','color'=>'var(--accent)'],
            ['icon'=>'📋','label'=>'Incoming Orders','sub'=>'View and manage orders','color'=>'#60a5fa'],
            ['icon'=>'⭐','label'=>'Reviews','sub'=>'Read customer feedback','color'=>'#fbbf24'],
        ] as $item)
        <div class="glass-card action-card reveal" style="padding:28px 24px;cursor:pointer">
            <div class="action-icon" style="background:{{ $item['color'] }}18;border:1px solid {{ $item['color'] }}28;font-size:1.8rem;width:60px;height:60px">{{ $item['icon'] }}</div>
            <div style="font-weight:700;font-size:1rem;margin-top:4px">{{ $item['label'] }}</div>
            <div style="color:var(--text-muted);font-size:0.82rem;margin-top:4px">{{ $item['sub'] }}</div>
        </div>
        @endforeach
    </div>

</div>
</div>
@endsection
