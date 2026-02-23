<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Fix-It Auto Services Management')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --light-bg: #f8f9fa;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
        }
        
        .navbar-brand {
            font-weight: 700;
            color: var(--secondary-color) !important;
        }
        
        .sidebar {
            background: var(--primary-color);
            color: white;
            min-height: calc(100vh - 56px);
            padding: 0;
        }
        
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-left: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .sidebar .nav-link:hover {
            color: white;
            background: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--secondary-color);
        }
        
        .sidebar .nav-link.active {
            color: white;
            background: rgba(255, 255, 255, 0.15);
            border-left: 3px solid var(--secondary-color);
        }
        
        .sidebar .nav-link i {
            width: 24px;
            text-align: center;
            margin-right: 10px;
        }
        
        .stat-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card .card-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        
        .stat-card.bg-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-card.bg-success { background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); }
        .stat-card.bg-warning { background: linear-gradient(135deg, #f7971e 0%, #ffd200 100%); }
        .stat-card.bg-danger { background: linear-gradient(135deg, #ff416c 0%, #ff4b2b 100%); }
        .stat-card.bg-info { background: linear-gradient(135deg, #00c6ff 0%, #0072ff 100%); }
        .stat-card.bg-secondary { background: linear-gradient(135deg, #8e9eab 0%, #eef2f3 100%); }
        
        .table th {
            border-top: none;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .badge {
            padding: 6px 12px;
            font-weight: 500;
        }
        
        .btn-primary {
            background: var(--secondary-color);
            border: none;
            padding: 8px 20px;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .page-header {
            border-bottom: 2px solid var(--light-bg);
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        
        .customer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--secondary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
        }
        
        .vehicle-icon {
            font-size: 24px;
            color: var(--secondary-color);
        }
        
        .service-status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .status-scheduled { background: #e3f2fd; color: #1976d2; }
        .status-in-progress { background: #fff3e0; color: #f57c00; }
        .status-completed { background: #e8f5e9; color: #388e3c; }
        .status-cancelled { background: #ffebee; color: #d32f2f; }
        
        .payment-status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .payment-pending { background: #fff3e0; color: #f57c00; }
        .payment-partial { background: #e3f2fd; color: #1976d2; }
        .payment-paid { background: #e8f5e9; color: #388e3c; }
        .payment-overdue { background: #ffebee; color: #d32f2f; }
        
        .chart-container {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        
        @media (max-width: 768px) {
            .sidebar {
                min-height: auto;
            }
            
            .stat-card {
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="fas fa-car me-2"></i>
                Fix-It Auto Services
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <!-- Notification Bell -->
                    <li class="nav-item dropdown">
                    <!-- Quality Control & Compliance -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="qualityDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Quality & Compliance</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="qualityDropdown">
                            <a class="dropdown-item" href="{{ route("quality-control.dashboard") }}">
                                <i class="fas fa-tachometer-alt"></i> Quality Dashboard
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.checklists.index") }}">
                                <i class="fas fa-list-check"></i> Checklists
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.audits.index") }}">
                                <i class="fas fa-clipboard-list"></i> Quality Audits
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.ncrs.index") }}">
                                <i class="fas fa-exclamation-triangle"></i> NCRs
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.corrective-actions.index") }}">
                                <i class="fas fa-wrench"></i> Corrective Actions
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route("compliance.dashboard") }}">
                                <i class="fas fa-shield-alt"></i> Compliance Dashboard
                            </a>
                            <a class="dropdown-item" href="{{ route("compliance.standards.index") }}">
                                <i class="fas fa-book"></i> Standards
                            </a>
                            <a class="dropdown-item" href="{{ route("compliance.documents.index") }}">
                                <i class="fas fa-file-alt"></i> Documents
                            </a>
                            <a class="dropdown-item" href="{{ route("audit.dashboard") }}">
                                <i class="fas fa-search"></i> Audit Management
                            </a>
                        </div>
                    </li>
                        <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notificationCount">
                                3
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" style="width: 300px;">
                            <li class="dropdown-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Notifications</span>
                                    <a href="#" class="small text-decoration-none">Mark all as read</a>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-calendar-check text-success"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-semibold">Appointment Reminder</div>
                                            <div class="small text-muted">Your service appointment is tomorrow at 10:00 AM</div>
                                            <div class="small text-muted">2 hours ago</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-file-invoice-dollar text-primary"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-semibold">Invoice Ready</div>
                                            <div class="small text-muted">Your invoice #INV-2026-00123 is ready for payment</div>
                                            <div class="small text-muted">1 day ago</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="#">
                                    <div class="d-flex">
                                        <div class="flex-shrink-0">
                                            <i class="fas fa-gift text-warning"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <div class="fw-semibold">Loyalty Reward</div>
                                            <div class="small text-muted">You've earned enough points for a free oil change!</div>
                                            <div class="small text-muted">3 days ago</div>
                                        </div>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-item text-center">
                                <a href="#" class="text-decoration-none">View all notifications</a>
                            </li>
                        </ul>
                    </li>
                    
                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown">
                    <!-- Quality Control & Compliance -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="qualityDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Quality & Compliance</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="qualityDropdown">
                            <a class="dropdown-item" href="{{ route("quality-control.dashboard") }}">
                                <i class="fas fa-tachometer-alt"></i> Quality Dashboard
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.checklists.index") }}">
                                <i class="fas fa-list-check"></i> Checklists
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.audits.index") }}">
                                <i class="fas fa-clipboard-list"></i> Quality Audits
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.ncrs.index") }}">
                                <i class="fas fa-exclamation-triangle"></i> NCRs
                            </a>
                            <a class="dropdown-item" href="{{ route("quality-control.corrective-actions.index") }}">
                                <i class="fas fa-wrench"></i> Corrective Actions
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route("compliance.dashboard") }}">
                                <i class="fas fa-shield-alt"></i> Compliance Dashboard
                            </a>
                            <a class="dropdown-item" href="{{ route("compliance.standards.index") }}">
                                <i class="fas fa-book"></i> Standards
                            </a>
                            <a class="dropdown-item" href="{{ route("compliance.documents.index") }}">
                                <i class="fas fa-file-alt"></i> Documents
                            </a>
                            <a class="dropdown-item" href="{{ route("audit.dashboard") }}">
                                <i class="fas fa-search"></i> Audit Management
                            </a>
                        </div>
                    </li>
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            {{ auth()->user()->name ?? 'User' }}
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 d-none d-md-block sidebar">
                <div class="pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('customers.*') ? 'active' : '' }}" href="{{ route('customers.index') }}">
                                <i class="fas fa-users"></i> Customers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                                <i class="fas fa-calendar-alt"></i> Appointments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}" href="{{ route('work-orders.index') }}">
                                <i class="fas fa-wrench"></i> Work Orders
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}" href="{{ route('inspections.index') }}">
                                <i class="fas fa-car"></i> Vehicle Inspections
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                                <i class="fas fa-box"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                    </ul>
                    
                    <div class="mt-4 px-3">
                        <small class="text-muted">QUICK STATS</small>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between text-white-50 mb-1">
                                <small>Today's Appointments</small>
                                <small>12</small>
                            </div>
                            <div class="d-flex justify-content-between text-white-50 mb-1">
                                <small>Pending Services</small>
                                <small>8</small>
                            </div>
                            <div class="d-flex justify-content-between text-white-50">
                                <small>Revenue Today</small>
                                <small>$2,450</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 col-md-9 ms-sm-auto px-4 py-3">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                
                @if(session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show" role="alert">
                        {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Auto-dismiss alerts after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
    
    @stack('scripts')
</body>
</html>