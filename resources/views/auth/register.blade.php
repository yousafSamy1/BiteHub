@extends('frontend.layouts.app')
@section('title', 'Register')

@section('content')
<div class="auth-page">
    <div class="auth-bg-wrapper">
        <div class="auth-bg-image" style="background-image: url('https://images.unsplash.com/photo-1544025162-d76694265947?q=80&w=2000&auto=format&fit=crop')"></div>
        <div class="auth-bg-overlay"></div>
    </div>
    
    <div class="auth-container animate-scaleIn">
        <!-- Left Side: Animation -->
        <div class="auth-animation-pane" style="flex-direction:column; text-align:center;">
            <img src="https://media.giphy.com/media/l1KdbHUPe27GQsJH2/giphy.gif" alt="Dancing 3D Character" style="width: 300px; height: auto; filter: drop-shadow(0 15px 25px rgba(0,0,0,0.3)); transform: scale(1.1); margin-bottom: 20px;">
            <h3 style="color:var(--text-primary); margin-top:10px; font-weight:700;">Join the Feast!</h3>
            <p style="color:var(--text-secondary); font-size:0.9rem;">Cook, cater, or simply enjoy amazing food.</p>
        </div>
        
        <!-- Right Side: Form -->
        <div class="auth-card" style="max-height: 85vh; overflow-y: auto;">
            <div class="text-center mb-3 text-primary">
                <i class="fas fa-fire" style="font-size:2.5rem;color:var(--primary)"></i>
            </div>
            <h2>Create Account</h2>
            <p class="subtitle" id="roleSubtitle">Join BiteHub today</p>

            {{-- Validation Errors --}}
            @if($errors->any())
            <div style="background:rgba(239,83,80,0.1);border:1px solid var(--danger);border-radius:var(--radius);padding:12px;margin-bottom:16px;color:var(--danger);font-size:0.9rem;text-align:center">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input name="name" class="form-control" placeholder="Enter your full name" required value="{{ old('name') }}" autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required value="{{ old('email') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Create a password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" placeholder="Confirm your password" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input name="phone" class="form-control" placeholder="+20-xxx-xxx-xxxx" value="{{ old('phone') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Address</label>
                    <input name="address" class="form-control" placeholder="Your address" value="{{ old('address') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">I want to join as</label>
                    @php $selectedRole = old('role', request('role', 'Customer')); @endphp
                    <div class="role-selector">
                        <label class="role-option {{ $selectedRole === 'Customer' ? 'active' : '' }}" onclick="selectRole(this,'Customer')">
                            <input type="radio" name="role" value="Customer" {{ $selectedRole === 'Customer' ? 'checked' : '' }}>
                            <div class="role-icon">🛒</div><div class="role-name">Customer</div>
                        </label>
                        <label class="role-option {{ $selectedRole === 'KitchenOwner' ? 'active' : '' }}" onclick="selectRole(this,'KitchenOwner')">
                            <input type="radio" name="role" value="KitchenOwner" {{ $selectedRole === 'KitchenOwner' ? 'checked' : '' }}>
                            <div class="role-icon">👨‍🍳</div><div class="role-name">Kitchen Owner</div>
                        </label>
                        <label class="role-option {{ $selectedRole === 'Caterer' ? 'active' : '' }}" onclick="selectRole(this,'Caterer')">
                            <input type="radio" name="role" value="Caterer" {{ $selectedRole === 'Caterer' ? 'checked' : '' }}>
                            <div class="role-icon">🎪</div><div class="role-name">Caterer</div>
                        </label>
                    </div>
                </div>
                <div id="kitchen-fields" style="display:{{ $selectedRole === 'KitchenOwner' ? 'block' : 'none' }}">
                    <div class="form-group">
                        <label class="form-label">Kitchen Name</label>
                        <input name="kitchen_name" class="form-control" placeholder="Your kitchen name" value="{{ old('kitchen_name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Attachments (Photos, Docs)</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">You can upload multiple files.</small>
                    </div>
                </div>
                <div id="caterer-fields" style="display:{{ $selectedRole === 'Caterer' ? 'block' : 'none' }}">
                    <div class="form-group">
                        <label class="form-label">Business Name</label>
                        <input name="business_name" class="form-control" placeholder="Your business name" value="{{ old('business_name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Attachments (Photos, Docs)</label>
                        <input type="file" name="attachments[]" class="form-control" multiple accept="image/*,.pdf,.doc,.docx">
                        <small class="text-muted">You can upload multiple files.</small>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-user-plus"></i> Create Account</button>
            </form>
            <div class="auth-footer">Already have an account? <a href="{{ route('login') }}">Sign in</a></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function selectRole(el, role) {
    document.querySelectorAll('.role-option').forEach(o => o.classList.remove('active'));
    el.classList.add('active');
    
    // Toggle fields
    document.getElementById('kitchen-fields').style.display = role === 'KitchenOwner' ? 'block' : 'none';
    document.getElementById('caterer-fields').style.display = role === 'Caterer' ? 'block' : 'none';
}
</script>
@endpush
@endsection
