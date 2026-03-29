<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Expired - Fix-It Auto Services</title>
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
        .expired-container {
            max-width: 500px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: center;
        }
        .expired-header {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            padding: 2rem;
        }
        .expired-body {
            padding: 2rem;
        }
        .expired-icon {
            font-size: 4rem;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        .btn-primary {
            background: linear-gradient(135deg, #3498db, #2980b9);
            border: none;
            padding: 0.75rem 2rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="expired-container">
        <div class="expired-header">
            <h1><i class="fas fa-car me-2"></i>Fix-It Auto Services</h1>
        </div>
        
        <div class="expired-body">
            <div class="expired-icon">
                <i class="fas fa-clock"></i>
            </div>
            
            <h2 class="mb-3">Form Link Expired</h2>
            
            <p class="text-muted mb-4">
                This customer registration form link has expired or is no longer valid.
                Form links are valid for 3 days from generation.
            </p>
            
            <div class="alert alert-info mb-4">
                <i class="fas fa-info-circle me-2"></i>
                <strong>What to do:</strong> Please contact Fix-It Auto Services directly to request a new registration form or to register as a customer.
            </div>
            
            <div class="contact-info mb-4">
                <h5 class="mb-3">Contact Information</h5>
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