<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account – Fix-It Auto Services Booking</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/svg+xml" href="https://fixitautoservices.com/favicon.svg">
    <style>
        body {
            font-family: 'Segoe UI', -apple-system, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #3d0000 50%, #8b0000 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            width: 100%;
            max-width: 480px;
            border: none;
            border-radius: 24px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #cc0000, #8b0000);
            padding: 32px 28px 24px;
            text-align: center;
            color: #fff;
        }
        .card-header h2 { font-size: 20px; font-weight: 700; margin: 0; }
        .card-header p { opacity: 0.8; font-size: 13px; margin: 6px 0 0; }
        .card-body { padding: 28px; }
        .form-control {
            border-radius: 10px;
            padding: 12px 14px;
            border: 2px solid #e2e8f0;
            font-size: 14px;
            transition: all 0.2s;
        }
        .form-control:focus { border-color: #cc0000; box-shadow: 0 0 0 3px rgba(204,0,0,0.15); }
        .form-label { font-weight: 600; font-size: 13px; color: #475569; margin-bottom: 4px; }
        .btn-primary {
            background: #cc0000;
            border: none;
            border-radius: 12px;
            padding: 13px;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.2s;
        }
        .btn-primary:hover { background: #990000; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(204,0,0,0.3); }
        .alert { border-radius: 12px; font-size: 13px; }
        .back-link { text-align: center; margin-top: 16px; font-size: 13px; color: #94a3b8; }
        .back-link a { color: #94a3b8; }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h2><i class="fas fa-user-plus me-2"></i>Create Account</h2>
            <p>Sign up to start booking appointments online</p>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0" style="list-style:none;padding:0;">
                        @foreach($errors->all() as $e)
                            <li><i class="fas fa-exclamation-circle me-1"></i> {{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('booking.register.post') }}">
                @csrf
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">First Name</label>
                        <input type="text" name="first_name" class="form-control" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Last Name</label>
                        <input type="text" name="last_name" class="form-control" value="{{ old('last_name') }}" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required placeholder="your@email.com">
                </div>
                <div class="mb-3">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}" required placeholder="0917XXXXXXX">
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6">
                </div>
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required minlength="6">
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </button>
            </form>

            <div class="back-link">
                <a href="{{ route('booking.portal') }}"><i class="fas fa-arrow-left me-1"></i>Back</a>
                &middot;
                <a href="{{ route('booking.login') }}">Already have an account?</a>
            </div>
        </div>
    </div>
</body>
</html>
