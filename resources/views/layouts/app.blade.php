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
    <meta http-equiv="Cache-Control" content="no-store">
    <meta name="version" content="v20260722-1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}">
    <title>@yield('title', 'Fix-It Auto Services Management')</title>

    <!-- Vite-built CSS (Tailwind + app styles) -->
    @vite('resources/css/app.css')

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Modern Redesign Styles --}}
    @include('partials.redesign-styles')
    {{-- Resizable Sidebar Styles --}}
    @include('partials.sidebar-resize-styles')
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

        /* Purple color utilities for Accounting role */
        .alert-purple {
            background-color: #f3e8ff;
            border-color: #d8b4fe;
            color: #6b21a8;
        }

        .alert-purple .alert-link {
            color: #4c1d95;
        }

        .bg-purple {
            background-color: #6f42c1 !important;
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .badge-purple {
            background-color: #6f42c1;
            color: white;
        }
    </style>
    <!-- Custom CSS -->
    <style>
        /* Sidebar active border colors by module */
        .sidebar .nav-link.active[href*="appointments"] { border-left-color: #3b82f6 !important; }
        .sidebar .nav-link.active[href*="inspections"] { border-left-color: #dc2626 !important; }
        .sidebar .nav-link.active[href*="estimates"] { border-left-color: #8b5cf6 !important; }
        .sidebar .nav-link.active[href*="job-orders"] { border-left-color: #f97316 !important; }
        .sidebar .nav-link.active[href*="service-records"] { border-left-color: #6366f1 !important; }
        .sidebar .nav-link.active[href*="archives"] { border-left-color: #6b7280 !important; }
        .sidebar .nav-link.active[href*="invoices"] { border-left-color: #10b981 !important; }
        .sidebar .nav-link.active[href*="payments"] { border-left-color: #10b981 !important; }

        :root {
            --primary-color: #0b0d12;
            --secondary-color: #dc2626;
            --accent-color: #dc2626;
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
            background-color: #0d0f14 !important; /* Near-black to match website navbar */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1) !important;
            z-index: 1030 !important;
        }

        .navbar-brand {
            color: #dc2626 !important; /* Brand red to match website */
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
            background-color: #0b0d12 !important; /* Near-black sidebar to match website */
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
                padding-left: 0 !important;
                padding-right: 0 !important;
            }

            .container-fluid > .row {
                margin-left: 0 !important;
                margin-right: 0 !important;
            }

            /* Contain the resize handle on mobile too */
            #sidebar {
                position: relative;
            }
        }

        /* Desktop sidebar pattern */
        @media (min-width: 768px) {
            /* Use flexbox for proper sidebar layout */
            .container-fluid > .row {
                display: flex !important;
                min-height: auto !important;
                flex-wrap: nowrap !important; /* Prevent sidebar from wrapping below content */
            }

            /* Desktop: sidebar fills outer container width */
            #sidebar {
                width: 100% !important;
            }

            /* Main content fills remaining space */
            .main-content-area {
                flex: 1 1 0 !important; /* Take remaining space, allow shrink below min-content */
                min-width: 0 !important; /* Allow content to shrink below natural min-width */
                padding: 20px !important;
                background-color: #f5f7fa !important;
                overflow-y: auto !important;
                min-height: auto !important;
            }
        }

        /* @media (min-width: 992px) removed — conflicts with JS resize inline style */
        /* Let the @media (min-width: 768px) rule handle all desktop widths */



        /* ============================================================
           SIDEBAR OUTER — positioned anchor for the resize handle
           This wraps the sticky sidebar + the absolute resize handle.
           position: relative creates a reliable containing block for
           the absolute handle child (avoids sticky+overflow quirks).
           flex: 0 0 ... controls width; JS resize updates this via outer.
           ============================================================ */
        .sidebar-outer {
            position: relative !important;
            flex: 0 0 250px;
            display: flex !important;
            padding-left: 0 !important;
        }

        /* ============================================================
           SIDEBAR — nested inside .sidebar-outer
           position: sticky so it follows the page when scrolling.
           width: 100% fills the .sidebar-outer container.
           No flex here — .sidebar-outer controls sizing.
           ============================================================ */
        .sidebar {
            background-color: #2c3e50 !important;
            color: white;
            padding: 0;
            position: sticky !important;
            top: 56px !important;
            height: calc(100vh - 56px) !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            z-index: 1020 !important;
            width: 100% !important;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8) !important;
            padding: 12px 20px;
            border-left: 3px solid transparent !important;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-left: 3px solid #dc2626 !important;
        }

        .sidebar .nav-link:hover {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.1) !important;
            border-left: 3px solid #dc2626 !important;
        }

        .sidebar .nav-link.active {
            color: #ffffff !important;
            background-color: rgba(255, 255, 255, 0.15) !important;
            border-left: 3px solid #dc2626 !important;
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

        .stat-card.bg-primary { background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%); }
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
        .table-dark th,
        .table-dark td {
            color: var(--bs-table-color, #fff);
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
            background: #b91c1c;
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
        
        /* QUOTATION COUNTER STYLES - Professional, non-expanding */
        .quotation-counter {
            position: absolute !important;
            top: 8px !important;
            right: 8px !important;
            font-size: 0.6rem !important;
            padding: 0.15rem 0.35rem !important;
            min-width: 18px !important;
            height: 18px !important;
            line-height: 1 !important;
            border-radius: 9px !important;
            border: 1px solid white !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2) !important;
            transform: scale(0.9) !important;
            transform-origin: top right !important;
            z-index: 100 !important;
            background-color: #dc3545 !important; /* Bootstrap danger color */
            color: white !important;
            font-weight: bold !important;
        }
        
        /* Ensure parent has relative positioning for absolute counter */
        .sidebar .nav-link {
            position: relative !important;
            padding-right: 30px !important; /* Make space for counter */
        }
        
        /* Active state adjustments */
        .sidebar .nav-link.active .quotation-counter {
            border-color: rgba(255,255,255,0.3) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important;
        }
        
        /* Hover effect */
        .sidebar .nav-link:hover .quotation-counter {
            transform: scale(1) !important;
            transition: transform 0.2s ease !important;
        }

    </style>

    {{-- Unread Record System Styles (used by job-orders, appointments, estimates, inspections, invoices) --}}
    @include('partials.unread.unread-styles')

    <!-- Page-specific styles -->
    @stack('styles')

    {{-- Anti-flash script: apply theme instantly before any rendering --}}
    <script>
        (function() {
            var saved = localStorage.getItem('fixit_theme');
            if (saved === 'dark' || (!saved && window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>
    <noscript><style>[data-theme="dark"] { display: none; }</style></noscript>
</head>
<body class="@yield('body-class')">
    <!-- jQuery (for AJAX and DOM manipulation) - MUST be BEFORE content scripts that use $() -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

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
                    <!-- Quality Control & Compliance -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="qualityDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-clipboard-check"></i>
                            <span>Quality & Compliance</span>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="qualityDropdown">
                            <a class="dropdown-item" href="{{ route('quality-control.dashboard.index') }}">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a class="dropdown-item" href="{{ route('quality-control.checklists.index') }}">
                                <i class="fas fa-clipboard-list"></i> Checklists
                            </a>
                            <a class="dropdown-item" href="{{ route('quality-control.job-order-quality.index') }}">
                                <i class="fas fa-clipboard-check"></i> Work Order Quality
                            </a>
                            <a class="dropdown-item" href="{{ route('quality-control.quality-checks.index') }}">
                                <i class="fas fa-check-double"></i> Quality Checks
                            </a>
                            <a class="dropdown-item" href="{{ route('quality-control.audits.index') }}">
                                <i class="fas fa-search"></i> Quality Audits
                            </a>
                            <a class="dropdown-item" href="{{ route('quality-control.ncrs.index') }}">
                                <i class="fas fa-exclamation-triangle"></i> Non-Conformance
                            </a>
                            <a class="dropdown-item" href="{{ route('quality-control.corrective-actions.index') }}">
                                <i class="fas fa-tools"></i> Corrective Actions
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="{{ route('compliance.dashboard') }}">
                                <i class="fas fa-file-contract"></i> Compliance
                            </a>
                        </div>
                    </li>
                    
                    <!-- Theme Toggle Button -->
                    <li class="nav-item d-flex align-items-center">
                        <button class="theme-toggle-btn" id="themeToggle" title="Toggle Dark/Light Mode">
                            <i class="fas fa-moon" id="themeIcon"></i>
                        </button>
                    </li>

                    <!-- Notification Bell -->
                    <li class="nav-item dropdown">
                        <a class="nav-link" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" title="Notifications">
                            <i class="fas fa-bell"></i>
                            <span class="badge rounded-pill" id="notificationCount">0</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" id="notificationDropdown">
                            <li class="dropdown-header">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold">Notifications</span>
                                    <a href="#" class="small" id="markAllRead">Mark all as read</a>
                                </div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li id="notificationItems">
                                <div class="dropdown-item text-muted">Loading…</div>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li class="dropdown-item view-all">
                                <a href="{{ route('appointments.index') }}" class="text-decoration-none">View all notifications</a>
                            </li>
                        </ul>
                    </li>

                    <!-- User Profile Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-car"></i>
                            <span>{{ Auth::user()->name ?? 'User' }}</span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-user me-2"></i> Profile
                            </a>
                            <a class="dropdown-item" href="#">
                                <i class="fas fa-cog me-2"></i> Settings
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                                </button>
                            </form>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Mobile sidebar overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Outer (positioned anchor for resize handle) -->
            <div class="sidebar-outer d-md-block" id="sidebarOuter">
                <!-- Sidebar -->
                <div class="sidebar" id="sidebar">
                    @include('partials.sidebar')

                    {{-- Collapse toggle button (nested inside .sidebar) --}}
                    <button class="sidebar-collapse-toggle" id="sidebarCollapseBtn" title="Toggle sidebar">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                </div>

                {{-- Drag resize handle --}}
                <div class="sidebar-resize-handle" id="sidebarResizeHandle">
                    <span class="handle-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </span>
                </div>
            </div>

            <!-- Main Content -->
            <div class="px-4 py-3 main-content-area" id="mainContent">
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

                @if(session('info'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ session('info') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

    @include('layouts.branch-indicator')
                @yield('content')
            </div>
        </div>
    </div>

    <!-- SweetAlert2 (for beautiful alerts and confirmations) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables (for table pagination and sorting) -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{-- Resizable Sidebar Scripts --}}
    @include('partials.sidebar-resize-scripts')
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Auto-dismiss alerts after 5 seconds (exclude alerts with .no-auto-dismiss class)
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert:not(.no-auto-dismiss)');
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

        // Mobile sidebar toggle is handled in sidebar-resize-scripts.blade.php
        // Only keep the close-on-link-click for mobile
        (function() {
            const sidebar = document.getElementById('sidebar');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            if (!sidebar || !sidebarOverlay) return;

            // Close sidebar when clicking overlay
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                sidebarOverlay.classList.remove('show');
            });

            // Close sidebar when clicking a link on mobile
            const sidebarLinks = sidebar.querySelectorAll('.nav-link');
            sidebarLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 768) {
                        sidebar.classList.remove('show');
                        sidebarOverlay.classList.remove('show');
                    }
                });
            });

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    sidebar.classList.remove('show');
                    sidebarOverlay.classList.remove('show');
                }
            });
        })();

        // Service Management Collapse State Persistence
        // Note: The initial state is now handled in sidebar-resize-scripts.blade.php
        // (runs at script-load time to prevent flash). This block only handles
        // persistence listeners and chevron updates for any external triggers.
        document.addEventListener('DOMContentLoaded', function() {
            const serviceManagementToggle = document.getElementById('serviceManagementToggle');
            const serviceManagementCollapse = document.getElementById('serviceManagementCollapse');
            const chevronIcon = serviceManagementToggle ? serviceManagementToggle.querySelector('.fa-chevron-down, .fa-chevron-up') : null;

            function updateChevron() {
                if (!chevronIcon) return;
                const isExpanded = serviceManagementCollapse && serviceManagementCollapse.classList.contains('show');
                if (isExpanded) {
                    chevronIcon.classList.remove('fa-chevron-down');
                    chevronIcon.classList.add('fa-chevron-up');
                } else {
                    chevronIcon.classList.remove('fa-chevron-up');
                    chevronIcon.classList.add('fa-chevron-down');
                }
            }

            if (serviceManagementToggle && serviceManagementCollapse) {
                // Listen for collapse events
                serviceManagementCollapse.addEventListener('show.bs.collapse', function() {
                    localStorage.setItem('serviceManagementCollapseState', 'expanded');
                    serviceManagementToggle.setAttribute('aria-expanded', 'true');
                    updateChevron();
                });

                serviceManagementCollapse.addEventListener('hide.bs.collapse', function() {
                    localStorage.setItem('serviceManagementCollapseState', 'collapsed');
                    serviceManagementToggle.setAttribute('aria-expanded', 'false');
                    updateChevron();
                });

                // Initial chevron sync (in case sidebar-resize-scripts didn't run first)
                updateChevron();
            }
        });
        
        // Quotation Counter Update Function
        function updateQuotationCounter() {
            fetch('{{ route("quotations.pending-count") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                const counterElement = document.getElementById('quotation-counter');
                if (data.count > 0) {
                    if (counterElement) {
                        counterElement.textContent = data.count;
                    } else {
                        // Create counter if it doesn't exist
                        const quotationsLink = document.querySelector('a[href*="quotations"]');
                        if (quotationsLink) {
                            const badge = document.createElement('sup');
                            badge.className = 'quotation-counter';
                            badge.id = 'quotation-counter';
                            badge.textContent = data.count;
                            quotationsLink.appendChild(badge);
                        }
                    }
                } else {
                    // Remove counter if count is 0
                    if (counterElement) {
                        counterElement.remove();
                    }
                }
            })
            .catch(error => console.error('Error updating quotation counter:', error));
        }
        
        // Update counter every 30 seconds
        setInterval(updateQuotationCounter, 30000);
        
        // Also update when the page becomes visible again
        document.addEventListener('visibilitychange', function() {
            if (!document.hidden) {
                updateQuotationCounter();
            }
        });
    </script>

    <!-- Dark Mode Toggle Script -->
    <script>
        (function() {
            const STORAGE_KEY = 'fixit_theme';
            const DARK_THEME = 'dark';
            const LIGHT_THEME = 'light';

            /**
             * Determine initial theme:
             * 1. Use localStorage if previously saved
             * 2. Fall back to system preference (prefers-color-scheme)
             * 3. Default to light
             */
            function getInitialTheme() {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved === DARK_THEME || saved === LIGHT_THEME) {
                    return saved;
                }
                // Auto-detect system preference
                if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    return DARK_THEME;
                }
                return LIGHT_THEME;
            }

            function setTheme(theme) {
                const icon = document.getElementById('themeIcon');
                if (theme === DARK_THEME) {
                    document.documentElement.setAttribute('data-theme', DARK_THEME);
                    if (icon) icon.className = 'fas fa-sun';
                } else {
                    document.documentElement.removeAttribute('data-theme');
                    if (icon) icon.className = 'fas fa-moon';
                }
                localStorage.setItem(STORAGE_KEY, theme);
            }

            function toggleTheme() {
                const current = localStorage.getItem(STORAGE_KEY) || LIGHT_THEME;
                const next = current === DARK_THEME ? LIGHT_THEME : DARK_THEME;
                setTheme(next);
            }

            // Apply theme immediately on page load (before DOMContentLoaded)
            setTheme(getInitialTheme());

            // Bind toggle button
            document.addEventListener('DOMContentLoaded', function() {
                const btn = document.getElementById('themeToggle');
                if (btn) {
                    btn.addEventListener('click', toggleTheme);
                }

                // Listen for system theme changes (e.g., user changes OS setting)
                if (window.matchMedia) {
                    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
                        // Only auto-switch if user hasn't manually set a preference
                        if (!localStorage.getItem(STORAGE_KEY)) {
                            setTheme(e.matches ? DARK_THEME : LIGHT_THEME);
                        }
                    });
                }

                // Add keyboard shortcut (Alt+T) to toggle theme
                document.addEventListener('keydown', function(e) {
                    if (e.altKey && e.key.toLowerCase() === 't') {
                        e.preventDefault();
                        toggleTheme();
                    }
                });
            });
        })();
    </script>

        <!-- Notification bell behavior: real data from /api/notifications + mark-all-read -->
    <script>
        (function () {
            var itemsEl = document.getElementById('notificationItems');
            var badge = document.getElementById('notificationCount');

            function renderCount(n) {
                if (badge) {
                    badge.textContent = n > 0 ? n : '';
                    badge.style.display = n > 0 ? '' : 'none';
                }
            }

            function renderItems(items) {
                if (!itemsEl) return;
                if (!items || items.length === 0) {
                    itemsEl.innerHTML = '<div class="dropdown-item text-muted">No notifications yet.</div>';
                    return;
                }
                itemsEl.innerHTML = items.map(function (n) {
                    var icon = '<i class="fas fa-calendar-check text-success"></i>';
                    var tag = n.read ? '' : '<span class="badge bg-primary ms-1">new</span>';
                    var time = n.time ? '<div class="small text-gray-600">' + n.time + '</div>' : '';
                    return '<a class="dropdown-item" href="' + (n.link || '/appointments') + '">' +
                        '<div class="flex-shrink-0 pe-3">' + icon + '</div>' +
                        '<div class="flex-grow-1 min-w-0">' +
                            '<div class="fw-semibold">' + (n.message || 'Notification') + tag + '</div>' +
                            time +
                        '</div>' +
                    '</a>';
                }).join('');
            }

            function loadNotifications() {
                fetch('/api/notifications', {
                    headers: { 'Accept': 'application/json' }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    renderCount(data.unread_count || 0);
                    renderItems(data.items || []);
                })
                .catch(function () {});
            }

            document.addEventListener('DOMContentLoaded', function () {
                loadNotifications();

                var mark = document.getElementById('markAllRead');
                if (mark) {
                    mark.addEventListener('click', function (e) {
                        e.preventDefault();
                        fetch('/api/notifications/read-all', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : ''
                            }
                        })
                        .then(function (r) { return r.json(); })
                        .then(function (data) {
                            renderCount(data.unread_count || 0);
                            loadNotifications();
                        })
                        .catch(function () {});
                    });
                }
            });
        })();
    </script>

    <!-- Vite-built JS (app bundle with Cropper.js, etc.) -->
    @vite('resources/js/app.js')

    {{-- Unread Record System JS (initUnreadSystem / markRecordAsRead / markAllAsRead) --}}
    @include('partials.unread.unread-scripts')

    {{-- Auto-uppercase all fill-up / fill-in fields (app-wide) --}}
    @include('partials.uppercase-inputs')

    @stack('scripts')
</body>
</html>