@extends('layouts.app')

@section('title', 'Payment Methods')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">Payment Methods</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('portal.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('portal.billing.index') }}">Billing</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Payment Methods</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('portal.billing.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Billing
            </a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                <i class="bi bi-plus-circle me-1"></i> Add Payment Method
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 bg-primary bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Methods</h6>
                            <h3 class="mb-0">{{ $paymentMethods->count() }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-credit-card display-6 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-success bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Active</h6>
                            <h3 class="mb-0">{{ $activeCount }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-check-circle display-6 text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-warning bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Expiring Soon</h6>
                            <h3 class="mb-0">{{ $expiringSoonCount }}</h3>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-exclamation-triangle display-6 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 bg-info bg-opacity-10">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Default Method</h6>
                            <h5 class="mb-0">
                                @if($defaultMethod)
                                    @if($defaultMethod->type === 'credit_card')
                                    Credit Card
                                    @elseif($defaultMethod->type === 'bank_account')
                                    Bank Account
                                    @else
                                    {{ ucfirst($defaultMethod->type) }}
                                    @endif
                                @else
                                    <span class="text-muted">Not Set</span>
                                @endif
                            </h5>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="bi bi-star display-6 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods List -->
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Your Payment Methods</h5>
                <div class="text-muted">
                    {{ $paymentMethods->count() }} method(s)
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($paymentMethods->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Type</th>
                            <th>Details</th>
                            <th>Status</th>
                            <th>Expiration</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentMethods as $method)
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    @if($method->type === 'credit_card')
                                    <i class="bi bi-credit-card me-3 text-primary fs-4"></i>
                                    <div>
                                        <div class="fw-medium">Credit Card</div>
                                        <div class="text-muted small">{{ $method->card_type ?? 'Card' }}</div>
                                    </div>
                                    @elseif($method->type === 'bank_account')
                                    <i class="bi bi-bank me-3 text-success fs-4"></i>
                                    <div>
                                        <div class="fw-medium">Bank Account</div>
                                        <div class="text-muted small">{{ $method->bank_name ?? 'Bank Account' }}</div>
                                    </div>
                                    @elseif($method->type === 'paypal')
                                    <i class="bi bi-paypal me-3 text-info fs-4"></i>
                                    <div>
                                        <div class="fw-medium">PayPal</div>
                                        <div class="text-muted small">{{ $method->email ?? 'PayPal Account' }}</div>
                                    </div>
                                    @else
                                    <i class="bi bi-wallet me-3 text-secondary fs-4"></i>
                                    <div>
                                        <div class="fw-medium">{{ ucfirst($method->type) }}</div>
                                        <div class="text-muted small">Payment Method</div>
                                    </div>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($method->type === 'credit_card')
                                <div class="fw-medium">•••• {{ $method->last_four }}</div>
                                <div class="text-muted small">
                                    {{ $method->card_holder_name }}
                                    @if($method->is_default)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 ms-1">Default</span>
                                    @endif
                                </div>
                                @elseif($method->type === 'bank_account')
                                <div class="fw-medium">•••• {{ $method->last_four }}</div>
                                <div class="text-muted small">
                                    {{ $method->account_holder_name }}
                                    @if($method->is_default)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 ms-1">Default</span>
                                    @endif
                                </div>
                                @elseif($method->type === 'paypal')
                                <div class="fw-medium">{{ $method->email }}</div>
                                <div class="text-muted small">
                                    PayPal Account
                                    @if($method->is_default)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 ms-1">Default</span>
                                    @endif
                                </div>
                                @else
                                <div class="fw-medium">{{ $method->nickname ?? 'Payment Method' }}</div>
                                <div class="text-muted small">
                                    {{ ucfirst($method->type) }}
                                    @if($method->is_default)
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 ms-1">Default</span>
                                    @endif
                                </div>
                                @endif
                            </td>
                            <td>
                                @if($method->is_active)
                                <span class="badge bg-success">Active</span>
                                @else
                                <span class="badge bg-secondary">Inactive</span>
                                @endif
                                @if($method->is_verified)
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 ms-1">Verified</span>
                                @else
                                <span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 ms-1">Pending</span>
                                @endif
                            </td>
                            <td>
                                @if($method->expiration_date)
                                    @if($method->expiration_date->isPast())
                                    <span class="badge bg-danger">Expired</span>
                                    @elseif($method->expiration_date->diffInDays(now()) <= 30)
                                    <span class="badge bg-warning text-dark">Expires {{ $method->expiration_date->format('M Y') }}</span>
                                    @else
                                    <span class="text-muted">{{ $method->expiration_date->format('M Y') }}</span>
                                    @endif
                                @else
                                <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <div class="dropdown">
                                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        @if(!$method->is_default)
                                        <li>
                                            <form method="POST" action="{{ route('portal.billing.set-default-payment-method', $method->id) }}" class="d-inline">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="bi bi-star me-2"></i> Set as Default
                                                </button>
                                            </form>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        @endif
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editPaymentMethodModal" data-method-id="{{ $method->id }}">
                                                <i class="bi bi-pencil me-2"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#verifyPaymentMethodModal" data-method-id="{{ $method->id }}">
                                                <i class="bi bi-shield-check me-2"></i> Verify
                                            </a>
                                        </li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <form method="POST" action="{{ route('portal.billing.delete-payment-method', $method->id) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment method?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bi bi-trash me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="bi bi-credit-card display-1 text-muted mb-3"></i>
                <h5 class="text-muted mb-2">No payment methods found</h5>
                <p class="text-muted mb-4">Add a payment method to make payments faster and easier</p>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPaymentMethodModal">
                    <i class="bi bi-plus-circle me-1"></i> Add Your First Payment Method
                </button>
            </div>
            @endif
        </div>
    </div>

    <!-- Security Notice -->
    <div class="alert alert-info mt-4">
        <div class="d-flex">
            <div class="flex-shrink-0">
                <i class="bi bi-shield-check fs-4"></i>
            </div>
            <div class="flex-grow-1 ms-3">
                <h6 class="alert-heading mb-2">Payment Security</h6>
                <p class="mb-2">Your payment information is securely encrypted and stored. We never store your full card numbers or CVV codes.</p>
                <div class="small">
                    <i class="bi bi-lock me-1"></i> PCI DSS compliant
                    <i class="bi bi-shield-check ms-3 me-1"></i> 256-bit encryption
                    <i class="bi bi-eye-slash ms-3 me-1"></i> Tokenized storage
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Payment Method Modal -->
<div class="modal fade" id="addPaymentMethodModal" tabindex="-1" aria-labelledby="addPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPaymentMethodModalLabel">Add Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.add-payment-method') }}" id="addPaymentMethodForm">
                @csrf
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-4" id="paymentMethodTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="credit-card-tab" data-bs-toggle="tab" data-bs-target="#credit-card" type="button" role="tab" aria-controls="credit-card" aria-selected="true">
                                <i class="bi bi-credit-card me-1"></i> Credit Card
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="bank-account-tab" data-bs-toggle="tab" data-bs-target="#bank-account" type="button" role="tab" aria-controls="bank-account" aria-selected="false">
                                <i class="bi bi-bank me-1"></i> Bank Account
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="paypal-tab" data-bs-toggle="tab" data-bs-target="#paypal" type="button" role="tab" aria-controls="paypal" aria-selected="false">
                                <i class="bi bi-paypal me-1"></i> PayPal
                            </button>
                        </li>
                    </ul>
                    
                    <div class="tab-content" id="paymentMethodTabsContent">
                        <!-- Credit Card Tab -->
                        <div class="tab-pane fade show active" id="credit-card" role="tabpanel" aria-labelledby="credit-card-tab">
                            <input type="hidden" name="type" value="credit_card">
                            
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="card_number" class="form-label">Card Number</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="card_number" name="card_number" 
                                               placeholder="1234 5678 9012 3456" maxlength="19" required>
                                        <span class="input-group-text">
                                            <i class="bi bi-credit-card" id="cardIcon"></i>
                                        </span>
                                    </div>
                                    <div class="form-text">Enter your 16-digit card number</div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="card_holder_name" class="form-label">Cardholder Name</label>
                                    <input type="text" class="form-control" id="card_holder_name" name="card_holder_name" 
                                           placeholder="John Doe" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="expiry_month" class="form-label">Expiry Month</label>
                                    <select class="form-select" id="expiry_month" name="expiry_month" required>
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                        <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }} - {{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="expiry_year" class="form-label">Expiry Year</label>
                                    <select class="form-select" id="expiry_year" name="expiry_year" required>
                                        <option value="">Year</option>
                                        @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="cvv" class="form-label">CVV</label>
                                    <input type="text" class="form-control" id="cvv" name="cvv" 
                                           placeholder="123" maxlength="4" required>
                                    <div class="form-text">3 or 4 digit security code</div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="card_nickname" class="form-label">Nickname (Optional)</label>
                                    <input type="text" class="form-control" id="card_nickname" name="nickname" 
                                           placeholder="e.g., Personal Visa">
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="make_default_card" name="is_default" value="1">
                                <label class="form-check-label" for="make_default_card">
                                    Set as default payment method
                                </label>
                            </div>
                        </div>
                        
                        <!-- Bank Account Tab -->
                        <div class="tab-pane fade" id="bank-account" role="tabpanel" aria-labelledby="bank-account-tab">
                            <input type="hidden" name="type" value="bank_account">
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="bank_name" class="form-label">Bank Name</label>
                                    <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                           placeholder="e.g., Chase Bank" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="account_type" class="form-label">Account Type</label>
                                    <select class="form-select" id="account_type" name="account_type" required>
                                        <option value="">Select type</option>
                                        <option value="checking">Checking</option>
                                        <option value="savings">Savings</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="account_holder_name" class="form-label">Account Holder Name</label>
                                    <input type="text" class="form-control" id="account_holder_name" name="account_holder_name" 
                                           placeholder="John Doe" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="routing_number" class="form-label">Routing Number</label>
                                    <input type="text" class="form-control" id="routing_number" name="routing_number" 
                                           placeholder="123456789" maxlength="9" required>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="account_number" class="form-label">Account Number</label>
                                    <input type="text" class="form-control" id="account_number" name="account_number" 
                                           placeholder="1234567890" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="bank_nickname" class="form-label">Nickname (Optional)</label>
                                    <input type="text" class="form-control" id="bank_nickname" name="nickname" 
                                           placeholder="e.g., Personal Checking">
                                </div>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="make_default_bank" name="is_default" value="1">
                                <label class="form-check-label" for="make_default_bank">
                                    Set as default payment method
                                </label>
                            </div>
                        </div>
                        
                        <!-- PayPal Tab -->
                        <div class="tab-pane fade" id="paypal" role="tabpanel" aria-labelledby="paypal-tab">
                            <input type="hidden" name="type" value="paypal">
                            
                            <div class="text-center mb-4">
                                <i class="bi bi-paypal display-1 text-info mb-3"></i>
                                <h5>Connect PayPal Account</h5>
                                <p class="text-muted">You'll be redirected to PayPal to authorize the connection</p>
                            </div>
                            
                            <div class="mb-3">
                                <label for="paypal_email" class="form-label">PayPal Email</label>
                                <input type="email" class="form-control" id="paypal_email" name="email" 
                                       placeholder="your.email@example.com" required>
                            </div>
                            
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" id="make_default_paypal" name="is_default" value="1">
                                <label class="form-check-label" for="make_default_paypal">
                                    Set as default payment method
                                </label>
                            </div>
                            
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-2"></i>
                                By connecting your PayPal account, you agree to our <a href="#" class="alert-link">Terms of Service</a> and authorize us to process payments through PayPal.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-plus-circle me-1"></i> Add Payment Method
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Payment Method Modal -->
<div class="modal fade" id="editPaymentMethodModal" tabindex="-1" aria-labelledby="editPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editPaymentMethodModalLabel">Edit Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.update-payment-method') }}" id="editPaymentMethodForm">
                @csrf
                @method('PUT')
                <input type="hidden" id="edit_method_id" name="method_id">
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_nickname" class="form-label">Nickname</label>
                        <input type="text" class="form-control" id="edit_nickname" name="nickname" 
                               placeholder="e.g., Personal Visa">
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_expiry_month" class="form-label">Expiry Month</label>
                        <select class="form-select" id="edit_expiry_month" name="expiry_month">
                            <option value="">Month</option>
                            @for($i = 1; $i <= 12; $i++)
                            <option value="{{ str_pad($i, 2, '0', STR_PAD_LEFT) }}">{{ str_pad($i, 2, '0', STR_PAD_LEFT) }} - {{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_expiry_year" class="form-label">Expiry Year</label>
                        <select class="form-select" id="edit_expiry_year" name="expiry_year">
                            <option value="">Year</option>
                            @for($i = date('Y'); $i <= date('Y') + 10; $i++)
                            <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="edit_is_active" name="is_active" value="1">
                        <label class="form-check-label" for="edit_is_active">
                            Active
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Verify Payment Method Modal -->
<div class="modal fade" id="verifyPaymentMethodModal" tabindex="-1" aria-labelledby="verifyPaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verifyPaymentMethodModalLabel">Verify Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('portal.billing.verify-payment-method') }}" id="verifyPaymentMethodForm">
                @csrf
                <input type="hidden" id="verify_method_id" name="method_id">
                
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-check display-1 text-success mb-3"></i>
                        <h5>Verification Required</h5>
                        <p class="text-muted">We need to verify your payment method for security purposes.</p>
                    </div>
                    
                    <div class="mb-3">
                        <label for="verification_amount_1" class="form-label">First Verification Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="verification_amount_1" name="amount_1" 
                                   step="0.01" min="0.01" max="1.00" placeholder="0.01" required>
                        </div>
                        <div class="form-text">Enter the first small deposit amount (less than ₱1.00)</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="verification_amount_2" class="form-label">Second Verification Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="verification_amount_2" name="amount_2" 
                                   step="0.01" min="0.01" max="1.00" placeholder="0.01" required>
                        </div>
                        <div class="form-text">Enter the second small deposit amount (less than ₱1.00)</div>
                    </div>
                    
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Two small deposits will be made to your account. Check your bank statement and enter the amounts above to verify ownership.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-shield-check me-1"></i> Verify
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    // Card number formatting
    document.getElementById('card_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        let formatted = '';
        
        for (let i = 0; i < value.length && i < 16; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += ' ';
            }
            formatted += value[i];
        }
        
        e.target.value = formatted;
        
        // Detect card type and update icon
        const cardNumber = value.substring(0, 2);
        const cardIcon = document.getElementById('cardIcon');
        
        if (cardNumber.startsWith('4')) {
            cardIcon.className = 'bi bi-credit-card text-primary';
        } else if (cardNumber.startsWith('5')) {
            cardIcon.className = 'bi bi-credit-card text-success';
        } else if (cardNumber.startsWith('3')) {
            cardIcon.className = 'bi bi-credit-card text-info';
        } else {
            cardIcon.className = 'bi bi-credit-card';
        }
    });

    // Tab switching
    document.querySelectorAll('#paymentMethodTabs button').forEach(tab => {
        tab.addEventListener('click', function() {
            const target = this.getAttribute('data-bs-target');
            const type = target.substring(1).replace('-', '_');
            
            // Update hidden type field
            document.querySelector('input[name="type"]').value = type;
            
            // Reset default checkboxes
            document.querySelectorAll('input[name="is_default"]').forEach(cb => {
                cb.checked = false;
            });
        });
    });

    // Edit modal setup
    const editModal = document.getElementById('editPaymentMethodModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const methodId = button.getAttribute('data-method-id');
            
            document.getElementById('edit_method_id').value = methodId;
            
            // Here you would typically fetch the method details via AJAX
            // For now, we'll just set the form up
        });
    }

    // Verify modal setup
    const verifyModal = document.getElementById('verifyPaymentMethodModal');
    if (verifyModal) {
        verifyModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const methodId = button.getAttribute('data-method-id');
            
            document.getElementById('verify_method_id').value = methodId;
        });
    }

    // Form validation
    document.getElementById('addPaymentMethodForm').addEventListener('submit', function(e) {
        const type = document.querySelector('input[name="type"]').value;
        
        if (type === 'credit_card') {
            const cardNumber = document.getElementById('card_number').value.replace(/\D/g, '');
            const cvv = document.getElementById('cvv').value;
            
            if (cardNumber.length !== 16) {
                e.preventDefault();
                alert('Please enter a valid 16-digit card number');
                return false;
            }
            
            if (cvv.length < 3 || cvv.length > 4) {
                e.preventDefault();
                alert('Please enter a valid CVV (3 or 4 digits)');
                return false;
            }
        } else if (type === 'bank_account') {
            const routingNumber = document.getElementById('routing_number').value;
            const accountNumber = document.getElementById('account_number').value;
            
            if (routingNumber.length !== 9) {
                e.preventDefault();
                alert('Please enter a valid 9-digit routing number');
                return false;
            }
            
            if (accountNumber.length < 5) {
                e.preventDefault();
                alert('Please enter a valid account number');
                return false;
            }
        }
    });

    // Routing number formatting
    document.getElementById('routing_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value.substring(0, 9);
    });

    // Account number formatting
    document.getElementById('account_number').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value;
    });

    // CVV formatting
    document.getElementById('cvv').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        e.target.value = value.substring(0, 4);
    });
</script>
@endsection