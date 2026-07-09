@extends('frontend.layouts.app')
@section('title', 'Login')

@section('content')
<div class="auth-page">
    <div class="auth-bg-wrapper">
        <div class="auth-bg-image" style="background-image: url('https://images.unsplash.com/photo-1555939594-58d7cb561ad1?q=80&w=2000&auto=format&fit=crop')"></div>
        <div class="auth-bg-overlay"></div>
    </div>
    
    <div class="auth-container animate-scaleIn">
        <!-- Left Side: Animation -->
        <div class="auth-animation-pane" style="flex-direction:column; text-align:center;">
            <img src="https://media.giphy.com/media/l1KdbHUPe27GQsJH2/giphy.gif" alt="Dancing 3D Character" style="width: 280px; height: auto; filter: drop-shadow(0 15px 25px rgba(0,0,0,0.3)); transform: scale(1.1); margin-bottom: 20px;">
            <h3 style="color:var(--text-primary); margin-top:10px; font-weight:700;">Welcome to BiteHub!</h3>
            <p style="color:var(--text-secondary); font-size:0.9rem;">Get ready for a delicious experience.</p>
        </div>
        
        <!-- Right Side: Form -->
        <div class="auth-card">
            <div class="text-center mb-3">
                <i class="fas fa-fire" style="font-size:2.5rem;color:var(--primary)"></i>
            </div>
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your BiteHub account</p>

            {{-- Session Status --}}
            @if(session('status'))
            <div style="background:rgba(102,187,106,0.1);border:1px solid var(--success);border-radius:var(--radius);padding:12px;margin-bottom:16px;color:var(--success);font-size:0.9rem;text-align:center">
                {{ session('status') }}
            </div>
            @endif

            {{-- Validation Errors --}}
            @if($errors->any())
            <div style="background:rgba(239,83,80,0.1);border:1px solid var(--danger);border-radius:var(--radius);padding:12px;margin-bottom:16px;color:var(--danger);font-size:0.9rem;text-align:center">
                <i class="fas fa-exclamation-circle"></i> {{ $errors->first() }}
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="you@example.com" required value="{{ old('email') }}" autofocus>
                </div>
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter your password" required>
                </div>
                <div class="form-group" style="display:flex;align-items:center;justify-content:space-between">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.9rem;color:var(--text-secondary)">
                        <input type="checkbox" name="remember" style="accent-color:var(--primary);width:16px;height:16px">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" style="font-size:0.85rem;color:var(--primary)">Forgot password?</a>
                    @endif
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg"><i class="fas fa-sign-in-alt"></i> Sign In</button>
            </form>
            <div class="auth-footer">
                Don't have an account? <a href="{{ route('register') }}">Create one</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@endpush
@endsection
