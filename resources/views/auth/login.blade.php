<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fix-It Auto Services - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2d3748;
            --secondary-color: #4a5568;
            --accent-color: #3182ce;
            --success-color: #38a169;
            --warning-color: #d69e2e;
            --danger-color: #e53e3e;
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --gradient-dark: linear-gradient(135deg, #141e30 0%, #243b55 100%);
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: var(--gradient-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }
        
        .login-container {
            display: flex;
            max-width: 1200px;
            width: 100%;
            background: white;
            border-radius: 25px;
            overflow: hidden;
            box-shadow: 0 25px 75px rgba(0, 0, 0, 0.4);
            min-height: 700px;
        }
        
        .login-left {
            flex: 1;
            background: var(--gradient-primary);
            color: white;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255, 255, 255, 0.1);
            transform: rotate(45deg);
        }
        
        .login-right {
            flex: 1;
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .brand-logo {
            font-size: 2.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .brand-logo i {
            background: rgba(255, 255, 255, 0.2);
            padding: 15px;
            border-radius: 15px;
            font-size: 2rem;
        }
        
        .brand-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 40px;
        }
        
        .features-list {
            list-style: none;
            margin-top: 40px;
        }
        
        .features-list li {
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1rem;
        }
        
        .features-list i {
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            border-radius: 10px;
            font-size: 1.2rem;
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .login-header h2 {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: var(--secondary-color);
            font-size: 1rem;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            display: block;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e2e8f0;
            border-right: none;
            color: var(--secondary-color);
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-left: none;
            padding: 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
            height: 52px;
        }
        
        .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 0.25rem rgba(49, 130, 206, 0.25);
        }
        
        .form-control.is-invalid {
            border-color: var(--danger-color);
        }
        
        .btn-login {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .role-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 30px;
            justify-content: center;
        }
        
        .role-badge {
            background: #f8f9fa;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 20px;
            text-align: center;
            flex: 1;
            min-width: 120px;
            transition: all 0.3s ease;
        }
        
        .role-badge:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .role-badge i {
            font-size: 1.5rem;
            margin-bottom: 8px;
            display: block;
        }
        
        .role-badge.super-admin {
            border-color: #e53e3e;
            color: #e53e3e;
        }
        
        .role-badge.admin {
            border-color: #d69e2e;
            color: #d69e2e;
        }
        
        .role-badge.office-staff {
            border-color: #3182ce;
            color: #3182ce;
        }
        
        .role-badge.technician {
            border-color: #38a169;
            color: #38a169;
        }
        
        .role-badge.accounting {
            border-color: #805ad5;
            color: #805ad5;
        }
        
        .role-title {
            font-weight: 600;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .role-desc {
            font-size: 0.8rem;
            opacity: 0.8;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            padding: 15px;
            margin-bottom: 25px;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
            color: var(--secondary-color);
            font-size: 0.9rem;
        }
        
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 30px;
            border: 2px dashed #e2e8f0;
        }
        
        .demo-credentials h6 {
            color: var(--primary-color);
            margin-bottom: 15px;
            font-weight: 600;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .credential-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .credential-label {
            font-weight: 500;
            color: var(--secondary-color);
        }
        
        .credential-value {
            font-weight: 600;
            color: var(--primary-color);
            font-family: monospace;
        }
        
        @media (max-width: 992px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }
            
            .login-left, .login-right {
                padding: 40px 30px;
            }
            
            .brand-logo {
                font-size: 2.2rem;
            }
        }
        
        @media (max-width: 576px) {
            .login-left, .login-right {
                padding: 30px 20px;
            }
            
            .brand-logo {
                font-size: 1.8rem;
            }
            
            .login-header h2 {
                font-size: 1.8rem;
            }
            
            .role-badges {
                flex-direction: column;
            }
        }
        
        .floating-shapes {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            overflow: hidden;
            z-index: 0;
        }
        
        .shape {
            position: absolute;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }
        
        .shape-1 {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
        }
        
        .shape-2 {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
        }
        
        .shape-3 {
            width: 150px;
            height: 150px;
            top: 50%;
            left: 20%;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side: Brand & Features -->
        <div class="login-left">
            <div class="floating-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
            </div>
            
            <div class="brand-logo">
                <i class="fas fa-car"></i>
                Fix-It Auto Services
            </div>
            
            <p class="brand-subtitle">
                Professional Automotive Service Management System
            </p>
            
            <ul class="features-list">
                <li>
                    <i class="fas fa-cogs"></i>
                    <div>
                        <strong>Complete Service Management</strong>
                        <div class="small">From appointment to delivery</div>
                    </div>
                </li>
                <li>
                    <i class="fas fa-users-cog"></i>
                    <div>
                        <strong>Role-Based Access Control</strong>
                        <div class="small">5 distinct user roles with specific permissions</div>
                    </div>
                </li>
                <li>
                    <i class="fas fa-chart-line"></i>
                    <div>
                        <strong>Real-Time Analytics</strong>
                        <div class="small">Business intelligence and reporting</div>
                    </div>
                </li>
                <li>
                    <i class="fas fa-mobile-alt"></i>
                    <div>
                        <strong>Mobile Responsive</strong>
                        <div class="small">Access from any device, anywhere</div>
                    </div>
                </li>
            </ul>
        </div>
        
        <!-- Right Side: Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Sign in to access your dashboard</p>
            </div>
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                
                <div class="form-group">
                    <label for="email" class="form-label">Email or Employee ID</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                        <input type="text" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="Enter your email address or employee ID"
                               required
                               autofocus>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="form-text text-muted mt-1">
                        <i class="fas fa-info-circle me-1"></i>
                        You can use: Email, Employee ID, or "Admin" for super admin access
                    </small>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="Enter your password"
                               required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="form-group form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                
                <button type="submit" class="btn btn-login">
                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                </button>
            </form>
            
            <!-- Demo Credentials -->
            <div class="demo-credentials">
                <h6><i class="fas fa-key me-2"></i>Super Admin Credentials (For You)</h6>
                <div class="credential-item">
                    <span class="credential-label">Username/Email:</span>
                    <span class="credential-value">Admin</span>
                </div>
                <div class="credential-item">
                    <span class="credential-label">Password:</span>
                    <span class="credential-value">Admin123</span>
                </div>
                <div class="small text-muted mt-2">
                    <i class="fas fa-info-circle me-1"></i>
                    Use these credentials to login as Super Admin
                </div>
            </div>
            
            <!-- Role Badges -->
            <div class="role-badges">
                <div class="role-badge super-admin">
                    <i class="fas fa-crown"></i>
                    <div class="role-title">Super Admin</div>
                    <div class="role-desc">Full System Access</div>
                </div>
                <div class="role-badge admin">
                    <i class="fas fa-user-tie"></i>
                    <div class="role-title">Admin</div>
                    <div class="role-desc">Shop Management</div>
                </div>
                <div class="role-badge office-staff">
                    <i class="fas fa-user-friends"></i>
                    <div class="role-title">Office Staff</div>
                    <div class="role-desc">Customer Service</div>
                </div>
                <div class="role-badge technician">
                    <i class="fas fa-tools"></i>
                    <div class="role-title">Technician</div>
                    <div class="role-desc">Repair & Service</div>
                </div>
                <div class="role-badge accounting">
                    <i class="fas fa-calculator"></i>
                    <div class="role-title">Accounting</div>
                    <div class="role-desc">Finance