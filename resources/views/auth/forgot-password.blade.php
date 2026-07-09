<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — BiteHub</title>
    <!-- Read theme from localStorage BEFORE render to avoid flash -->
    <script>
        (function() {
            var t = localStorage.getItem('bitehub_theme') || 'dark';
            document.documentElement.setAttribute('data-theme', t);
        })();
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        /* ─── DARK (default) ─────────────────────────────────────────── */
        :root,
        [data-theme="dark"] {
            --bg:           #0d0d0d;
            --card-bg:      rgba(22, 22, 22, 0.95);
            --card-border:  rgba(255,255,255,0.07);
            --card-glow:    rgba(255,107,53,0.08);
            --blob1:        rgba(255,107,53,0.12);
            --blob2:        rgba(255,159,28,0.08);
            --heading:      #ffffff;
            --subtext:      #777777;
            --label:        #888888;
            --input-bg:     rgba(255,255,255,0.04);
            --input-border: rgba(255,255,255,0.1);
            --input-color:  #ffffff;
            --placeholder:  #444444;
            --divider:      rgba(255,255,255,0.07);
            --footer-link:  #555555;
            --alert-success-bg:     rgba(34,197,94,0.1);
            --alert-success-border: rgba(34,197,94,0.25);
            --alert-success-color:  #4ade80;
            --alert-error-bg:       rgba(255,107,53,0.1);
            --alert-error-border:   rgba(255,107,53,0.25);
            --alert-error-color:    #ff6b35;
        }

        /* ─── LIGHT ──────────────────────────────────────────────────── */
        [data-theme="light"] {
            --bg:           #f5f0eb;
            --card-bg:      rgba(255, 255, 255, 0.97);
            --card-border:  rgba(0,0,0,0.07);
            --card-glow:    rgba(255,107,53,0.06);
            --blob1:        rgba(255,107,53,0.08);
            --blob2:        rgba(255,159,28,0.06);
            --heading:      #1a1a1a;
            --subtext:      #666666;
            --label:        #888888;
            --input-bg:     rgba(0,0,0,0.03);
            --input-border: rgba(0,0,0,0.12);
            --input-color:  #1a1a1a;
            --placeholder:  #aaaaaa;
            --divider:      rgba(0,0,0,0.08);
            --footer-link:  #999999;
            --alert-success-bg:     rgba(34,197,94,0.08);
            --alert-success-border: rgba(34,197,94,0.3);
            --alert-success-color:  #16a34a;
            --alert-error-bg:       rgba(255,107,53,0.08);
            --alert-error-border:   rgba(255,107,53,0.3);
            --alert-error-color:    #e84e17;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: background 0.3s;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(ellipse 700px 500px at 20% 30%, var(--blob1) 0%, transparent 60%),
                radial-gradient(ellipse 500px 400px at 80% 70%, var(--blob2) 0%, transparent 60%);
            pointer-events: none;
            z-index: 0;
        }

        .card {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 440px;
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 24px;
            padding: 48px 40px 44px;
            box-shadow: 0 32px 80px rgba(0,0,0,0.1), 0 0 0 1px var(--card-glow);
            animation: fadeUp 0.5s ease both;
            transition: background 0.3s, border-color 0.3s;
        }

        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .logo-wrap { text-align: center; margin-bottom: 32px; }
        .logo-icon { font-size: 2.6rem; display: block; margin-bottom: 8px; line-height: 1; }
        .logo-text {
            font-size: 1.8rem;
            font-weight: 900;
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -1px;
        }

        .heading { font-size: 1.45rem; font-weight: 800; color: var(--heading); text-align: center; margin-bottom: 10px; transition: color 0.3s; }
        .subtext  { font-size: 0.875rem; color: var(--subtext); text-align: center; line-height: 1.65; margin-bottom: 32px; transition: color 0.3s; }

        .alert {
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 22px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success {
            background: var(--alert-success-bg);
            border: 1px solid var(--alert-success-border);
            color: var(--alert-success-color);
        }
        .alert-error {
            background: var(--alert-error-bg);
            border: 1px solid var(--alert-error-border);
            color: var(--alert-error-color);
        }

        .field { margin-bottom: 20px; }
        .field label {
            display: block;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.8px;
            text-transform: uppercase;
            color: var(--label);
            margin-bottom: 8px;
            transition: color 0.3s;
        }
        .input-wrap { position: relative; }
        .input-wrap .icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1rem;
            pointer-events: none;
        }
        .field input {
            width: 100%;
            padding: 14px 16px 14px 46px;
            background: var(--input-bg);
            border: 1px solid var(--input-border);
            border-radius: 12px;
            color: var(--input-color);
            font-size: 0.95rem;
            font-family: 'Inter', sans-serif;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.3s, color 0.3s;
            outline: none;
        }
        .field input:focus {
            border-color: #ff6b35;
            box-shadow: 0 0 0 3px rgba(255,107,53,0.15);
        }
        .field input::placeholder { color: var(--placeholder); }
        .field-error { font-size: 0.78rem; color: var(--alert-error-color); margin-top: 6px; font-weight: 500; }

        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #ff6b35, #ff9f1c);
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 1rem;
            font-weight: 800;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            margin-top: 6px;
            box-shadow: 0 8px 24px rgba(255,107,53,0.35);
            transition: transform 0.18s, box-shadow 0.18s, opacity 0.18s;
            letter-spacing: 0.3px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(255,107,53,0.45); opacity: 0.95; }
        .btn-submit:active { transform: translateY(0); }

        .divider { display: flex; align-items: center; gap: 12px; margin: 28px 0 20px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--divider); transition: background 0.3s; }
        .divider span { font-size: 0.75rem; color: var(--footer-link); font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }

        .footer-links { display: flex; justify-content: center; }
        .footer-links a { font-size: 0.85rem; color: var(--footer-link); text-decoration: none; font-weight: 500; transition: color 0.2s; }
        .footer-links a span { color: #ff6b35; font-weight: 700; }
        .footer-links a:hover { color: #ff6b35; }
    </style>
</head>
<body>
<div class="card">

    <div class="logo-wrap">
        <span class="logo-icon">🔥</span>
        <div class="logo-text">BiteHub</div>
    </div>

    <h1 class="heading">Forgot your password?</h1>
    <p class="subtext">
        No worries! Enter your email address and we'll send you a secure link to reset your password.
    </p>

    @if (session('status'))
        <div class="alert alert-success">
            <span>✅</span>
            <span>{{ session('status') }}</span>
        </div>
    @endif

    @if ($errors->has('email') && !session('status'))
        <div class="alert alert-error">
            <span>⚠️</span>
            <span>{{ $errors->first('email') }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email Address</label>
            <div class="input-wrap">
                <span class="icon">📧</span>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                    autofocus
                    autocomplete="email"
                >
            </div>
            @error('email')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-submit">
            📨 Send Reset Link
        </button>
    </form>

    <div class="divider"><span>or</span></div>

    <div class="footer-links">
        <a href="{{ route('login') }}">← <span>Back to Login</span></a>
    </div>

</div>
</body>
</html>
