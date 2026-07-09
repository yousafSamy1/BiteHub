<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Deleted – BiteHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #0f0f0f; font-family: 'Segoe UI', Arial, sans-serif; color: #e0e0e0; }
        .wrapper { max-width: 560px; margin: 0 auto; background: #1a1a1a; border-radius: 16px; overflow: hidden; border: 1px solid #2a2a2a; }
        .header { background: linear-gradient(135deg, #ef4444, #b91c1c); padding: 40px 32px; text-align: center; }
        .header-icon { font-size: 3rem; display: block; margin-bottom: 10px; }
        .logo { font-size: 1.4rem; font-weight: 900; color: rgba(255,255,255,0.6); letter-spacing: -0.5px; }
        .logo strong { color: #fff; }
        .body { padding: 36px 32px; }
        .greeting { font-size: 1.45rem; font-weight: 800; color: #fff; margin-bottom: 10px; }
        .subtitle { color: #aaa; font-size: 0.95rem; line-height: 1.7; margin-bottom: 28px; }
        .alert-box { background: rgba(239,68,68,0.08); border: 1px solid rgba(239,68,68,0.25); border-radius: 12px; padding: 20px 24px; margin-bottom: 24px; }
        .alert-box p { color: #fca5a5; font-size: 0.9rem; line-height: 1.6; }
        .alert-box strong { color: #f87171; }
        .info-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #2a2a2a; font-size: 0.88rem; }
        .info-row:last-child { border-bottom: none; }
        .info-label { color: #666; }
        .info-value { color: #ccc; font-weight: 600; }
        .divider { height: 1px; background: #2a2a2a; margin: 28px 0; }
        .cta { text-align: center; margin-top: 28px; }
        .cta a { display: inline-block; background: linear-gradient(135deg, #ff6b35, #ff9f1c); color: #fff; text-decoration: none; padding: 13px 32px; border-radius: 30px; font-weight: 800; font-size: 0.95rem; }
        .footer { background: #111; padding: 24px 32px; text-align: center; border-top: 1px solid #222; }
        .footer p { font-size: 0.78rem; color: #555; line-height: 1.7; }
        .footer a { color: #ff6b35; text-decoration: none; }
    </style>
</head>
<body>
<div style="padding: 30px 0; background:#0f0f0f;">
<div class="wrapper">

    <!-- Header -->
    <div class="header">
        <span class="header-icon">⚠️</span>
        <div class="logo"><strong>Bite</strong>Hub</div>
    </div>

    <!-- Body -->
    <div class="body">
        <p class="greeting">Account Deleted, {{ $userName }}</p>
        <p class="subtitle">
            We're confirming that your BiteHub account has been <strong style="color:#f87171;">permanently deleted</strong> as requested. All your personal data, order history, and saved preferences have been removed from our system.
        </p>

        <!-- Details -->
        <div style="background:#242424; border:1px solid #333; border-radius:12px; padding:20px 24px; margin-bottom:24px;">
            <div class="info-row">
                <span class="info-label">Account Name</span>
                <span class="info-value">{{ $userName }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Deletion Date</span>
                <span class="info-value">{{ now()->format('d M Y, g:i A') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="info-value" style="color:#f87171;">Permanently Deleted</span>
            </div>
        </div>

        <!-- Warning -->
        <div class="alert-box">
            <p>
                <strong>This action cannot be undone.</strong> If you did not request this deletion or believe this was a mistake, please contact us immediately at
                <a href="mailto:bitehub.eg@gmail.com" style="color:#f87171;">bitehub.eg@gmail.com</a>.
            </p>
        </div>

        <div class="divider"></div>

        <p style="color:#888; font-size:0.88rem; text-align:center; margin-bottom:20px;">
            We're sorry to see you go. If you ever change your mind, you're always welcome back.
        </p>

        <div class="cta">
            <a href="{{ url('/register') }}">Create a New Account</a>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>
            This email was sent by <a href="{{ url('/') }}">BiteHub</a> because an account deletion was requested.<br>
            &copy; {{ date('Y') }} BiteHub &mdash; Egypt's #1 Home Food Platform.
        </p>
    </div>

</div>
</div>
</body>
</html>
