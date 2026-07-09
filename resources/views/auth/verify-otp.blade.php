@extends('frontend.layouts.app')
@section('title', 'Verify Your Email')

@section('content')
<div class="container" style="display: flex; justify-content: center; align-items: center; min-height: calc(100vh - var(--nav-h)); padding: 40px 20px;">
    <div class="glass-card reveal" style="width: 100%; max-width: 480px; padding: 40px; text-align: center; border-top: 4px solid var(--primary);">
        
        <div style="width: 70px; height: 70px; border-radius: 50%; background: linear-gradient(135deg, rgba(255,107,53,0.1), rgba(255,167,38,0.05)); border: 2px solid var(--primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; color: var(--primary); margin: 0 auto 24px;">
            <i class="fas fa-envelope-open-text"></i>
        </div>

        <h2 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 10px; color: var(--text-primary);">Check your email</h2>
        <p style="color: var(--text-secondary); font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px;">
            We've sent a 6-digit verification code to <strong>{{ session('verify_email') }}</strong>. Please enter the code below to verify your account.
        </p>

        <!-- Validation Errors -->
        @if ($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 12px; font-size: 0.85rem; text-align: left; margin-bottom: 24px; border: 1px solid rgba(239, 68, 68, 0.2);">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (session('status'))
            <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 12px; border-radius: 12px; font-size: 0.85rem; text-align: left; margin-bottom: 24px; border: 1px solid rgba(16, 185, 129, 0.2);">
                <i class="fas fa-check-circle me-1"></i> {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('verify.otp.submit') }}">
            @csrf

            <!-- OTP Input Boxes -->
            <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 30px; direction: ltr;">
                @for ($i = 0; $i < 6; $i++)
                    <input type="text" name="otp[]" class="otp-input form-control" maxlength="1" required autofocus autocomplete="one-time-code" style="width: 50px; height: 60px; font-size: 1.5rem; text-align: center; font-weight: 800; border-radius: 12px; padding: 0;">
                @endfor
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 12px; font-size: 1.1rem; font-weight: 700; padding: 14px; box-shadow: 0 4px 15px rgba(255,107,53,0.3);">
                Verify Account
            </button>
        </form>

        <form method="POST" action="{{ route('verify.otp.resend') }}" style="margin-top: 24px;">
            @csrf
            <p style="color: var(--text-muted); font-size: 0.9rem;">
                Didn't receive the code? 
                <button type="submit" style="background: none; border: none; color: var(--primary); font-weight: 700; cursor: pointer; padding: 0; text-decoration: underline;">
                    Resend Code
                </button>
            </p>
        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.otp-input');

    inputs.forEach((input, index) => {
        // Move to next input on type
        input.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, ''); // Ensure numbers only
            
            if (this.value.length === 1) {
                if (index < inputs.length - 1) inputs[index + 1].focus();
            }
        });

        // Move to previous on backspace if empty
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value) {
                if (index > 0) inputs[index - 1].focus();
            }
        });

        // Handle pasting standard 6 digit codes
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
            if (pastedData) {
                pastedData.split('').forEach((char, i) => {
                    if (inputs[i]) {
                        inputs[i].value = char;
                        if (i < 5) inputs[i + 1].focus();
                    }
                });
            }
        });
    });
});
</script>
@endsection
