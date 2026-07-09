<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to BiteHub!</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .header { background: linear-gradient(135deg, #ff6b35, #ff9f1c); padding: 50px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 32px; font-weight: 800; letter-spacing: -1px; }
        .body { padding: 45px 35px; }
        .greeting { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 15px; }
        .message { color: #4b5563; font-size: 16px; line-height: 1.7; margin-bottom: 30px; }
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 12px; padding: 25px; margin-bottom: 30px; }
        .card-label { font-size: 12px; font-weight: 700; color: #ff6b35; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
        .card-value { font-size: 18px; font-weight: 600; color: #111827; }
        .cta-container { text-align: center; margin-top: 20px; }
        .cta-button { 
            display: inline-block; 
            background: #ff6b35; 
            color: #ffffff; 
            text-decoration: none; 
            padding: 16px 40px; 
            border-radius: 50px; 
            font-weight: 700; 
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(255,107,53,0.3);
        }
        .footer { margin-top: 40px; padding-top: 25px; border-top: 1px solid #f3f4f6; text-align: center; }
        .legal { font-size: 12px; color: #9ca3af; line-height: 1.6; }
    </style>
</head>
<body>
    <div style="background-color: #f8f9fa; padding: 20px 0;">
        <div class="wrapper">
            <div class="header">
                <h1>Welcome to BiteHub</h1>
            </div>
            <div class="body">
                <h2 class="greeting">Hey {{ $user->FullName }}! 👋</h2>
                <p class="message">
                    Your account is now verified! We're thrilled to have you join BiteHub — Egypt's largest homemade food community. Whether you're here to discover amazing meals or grow your food business, you're in the right place.
                </p>
                
                <div class="card">
                    <div class="card-label">Your Registered Email</div>
                    <div class="card-value">{{ $user->Email }}</div>
                    <div style="margin-top: 10px; font-size: 14px; color: #6b7280;">
                        Role: <strong>{{ $user->Role }}</strong>
                    </div>
                </div>

                <div class="cta-container">
                    <a href="{{ url('/') }}" class="cta-button">Start Exploring Now &rarr;</a>
                </div>

                <div class="footer">
                    <p class="legal">
                        You received this email because you created an account on BiteHub.<br>
                        &copy; {{ date('Y') }} BiteHub — Egypt's #1 Home Food Platform
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
