<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 560px; margin: 0 auto; padding: 24px; }
        .header { background: linear-gradient(135deg, #1a1a1a, #8b0000); color: #fff; padding: 24px; text-align: center; border-radius: 8px 8px 0 0; }
        .header h1 { margin: 0; font-size: 1.4rem; }
        .body { padding: 24px; background: #fff; border: 1px solid #ddd; border-top: none; border-radius: 0 0 8px 8px; }
        .btn { display: inline-block; padding: 12px 28px; background: #cc0000; color: #fff !important; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 16px 0; }
        .footer { margin-top: 24px; font-size: 0.8rem; color: #999; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔑 Your Booking Login Link</h1>
        </div>
        <div class="body">
            <p>Hello {{ $customer->first_name }},</p>
            <p>Click the button below to sign in and book your appointment at Fix-It Auto Services. This link expires in 2 hours.</p>
            <p style="text-align: center;">
                <a href="{{ $magicUrl }}" class="btn">Sign In to Book</a>
            </p>
            <p style="font-size: 0.85rem; color: #888;">If the button doesn't work, copy and paste this URL into your browser:</p>
            <p style="font-size: 0.8rem; word-break: break-all; background: #f5f5f5; padding: 12px; border-radius: 4px;">{{ $magicUrl }}</p>
            <p>If you didn't request this link, you can safely ignore this email.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Fix-It Auto Services Center. All rights reserved.
        </div>
    </div>
</body>
</html>
