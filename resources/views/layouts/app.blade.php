<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Cache Control Meta Tags -->
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Fix-It Auto Services Management')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <style>
        /* SIMPLE PAGINATION FIX - Prevent expansion, force fixed sizes */
        .pagination {
            display: inline-flex !important;
            flex-wrap: nowrap !important;
            justify-content: center !important;
            align-items: center !important;
            gap: 4px !important;
            margin: 0 !important;
            padding: 0 !important;
            width: auto !important;
            max-width: none !important;
        }

        .pagination .page-item {
            display: inline-block !important;
            margin: 0 !important;
            padding: 0 !important;
            width: auto !important;
            flex: none !important;
        }

        .pagination .page-link {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 38px !important;
            width: 38px !important;
            min-width: 38px !important;
            max-width: 38px !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 4px !important;
            border: 1px solid #dee2e6 !important;
            color: #2c3e50 !important;
            text-decoration: none !important;
            box-sizing: border-box !important;
            font-size: 14px !important;
            line-height: 1 !important;
        }

        .pagination .page-link:hover {
            background-color: #f8f9fa !important;
        }

        .pagination .page-item.active .page-link {
            background-color: #2c3e50 !important;
            border-color: #2c3e50 !important;
            color: white !important;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d !important;
            pointer-events: none !important;
            background-color: #fff !important;
        }

        /* Arrow icons */
        .pagination .page-link i {
            font-size: 14px !important;
            line-height: 1 !important;
            display: inline-block !important;
        }

        /* Pagination container - don't let it expand */
        .pagination-container {
            display: inline-block !important;
            width: auto !important;
            min-width: fit-content !important;
            padding: 10px 0 !important;
            margin: 0 auto !important;
        }

        /* Override any Bootstrap container classes */
        .w-100 .pagination-container,
        .w-md-auto .pagination-container {
            width: auto !important;
            display: inline-block !important;
        }

        /* Remove debug borders */
        /* .pagination-container, .pagination, .pagination .page-item {
            border: none !important;
            background: transparent !important;
        } */
    </style>
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
            /* padding-top removed - handled by fixed positioning */
        }

        .navbar-brand {
            font-weight: 700;
            color: var(--secondary-color) !important;
        }

        /* Body padding for fixed navbar */
        body {
            padding-top: 56px !important;
        }

        /* ENSURE CONSISTENT NAVBAR ACROSS ALL PAGES */
        .navbar {
            background-color: #343a40 !important; /* Bootstrap bg-dark color */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            z-index: 1030 !important;
        }

        .navbar-brand {
            color: #3498db !important; /* Consistent brand color */
            font-size: 1.25rem !important;
        }

        .navbar-nav .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
        }

        .navbar-nav .nav-link:hover {
            color: #ffffff !important;
        }

        /* ENSURE CONSISTENT SIDEBAR ACROSS ALL PAGES */
        .sidebar {
            background-color: #2c3e50 !important; /* Consistent sidebar color */
            /* Remove fixed height to prevent flex stretching */
            /* min-height: calc(100vh - 56px) !important; */
            position: sticky !important;
            top: 56px !important;
            z-index: 1020 !important;
            transition: transform 0.3s ease-in-out;
        }

        /* Mobile sidebar styles */
        @media (max-width: 767.98px) {
            #sidebar {
                position: fixed !important;
                top: 56px !important;
                left: 0 !important;
                width: 250px !important;
                height: calc(100vh - 56px) !important;
                transform: translateX(-100%);
                z-index: 1040 !important;
                overflow-y: auto !important;
                background-color: #2c3e50 !important;
                transition: transform 0.3s ease-in-out !important;
            }

            #sidebar.show {
                transform: translateX(0) !important;
            }

            /* Overlay for mobile sidebar */
            .sidebar-overlay {
                position: fixed;
                top: 56px;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                z-index: 1039;
                display: none;
                transition: opacity 0.3s ease-in-out !important;
            }

            .sidebar-overlay.show {
                display: block;
                opacity: 1 !important;
            }

            /* On mobile, main content should not have left margin */
            .main-content-area {
                padding: 15px !important;
                background-color: #f5f7fa !important;
            }

            /* Container should not have extra padding */
            .container-fluid {
                padding-top: 0 !important;
            }
        }

        /* Desktop sidebar pattern */
        @media (min-width: 768px) {
            /* Use flexbox for proper sidebar layout */
            .container-fluid > .row {
                display: flex !important;
                /* align-items: flex-start !important; */ /* Prevent stretching of children */
                /* Remove min-height to prevent stretching issues */
                /* min-height: calc(100vh - 56px) !important; */
            }

            /* Sidebar stays sticky */
            .sidebar {
                position: sticky !important;
                top: 56px !important;
                height: calc(100vh - 56px) !important;
                overflow-y: auto !important;
                z-index: 1000 !important;
                background-color: #2c3e50 !important;
                flex: 0 0 250px !important; /* Fixed width */
            }

            /* Main content fills remaining space */
            .main-content-area {
                flex: 1 !important; /* Take remaining space */
                padding: 20px !important;
                /* Remove min-height to prevent card-body stretching */
                /* min-height: calc(100vh - 56px) !important; */
                background-color: #f5f7fa !important;
                overflow-y: auto !important;
            }
        }

        /* Adjust for larger screens */
        @media (min-width: 992px) {
            .sidebar {
                flex: 0 0 200px !important; /* Slightly narrower on larger screens */
            }
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            border-left: 3px solid transparent !important;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-left: 3px solid #3498db !important;
        }

        .container-fluid > .row {
            /* Remove forced minimum height - let content determine height naturally */
            min-height: auto;
        }

        .sidebar {
            background-color: #2c3e50 !important; /* Consistent sidebar color */
            color: white;
            padding: 0;
            /* Remove fixed height to prevent flex stretching */
            /* min-height: calc(100vh - 56px) !important; */
            position: sticky !important;
            top: 56px !important;
            z-index: 1020 !important;
            background-attachment: local;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 12px 20px;
            border-left: 3px solid transparent !important;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-left: 3px solid #3498db !important;
        }

        .sidebar .nav-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.15) !important;
            border-left: 3px solid #3498db !important;
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

        /* PAGE-SPECIFIC BODY CLASSES */
        body.inventory-page {
            /* Add inventory page body styles here */
            background-color: #f8fafc !important;
        }

        body.hr-payroll-page {
            /* Add HR Payroll page body styles here */
            background-color: #f0f9ff !important;
        }

        /* PAGE-SPECIFIC CONTENT AREA STYLES */
        .inventory-content {
            /* Inventory page content area styles */
            background-color: white !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
            /* Prevent excessive stretching */
            min-height: auto !important;
            height: auto !important;
        }

        .hr-payroll-content {
            /* HR Payroll page content area styles */
            background-color: white !important;
            border-radius: 8px !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05) !important;
            border-left: 4px solid #38b2ac !important;
            /* Prevent excessive stretching */
            min-height: auto !important;
            height: auto !important;
        }

        /* FIX FOR STRETCHED ELEMENTS ON SPECIFIC PAGES */

        /* Inventory Page Specific Fixes */
        body.inventory-page .card {
            /* Prevent cards from stretching too much */
            min-height: auto !important;
            height: auto !important;
        }

        body.inventory-page .card-body {
            /* Fix card-body height issue */
            min-height: auto !important;
            height: auto !important;
            max-height: none !important;
        }

        body.inventory-page .table-responsive {
            /* Allow tables to scroll instead of stretching */
            max-height: 600px !important;
            overflow-y: auto !important;
        }

        body.inventory-page .form-group {
            /* Keep form elements compact */
            margin-bottom: 1rem !important;
        }

        /* HR Payroll Page Specific Fixes */
        body.hr-payroll-page .card {
            /* Prevent cards from stretching too much */
            min-height: auto !important;
            height: auto !important;
        }

        body.hr-payroll-page .card-body {
            /* Fix card-body height issue */
            min-height: auto !important;
            height: auto !important;
            max-height: none !important;
        }

        body.hr-payroll-page .table-responsive {
            /* Allow tables to scroll instead of stretching */
            max-height: 500px !important;
            overflow-y: auto !important;
        }

        body.hr-payroll-page .stat-card {
            /* Keep stat cards compact */
            min-height: 120px !important;
            max-height: 140px !important;
        }

        body.hr-payroll-page .stat-card .card-body {
            /* Stat card body should also be compact */
            min-height: auto !important;
            height: auto !important;
            padding: 1rem !important;
        }

        /* GENERAL FIX FOR ALL PAGES */
        .main-content-area {
            /* Let content determine its own height */
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
        <div class="container-fluid">
            <!-- Mobile sidebar toggle button -->
            <button class="btn btn-dark d-md-none me-2" type="button" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>

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
                            <a class="dropdown-item" href="{{ route("quality-control.dashboard.index") }}">
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
                                            <div class="small text-gray-600">Your service appointment is tomorrow at 10:00 AM</div>
                                            <div class="small text-gray-600">2 hours ago</div>
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
                                            <div class="small text-gray-600">Your invoice #INV-2026-00123 is ready for payment</div>
                                            <div class="small text-gray-600">1 day ago</div>
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
                                            <div class="small text-gray-600">You've earned enough points for a free oil change!</div>
                                            <div class="small text-gray-600">3 days ago</div>
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
                            <a class="dropdown-item" href="{{ route("quality-control.dashboard.index") }}">
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

    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-2 col-md-3 sidebar d-md-block" id="sidebar">
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
                            @php
                                // Check if current route is a Service Management route
                                $isServiceManagementRoute = request()->routeIs([
                                    'appointments.*',
                                    'estimates.*', 
                                    'work-orders.*',
                                    'invoices.*',
                                    'payments.*',
                                    'inspections.*'
                                ]);
                                
                                // Check localStorage for saved state (will be handled by JavaScript)
                                // For initial load, expand if we're on a Service Management page
                                $shouldExpand = $isServiceManagementRoute;
                            @endphp
                            
                            <a class="nav-link" data-bs-toggle="collapse" href="#serviceManagementCollapse" role="button" 
                               aria-expanded="{{ $shouldExpand ? 'true' : 'false' }}" 
                               aria-controls="serviceManagementCollapse"
                               id="serviceManagementToggle">
                                <i class="fas fa-cogs"></i> Service Management
                                <i class="fas fa-chevron-{{ $shouldExpand ? 'up' : 'down' }} float-end mt-1"></i>
                            </a>
                            <div class="collapse {{ $shouldExpand ? 'show' : '' }}" id="serviceManagementCollapse">
                                <ul class="nav flex-column ms-4">
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('appointments.*') ? 'active' : '' }}" href="{{ route('appointments.index') }}">
                                            <i class="fas fa-calendar-alt"></i> Appointments
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('inspections.*') ? 'active' : '' }}" href="{{ route('inspections.index') }}">
                                            <i class="fas fa-car"></i> Vehicle Inspections
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('estimates.*') ? 'active' : '' }}" href="{{ route('estimates.index') }}">
                                            <i class="fas fa-file-invoice-dollar"></i> Estimates
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('work-orders.*') ? 'active' : '' }}" href="{{ route('work-orders.index') }}">
                                            <i class="fas fa-wrench"></i> Work Orders
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('invoices.*') ? 'active' : '' }}" href="{{ route('invoices.index') }}">
                                            <i class="fas fa-file-invoice"></i> Invoices
                                        </a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link {{ request()->routeIs('payments.*') ? 'active' : '' }}" href="{{ route('payments.index') }}">
                                            <i class="fas fa-credit-card"></i> Payments
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('inventory.*') ? 'active' : '' }}" href="{{ route('inventory.index') }}">
                                <i class="fas fa-box"></i> Inventory
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('personnel.*') ? 'active' : '' }}" href="{{ route('personnel.index') }}">
                                <i class="fas fa-users"></i> Personnel
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('hr-payroll.*') ? 'active' : '' }}" href="{{ route('hr-payroll.dashboard') }}">
                                <i class="fas fa-users-cog"></i> HR Payroll
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                    </ul>

                    <div class="mt-4 px-3">
                        <small class="text-gray-600">QUICK STATS</small>
                        <div class="mt-2">
                            <div class="d-flex justify-content-between text-gray-700 mb-1">
                                <small>Today's Appointments</small>
                                <small>12</small>
                            </div>
                            <div class="d-flex justify-content-between text-gray-700 mb-1">
                                <small>Pending Services</small>
                                <small>8</small>
                            </div>
                            <div class="d-flex justify-content-between text-gray-700">
                                <small>Revenue Today</small>
                                <small>₱2,450</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-10 col-md-9 px-4 py-3 main-content-area">
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

    <!-- jQuery (for AJAX and DOM manipulation) - MUST BE BEFORE Bootstrap and DataTables -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- SweetAlert2 (for beautiful alerts and confirmations) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- DataTables (for table pagination and sorting) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
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
        
        // Sidebar toggle functionality for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('show');
                sidebarOverlay.classList.toggle('show');
            });
            
            // Close sidebar when clicking overlay
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });
            
            // Close sidebar when clicking a link on mobile
            if (window.innerWidth < 768) {
                const sidebarLinks = sidebar.querySelectorAll('.nav-link');
                sidebarLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    });
                });
            }
            
            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    // On desktop, ensure sidebar is visible
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                }
            });
        }
        
        // Service Management Collapse State Persistence
        document.addEventListener('DOMContentLoaded', function() {
            const serviceManagementToggle = document.getElementById('serviceManagementToggle');
            const serviceManagementCollapse = document.getElementById('serviceManagementCollapse');
            const chevronIcon = serviceManagementToggle ? serviceManagementToggle.querySelector('.fa-chevron-down, .fa-chevron-up') : null;
            
            if (serviceManagementToggle && serviceManagementCollapse) {
                // Check if we're on a Service Management page
                const isServiceManagementPage = window.location.pathname.includes('/appointments') ||
                                               window.location.pathname.includes('/estimates') ||
                                               window.location.pathname.includes('/work-orders') ||
                                               window.location.pathname.includes('/invoices') ||
                                               window.location.pathname.includes('/payments') ||
                                               window.location.pathname.includes('/inspections');
                
                // Check localStorage for saved state
                const savedState = localStorage.getItem('serviceManagementCollapseState');
                
                // Determine initial state: saved state OR if we're on a Service Management page
                const shouldBeExpanded = savedState === 'expanded' || (savedState === null && isServiceManagementPage);
                
                // Set initial state
                if (shouldBeExpanded && !serviceManagementCollapse.classList.contains('show')) {
                    serviceManagementCollapse.classList.add('show');
                    serviceManagementToggle.setAttribute('aria-expanded', 'true');
                    if (chevronIcon) {
                        chevronIcon.classList.remove('fa-chevron-down');
                        chevronIcon.classList.add('fa-chevron-up');
                    }
                }
                
                // Listen for collapse events
                serviceManagementCollapse.addEventListener('show.bs.collapse', function() {
                    localStorage.setItem('serviceManagementCollapseState', 'expanded');
                    serviceManagementToggle.setAttribute('aria-expanded', 'true');
                    if (chevronIcon) {
                        chevronIcon.classList.remove('fa-chevron-down');
                        chevronIcon.classList.add('fa-chevron-up');
                    }
                });
                
                serviceManagementCollapse.addEventListener('hide.bs.collapse', function() {
                    localStorage.setItem('serviceManagementCollapseState', 'collapsed');
                    serviceManagementToggle.setAttribute('aria-expanded', 'false');
                    if (chevronIcon) {
                        chevronIcon.classList.remove('fa-chevron-up');
                        chevronIcon.classList.add('fa-chevron-down');
                    }
                });
                
                // Also update state when clicking the toggle directly (for better UX)
                serviceManagementToggle.addEventListener('click', function() {
                    // The Bootstrap collapse event will handle the state, but we need to update chevron
                    setTimeout(() => {
                        const isExpanded = serviceManagementCollapse.classList.contains('show');
                        if (chevronIcon) {
                            if (isExpanded) {
                                chevronIcon.classList.remove('fa-chevron-down');
                                chevronIcon.classList.add('fa-chevron-up');
                            } else {
                                chevronIcon.classList.remove('fa-chevron-up');
                                chevronIcon.classList.add('fa-chevron-down');
                            }
                        }
                    }, 50); // Small delay to ensure Bootstrap has updated the classes
                });
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>