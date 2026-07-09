<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        .header { background-color: #ff6b35; color: white; padding: 15px; text-align: center; border-radius: 8px 8px 0 0; font-size: 24px; font-weight: bold; }
        .content { margin-top: 20px; font-size: 16px; color: #333; line-height: 1.6; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #888; border-top: 1px solid #ddd; padding-top: 15px; }
        .btn { display: inline-block; padding: 12px 24px; background-color: #ff6b35; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">BiteHub Delivery</div>
        <div class="content">
            <p>Hi <strong>{{ $details['name'] }}</strong>,</p>
            <p>Great news! the administrator has reviewed your submitted verification documents and your Delivery Agent account is now <strong>fully approved</strong>.</p>
            <p>You can now log in to your dashboard to start viewing available orders, managing your deliveries, and earning with BiteHub.</p>
            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="btn">Login to Dashboard</a>
            </div>
        </div>
        <div class="footer">
            <p>Need help? Contact our support team directly via the platform.</p>
            <p>&copy; {{ date('Y') }} BiteHub. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
