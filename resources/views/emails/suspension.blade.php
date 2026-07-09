<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #334155; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #f1f5f9; }
        .logo { font-size: 24px; font-weight: bold; color: #ff6b35; text-decoration: none; }
        .content { padding: 30px 0; }
        .alert { background-color: #fef2f2; border: 1px solid #fee2e2; border-radius: 8px; padding: 20px; color: #991b1b; margin-bottom: 20px; }
        .footer { font-size: 12px; color: #94a3b8; text-align: center; padding-top: 20px; border-top: 1px solid #f1f5f9; }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo">BiteHub</div>
    </div>
    <div class="content">
        <h2>Account Suspension Notice</h2>
        <p>Hello {{ $user->FullName }},</p>
        
        <div class="alert">
            <strong>Important:</strong> Your account has been automatically suspended due to multiple violations of our community guidelines regarding prohibited language.
        </div>
        
        <p>Our automated systems detected 3 separate instances of profanity in your recent messages. Following our "three-strikes" policy, your access to the BiteHub platform has been restricted.</p>
        
        <p><strong>What this means:</strong></p>
        <ul>
            <li>You can no longer send or receive messages.</li>
            <li>You cannot place new orders or accept new requests.</li>
            <li>Access to your account dashboard is currently restricted.</li>
        </ul>
        
        <p>If you believe this was an error, you may contact our support team at <a href="mailto:support@bitehub.com">support@bitehub.com</a> to appeal this decision. Our administrators will review the reported messages and manually evaluate your case.</p>
        
        <p>Thank you for your understanding as we work to keep BiteHub a professional and friendly community.</p>
    </div>
    <div class="footer">
        &copy; {{ date('Y') }} BiteHub - Homemade Food Marketplace
    </div>
</body>
</html>
