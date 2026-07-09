<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9fafb; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .header { text-align: center; border-bottom: 2px solid #ef5350; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #ef5350; margin: 0; font-size: 24px; }
        .content p { line-height: 1.6; }
        .credentials { background: #fef1f0; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 16px; margin: 20px 0; border: 1px dashed #ef5350; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #777; }
        .btn { display: inline-block; padding: 12px 24px; background: #ef5350; color: #fff; text-decoration: none; border-radius: 6px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to BiteHub!</h1>
        </div>
        <div class="content">
            <p>Hello <strong>{{ $details['name'] }}</strong>,</p>
            <p>An administrative account has been created for you as a <strong>Delivery Agent</strong>.</p>
            <p>You can use the following credentials to access your dashboard:</p>
            
            <div class="credentials">
                <strong>Email:</strong> {{ $details['email'] }}<br>
                <strong>Password:</strong> {{ $details['password'] }}
            </div>
            
            <p>Please log in and immediately navigate to your profile settings to change this randomly generated password for your security.</p>
            
            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="btn">Login to Dashboard</a>
            </div>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} BiteHub Platform. All rights reserved.
        </div>
    </div>
</body>
</html>
