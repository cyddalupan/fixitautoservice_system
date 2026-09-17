<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In · Fix-It Auto Services</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --red: #dc2626;
            --red-dark: #b91c1c;
            --red-600: #dc2626;
            --dark-900: #0b0d12;
            --dark-800: #10131a;
            --dark-700: #181c25;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --line: #262b36;
        }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background:
                radial-gradient(1200px 600px at 20% -10%, rgba(220,38,38,0.12), transparent 50%),
                radial-gradient(1000px 500px at 100% 110%, rgba(220,38,38,0.08), transparent 50%),
                linear-gradient(160deg, var(--dark-900) 0%, var(--dark-800) 100%);
            padding: 24px;
            color: var(--text);
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--dark-800);
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 40px 36px;
            box-shadow: 0 30px 60px -20px rgba(0,0,0,0.6);
        }

        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 32px;
        }

        .brand-logo {
            width: 64px;
            height: 64px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            background: var(--dark-700);
            overflow: hidden;
            margin-bottom: 14px;
        }

        .brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .brand h1 {
            font-family: 'Chakra Petch', 'Inter', sans-serif;
            font-size: 1.35rem;
            font-weight: 700;
            color: #fff;
            letter-spacing: -0.02em;
        }

        .brand h1 span { color: var(--red-600); }

        .brand p {
            color: var(--muted);
            font-size: 0.82rem;
            margin-top: 4px;
        }

        .alert {
            border-radius: 10px;
            padding: 12px 14px;
            font-size: 0.86rem;
            margin-bottom: 20px;
            border: 1px solid transparent;
        }
        .alert-danger { background: rgba(220,38,38,0.1); color: #fca5a5; border-color: rgba(220,38,38,0.25); }
        .alert-success { background: rgba(34,197,94,0.1); color: #86efac; border-color: rgba(34,197,94,0.25); }

        .field { margin-bottom: 18px; }

        .field label {
            display: block;
            font-size: 0.82rem;
            font-weight: 500;
            color: #cbd5e1;
            margin-bottom: 7px;
        }

        .field input {
            width: 100%;
            padding: 12px 14px;
            font-size: 0.95rem;
            color: var(--text);
            background: var(--dark-700);
            border: 1px solid var(--line);
            border-radius: 10px;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            font-family: inherit;
        }

        .field input::placeholder { color: #475569; }
        .field input:focus {
            border-color: var(--red-600);
            box-shadow: 0 0 0 3px rgba(220,38,38,0.15);
        }
        .field input.is-invalid { border-color: #ef4444; }
        .invalid-feedback { color: #f87171; font-size: 0.78rem; margin-top: 5px; }

        .btn {
            width: 100%;
            padding: 13px;
            border: none;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            background: var(--red-600);
            color: #fff;
            cursor: pointer;
            transition: background 0.15s, transform 0.1s;
            margin-top: 4px;
        }
        .btn:hover { background: var(--red-dark); }
        .btn:active { transform: translateY(1px); }

        .extra {
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.82rem;
        }
        .extra a { color: var(--muted); text-decoration: none; }
        .extra a:hover { color: var(--red-600); }

        .foot {
            margin-top: 26px;
            text-align: center;
            font-size: 0.75rem;
            color: #475569;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="brand">
            <div class="brand-logo">
                <img src="{{ asset('images/logo-120.png') }}" alt="Fix-It">
            </div>
            <h1>Fix-It <span>Auto Services</span></h1>
            <p>Sign in to your dashboard</p>
        </div>

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="field">
                <label for="email">Email</label>
                <input type="text" id="email" name="email"
                       value="{{ old('email') }}" placeholder="you@example.com"
                       class="@error('email') is-invalid @enderror" required autofocus autocomplete="username">
                @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="field">
                <label for="password">Password</label>
                <input type="password" id="password" name="password"
                       placeholder="••••••••"
                       class="@error('password') is-invalid @enderror" required autocomplete="current-password">
                @error('password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="extra">
                <label style="display:flex;align-items:center;gap:6px;color:var(--muted);font-size:0.82rem;cursor:pointer;">
                    <input type="checkbox" name="remember" id="remember" style="accent-color:var(--red-600);width:14px;height:14px;">
                    Remember me
                </label>
            </div>

            <button type="submit" class="btn">Sign In</button>
        </form>

        <div class="foot">© {{ date('Y') }} Fix-It Auto Services</div>
    </div>
</body>
</html>
