<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Customer Portal - Fix-It Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .register-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .register-body {
            padding: 30px;
        }
        .brand-logo {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .brand-subtitle {
            opacity: 0.9;
            font-size: 0.9rem;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
        }
        .register-footer {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #eee;
            font-size: 0.9rem;
        }
        .alert {
            border-radius: 10px;
            border: none;
        }
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .step-indicator::before {
            content: '';
            position: absolute;
            top: 15px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 1;
        }
        .step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }
        .step-number {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 5px;
            font-weight: bold;
        }
        .step.active .step-number {
            background: #667eea;
            color: white;
        }
        .step-label {
            font-size: 0.8rem;
            color: #6c757d;
        }
        .step.active .step-label {
            color: #667eea;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="register-card">
        <div class="register-header">
            <div class="brand-logo">
                <i class="fas fa-car me-2"></i>Fix-It Auto
            </div>
            <div class="brand-subtitle">Customer Portal Registration</div>
        </div>
        
        <div class="register-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            
            <div class="step-indicator">
                <div class="step active">
                    <div class="step-number">1</div>
                    <div class="step-label">Verify Identity</div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-label">Create Account</div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-label">Complete</div>
                </div>
            </div>
            
            <h4 class="mb-4">Verify Your Identity</h4>
            <p class="text-muted mb-4">
                To create your customer portal account, we need to verify that you're an existing customer.
                Please enter the email or phone number we have on file.
            </p>
            
            <form method="POST" action="{{ route('portal.register.post') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="customer_email" class="form-label">Email Address on File</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control @error('customer_email') is-invalid @enderror" 
                               id="customer_email" name="customer_email" value="{{ old('customer_email') }}" 
                               placeholder="Enter the email we have on file" required>
                        @error('customer_email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">Use the email address you provided when visiting our shop.</small>
                </div>
                
                <div class="mb-3">
                    <label for="customer_phone" class="form-label">Phone Number on File</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-phone"></i></span>
                        <input type="tel" class="form-control @error('customer_phone') is-invalid @enderror" 
                               id="customer_phone" name="customer_phone" value="{{ old('customer_phone') }}" 
                               placeholder="Enter the phone number we have on file" required>
                        @error('customer_phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">Use the phone number you provided when visiting our shop.</small>
                </div>
                
                <div class="mb-3">
                    <label for="password" class="form-label">Create Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Create a secure password" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <small class="text-muted">Minimum 8 characters with letters and numbers.</small>
                </div>
                
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirm Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" 
                               id="password_confirmation" name="password_confirmation" 
                               placeholder="Confirm your password" required>
                    </div>
                </div>
                
                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" 
                           id="terms" name="terms" required>
                    <label class="form-check-label" for="terms">
                        I agree to the <a href="#" data-bs-toggle="modal" data-bs-target="#termsModal">Terms of Service</a> 
                        and <a href="#" data-bs-toggle="modal" data-bs-target="#privacyModal">Privacy Policy</a>
                    </label>
                    @error('terms')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-user-plus me-2"></i> Create Portal Account
                    </button>
                    <a href="{{ route('portal.login') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-sign-in-alt me-2"></i> Already have an account? Sign In
                    </a>
                </div>
            </form>
        </div>
        
        <div class="register-footer">
            <p class="mb-0 text-muted">
                <i class="fas fa-shield-alt me-1"></i> Secure Customer Portal
                <br>
                <small>© {{ date('Y') }} Fix-It Auto Services. All rights reserved.</small>
            </p>
        </div>
    </div>
    
    <!-- Terms Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Terms of Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Customer Portal Terms of Service</h6>
                    <p><strong>Last Updated:</strong> February 22, 2026</p>
                    
                    <h6>1. Account Registration</h6>
                    <p>To access the Fix-It Auto Services Customer Portal, you must be an existing customer with a valid service history. You agree to provide accurate information and keep your account credentials secure.</p>
                    
                    <h6>2. Portal Usage</h6>
                    <p>The portal is provided for your convenience to:</p>
                    <ul>
                        <li>View your vehicle service history</li>
                        <li>Schedule appointments</li>
                        <li>Request services</li>
                        <li>Communicate with our service team</li>
                        <li>Access shared documents</li>
                    </ul>
                    
                    <h6>3. Service Requests</h6>
                    <p>Service requests submitted through the portal are subject to availability and confirmation by our service team. We will contact you to confirm details and schedule.</p>
                    
                    <h6>4. Privacy</h6>
                    <p>Your personal information and vehicle data are protected according to our Privacy Policy. We do not share your information with third parties without your consent.</p>
                    
                    <h6>5. Limitation of Liability</h6>
                    <p>Fix-It Auto Services is not liable for any indirect, incidental, or consequential damages arising from portal usage.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Privacy Modal -->
    <div class="modal fade" id="privacyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Privacy Policy</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h6>Customer Portal Privacy Policy</h6>
                    <p><strong>Last Updated:</strong> February 22, 2026</p>
                    
                    <h6>1. Information We Collect</h6>
                    <p>We collect information necessary to provide you with automotive services:</p>
                    <ul>
                        <li>Contact information (name, email, phone)</li>
                        <li>Vehicle information (make, model, VIN, license plate)</li>
                        <li>Service history and records</li>
                        <li>Communication preferences</li>
                    </ul>
                    
                    <h6>2. How We Use Your Information</h6>
                    <p>Your information is used to:</p>
                    <ul>
                        <li>Provide automotive services and repairs</li>
                        <li>Schedule appointments and send reminders</li>
                        <li>Maintain service records and history</li>
                        <li>Send service notifications and updates</li>
                        <li>Improve our services</li>
                    </ul>
                    
                    <h6>3. Data Security</h6>
                    <p>We implement industry-standard security measures to protect your information. Your portal account is protected with encryption and secure authentication.</p>
                    
                    <h6>4. Data Retention</h6>
                    <p>We retain your service records as required by law and for ongoing service needs. You may request deletion of your portal account at any time.</p>
                    
                    <h6>5. Your Rights</h6>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access your personal information</li>
                        <li>Correct inaccurate information</li>
                        <li>Request deletion of your information</li>
                        <li>Opt-out of marketing communications</li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>