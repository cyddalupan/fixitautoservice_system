<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book an Appointment – Fix-It Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://fixitautoservices.com/favicon.svg">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #3d0000 50%, #8b0000 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .portal-card {
            background: rgba(255,255,255,0.98);
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 480px;
            overflow: hidden;
            backdrop-filter: blur(10px);
        }
        .portal-header {
            background: linear-gradient(135deg, #cc0000, #8b0000);
            padding: 40px 30px;
            text-align: center;
            color: #fff;
        }
        .portal-header .logo {
            font-size: 48px;
            font-weight: 800;
            letter-spacing: -1px;
            line-height: 1;
        }
        .portal-header .logo-sub {
            font-size: 12px;
            opacity: 0.85;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .portal-header h1 {
            font-size: 22px;
            margin-top: 16px;
            font-weight: 700;
        }
        .portal-header p {
            opacity: 0.85;
            font-size: 14px;
            margin-top: 6px;
        }
        .portal-body { padding: 30px; }
        .btn-primary {
            background: #cc0000;
            border: none;
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
        }
        .btn-primary:hover { background: #990000; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(204,0,0,0.3); }
        .btn-outline-primary {
            border: 2px solid #cc0000;
            color: #cc0000;
            border-radius: 12px;
            padding: 14px 24px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
        }
        .btn-outline-primary:hover { background: #cc0000; color: #fff; }
        .feature-list { margin: 20px 0; }
        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 0;
            color: #475569;
            font-size: 14px;
        }
        .feature-item i { color: #cc0000; width: 20px; text-align: center; font-size: 16px; }
        .divider {
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 24px 0;
            color: #94a3b8;
            font-size: 13px;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #e2e8f0;
        }
        .footer-text {
            text-align: center;
            color: #94a3b8;
            font-size: 12px;
            margin-top: 20px;
        }
        .footer-text a { color: #94a3b8; text-decoration: underline; }
        .alert { border-radius: 12px; font-size: 14px; }
        @media (max-width: 500px) {
            .portal-header { padding: 30px 20px; }
            .portal-body { padding: 20px; }
        }
    </style>
</head>
<body>
    <div class="portal-card">
        <div class="portal-header">
            <div class="logo">FIX-IT</div>
            <div class="logo-sub">Auto Services</div>
            <h1><i class="fas fa-calendar-check me-2"></i>Online Booking</h1>
            <p>Schedule your service appointment in minutes</p>
        </div>

        <div class="portal-body">
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i> {{ session('error') }}
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                </div>
            @endif

            <div class="feature-list">
                <div class="feature-item">
                    <i class="fas fa-calendar-day"></i>
                    <span>Choose your preferred date and time</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-tools"></i>
                    <span>Select from our range of services</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-car"></i>
                    <span>Manage your vehicles online</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-bell"></i>
                    <span>Get email confirmations &amp; reminders</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-sync-alt"></i>
                    <span>Reschedule or cancel anytime</span>
                </div>
            </div>

            <a href="{{ route('booking.login') }}" class="btn btn-primary w-100 mb-3">
                <i class="fas fa-sign-in-alt me-2"></i>Sign In
            </a>
            <a href="{{ route('booking.register') }}" class="btn btn-outline-primary w-100">
                <i class="fas fa-user-plus me-2"></i>Create Account
            </a>

            <div class="divider">Already have a booking link?</div>

            <form action="{{ route('booking.magic') }}" method="GET" class="mb-2">
                <div class="input-group">
                    <input type="text" name="token" class="form-control" placeholder="Paste your booking token" style="border-radius:10px 0 0 10px;padding:12px;border:2px solid #e2e8f0;">
                    <button class="btn btn-primary" type="submit" style="border-radius:0 10px 10px 0;">
                        <i class="fas fa-arrow-right"></i>
                    </button>
                </div>
            </form>

            <div class="footer-text">
                <a href="https://fixitautoservices.com">← Back to Fix-It Auto Services</a>
            </div>
        </div>
    </div>
</body>
</html>
