@extends('frontend.layouts.app')
@section('title', 'My Profile')

@php
// Build the correct image URL using the same pattern as the rest of the app
$userImgUrl = ($user->Image && file_exists(public_path('upload/admin_images/'.$user->Image)))
    ? url('upload/admin_images/'.$user->Image)
    : null;
@endphp
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.css" />
<style>
.profile-wrap { display:grid; grid-template-columns:280px 1fr; gap:28px; align-items:start; padding: calc(var(--nav-h) + 30px) 0 80px; width: 100%; max-width: 100%; }
@media(max-width:991px){ 
    .profile-wrap { display: flex !important; flex-direction: column !important; gap: 24px !important; padding: calc(var(--nav-h) + 20px) 0 60px !important; } 
    .profile-sidebar { position:static !important; width: 100% !important; max-width: 100% !important; } 
    .profile-avatar-wrap { padding: 24px 16px !important; width: 100% !important; }
    .profile-content { width: 100% !important; max-width: 100% !important; overflow: hidden !important; }
}

.profile-sidebar { position:sticky; top:90px; transition: all 0.3s ease; }
.profile-avatar-wrap {
    display:flex; flex-direction:column; align-items:center; text-align:center; padding:32px 24px;
    background:linear-gradient(135deg,rgba(255,107,53,0.08),rgba(255,167,38,0.04));
    border:1px solid rgba(255,107,53,0.2); border-radius:var(--radius-lg); margin-bottom:16px;
    box-sizing: border-box;
}
.avatar-circle {
    width:100px; height:100px; border-radius:50%;
    background:linear-gradient(135deg,var(--primary),var(--accent));
    display:flex; align-items:center; justify-content:center;
    font-size:2.8rem; font-weight:900; color:#fff;
    box-shadow:0 8px 28px rgba(255,107,53,0.4); border:4px solid var(--bg-card);
    margin-bottom:14px; overflow:hidden; flex-shrink: 0;
}
.avatar-circle img { width:100%; height:100%; object-fit:cover; }
.sidebar-stat { display:flex; justify-content:space-between; align-items:center; padding:12px 0; border-top:1px solid var(--border-color); font-size:0.88rem; gap:10px; width: 100%; }
.sidebar-stat .lbl { color:var(--text-muted); display:flex; align-items:center; gap:8px; white-space:nowrap; }
.sidebar-stat .val { font-weight:700; color:var(--text-primary); text-align:right; word-break:break-all; }

.tab-nav { display:flex; gap:8px; margin-bottom:24px; border-bottom:1px solid var(--border-color); padding-bottom:0; overflow-x:auto; -webkit-overflow-scrolling:touch; scrollbar-width:none; width: 100%; }
.tab-nav::-webkit-scrollbar { display:none; }
.tab-btn {
    padding:10px 20px; border:none; background:none; cursor:pointer;
    color:var(--text-muted); font-size:0.9rem; font-weight:600;
    border-bottom:2px solid transparent; margin-bottom:-1px;
    transition:all 0.2s; font-family:var(--font-body); white-space:nowrap;
}
.tab-btn:hover { color:var(--text-primary); }
.tab-btn.active { color:var(--primary); border-bottom-color:var(--primary); }
.tab-pane { display:none; width: 100%; }
.tab-pane.active { display:block; }

.avatar-upload-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 16px; border-radius:20px; font-size:0.8rem; cursor:pointer;
    background:rgba(255,107,53,0.12); color:var(--primary); border:1px solid rgba(255,107,53,0.3);
    transition:var(--transition-fast); margin-top:8px;
}
.avatar-upload-btn:hover { background:rgba(255,107,53,0.2); }
.address-card { background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius); padding: 20px; margin-bottom: 16px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; transition: all 0.2s ease; width: 100%; box-sizing: border-box; }
.address-card p { word-break: break-word; overflow-wrap: anywhere; }
@media(max-width:768px){ 
    .address-card { flex-direction:column; align-items:stretch; padding: 16px !important; } 
    .address-card > div:last-child { flex-direction:row !important; flex-wrap: wrap !important; gap: 8px !important; margin-top: 12px; } 
    .address-card .btn { flex: 1 !important; min-width: 100px !important; justify-content: center !important; font-size: 0.8rem !important; padding: 8px 12px !important; }
    .tab-nav { padding: 0 4px; margin-left: -4px; margin-right: -4px; }
    .tab-btn { padding: 8px 14px !important; font-size: 0.85rem !important; }
    .sidebar-stat { flex-wrap: wrap !important; }
    .sidebar-stat .val { text-align: left !important; width: 100% !important; margin-top: 2px !important; }
}
@media(max-width:480px){
    .glass-card { padding: 16px !important; }
}
.address-card.primary { border-color: var(--primary); background: rgba(255,107,53,0.03); box-shadow: 0 4px 12px rgba(255,107,53,0.1); }
#map { height: 400px; width: 100%; border-radius: 12px; margin-bottom: 20px; border: 1px solid var(--border-color); z-index: 10; background: var(--bg-card2); max-width: 100%; }
</style>

<div class="container">
<div class="profile-wrap">

    {{-- ── LEFT SIDEBAR ── --}}
    <div class="profile-sidebar">

        {{-- Avatar Card --}}
        <div class="profile-avatar-wrap glass-card">
            <div class="avatar-circle" id="avatarPreviewWrap">
                @if($userImgUrl)
                    <img src="{{ $userImgUrl }}" alt="{{ $user->FullName }}" id="avatarPreviewImg">
                @else
                    <span id="avatarLetter">{{ strtoupper(substr($user->FullName ?? 'U', 0, 1)) }}</span>
                @endif
            </div>

            <div style="font-weight:800;font-size:1.15rem;margin-bottom:4px;text-align:center">{{ $user->FullName }}</div>
            <div style="font-size:0.82rem;color:var(--text-muted);margin-bottom:4px;text-align:center">{{ $user->Email }}</div>
            <div style="display:inline-flex;align-items:center;justify-content:center;gap:6px;padding:3px 12px;background:rgba(255,107,53,0.1);border-radius:20px;font-size:0.75rem;font-weight:700;color:var(--primary);margin-bottom:12px">
                <i class="fas fa-user-circle"></i> {{ $user->Role }} (ID: {{ $customer->CustomerID ?? 'N/A' }})
            </div>

            {{-- Mini Stats --}}
            <div style="width:100%">
                <div class="sidebar-stat">
                    <span class="lbl"><i class="fas fa-wallet"></i> Wallet</span>
                    <span class="val" style="color:#4ade80">{{ number_format($walletBalance, 2) }} EGP</span>
                </div>
                <div class="sidebar-stat">
                    <span class="lbl"><i class="fas fa-star"></i> BitePoints</span>
                    <span class="val" style="color:#f59e0b">{{ number_format($loyaltyPoints) }} pts</span>
                </div>
                <div class="sidebar-stat">
                    <span class="lbl"><i class="fas fa-receipt"></i> Total Orders</span>
                    <span class="val">{{ $totalOrders }}</span>
                </div>
                <div class="sidebar-stat">
                    <span class="lbl"><i class="fas fa-calendar-alt"></i> Active Plans</span>
                    <span class="val">{{ $activePlansCount ?? 0 }}</span>
                </div>
            </div>
        </div>

        {{-- Nav Links --}}
        <div class="glass-card" style="padding:12px">
            <a href="{{ route('dashboard.customer') }}" class="quick-action" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;color:var(--text-secondary);text-decoration:none;transition:var(--transition-fast);font-size:0.9rem" onmouseover="this.style.background='rgba(255,107,53,0.07)'" onmouseout="this.style.background='none'">
                <i class="fas fa-gauge-high" style="width:20px;text-align:center;color:var(--primary)"></i> Dashboard
            </a>
            <a href="{{ route('frontend.subscriptions') }}" class="quick-action" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;color:var(--text-secondary);text-decoration:none;transition:var(--transition-fast);font-size:0.9rem" onmouseover="this.style.background='rgba(255,107,53,0.07)'" onmouseout="this.style.background='none'">
                <i class="fas fa-calendar-alt" style="width:20px;text-align:center;color:#60a5fa"></i> Meal Plans
            </a>
            <a href="{{ route('frontend.cart') }}" class="quick-action" style="display:flex;align-items:center;gap:12px;padding:12px;border-radius:12px;color:var(--text-secondary);text-decoration:none;transition:var(--transition-fast);font-size:0.9rem" onmouseover="this.style.background='rgba(255,107,53,0.07)'" onmouseout="this.style.background='none'">
                <i class="fas fa-shopping-bag" style="width:20px;text-align:center;color:#f472b6"></i> My Cart
            </a>
        </div>

    </div>

    {{-- ── RIGHT CONTENT ── --}}
    <div class="profile-content">

        @if(session('message'))
        <div class="info-box {{ session('alert-type') === 'success' ? 'info-success' : 'info-error' }}" style="margin-bottom:20px">
            <i class="fas fa-{{ session('alert-type') === 'success' ? 'check-circle' : 'exclamation-circle' }}"></i>
            <span>{{ session('message') }}</span>
        </div>
        @endif

        {{-- Tabs --}}
        <div class="tab-nav">
            <button class="tab-btn active" onclick="showTab('info', this)"><i class="fas fa-user"></i> Personal Info</button>
            <button class="tab-btn" onclick="showTab('photo', this)"><i class="fas fa-camera"></i> Profile Photo</button>
            <button class="tab-btn" onclick="showTab('addresses', this)"><i class="fas fa-map-marked-alt"></i> Addresses</button>
            <button class="tab-btn" onclick="showTab('password', this)"><i class="fas fa-lock"></i> Password</button>
            <button class="tab-btn" onclick="showTab('plans', this)"><i class="fas fa-calendar-alt"></i> My Plans</button>
            <button class="tab-btn" onclick="showTab('chats', this)"><i class="fas fa-comments"></i> My Conversations @if(count($activeSessions ?? []) > 0) <span class="badge bg-danger" style="font-size:0.6rem; border-radius:50%; padding:2px 5px; vertical-align:top; margin-left:2px;">{{ count($activeSessions) }}</span> @endif</button>
        </div>

        {{-- ─── TAB: Personal Info ─── --}}
        <div class="tab-pane active" id="tab-info">
            <div class="glass-card">
                <h3 style="margin:0 0 24px;font-size:1.05rem;display:flex;align-items:center;gap:10px">
                    <span style="width:34px;height:34px;background:linear-gradient(135deg,var(--primary),var(--accent));border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-user" style="color:#fff"></i></span>
                    Personal Information
                </h3>
                <form method="POST" action="{{ route('frontend.profile.update') }}">
                    @csrf
                    <div class="grid grid-2" style="gap:20px;margin-bottom:20px">
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->FullName) }}" required>
                        </div>
                        <div class="form-group" style="margin:0">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->Email) }}" readonly style="opacity:0.7; cursor:not-allowed;" title="Email address cannot be changed.">
                        </div>
                    </div>
                    <div class="form-group" style="margin:0 0 24px">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->PhoneNumber ?? '') }}" placeholder="e.g. 01XXXXXXXXX">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </form>
                
                <hr style="border-color:var(--border-color); margin:30px 0;">
                
                <div style="background:rgba(248,113,113,0.05); border:1px solid rgba(248,113,113,0.2); border-radius:12px; padding:24px;">
                    <h4 style="color:#f87171; margin-top:0; font-size:1.05rem;"><i class="fas fa-exclamation-triangle"></i> Danger Zone</h4>
                    <p style="color:var(--text-muted); font-size:0.9rem; margin-bottom:16px;">Once you delete your account, there is no going back. Please be certain.</p>
                    
                    <form action="{{ route('frontend.profile.delete') }}" method="POST" onsubmit="event.preventDefault(); const f=this; window.biteConfirm('Are you absolutely sure you want to permanently delete your account?', function(res){ if(res) f.submit(); });">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="background:rgba(248,113,113,0.1); color:#f87171; border:1px solid rgba(248,113,113,0.3);">
                            <i class="fas fa-trash-alt"></i> Delete Account
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ─── TAB: Profile Photo ─── --}}
        <div class="tab-pane" id="tab-photo">
            <div class="glass-card">
                <h3 style="margin:0 0 24px;font-size:1.05rem;display:flex;align-items:center;gap:10px">
                    <span style="width:34px;height:34px;background:linear-gradient(135deg,#60a5fa,#a78bfa);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-camera" style="color:#fff"></i></span>
                    Update Profile Photo
                </h3>
                <form method="POST" action="{{ route('frontend.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="update_photo" value="1">

                    <div style="display:flex;align-items:center;gap:28px;margin-bottom:28px;flex-wrap:wrap">
                        <div style="width:110px;height:110px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--accent));display:flex;align-items:center;justify-content:center;font-size:2.5rem;font-weight:900;color:#fff;overflow:hidden;border:4px solid var(--bg-card2);flex-shrink:0">
                            @if($userImgUrl)
                            <img id="photoPreview" src="{{ $userImgUrl }}" alt="Preview"
                                 style="width:100%;height:100%;object-fit:cover">
                            @else
                            <img id="photoPreview" src="" alt="Preview" style="display:none;width:100%;height:100%;object-fit:cover">
                            <span id="photoLetter">{{ strtoupper(substr($user->FullName ?? 'U', 0, 1)) }}</span>
                            @endif
                        </div>
                        <div>
                            <label class="avatar-upload-btn" for="photoInput">
                                <i class="fas fa-upload"></i> Choose New Photo
                            </label>
                            <input type="file" id="photoInput" name="photo" accept="image/*" style="display:none" onchange="previewPhoto(event)">
                            <p style="color:var(--text-muted);font-size:0.8rem;margin-top:10px">JPG, PNG or WebP. Max 2MB.<br>Square images work best.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check"></i> Update Photo
                    </button>
                </form>
            </div>
        </div>

                {{-- ─── TAB: Addresses ─── --}}
        <div class="tab-pane" id="tab-addresses">
            <div class="glass-card">
                <h3 style="margin:0 0 24px;font-size:1.05rem;display:flex;align-items:center;gap:10px">
                    <span style="width:34px;height:34px;background:linear-gradient(135deg,#10b981,#34d399);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-map-marked-alt" style="color:#fff"></i></span>
                    My saved addresses
                </h3>
                
                @if($addresses->isEmpty())
                    <p style="color:var(--text-muted);">You don't have any saved addresses yet.</p>
                @else
                    @foreach($addresses as $addr)
                    <div class="address-card {{ $addr->IsPrimary ? 'primary' : '' }}">
                        <div>
                            <div style="display:flex; align-items:center; gap:10px; margin-bottom: 6px;">
                                <h5 style="margin:0; font-size:1.1rem; color: var(--text-primary);">
                                    <i class="fas fa-hashtag" style="color:var(--primary); font-size:0.9rem;"></i> Address
                                </h5>
                                @if($addr->IsPrimary)
                                    <span style="background: var(--primary); color: #fff; font-size: 0.7rem; padding: 2px 8px; border-radius: 12px; font-weight: bold; white-space: nowrap;">PRIMARY</span>
                                @endif
                            </div>
                            <p style="color: var(--text-secondary); margin: 0; font-size: 0.95rem; word-break: break-word; overflow-wrap: anywhere;">{{ $addr->Address }}</p>
                        </div>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            @if(!$addr->IsPrimary)
                                <form action="{{ route('frontend.addresses.primary', $addr->AddressID) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-primary btn-sm"><i class="fas fa-check"></i> Set Primary</button>
                                </form>
                            @endif
                            <form action="{{ route('frontend.addresses.delete', $addr->AddressID) }}" method="POST" onsubmit="event.preventDefault(); const f=this; window.biteConfirm('Are you sure you want to delete this address?', function(res){ if(res) f.submit(); });">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm" style="border:1px solid #f87171; color:#f87171; background:transparent;"><i class="fas fa-trash"></i> Delete</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                @endif
                
                <h4 style="margin: 30px 0 16px;">Add New Address</h4>
                <form action="{{ route('frontend.addresses.store') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label class="form-label">Search and Pin Location on Map <span style="color:var(--primary)">*</span></label>
                        <div id="map"></div>
                        <input type="hidden" name="latitude" id="latInput" required>
                        <input type="hidden" name="longitude" id="lngInput" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Full Address Description</label>
                        <input type="text" id="addressInput" name="address" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Save Address</button>
                </form>
            </div>
        </div>

        {{-- ─── TAB: Password ─── --}}
        <div class="tab-pane" id="tab-password">
            <div class="glass-card">
                <h3 style="margin:0 0 24px;font-size:1.05rem;display:flex;align-items:center;gap:10px">
                    <span style="width:34px;height:34px;background:linear-gradient(135deg,#f59e0b,#ef4444);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-lock" style="color:#fff"></i></span>
                    Change Password
                </h3>
                <form method="POST" action="{{ route('frontend.profile.password') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required placeholder="Enter your current password">
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="Minimum 8 characters" minlength="8"
                               oninput="checkStrength(this.value)">
                        <div style="margin-top:8px;height:4px;border-radius:4px;background:var(--border-color);overflow:hidden">
                            <div id="strengthBar" style="height:100%;width:0;border-radius:4px;transition:all 0.3s"></div>
                        </div>
                        <div id="strengthLabel" style="font-size:0.75rem;color:var(--text-muted);margin-top:4px"></div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" required placeholder="Repeat new password">
                    </div>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-shield-alt"></i> Update Password
                    </button>
                </form>
            </div>
        </div>

        {{-- ─── TAB: My Plans (Subscriptions) ─── --}}
        <div class="tab-pane" id="tab-plans">

            @if($mySubscriptions->isEmpty())
            <div class="glass-card" style="text-align:center;padding:60px 20px">
                <div style="font-size:4rem;opacity:0.3;margin-bottom:16px">📅</div>
                <h3 style="font-size:1.1rem;margin-bottom:8px">No Plans</h3>
                <p style="color:var(--text-muted);margin-bottom:24px">Subscribe to a meal plan and enjoy daily fresh food.</p>
                <a href="{{ route('frontend.subscriptions') }}" class="btn btn-primary">Browse Plans</a>
            </div>
            @else
                @foreach($mySubscriptions as $sub)
                @php
                    $planColors = ['Daily'=>'#60a5fa','Weekly'=>'#f59e0b','Monthly'=>'#4ade80'];
                    $planColor  = $planColors[$sub->PlanTime] ?? 'var(--primary)';
                    $daysLeft   = max(0, \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($sub->EndDate), false));
                    $pct        = $sub->StartDate && $sub->EndDate
                        ? min(100, round(\Carbon\Carbon::parse($sub->StartDate)->diffInDays(now()) / max(1, \Carbon\Carbon::parse($sub->StartDate)->diffInDays(\Carbon\Carbon::parse($sub->EndDate))) * 100))
                        : 0;
                @endphp
                <div class="glass-card" style="padding:28px;margin-bottom:20px;border:1px solid {{ $planColor }}44; {{ $sub->Status !== 'Active' ? 'opacity:0.6; filter:grayscale(80%);' : '' }}">
                    {{-- Header --}}
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;margin-bottom:20px">
                        <div style="display:flex;align-items:center;gap:16px">
                            <div style="width:56px;height:56px;border-radius:14px;background:{{ $planColor }}22;display:flex;align-items:center;justify-content:center;font-size:1.6rem;flex-shrink:0">
                                {{ $sub->PlanTime === 'Monthly' ? '🏆' : ($sub->PlanTime === 'Weekly' ? '⭐' : '☀️') }}
                            </div>
                            <div>
                                <div style="font-weight:800;font-size:1.2rem;color:{{ $planColor }}">{{ $sub->kitchenPlan->Title ?? $sub->PlanTime . ' Plan' }}</div>
                                <div style="font-size:0.82rem;color:var(--text-muted)">
                                    @if($sub->kitchenPlan)
                                        <i class="fas fa-utensils"></i> {{ $sub->kitchenPlan->kitchen->KitchenName ?? 'Kitchen' }} | 
                                    @endif
                                    {{ \Carbon\Carbon::parse($sub->StartDate)->format('d M Y') }} →
                                    {{ \Carbon\Carbon::parse($sub->EndDate)->format('d M Y') }}
                                </div>
                            </div>
                        </div>
                        <div style="text-align:right">
                            <div style="font-size:1.3rem;font-weight:800">{{ number_format($sub->Price, 2) }} EGP</div>
                            @php
                                $sColors = ['Active'=>'#4ade80', 'Pending'=>'#f59e0b', 'Cancelled'=>'#f87171', 'Expired'=>'#9ca3af'];
                                $sC = $sColors[$sub->Status] ?? '#4ade80';
                            @endphp
                            <span style="font-size:0.75rem;background:{{ $sC }}22;color:{{ $sC }};padding:3px 12px;border-radius:20px;font-weight:700">
                                <i class="fas fa-circle" style="font-size:0.5rem;vertical-align:middle"></i> {{ strtoupper($sub->Status) }}
                            </span>
                        </div>
                    </div>

                    {{-- Progress Bar --}}
                    @if($sub->Status === 'Active')
                    <div style="margin-bottom:8px;display:flex;justify-content:space-between;font-size:0.8rem;color:var(--text-muted)">
                        <span>Progress</span>
                        <span><strong style="color:{{ $planColor }}">{{ $daysLeft }} days</strong> remaining</span>
                    </div>
                    <div style="height:8px;background:var(--bg-card2);border-radius:20px;overflow:hidden;margin-bottom:20px">
                        <div style="height:100%;width:{{ $pct }}%;background:{{ $planColor }};border-radius:20px;transition:width 1s ease"></div>
                    </div>
                    @endif

                    {{-- Meals --}}
                    @if($sub->menuItems->count() > 0)
                    <div style="margin-bottom:20px">
                        <div style="font-size:0.8rem;font-weight:700;color:var(--text-muted);letter-spacing:1px;margin-bottom:10px">SUBSCRIBED MEALS</div>
                        <div style="display:flex;flex-wrap:wrap;gap:8px">
                            @foreach($sub->menuItems as $item)
                            @php $s = $item->pivot->Status; @endphp
                            <span style="padding:5px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;
                                background:{{ $s==='Approved'?'rgba(74,222,128,0.1)':($s==='Rejected'?'rgba(248,113,113,0.1)':'rgba(251,191,36,0.1)') }};
                                color:{{ $s==='Approved'?'#4ade80':($s==='Rejected'?'#f87171':'#fbbf24') }};
                                border:1px solid {{ $s==='Approved'?'#4ade8040':($s==='Rejected'?'#f8717140':'#fbbf2440') }}">
                                <i class="fas fa-{{ $s==='Approved'?'check-circle':($s==='Rejected'?'times-circle':'clock') }}"></i>
                                {{ $item->ItemName }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Actions --}}
                    @if($sub->Status === 'Active')
                    <div style="display:flex;gap:12px;flex-wrap:wrap">
                        {{-- Renew --}}
                        <form method="POST" action="{{ route('frontend.subscription.renew', $sub->SubscriptionID) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary" style="padding:10px 20px;font-size:0.88rem"
                                    onclick="event.preventDefault(); const f=this.closest('form'); window.biteConfirm('Renew this plan? The same amount will be charged again.', function(res){ if(res) f.submit(); });">
                                <i class="fas fa-rotate"></i> Renew Plan
                            </button>
                        </form>
                        {{-- Change Plan --}}
                        <a href="{{ route('frontend.subscriptions') }}" class="btn btn-outline" style="padding:10px 20px;font-size:0.88rem">
                            <i class="fas fa-arrows-rotate"></i> Change Plan
                        </a>
                        {{-- Cancel --}}
                        <form method="POST" action="{{ route('frontend.subscription.cancel', $sub->SubscriptionID) }}">
                            @csrf
                            <button type="submit" class="btn" style="padding:10px 20px;font-size:0.88rem;background:rgba(248,113,113,0.1);color:#f87171;border:1px solid #f8717140"
                                    onclick="event.preventDefault(); const f=this.closest('form'); window.biteConfirm('Are you sure you want to cancel this plan?', function(res){ if(res) f.submit(); });">
                                <i class="fas fa-times"></i> Cancel Plan
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
                @endforeach
            @endif
        </div>

        {{-- ─── TAB: My Conversations ─── --}}
        <div class="tab-pane" id="tab-chats">
            <div class="glass-card">
                <h3 style="margin:0 0 24px;font-size:1.05rem;display:flex;align-items:center;gap:10px">
                    <span style="width:34px;height:34px;background:linear-gradient(135deg,#8b5cf6,#6366f1);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:0.9rem"><i class="fas fa-comments" style="color:#fff"></i></span>
                    My Customization Requests
                </h3>

                @if(empty($activeSessions) || count($activeSessions) == 0)
                <div style="text-align:center;padding:40px 20px">
                    <div style="font-size:3.5rem;opacity:0.2;margin-bottom:12px">💬</div>
                    <h4 style="font-size:1rem;color:var(--text-muted)">No active conversations</h4>
                    <p style="font-size:0.85rem;color:var(--text-muted)">Chat with kitchens to customize your dishes before ordering.</p>
                </div>
                @else
                <div class="chats-container">
                    @foreach($activeSessions as $session)
                    <div class="chat-row-card glass-card" style="padding:20px;margin-bottom:12px;border:1px solid {{ $session['unread'] ? 'var(--primary)' : 'var(--border-color)' }}; cursor:pointer; transition:all 0.2s;" onclick="reopenFromList('{{ $session['session_id'] ?? '' }}', {{ $session['menu_item_id'] ?? 0 }}, '{{ addslashes($session['item_name'] ?? '') }}', {{ $session['item_price'] ?? 0 }}, {{ $session['kitchen_id'] ?? 0 }}, {{ $session['caterer_id'] ?? 0 }}, '{{ $session['owner_type'] ?? 'kitchen' }}', {{ $session['order_id'] ?? 'null' }})">
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:16px">
                            <div style="display:flex;align-items:center;gap:16px;flex:1;min-width:0">
                                <div style="width:48px;height:48px;border-radius:12px;background:rgba(139, 92, 246, 0.1);display:flex;align-items:center;justify-content:center;font-size:1.4rem;flex-shrink:0;color:#8b5cf6">
                                    <i class="fas fa-utensils"></i>
                                </div>
                                <div style="flex:1;min-width:0">
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <h5 style="margin:0;font-size:0.95rem;font-weight:700">{{ $session['item_name'] }}</h5>
                                        @if($session['unread'])
                                            <span style="background:var(--danger);width:8px;height:8px;border-radius:50%"></span>
                                        @endif
                                    </div>
                                    <p style="margin:4px 0 0;font-size:0.8rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                                        {{ $session['last_message'] }}
                                    </p>
                                </div>
                            </div>
                            <div style="text-align:right">
                                <div style="font-size:0.7rem;color:var(--text-muted);margin-bottom:4px">{{ $session['last_time'] }}</div>
                                <button class="btn btn-primary btn-sm" style="padding:4px 12px;font-size:0.75rem">Open Chat</button>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>

        </div>

    </div>

</div>
</div>

@push('scripts')
<script>
function showTab(id, el) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    
    document.getElementById('tab-' + id).classList.add('active');
    
    if (el) {
        el.classList.add('active');
    } else {
        // Find button by onclick content if called programmatically
        const btn = document.querySelector(`.tab-btn[onclick*="'${id}'"]`);
        if (btn) btn.classList.add('active');
    }
}

function previewPhoto(e) {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function(r) {
        const prev = document.getElementById('photoPreview');
        const letter = document.getElementById('photoLetter');
        if (prev) { prev.src = r.target.result; prev.style.display = 'block'; }
        if (letter) letter.style.display = 'none';

        // also update sidebar
        const wrap = document.getElementById('avatarPreviewWrap');
        if (wrap) wrap.innerHTML = `<img src="${r.target.result}" style="width:100%;height:100%;object-fit:cover;border-radius:50%">`;
    };
    reader.readAsDataURL(file);
}

function checkStrength(val) {
    const bar   = document.getElementById('strengthBar');
    const label = document.getElementById('strengthLabel');
    if (!bar) return;
    let score = 0;
    if (val.length >= 8)  score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const levels = [
        { w:0,   c:'transparent', t:'' },
        { w:'25%', c:'#f87171',   t:'Weak' },
        { w:'50%', c:'#fb923c',   t:'Fair' },
        { w:'75%', c:'#fbbf24',   t:'Good' },
        { w:'100%',c:'#4ade80',   t:'Strong 💪' },
    ];
    bar.style.width  = levels[score].w;
    bar.style.background = levels[score].c;
    label.textContent = levels[score].t;
    label.style.color = levels[score].c;
}

// Auto-open tabs based on hash
if (window.location.hash) {
    const hash = window.location.hash.replace('#', '');
    if (document.getElementById('tab-' + hash)) {
        setTimeout(() => showTab(hash), 100);
    }
}
</script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script src="https://unpkg.com/leaflet-control-geocoder/dist/Control.Geocoder.js"></script>
<script>
    var defaultLat = 30.0444;
    var defaultLng = 31.2357;
    var map;
    var marker;

    function initMapOnce() {
        if (map) return; // already initialized
        var mapCont = document.getElementById('map');
        if(!mapCont) return;

        map = L.map('map').setView([defaultLat, defaultLng], 12);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(map);

        var geocoder = L.Control.geocoder({
            defaultMarkGeocode: false,
            placeholder: 'Search for your address...',
        }).on('markgeocode', function(e) {
            var bbox = e.geocode.bbox;
            var center = e.geocode.center;
            map.fitBounds(bbox);
            if (marker) { map.removeLayer(marker); }
            marker = L.marker(center).addTo(map);
            document.getElementById('latInput').value = center.lat;
            document.getElementById('lngInput').value = center.lng;
            var addressInput = document.getElementById('addressInput');
            if(!addressInput.value) { addressInput.value = e.geocode.name; }
        }).addTo(map);

        map.on('click', function(e) {
            if(marker) { map.removeLayer(marker); }
            marker = L.marker(e.latlng).addTo(map);
            document.getElementById('latInput').value = e.latlng.lat;
            document.getElementById('lngInput').value = e.latlng.lng;
            
            var addressInput = document.getElementById('addressInput');
            fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${e.latlng.lat}&lon=${e.latlng.lng}&accept-language=ar`)
                .then(r => r.json())
                .then(data => { if(data && data.display_name) addressInput.value = data.display_name; });
        });

        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                map.flyTo([position.coords.latitude, position.coords.longitude], 14);
            });
        }
    }

    // Auto-open addresses tab if hash is #addresses
    if (window.location.hash === '#addresses') {
        setTimeout(() => showTab('addresses'), 100);
    }
    
    // Inject logic into showTab
    const origShowTab = window.showTab;
    window.showTab = function(id) {
        origShowTab(id);
        if (id === 'addresses') {
            setTimeout(() => {
                initMapOnce();
                if(map) map.invalidateSize();
            }, 100);
        }
    };
</script>

<script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const userId = "{{ auth()->id() ?? 'guest' }}";
    if (!localStorage.getItem('bitehub_tour_profile_' + userId)) {
        const driver = window.driver.js.driver;
        const driverObj = driver({
            showProgress: true,
            steps: [
                { element: '.profile-avatar-wrap', popover: { title: '👤 Your Profile', description: 'This panel shows your name, email, wallet balance, BitePoints, and order stats at a glance.', side: 'right', align: 'start' }},
                { element: '.tab-nav', popover: { title: '🗂️ Profile Sections', description: 'Use these tabs to update your personal info, profile photo, saved addresses, password, and meal plans.', side: 'bottom', align: 'start' }},
                { element: '#tab-info', popover: { title: '✏️ Personal Info', description: 'Edit your full name and phone number here. Your email address cannot be changed for security.', side: 'top', align: 'start' }}
            ],
            onDestroyStarted: () => { localStorage.setItem('bitehub_tour_profile_' + userId, 'true'); driverObj.destroy(); },
            onPopoverRendered: (popover) => {
                let footer = popover.wrapper.querySelector('.driver-popover-navigation-btns');
                if (footer && !footer.querySelector('.skip-tour-btn')) {
                    let btn = document.createElement('button');
                    btn.innerHTML = 'Skip Tour'; btn.className = 'driver-popover-prev-btn skip-tour-btn';
                    btn.style.color = '#ef4444'; btn.style.borderColor = 'transparent'; btn.style.fontWeight = 'bold';
                    btn.onclick = () => driverObj.destroy();
                    footer.insertBefore(btn, footer.firstChild);
                }
            }
        });
        setTimeout(() => { driverObj.drive(); }, 500);
    }
});
</script>
@endpush
@endsection
