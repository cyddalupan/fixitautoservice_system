@extends('layouts.app')

@section('title', 'Dashboard - Fix-It Auto Services')

@push('styles')
<style>
    /* Timeline Styles */
    .timeline {
        position: relative;
        padding-left: 30px;
    }
    .timeline-item {
        position: relative;
        padding-bottom: 10px;
    }
    .timeline-marker {
        position: absolute;
        left: -30px;
        top: 0;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        z-index: 2;
    }
    .timeline-content {
        position: relative;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        border-left: 3px solid #17a2b8;
    }
    .timeline-item:not(:last-child) .timeline-content::after {
        content: '';
        position: absolute;
        left: -30px;
        top: 20px;
        bottom: -10px;
        width: 2px;
        background: #dee2e6;
        z-index: 1;
    }
    
    /* Gradient Headers */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .bg-gradient-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .bg-gradient-success {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    
    /* Empty State */
    .empty-state-icon {
        opacity: 0.3;
    }
    
    /* Work Order Icon */
    .work-order-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1.2rem;
    }
    
    /* Customer Avatar */
    .customer-avatar {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 0.9rem;
        font-weight: bold;
    }
    
    /* Vehicle Icon */
    .vehicle-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 1rem;
    }
    
    /* Service Type Color */
    .service-type-color {
        width: 10px;
        height: 10px;
        border-radius: 2px;
    }
    
    /* Chart Container */
    .chart-container {
        position: relative;
        height: 160px;
    }
    
    /* Table Improvements */
    .table > :not(caption) > * > * {
        padding: 0.75rem 0.5rem;
    }
    .table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
        border-bottom: 2px solid #dee2e6;
    }
    .table tbody tr {
        transition: all 0.2s ease;
    }
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    /* Card Improvements */
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: all 0.3s ease;
    }
    .card:hover {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }
    .card-header {
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    
    /* Badge Improvements */
    .badge {
        font-weight: 500;
        padding: 0.35em 0.65em;
        font-size: 0.75em;
    }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="h3 mb-0">
        <i class="fas fa-tachometer-alt me-2"></i>Dashboard
    </h1>
    <p class="text-muted mb-0">
        Welcome back, <strong>{{ auth()->user()->name }}</strong>! 
        <span class="badge bg-{{ auth()->user()->role_badge_color }} ms-2">
            {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}
        </span>
    </p>
</div>

<!-- Role Information Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fas fa-user-shield me-2"></i>Your Role & Permissions
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Role: {{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</h6>
                        @if(auth()->user()->isSuperAdmin())
                            <div class="alert alert-danger">
                                <i class="fas fa-crown me-2"></i>
                                <strong>Super Admin Access</strong>
                                <p class="mb-0 mt-2">You have full system access including:</p>
                                <ul class="mb-0">
                                    <li>Full database access</li>
                                    <li>Manage all users & roles</li>
                                    <li>System configuration</li>
                                    <li>API keys / integrations</li>
                                    <li>Backup & restore</li>
                                    <li>Edit system settings</li>
                                    <li>Debug logs</li>
                                    <li>Update system modules</li>
                                </ul>
                            </div>
                        @elseif(auth()->user()->isAdmin())
                            <div class="alert alert-warning">
                                <i class="fas fa-user-tie me-2"></i>
                                <strong>Admin (Shop Manager/Owner)</strong>
                                <p class="mb-0 mt-2">Daily operations manager with access to:</p>
                                <ul class="mb-0">
                                    <li>Create / edit customers</li>
                                    <li>Create service orders</li>
                                    <li>Assign technicians</li>
                                    <li>View reports</li>
                                    <li>View inventory</li>
                                    <li>Manage appointments</li>
                                    <li>Approve completed jobs</li>
                                    <li>View payments</li>
                                </ul>
                            </div>
                        @elseif(auth()->user()->isOfficeStaff())
                            <div class="alert alert-info">
                                <i class="fas fa-user-friends me-2"></i>
                                <strong>Office Staff (Front Desk/Receptionist)</strong>
                                <p class="mb-0 mt-2">Customer-facing role with access to:</p>
                                <ul class="mb-0">
                                    <li>Create customer records</li>
                                    <li>Schedule appointments</li>
                                    <li>Create job orders</li>
                                    <li>Check job status</li>
                                    <li>Update customer contact info</li>
                                    <li>Send notifications</li>
                                </ul>
                            </div>
                        @elseif(auth()->user()->isTechnician())
                            <div class="alert alert-primary">
                                <i class="fas fa-tools me-2"></i>
                                <strong>Technician (Mechanic)</strong>
                                <p class="mb-0 mt-2">Shop mechanics with access to:</p>
                                <ul class="mb-0">
                                    <li>View assigned jobs</li>
                                    <li>Update job progress</li>
                                    <li>Mark job as completed</li>
                                    <li>Add repair notes</li>
                                    <li>Upload photos</li>
                                </ul>
                            </div>
                        @elseif(auth()->user()->isAccounting())
                            <div class="alert alert-purple">
                                <i class="fas fa-calculator me-2"></i>
                                <strong>Accounting / Cashier</strong>
                                <p class="mb-0 mt-2">Financial role with access to:</p>
                                <ul class="mb-0">
                                    <li>View completed jobs</li>
                                    <li>Create invoices</li>
                                    <li>Record payments</li>
                                    <li>Print receipts</li>
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-primary mb-3">Quick Actions</h6>
                        <div class="list-group">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isOfficeStaff())
                                <a href="{{ route('customers.index') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-users me-2"></i>Manage Customers
                                </a>
                            @endif
                            
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isOfficeStaff())
                                <a href="{{ route('appointments.index') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-calendar-alt me-2"></i>Schedule Appointments
                                </a>
                            @endif
                            
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isTechnician())
                                <a href="{{ route('work-orders.index') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-wrench me-2"></i>View Jobs
                                </a>
                            @endif
                            
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                                <a href="{{ route('reports.dashboard') }}" class="list-group-item list-group-item-action">
                                    <i class="fas fa-chart-bar me-2"></i>View Reports
                                </a>
                            @endif
                            
                            @if(auth()->user()->isSuperAdmin())
                                <a href="#" class="list-group-item list-group-item-action list-group-item-danger">
                                    <i class="fas fa-cog me-2"></i>System Settings
                                </a>
                                <a href="#" class="list-group-item list-group-item-action list-group-item-danger">
                                    <i class="fas fa-user-cog me-2"></i>User Management
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invoice & Payments Quick Widget -->
@if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin() || auth()->user()->isOfficeStaff())
<div class="row mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="fas fa-file-invoice-dollar text-primary me-2"></i>
                    Invoice & Payments Dashboard
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Quick Stats -->
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Invoices</h6>
                                <h3 class="mb-0 text-dark">{{ \App\Models\Invoice::count() }}</h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Pending Payments</h6>
                                <h3 class="mb-0 text-warning">
                                    {{ \App\Models\Invoice::where('payment_status', 'pending')->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Overdue Invoices</h6>
                                <h3 class="mb-0 text-danger">
                                    {{ \App\Models\Invoice::where('status', 'overdue')->count() }}
                                </h3>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-0 bg-light">
                            <div class="card-body text-center">
                                <h6 class="text-muted mb-2">Total Revenue</h6>
                                <h3 class="mb-0 text-success">
                                    ₱{{ number_format(\App\Models\Invoice::sum('total_amount'), 2) }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="row mt-3">
                    <div class="col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-bolt me-2"></i>Quick Invoice Actions
                                </h6>
                                <div class="d-grid gap-2">
                                    <a href="{{ route('invoices.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i> Create New Invoice
                                    </a>
                                    <a href="{{ route('invoices.index') }}" class="btn btn-outline-primary">
                                        <i class="fas fa-list me-1"></i> View All Invoices
                                    </a>
                                    <a href="{{ route('invoices.index') }}" class="btn btn-success">
                                        <i class="fas fa-money-bill-wave me-1"></i> Manage Invoices & Payments
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card border-0">
                            <div class="card-body">
                                <h6 class="text-primary mb-3">
                                    <i class="fas fa-clock me-2"></i>Recent Payments
                                </h6>
                                @php
                                    $recentPayments = \App\Models\Payment::with('invoice')
                                        ->orderBy('created_at', 'desc')
                                        ->limit(5)
                                        ->get();
                                @endphp
                                
                                @if($recentPayments->count() > 0)
                                    <div class="list-group list-group-flush">
                                        @foreach($recentPayments as $payment)
                                            <div class="list-group-item border-0 px-0">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <small class="text-muted">
                                                            {{ $payment->payment_date->format('M d') }}
                                                        </small>
                                                        <br>
                                                        <small>
                                                            <i class="fas fa-{{ $payment->payment_method === 'cash' ? 'money-bill' : 'credit-card' }} me-1"></i>
                                                            {{ ucfirst($payment->payment_method) }}
                                                        </small>
                                                    </div>
                                                    <div class="text-end">
                                                        <strong class="text-success">₱{{ number_format($payment->amount, 2) }}</strong>
                                                        <br>
                                                        <small class="text-muted">
                                                            @if($payment->invoice)
                                                                {{ $payment->invoice->invoice_number }}
                                                            @else
                                                                Manual Payment
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="text-center mt-2">
                                        <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-secondary">
                                            View All Invoices & Payments
                                        </a>
                                    </div>
                                @else
                                    <div class="text-center py-3">
                                        <i class="fas fa-history fa-2x text-muted mb-2"></i>
                                        <p class="text-muted mb-0">No recent payments</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Overdue Invoices Alert -->
                @php
                    $overdueInvoices = \App\Models\Invoice::where('status', 'overdue')
                        ->where('balance_due', '>', 0)
                        ->orderBy('due_date', 'asc')
                        ->limit(3)
                        ->get();
                @endphp
                
                @if($overdueInvoices->count() > 0)
                    <div class="alert alert-danger mt-3">
                        <h6 class="alert-heading">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Overdue Invoices Requiring Attention
                        </h6>
                        <div class="row mt-2">
                            @foreach($overdueInvoices as $invoice)
                                <div class="col-md-4 mb-2">
                                    <div class="card border-danger">
                                        <div class="card-body p-2">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-danger">
                                                        <strong>{{ $invoice->invoice_number }}</strong>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        {{ $invoice->customer->first_name }} {{ $invoice->customer->last_name }}
                                                    </small>
                                                </div>
                                                <div class="text-end">
                                                    <small class="text-danger">
                                                        <strong>₱{{ number_format($invoice->balance_due, 2) }}</strong>
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        Due: {{ $invoice->due_date->format('M d') }}
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="text-center mt-1">
                                                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-sm btn-outline-danger">
                                                    <i class="fas fa-eye me-1"></i> View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Total Customers</h6>
                        <h2 class="mb-0">{{ $stats['total_customers'] }}</h2>
                        <small class="opacity-75">+12% from last month</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Revenue This Month</h6>
                        <h2 class="mb-0">₱{{ number_format($stats['revenue_this_month'], 2) }}</h2>
                        <small class="opacity-75">+18% from last month</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Pending Services</h6>
                        <h2 class="mb-0">{{ $stats['pending_services'] }}</h2>
                        <small class="opacity-75">+3 from yesterday</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-wrench"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-danger text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h6 class="text-uppercase mb-0">Upcoming Services</h6>
                        <h2 class="mb-0">{{ $stats['upcoming_services'] }}</h2>
                        <small class="opacity-75">Next 30 days</small>
                    </div>
                    <div class="card-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Services - Professional Redesign -->
    <div class="col-lg-8 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-history me-2"></i>Recent Services
                        </h5>
                        <small class="opacity-75">Latest completed and ongoing services</small>
                    </div>
                    <div>
                        <span class="badge bg-light text-primary">{{ $recentServices->count() }} Services</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Job Order</th>
                                <th>Customer</th>
                                <th>Vehicle</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th class="text-end pe-4">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentServices as $service)
                            <tr class="border-bottom">
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="work-order-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                                            <i class="fas fa-wrench"></i>
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ $service->work_order_number }}</strong>
                                            <small class="text-muted">{{ $service->service_date->format('M d, Y') }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar bg-info text-white rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            {{ substr($service->customer->first_name, 0, 1) }}{{ substr($service->customer->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong class="d-block">{{ $service->customer->full_name }}</strong>
                                            <small class="text-muted">{{ $service->customer->phone }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="vehicle-icon bg-light text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px;">
                                            <i class="fas fa-car"></i>
                                        </div>
                                        <div>
                                            @if($service->vehicle)
                                                <strong class="d-block">{{ $service->vehicle->year ?? '' }} {{ $service->vehicle->make ?? 'Unknown' }}</strong>
                                                <small class="text-muted">{{ $service->vehicle->license_plate ?? 'No Plate' }}</small>
                                            @else
                                                <span class="text-muted">No vehicle</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">{{ $service->service_type }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge 
                                            @if($service->service_status == 'completed') bg-success
                                            @elseif($service->service_status == 'in_progress') bg-info
                                            @elseif($service->service_status == 'pending') bg-warning
                                            @else bg-secondary
                                            @endif mb-1">
                                            {{ ucfirst(str_replace('_', ' ', $service->service_status)) }}
                                        </span>
                                        <span class="badge 
                                            @if($service->payment_status == 'paid') bg-success
                                            @elseif($service->payment_status == 'partial') bg-warning
                                            @else bg-danger
                                            @endif">
                                            {{ ucfirst($service->payment_status) }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="d-flex flex-column align-items-end">
                                        <strong class="text-primary">₱{{ number_format($service->final_amount, 2) }}</strong>
                                        <div class="mt-1">
                                            @if($service->customer_rating)
                                                @for($i = 1; $i <= 5; $i++)
                                                    @if($i <= $service->customer_rating)
                                                        <i class="fas fa-star text-warning" style="font-size: 0.8rem;"></i>
                                                    @else
                                                        <i class="far fa-star text-muted" style="font-size: 0.8rem;"></i>
                                                    @endif
                                                @endfor
                                            @else
                                                <small class="text-muted">No rating</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-light border-0 py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted" style="font-size: 0.8rem;">Showing {{ $recentServices->count() }} of {{ $stats['total_services'] ?? 0 }} services</small>
                        </div>
                        <div>
                            <a href="{{ route('work-orders.index') }}" class="btn btn-primary btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                <i class="fas fa-eye me-1"></i> View All
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Upcoming Services -->
    <div class="col-lg-4 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-gradient-info text-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0">
                            <i class="fas fa-calendar-check me-1"></i>Upcoming Services
                        </h6>
                        <small class="opacity-75 d-block" style="font-size: 0.7rem;">Next 30 days schedule</small>
                    </div>
                    <div>
                        <span class="badge bg-light text-info" style="font-size: 0.7rem;">{{ $upcomingServices->count() }} Due</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                @if($upcomingServices->count() > 0)
                    <div class="timeline">
                        @foreach($upcomingServices as $index => $vehicle)
                        <div class="timeline-item {{ $index < $upcomingServices->count() - 1 ? 'mb-3' : '' }}">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h6 class="mb-1">
                                            {{ $vehicle->year ?? '' }} {{ $vehicle->make ?? 'Unknown Make' }} {{ $vehicle->model ?? '' }}
                                        </h6>
                                        <small class="text-muted d-block mb-2">
                                            <i class="fas fa-user me-1"></i>{{ $vehicle->customer->full_name ?? 'Unknown Customer' }}
                                        </small>
                                    </div>
                                    @if($vehicle->next_service_date)
                                        <div class="text-end">
                                            <span class="badge bg-info">{{ $vehicle->next_service_date->format('M d') }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-{{ $vehicle->service_status_color ?? 'secondary' }} me-2">
                                        {{ $vehicle->next_service_due ?? 'Service Due' }}
                                    </span>
                                    @if($vehicle->license_plate)
                                        <small class="text-muted">
                                            <i class="fas fa-tag me-1"></i>{{ $vehicle->license_plate }}
                                        </small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <div class="empty-state-icon mb-3">
                            <i class="fas fa-calendar-times fa-4x text-light"></i>
                        </div>
                        <h5 class="text-muted">No Upcoming Services</h5>
                        <p class="text-muted mb-4">No services scheduled for the next 30 days</p>
                        <a href="{{ route('appointments.create') }}" class="btn btn-info">
                            <i class="fas fa-plus me-1"></i> Schedule Service
                        </a>
                    </div>
                @endif
            </div>
            @if($upcomingServices->count() > 0)
            <div class="card-footer bg-light border-0 py-2">
                <div class="d-grid">
                    <a href="{{ route('appointments.index') }}" class="btn btn-outline-info btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                        <i class="fas fa-calendar-alt me-1"></i> View Full Calendar
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Service Types Distribution - Moved to separate row -->
<div class="row mt-3">
    <div class="col-lg-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-gradient-success text-white py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">
                            <i class="fas fa-chart-pie me-2"></i>Service Types Distribution
                        </h5>
                        <small class="opacity-75">Service category breakdown</small>
                    </div>
                    <div>
                        <span class="badge bg-light text-success">{{ $serviceTypes->count() }} Types</span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <div class="chart-container" style="height: 200px;">
                            <canvas id="serviceTypesChart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="service-types-list">
                            @foreach($serviceTypes as $type)
                            <div class="d-flex align-items-center mb-3">
                                <div class="service-type-color me-3" style="background-color: {{ $type->color ?? '#3498db' }}; width: 12px; height: 12px; border-radius: 2px;"></div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-1">{{ $type->service_type }}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="progress flex-grow-1 me-3" style="height: 8px;">
                                            @php
                                                $totalServices = $serviceTypes->sum('count');
                                                $percentage = $totalServices > 0 ? ($type->count / $totalServices) * 100 : 0;
                                            @endphp
                                            <div class="progress-bar" role="progressbar" style="width: {{ $percentage }}%; background-color: {{ $type->color ?? '#3498db' }};"></div>
                                        </div>
                                        <div class="text-end">
                                            <strong class="text-primary">{{ $type->count }}</strong>
                                            <small class="text-muted d-block">({{ number_format($percentage, 1) }}%)</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light border-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Total Services:</strong> <span class="text-primary">{{ $totalServices }}</span>
                    </div>
                    <div>
                        <a href="{{ route('work-orders.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-chart-bar me-1"></i> View Detailed Analytics
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Chart -->
<div class="row mt-4">
    <div class="col-12">
        <div class="chart-container">
            <h5 class="mb-3">
                <i class="fas fa-chart-line me-2"></i>Monthly Revenue Trend
            </h5>
            <canvas id="revenueChart" height="100"></canvas>
        </div>
    </div>
</div>

<!-- Top Customers -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-crown me-2"></i>Top Customers
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Customer</th>
                                <th>Total Services</th>
                                <th>Total Spent</th>
                                <th>Avg. Service Cost</th>
                                <th>Last Service</th>
                                <th>Loyalty Points</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="customer-avatar me-2">
                                            {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong>{{ $customer->full_name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $customer->service_records_count ?? 0 }}</td>
                                <td>₱{{ number_format($customer->service_records_sum_final_amount ?? 0, 2) }}</td>
                                <td>
                                    @if($customer->service_records_count > 0)
                                        ₱{{ number_format(($customer->service_records_sum_final_amount ?? 0) / $customer->service_records_count, 2) }}
                                    @else
                                        ₱0.00
                                    @endif
                                </td>
                                <td>
                                    @if($customer->last_service_date)
                                        {{ \Carbon\Carbon::parse($customer->last_service_date)->format('M d, Y') }}
                                    @else
                                        <span class="text-muted">No services</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-warning">{{ $customer->loyalty_points }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueChart = new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($revenueByMonth->toArray())) !!},
            datasets: [{
                label: 'Revenue',
                data: {!! json_encode(array_values($revenueByMonth->toArray())) !!},
                borderColor: '#3498db',
                backgroundColor: 'rgba(52, 152, 219, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Service Types Chart
    const serviceTypesCtx = document.getElementById('serviceTypesChart').getContext('2d');
    const serviceTypesChart = new Chart(serviceTypesCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($serviceTypes->pluck('service_type')->toArray()) !!},
            datasets: [{
                data: {!! json_encode($serviceTypes->pluck('count')->toArray()) !!},
                backgroundColor: {!! json_encode($serviceTypes->pluck('color')->toArray()) !!},
                borderWidth: 1,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%'
        }
    });
</script>
@endpush

<!-- Auto Mechanic Repair Shop Reports Section -->
<div class="row mt-5">
    <div class="col-12">
        <div class="card border-0 shadow-lg">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">
                    <i class="fas fa-chart-bar me-2"></i>Auto Mechanic Repair Shop Reports
                </h4>
                <p class="mb-0 mt-2 text-light">Comprehensive business intelligence for your auto repair shop</p>
            </div>
            <div class="card-body">
                <!-- Key Performance Indicators -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card border-primary">
                            <div class="card-body text-center">
                                <h6 class="text-primary mb-2">Average Repair Time</h6>
                                <h2 class="mb-0">{{ $mechanicReports['average_repair_time'] }}</h2>
                                <small class="text-muted">Days per job</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">-0.3 days</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-success">
                            <div class="card-body text-center">
                                <h6 class="text-success mb-2">First-Time Fix Rate</h6>
                                <h2 class="mb-0">{{ $mechanicReports['first_time_fix_rate'] }}%</h2>
                                <small class="text-muted">Jobs completed right first time</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">+2%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-warning">
                            <div class="card-body text-center">
                                <h6 class="text-warning mb-2">Parts Utilization</h6>
                                <h2 class="mb-0">{{ $mechanicReports['parts_utilization'] }}%</h2>
                                <small class="text-muted">Inventory turnover rate</small>
                                <div class="mt-2">
                                    <span class="badge bg-danger">-5%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card border-info">
                            <div class="card-body text-center">
                                <h6 class="text-info mb-2">Customer Retention</h6>
                                <h2 class="mb-0">{{ $mechanicReports['customer_retention'] }}%</h2>
                                <small class="text-muted">Returning customers</small>
                                <div class="mt-2">
                                    <span class="badge bg-success">+3%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mechanic Productivity Report -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-tools me-2"></i>Mechanic Productivity Report
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Mechanic</th>
                                                <th>Jobs Completed</th>
                                                <th>Avg. Repair Time</th>
                                                <th>Revenue Generated</th>
                                                <th>Customer Rating</th>
                                                <th>Efficiency Score</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($mechanicReports['mechanic_productivity'] as $mechanic)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div>
                                                            <strong>{{ $mechanic['name'] }}</strong><br>
                                                            <small class="text-muted">{{ $mechanic['title'] }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>{{ $mechanic['jobs_completed'] }}</td>
                                                <td>{{ $mechanic['avg_repair_time'] }} days</td>
                                                <td>₱{{ number_format($mechanic['revenue_generated'], 2) }}</td>
                                                <td>
                                                    @for($i = 1; $i <= 5; $i++)
                                                        @if($i <= floor($mechanic['rating']))
                                                            <i class="fas fa-star text-warning"></i>
                                                        @elseif($i == ceil($mechanic['rating']) && $mechanic['rating'] != floor($mechanic['rating']))
                                                            <i class="fas fa-star-half-alt text-warning"></i>
                                                        @else
                                                            <i class="far fa-star text-warning"></i>
                                                        @endif
                                                    @endfor
                                                    <small class="ms-1">{{ $mechanic['rating'] }}</small>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height: 20px;">
                                                        <div class="progress-bar 
                                                            @if($mechanic['efficiency'] >= 90) bg-success
                                                            @elseif($mechanic['efficiency'] >= 80) bg-info
                                                            @elseif($mechanic['efficiency'] >= 70) bg-warning
                                                            @else bg-danger
                                                            @endif" 
                                                            role="progressbar" 
                                                            style="width: {{ $mechanic['efficiency'] }}%;" 
                                                            aria-valuenow="{{ $mechanic['efficiency'] }}" 
                                                            aria-valuemin="0" 
                                                            aria-valuemax="100">
                                                            {{ $mechanic['efficiency'] }}%
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Service Category Analysis -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-cogs me-2"></i>Service Category Revenue
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="serviceCategoryChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-car me-2"></i>Vehicle Type Distribution
                                </h5>
                            </div>
                            <div class="card-body">
                                <canvas id="vehicleTypeChart" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Parts & Inventory Report -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="mb-0">
                                    <i class="fas fa-boxes me-2"></i>Parts & Inventory Report
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-danger">
                                            <div class="card-body text-center">
                                                <h6 class="text-danger mb-2">Low Stock Items</h6>
                                                <h2 class="mb-0">{{ $mechanicReports['inventory_report']['low_stock_items'] }}</h2>
                                                <small class="text-muted">Need reordering</small>
                                                <div class="mt-2">
                                                    <a href="{{ route('inventory.low-stock') }}" class="btn btn-sm btn-outline-danger">View Details</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-warning">
                                            <div class="card-body text-center">
                                                <h6 class="text-warning mb-2">Slow Moving Parts</h6>
                                                <h2 class="mb-0">{{ $mechanicReports['inventory_report']['slow_moving_parts'] }}</h2>
                                                <small class="text-muted">Over 90 days in stock</small>
                                                <div class="mt-2">
                                                    <a href="#" class="btn btn-sm btn-outline-warning">Review</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <div class="card border-success">
                                            <div class="card-body text-center">
                                                <h6 class="text-success mb-2">Top Selling Parts</h6>
                                                <h2 class="mb-0">{{ $mechanicReports['inventory_report']['top_selling_parts'] }}</h2>
                                                <small class="text-muted">This month</small>
                                                <div class="mt-2">
                                                    <a href="#" class="btn btn-sm btn-outline-success">See List</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <h6>Monthly Parts Consumption Trend</h6>
                                    <canvas id="partsConsumptionChart" height="100"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Report Actions -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-body">
                                <h5 class="text-primary mb-3">
                                    <i class="fas fa-download me-2"></i>Export Reports
                                </h5>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-outline-primary">
                                        <i class="fas fa-file-pdf me-1"></i> Download PDF Report
                                    </button>
                                    <button class="btn btn-outline-success">
                                        <i class="fas fa-file-excel me-1"></i> Export to Excel
                                    </button>
                                    <button class="btn btn-outline-info">
                                        <i class="fas fa-chart-line me-1"></i> Generate Custom Report
                                    </button>
                                    <button class="btn btn-outline-dark">
                                        <i class="fas fa-print me-1"></i> Print Dashboard
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Service Category Chart
    const serviceCategoryCtx = document.getElementById('serviceCategoryChart').getContext('2d');
    const serviceCategoryChart = new Chart(serviceCategoryCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_keys($mechanicReports['service_category_revenue'])) !!},
            datasets: [{
                label: 'Revenue (₱)',
                data: {!! json_encode(array_values($mechanicReports['service_category_revenue'])) !!},
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#e74c3c',
                    '#f39c12',
                    '#9b59b6',
                    '#1abc9c'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
    
    // Vehicle Type Chart
    const vehicleTypeCtx = document.getElementById('vehicleTypeChart').getContext('2d');
    const vehicleTypeChart = new Chart(vehicleTypeCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($mechanicReports['vehicle_type_distribution'])) !!},
            datasets: [{
                data: {!! json_encode(array_values($mechanicReports['vehicle_type_distribution'])) !!},
                backgroundColor: [
                    '#3498db',
                    '#2ecc71',
                    '#e74c3c',
                    '#f39c12',
                    '#9b59b6',
                    '#95a5a6'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'right'
                }
            }
        }
    });
    
    // Parts Consumption Chart
    const partsConsumptionCtx = document.getElementById('partsConsumptionChart').getContext('2d');
    const partsConsumptionChart = new Chart(partsConsumptionCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode(array_keys($mechanicReports['parts_consumption_trend'])) !!},
            datasets: [{
                label: 'Parts Cost (₱)',
                data: {!! json_encode(array_values($mechanicReports['parts_consumption_trend'])) !!},
                borderColor: '#e74c3c',
                backgroundColor: 'rgba(231, 76, 60, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₱' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });
</script>
@endpush
@endsection