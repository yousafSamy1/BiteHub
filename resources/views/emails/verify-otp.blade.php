<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email – BiteHub</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; }
        .wrapper { max-width: 600px; margin: 40px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.05); }
        .header { background: #ff6b35; padding: 40px 20px; text-align: center; }
        .header h1 { color: #ffffff; margin: 0; font-size: 28px; letter-spacing: 1px; font-weight: 800; }
        .body { padding: 45px 35px; text-align: center; }
        .title { font-size: 24px; font-weight: 700; color: #1f2937; margin-bottom: 20px; }
        .message { color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 35px; }
        .otp-box { 
            background-color: #fdf2f0; 
            border: 1px dashed rgba(255, 107, 53, 0.3);
            border-radius: 10px; 
            padding: 25px; 
            margin-bottom: 35px; 
            display: inline-block;
        }
        .otp-code { 
            font-size: 42px; 
            font-weight: 900; 
            color: #ff6b35; 
            letter-spacing: 8px; 
            font-family: 'Courier New', Courier, monospace;
        }
        .footer { padding-top: 20px; border-top: 1px solid #f3f4f6; text-align: center; }
        .expiry-note { font-size: 14px; color: #6b7280; line-height: 1.5; margin-bottom: 0; }
        .legal { font-size: 11px; color: #9ca3af; margin-top: 25px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>
    <div style="background-color: #f8f9fa; padding: 20px 0;">
        <div class="wrapper">
            <!-- Header -->
            <div class="header">
                <h1>BiteHub</h1>
            </div>
            
            <!-- Body -->
            <div class="body">
                <h2 class="title">Verify Your Email Address</h2>
                <p class="message">
                    Thank you for registering with BiteHub! Please use the verification code below to complete your registration.
                </p>
                
                <!-- OTP Code Box -->
                <div class="otp-box">
                    <span class="otp-code">{{ $otpCode }}</span>
                </div>
                
                <div class="footer">
                    <p class="expiry-note">
                        This code will expire in 15 minutes. If you did not request this, you can safely ignore this email.
                    </p>
                    <p class="legal">
                        &copy; {{ date('Y') }} BiteHub — Egypt's #1 Home Food Platform
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
