@extends('frontend.layouts.app')
@section('title', 'Register')

@section('content')
<div class="auth-page">
    <div class="glass-card auth-card animate-fadeInUp" style="max-width:520px">
        <div class="text-center mb-3"><i class="fas fa-fire" style="font-size:2.5rem;color:var(--primary)"></i></div>
        <h2>Create Account</h2>
        <p class="subtitle">Join BiteHub today</p>
        @if(session('success'))
        <div style="background:rgba(102,187,106,0.1);border:1px solid var(--success);border-radius:var(--radius);padding:20px;text-align:center;color:var(--success)">
            <i class="fas fa-check-circle" style="font-size:2rem;margin-bottom:8px;display:block"></i>
            Registration successful! <a href="{{ route('frontend.login') }}" style="color:var(--primary);font-weight:600">Login now</a>
        </div>
        @else
        @if($errors->any())
        <div style="background:rgba(239,83,80,0.1);border:1px solid var(--danger);border-radius:var(--radius);padding:12px;margin-bottom:16px;color:var(--danger);font-size:0.9rem;text-align:center">
            <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
        </div>
        @endif
        <form method="POST" action="{{ route('frontend.register') }}">
            @csrf
            <div class="form-group"><label class="form-label">Full Name</label><input name="name" class="form-control" placeholder="Enter your full name" required value="{{ old('name') }}"></div>
            <div class="form-group"><label class="form-label">Email Address</label><input type="email" name="email" class="form-control" placeholder="you@example.com" required value="{{ old('email') }}"></div>
            <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" class="form-control" placeholder="Create a password" required></div>
            <div class="form-group"><label class="form-label">Phone Number</label><input name="phone" class="form-control" placeholder="+20-xxx-xxx-xxxx" value="{{ old('phone') }}"></div>
            <div class="form-group"><label class="form-label">Address</label><input name="address" class="form-control" placeholder="Your address" value="{{ old('address') }}"></div>
            <div class="form-group">
                <label class="form-label">I want to join as</label>
                <div class="role-selector">
                    <label class="role-option active" onclick="selectRole(this,'Customer')"><input type="radio" name="role" value="Customer" checked><div class="role-icon">🛒</div><div class="role-name">Customer</div></label>
                    <label class="role-option" onclick="selectRole(this,'KitchenOwner')"><input type="radio" name="role" value="KitchenOwner"><div class="role-icon">👨‍🍳</div><div class="role-name">Kitchen Owner</div></label>
                    <label class="role-option" onclick="selectRole(this,'Caterer')"><input type="radio" name="role" value="Caterer"><div class="role-icon">🎪</div><div class="role-name">Caterer</div></label>
                    <label class="role-option" onclick="selectRole(this,'DeliveryAgent')"><input type="radio" name="role" value="DeliveryAgent"><div class="role-icon">🚲</div><div class="role-name">Delivery Agent</div></label>
                </div>
            </div>
            <div id="kitchen-fields" style="display:none"><div class="form-group"><label class="form-label">Kitchen Name</label><input name="kitchen_name" class="form-control" placeholder="Your kitchen name"></div></div>
            <div id="delivery-fields" style="display:none"><div class="form-group"><label class="form-label">Vehicle Type</label><select name="vehicle_type" class="form-control"><option>Bike</option><option>Car</option><option>Motorcycle</option></select></div></div>
            <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-user-plus"></i> Create Account</button>
        </form>
        <div class="auth-footer">Already have an account? <a href="{{ route('frontend.login') }}">Sign in</a></div>
        @endif
    </div>
</div>

@push('scripts')
<script>
function selectRole(el, role) {
    document.querySelectorAll('.role-option').forEach(o => o.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('kitchen-fields').style.display = role === 'KitchenOwner' ? 'block' : 'none';
    document.getElementById('delivery-fields').style.display = role === 'DeliveryAgent' ? 'block' : 'none';
}
</script>
@endpush
@endsection
