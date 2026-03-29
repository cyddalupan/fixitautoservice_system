<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Already Submitted - Fix-It Auto Services</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .submitted-container {
            max-width: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        .submitted-header {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
            padding: 2rem;
        }
        .submitted-body {
            padding: 2rem;
        }
        .submitted-icon {
            font-size: 4rem;
            color: #f39c12;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
        .token-info {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 1rem;
            margin: 1.5rem 0;
            border-left: 4px solid #3498db;
        }
    </style>
</head>
<body>
    <div class="submitted-container">
        <div class="submitted-header">
            <h1><i class="fas fa-car me-2"></i>Fix-It Auto Services</h1>
        </div>
        
        <div class="submitted-body">
            <div class="submitted-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            
            <h2 class="mb-3">Form Already Submitted</h2>
            
            <p class="text-muted mb-4">
                This customer registration form has already been submitted successfully.
                Each form link can only be used once for security purposes.
            </p>
            
            <div class="token-info">
                <h5 class="mb-2">Form Details</h5>
                <p class="mb-1">
                    <i class="fas fa-key me-2"></i>
                    <strong>Form Token:</strong> <code>{{ $token }}</code>
                </p>
                <p class="mb-0">
                    <i class="fas fa-calendar-alt me-2"></i>
                    <strong>Expires:</strong> {{ $expires_at }}
                </p>
            </div>
            
            <div class="alert alert-success mb-4">
                <i class="fas fa-check-circle me-2"></i>
                <strong>Submission Successful!</strong> Your customer registration has been received and processed.
            </div>
            
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>What happens next:</strong> Our team will review your registration and contact you if additional information is needed. You can expect to hear from us within 1-2 business days.
            </div>
            
            <div class="contact-info mb-4">
                <h5 class="mb-3">Need Assistance?</h5>
                <p class="mb-2">
                    <i class="fas fa-phone me-2"></i>
                    <strong>Phone:</strong> (02) 123-4567
                </p>
                <p class="mb-2">
                    <i class="fas fa-envelope me-2"></i>
                    <strong>Email:</strong> info@fixitautoservices.com
                </p>
                <p class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>
                    <strong>Address:</strong> 123 Auto Service St., Quezon City
                </p>
            </div>
            
            <a href="javascript:history.back()" class="btn btn-primary">
                <i class="fas fa-arrow-left me-2"></i> Go Back
            </a>
        </div>
    </div>
</body>
</html>