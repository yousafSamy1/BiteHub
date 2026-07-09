<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to the Administration Team – BiteHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #0d0d0d; font-family: 'Segoe UI', Helvetica, Arial, sans-serif; color: #d1d1d1; }
        .container { max-width: 600px; margin: 40px auto; background: #181818; border-radius: 20px; overflow: hidden; border: 1px solid #282828; box-shadow: 0 20px 50px rgba(0,0,0,0.5); }
        .hero { background: linear-gradient(135deg, #10b981, #059669); padding: 60px 40px; text-align: center; }
        .hero-icon { font-size: 4rem; margin-bottom: 20px; display: block; }
        .logo { font-size: 1.5rem; font-weight: 900; color: #fff; letter-spacing: -1px; text-transform: uppercase; }
        .logo span { color: rgba(255,255,255,0.7); }
        
        .content { padding: 48px 40px; }
        .title { font-size: 1.8rem; font-weight: 800; color: #fff; margin-bottom: 16px; line-height: 1.2; }
        .text { font-size: 1.05rem; line-height: 1.7; color: #b0b0b0; margin-bottom: 24px; }
        
        .credentials-card { background: #222; border: 1px solid #333; border-radius: 16px; padding: 24px; margin-bottom: 32px; }
        .card-label { font-size: 0.8rem; color: #666; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; margin-bottom: 12px; }
        .cred-item { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #333; }
        .cred-item:last-child { border-bottom: none; }
        .cred-label { color: #888; font-weight: 600; }
        .cred-value { color: #fff; font-weight: 700; }
        
        .btn { display: inline-block; background: #fff; color: #000; text-decoration: none; padding: 16px 32px; border-radius: 12px; font-weight: 800; font-size: 1rem; transition: all 0.3s ease; }
        
        .footer { background: #111; padding: 32px; text-align: center; border-top: 1px solid #222; }
        .footer p { font-size: 0.8rem; color: #555; line-height: 1.6; }
        .footer a { color: #10b981; text-decoration: none; font-weight: 700; }
    </style>
</head>
<body>
    <div class="container">
        <div class="hero">
            <span class="hero-icon">🏢</span>
            <div class="logo">Bite<span>Hub</span> ADMIN</div>
        </div>
        
        <div class="content">
            <h1 class="title">Administrative Access Granted</h1>
            <p class="text">
                Hi {{ $details['name'] }},<br><br>
                Welcome to the BiteHub team! You have been appointed as an <strong>Administrator</strong> for our home food platform. Your account is now active and ready for use.
            </p>
            
            <div class="credentials-card">
                <div class="card-label">Your Login Credentials</div>
                <div class="cred-item">
                    <span class="cred-label">Login URL</span>
                    <span class="cred-value">{{ url('/login') }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">Email</span>
                    <span class="cred-value">{{ $details['email'] }}</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">Password</span>
                    <span class="cred-value">{{ $details['password'] }}</span>
                </div>
            </div>
            
            <p class="text">
                Please log in to your dashboard to manage orders, users, and platform settings. We recommend changing your password after your first login.
            </p>
            
            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="btn">Access Dashboard</a>
            </div>
        </div>
        
        <div class="footer">
            <p>
                This is a secure system notification from <a href="{{ url('/') }}">BiteHub</a>.<br>
                If you did not expect this email, please contact support immediately.
            </p>
        </div>
    </div>
</body>
</html>
