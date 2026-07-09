<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your BiteHub Password</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .header { background: #ff9f1c; padding: 40px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; font-weight: 800; }
        .body { padding: 45px 35px; text-align: center; }
        .greeting { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 15px; }
        .message { color: #4b5563; font-size: 16px; line-height: 1.7; margin-bottom: 35px; }
        .cta-button { 
            display: inline-block; 
            background: #ff9f1c; 
            color: #ffffff; 
            text-decoration: none; 
            padding: 16px 40px; 
            border-radius: 50px; 
            font-weight: 700; 
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(255,159,28,0.3);
        }
        .info-box { background: #fff9f0; border: 1px solid #ffe8cc; border-radius: 10px; padding: 20px; margin: 30px 0; font-size: 14px; color: #af5700; }
        .footer { margin-top: 40px; padding-top: 25px; border-top: 1px solid #f3f4f6; text-align: center; }
        .legal { font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div style="background-color: #f8f9fa; padding: 20px 0;">
        <div class="wrapper">
            <div class="header">
                <h1>BiteHub</h1>
            </div>
            <div class="body">
                <h2 class="greeting">Password Reset Request</h2>
                <p class="message">
                    Hi {{ $userName }}, we received a request to reset your BiteHub password. Click the button below to choose a new one.
                </p>
                
                <a href="{{ $resetUrl }}" class="cta-button">Reset Password &rarr;</a>
                
                <div class="info-box">
                    <strong>Security Reminder:</strong> This link will expire in 60 minutes. If you didn't request a password reset, you can safely ignore this email.
                </div>

                <div class="footer">
                    <p class="legal">
                        &copy; {{ date('Y') }} BiteHub — Egypt's #1 Home Food Platform<br>
                        Helping you eat better, every day.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
