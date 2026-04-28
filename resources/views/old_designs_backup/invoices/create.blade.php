@extends('layouts.app')

@push('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
@endpush

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

<style>
    /* Invoice Create Page Specific Styles */
    .invoice-create-page .form-label.required:after {
        content: " *";
        color: #dc3545;
    }
    
    .invoice-create-page .card {
        border: 1px solid #e3e6f0;
        border-radius: 0.35rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
    }
    
    .invoice-create-page .card-header {
        background-color: #f8f9fc;
        border-bottom: 1px solid #e3e6f0;
        padding: 0.75rem 1.25rem;
    }
    
    .invoice-create-page .table th {
        font-weight: 600;
        color: #5a5c69;
        background-color: #f8f9fc;
        border-bottom: 2px solid #e3e6f0;
    }
    
    .invoice-create-page .btn-outline-primary {
        border-color: #4e73df;
        color: #4e73df;
    }
    
    .invoice-create-page .btn-outline-primary:hover {
        background-color: #4e73df;
        color: white;
    }
    
    .invoice-create-page .quick-actions .btn {
        transition: all 0.2s ease;
    }
    
    .invoice-create-page .quick-actions .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    }
    
    /* Item table styling */
    .invoice-create-page .items-table {
        background-color: white;
    }
    
    .invoice-create-page .items-table tbody tr:hover {
        background-color: #f8f9fa;
    }
    
    .invoice-create-page .items-table .form-control-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    
    /* Totals section styling */
    .invoice-create-page .totals-card {
        background-color: #f8f9fc;
        border-left: 4px solid #4e73df;
    }
    
    .invoice-create-page .total-row {
        font-weight: 600;
        font-size: 1.1rem;
        color: #5a5c69;
    }
    
    .invoice-create-page .grand-total {
        font-weight: 700;
        font-size: 1.25rem;
        color: #1cc88a;
    }
</style>
@endpush

@section('title', 'Create Invoice - Point of Sale')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar me-2"></i>Create Invoice
            </h1>
            <p class="text-muted mb-0">Create a new invoice for a customer</p>
        </div>
        <div>
            <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Invoices
            </a>
        </div>
    </div>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row invoice-create-page">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-shopping-cart me-2"></i>Invoice Items
                </h6>
            </div>
            <div class="card-body">
                <form id="invoiceForm" method="POST" action="{{ route('invoices.store') }}">
                    @csrf
                    
                    <!-- Customer & Vehicle Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label required">Customer</label>
                            <select class="form-select select2" name="customer_id" id="customerSelect" required>
                                <option value="">Select Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" 
                                            {{ $selectedCustomer && $selectedCustomer->id == $customer->id ? 'selected' : '' }}
                                            data-email="{{ $customer->email }}"
                                            data-phone="{{ $customer->phone }}">
                                        {{ $customer->first_name }} {{ $customer->last_name }} 
                                        ({{ $customer->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">Vehicle</label>
                            <select class="form-select select2" name="vehicle_id" id="vehicleSelect">
                                <option value="">Select Vehicle</option>
                                @foreach($vehicles as $vehicle)
                                    <option value="{{ $vehicle->id }}"
                                            {{ $selectedVehicle && $selectedVehicle->id == $vehicle->id ? 'selected' : '' }}
                                            data-customer-id="{{ $vehicle->customer_id }}">
                                        {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                        ({{ $vehicle->license_plate ?? 'No Plate' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Work Order Selection -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label">Work Order</label>
                            <select class="form-select select2" name="work_order_id" id="workOrderSelect">
                                <option value="">Select Work Order</option>
                                @foreach($workOrders as $workOrder)
                                    <option value="{{ $workOrder->id }}"
                                            {{ $selectedWorkOrder && $selectedWorkOrder->id == $workOrder->id ? 'selected' : '' }}
                                            data-customer-id="{{ $workOrder->customer_id }}"
                                            data-vehicle-id="{{ $workOrder->vehicle_id }}">
                                        WO-{{ str_pad($workOrder->id, 6, '0', STR_PAD_LEFT) }} - 
                                        {{ $workOrder->customer->first_name }} {{ $workOrder->customer->last_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label required">Invoice Date</label>
                            <input type="date" class="form-control" name="invoice_date" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>
                        
                        <div class="col-md-3">
                            <label class="form-label">Due Date</label>
                            <input type="date" class="form-control" name="due_date" 
                                   value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                        </div>
                    </div>
                    
                    <!-- Invoice Items Table -->
                    <div class="table-responsive mb-4">
                        <table class="table table-bordered" id="itemsTable">
                            <thead>
                                <tr>
                                    <th width="15%">Type</th>
                                    <th width="30%">Item Name</th>
                                    <th width="25%">Description</th>
                                    <th width="10%">Quantity</th>
                                    <th width="10%">Unit Price</th>
                                    <th width="10%">Total</th>
                                    <th width="5%"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <!-- Items will be added here dynamically -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="7" class="text-end">
                                        <button type="button" class="btn btn-sm btn-primary" id="addItemBtn">
                                            <i class="fas fa-plus me-1"></i> Add Item
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <!-- Tax & Discount -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Tax Rate</label>
                            <select class="form-select" name="tax_rate_id" id="taxRateSelect">
                                <option value="">No Tax</option>
                                @foreach($taxRates as $taxRate)
                                    <option value="{{ $taxRate->id }}" data-rate="{{ $taxRate->rate }}">
                                        {{ $taxRate->name }} ({{ $taxRate->rate }}%)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Discount</label>
                            <select class="form-select" name="discount_id" id="discountSelect">
                                <option value="">No Discount</option>
                                @foreach($discounts as $discount)
                                    <option value="{{ $discount->id }}" 
                                            data-type="{{ $discount->type }}"
                                            data-value="{{ $discount->value }}">
                                        {{ $discount->name }} 
                                        ({{ $discount->type === 'percentage' ? $discount->value . '%' : '₱' . number_format($discount->value, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label">Custom Discount Amount</label>
                            <input type="number" class="form-control" name="discount_amount" 
                                   id="customDiscount" step="0.01" min="0" placeholder="0.00">
                        </div>
                    </div>
                    
                    <!-- Notes -->
                    <div class="mb-4">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" name="notes" rows="3" 
                                  placeholder="Add any notes or special instructions..."></textarea>
                    </div>
                    
                    <!-- Summary -->
                    <div class="card bg-light">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-2">
                                        <strong>Subtotal:</strong>
                                        <span id="subtotalDisplay" class="float-end">₱0.00</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Tax:</strong>
                                        <span id="taxDisplay" class="float-end">₱0.00</span>
                                    </div>
                                    <div class="mb-2">
                                        <strong>Discount:</strong>
                                        <span id="discountDisplay" class="float-end">₱0.00</span>
                                    </div>
                                    <hr>
                                    <div class="mb-0">
                                        <h5 class="mb-0">
                                            <strong>Total:</strong>
                                            <span id="totalDisplay" class="float-end">₱0.00</span>
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <small>
                                            This invoice will be saved as a draft. You can send it to the customer 
                                            after reviewing all details.
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Hidden fields for items -->
                    <div id="itemsData"></div>
                    
                    <!-- Submit Buttons -->
                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save as Draft
                        </button>
                        <button type="button" class="btn btn-success" id="saveAndSendBtn">
                            <i class="fas fa-paper-plane me-1"></i> Save & Send
                        </button>
                        <a href="{{ route('invoices.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Quick Actions -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('customers.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-user-plus me-2"></i> Add New Customer
                    </a>
                    <a href="{{ route('vehicles.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-car me-2"></i> Add New Vehicle
                    </a>
                    <a href="{{ route('work-orders.create') }}" class="btn btn-outline-primary">
                        <i class="fas fa-wrench me-2"></i> Create Work Order
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Customer Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-user me-2"></i>Customer Information
                </h6>
            </div>
            <div class="card-body">
                <div id="customerInfo" class="text-center text-muted">
                    <i class="fas fa-user-circle fa-3x mb-3"></i>
                    <p>Select a customer to view details</p>
                </div>
            </div>
        </div>
        
        <!-- Common Services -->
        <div class="card">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-tools me-2"></i>Common Services
                </h6>
            </div>
            <div class="card-body">
                <div class="list-group">
                    <button type="button" class="list-group-item list-group-item-action add-service-item" 
                            data-type="service" data-name="Oil Change" data-price="2500">
                        <div class="d-flex justify-content-between">
                            <span>Oil Change</span>
                            <span class="badge bg-primary">₱2,500</span>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action add-service-item" 
                            data-type="service" data-name="Brake Service" data-price="4500">
                        <div class="d-flex justify-content-between">
                            <span>Brake Service</span>
                            <span class="badge bg-primary">₱4,500</span>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action add-service-item" 
                            data-type="service" data-name="Tire Rotation" data-price="1200">
                        <div class="d-flex justify-content-between">
                            <span>Tire Rotation</span>
                            <span class="badge bg-primary">₱1,200</span>
                        </div>
                    </button>
                    <button type="button" class="list-group-item list-group-item-action add-service-item" 
                            data-type="service" data-name="Engine Tune-up" data-price="7500">
                        <div class="d-flex justify-content-between">
                            <span>Engine Tune-up</span>
                            <span class="badge bg-primary">₱7,500</span>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Item Template (Hidden) -->
<template id="itemTemplate">
    <tr>
        <td>
            <select class="form-select form-select-sm item-type" name="items[INDEX][item_type]">
                <option value="service">Service</option>
                <option value="parts">Parts</option>
                <option value="labor">Labor</option>
                <option value="fee">Fee</option>
            </select>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm item-name" 
                   name="items[INDEX][item_name]" placeholder="Item name" required>
        </td>
        <td>
            <input type="text" class="form-control form-control-sm item-description" 
                   name="items[INDEX][description]" placeholder="Description">
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-quantity" 
                   name="items[INDEX][quantity]" value="1" min="0.01" step="0.01" required>
        </td>
        <td>
            <input type="number" class="form-control form-control-sm item-price" 
                   name="items[INDEX][unit_price]" value="0" min="0" step="0.01" required>
        </td>
        <td>
            <span class="item-total">₱0.00</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-item">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>
</template>

<style>
    .required:after {
        content: " *";
        color: red;
    }
    
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        let itemIndex = 0;
        const itemsBody = document.getElementById('itemsBody');
        const itemTemplate = document.getElementById('itemTemplate');
        
        // Initialize Select2
        if (typeof $ !== 'undefined') {
            $('.select2').select2();
        }
        
        // Add first item (only if addItem function exists)
        if (typeof addItem === 'function') {
            addItem();
        } else {
            console.warn('addItem function not defined yet');
        }
        
        // Add item button
        const addItemBtn = document.getElementById('addItemBtn');
        if (addItemBtn) {
            addItemBtn.addEventListener('click', function() {
                if (typeof addItem === 'function') {
                    addItem();
                } else {
                    console.error('addItem function not defined');
                }
            });
        }
        
        // Common service items
        document.querySelectorAll('.add-service-item').forEach(button => {
            button.addEventListener('click', function() {
                if (typeof addItem === 'function') {
                    addItem();
                    const lastRow = itemsBody.lastElementChild;
                    if (lastRow) {
                        lastRow.querySelector('.item-type').value = this.dataset.type;
                        lastRow.querySelector('.item-name').value = this.dataset.name;
                        lastRow.querySelector('.item-price').value = this.dataset.price;
                        updateItemTotal(lastRow);
                    }
                } else {
                    console.error('addItem function not defined');
                }
                calculateTotals();
            });
        });
        
        // Customer selection
        const customerSelect = document.getElementById('customerSelect');
        // DOM elements will be accessed inside document.ready
        console.log('DOM elements loaded, waiting for document.ready...');
        
        // Initialize everything after document is ready
        $(document).ready(function() {
            console.log('=== DOCUMENT READY ===');
            
            // Define helper functions
            function updateCustomerInfo(customerId) {
                console.log('Updating customer info for ID:', customerId);
                const customerInfo = document.getElementById('customerInfo');
                
                if (!customerId) {
                    if (customerInfo) {
                        customerInfo.innerHTML = '<div class="alert alert-info mb-0">Select a customer to see their details</div>';
                    }
                    return;
                }
                
                // Show loading message
                if (customerInfo) {
                    customerInfo.innerHTML = `
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Loading customer details...
                        </div>
                    `;
                }
                
                // Make AJAX call to get customer vehicles
                $.ajax({
                    url: '/customers/' + customerId + '/vehicles',
                    method: 'GET',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    success: function(response) {
                        console.log('Customer vehicles loaded:', response);
                        
                        if (customerInfo) {
                            if (response.vehicles && response.vehicles.length > 0) {
                                let vehiclesHtml = '';
                                response.vehicles.forEach(function(vehicle) {
                                    vehiclesHtml += `
                                        <div class="mb-2">
                                            <i class="fas fa-car me-2 text-muted"></i>
                                            <strong>${vehicle.year} ${vehicle.make} ${vehicle.model}</strong>
                                            <small class="text-muted ms-2">(${vehicle.license_plate || 'No plate'})</small>
                                            <br>
                                            <small class="text-muted">VIN: ${vehicle.vin || 'N/A'}</small>
                                        </div>
                                    `;
                                });
                                
                                customerInfo.innerHTML = `
                                    <div class="card border-primary">
                                        <div class="card-header bg-primary text-white py-2">
                                            <i class="fas fa-user me-2"></i>Customer Information
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Customer ID</small>
                                                    <strong>${customerId}</strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Total Vehicles</small>
                                                    <strong>${response.vehicles.length}</strong>
                                                </div>
                                            </div>
                                            <hr class="my-2">
                                            <div class="mt-2">
                                                <small class="text-muted d-block mb-2">Customer Vehicles:</small>
                                                ${vehiclesHtml}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            } else {
                                customerInfo.innerHTML = `
                                    <div class="card border-warning">
                                        <div class="card-header bg-warning text-white py-2">
                                            <i class="fas fa-user me-2"></i>Customer Information
                                        </div>
                                        <div class="card-body p-3">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Customer ID</small>
                                                    <strong>${customerId}</strong>
                                                </div>
                                                <div class="col-md-6">
                                                    <small class="text-muted d-block">Total Vehicles</small>
                                                    <strong>0</strong>
                                                </div>
                                            </div>
                                            <hr class="my-2">
                                            <div class="alert alert-warning mb-0">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                This customer has no vehicles registered.
                                            </div>
                                        </div>
                                    </div>
                                `;
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error loading customer vehicles:', error);
                        
                        if (customerInfo) {
                            customerInfo.innerHTML = `
                                <div class="card border-danger">
                                    <div class="card-header bg-danger text-white py-2">
                                        <i class="fas fa-user me-2"></i>Customer Information
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Customer ID</small>
                                                <strong>${customerId}</strong>
                                            </div>
                                            <div class="col-md-6">
                                                <small class="text-muted d-block">Status</small>
                                                <span class="badge bg-danger">Error Loading</span>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="alert alert-danger mb-0">
                                            <i class="fas fa-exclamation-circle me-2"></i>
                                            Could not load customer vehicles. Please try again.
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                    }
                });
            }
            
            // Initialize Select2
            console.log('Initializing Select2');
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
            console.log('Select2 initialized');
            
            // Now attach event listeners AFTER Select2 is initialized
            console.log('Attaching event listeners...');
            
            const customerSelect = document.getElementById('customerSelect');
            const vehicleSelect = document.getElementById('vehicleSelect');
            const workOrderSelect = document.getElementById('workOrderSelect');
            
            if (customerSelect) {
                console.log('Found customer select element');
                
                // Use Select2 change event
                $(customerSelect).on('change.select2', function() {
                    const customerId = this.value;
                    console.log('=== CUSTOMER CHANGE EVENT FIRED ===');
                    console.log('Customer changed to:', customerId);
                    updateCustomerInfo(customerId);
                    
                    // Filter vehicles for this customer
                    if (vehicleSelect) {
                        console.log('=== FILTERING VEHICLES ===');
                        console.log('Customer ID selected:', customerId);
                        console.log('Total options in vehicle dropdown:', vehicleSelect.options.length);
                        
                        // Log all options before filtering
                        console.log('All vehicle options:');
                        Array.from(vehicleSelect.options).forEach((option, index) => {
                            console.log(`  [${index}] Value: ${option.value}, Text: ${option.text}, data-customer-id: ${option.dataset.customerId}`);
                        });
                        
                        // First, enable all options
                        Array.from(vehicleSelect.options).forEach(option => {
                            option.disabled = false;
                            option.style.display = 'block';
                        });
                        
                        // If a customer is selected, disable options that don't match
                        if (customerId) {
                            console.log('Disabling non-matching vehicles for customer:', customerId);
                            let disabledCount = 0;
                            let enabledCount = 0;
                            
                            Array.from(vehicleSelect.options).forEach(option => {
                                if (option.value === '') {
                                    console.log('  Keeping "Select Vehicle" option enabled');
                                    return; // Keep "Select Vehicle" enabled
                                }
                                
                                const customerMatch = option.dataset.customerId === customerId;
                                console.log(`  Option ${option.value}: customer=${option.dataset.customerId}, match=${customerMatch}`);
                                
                                if (!customerMatch) {
                                    option.disabled = true;
                                    option.style.display = 'none';
                                    disabledCount++;
                                    
                                    // If this option was selected but doesn't match, clear selection
                                    if (option.selected) {
                                        console.log('  Clearing selected vehicle that doesnt match:', option.value);
                                        option.selected = false;
                                        vehicleSelect.value = '';
                                    }
                                } else {
                                    enabledCount++;
                                }
                            });
                            
                            console.log(`Filtering complete: ${enabledCount} enabled, ${disabledCount} disabled`);
                        } else {
                            console.log('No customer selected, showing all vehicles');
                        }
                        
                        // Update Select2
                        $(vehicleSelect).trigger('change.select2');
                        console.log('=== FILTERING COMPLETE ===');
                    }
                    
                    // Filter work orders for this customer
                    if (workOrderSelect) {
                        console.log('Filtering work orders for customer:', customerId);
                        
                        // First, enable all options
                        Array.from(workOrderSelect.options).forEach(option => {
                            option.disabled = false;
                            option.style.display = 'block';
                        });
                        
                        // If a customer is selected, disable options that don't match
                        if (customerId) {
                            Array.from(workOrderSelect.options).forEach(option => {
                                if (option.value === '') return; // Keep "Select Work Order" enabled
                                
                                const customerMatch = option.dataset.customerId === customerId;
                                
                                if (!customerMatch) {
                                    option.disabled = true;
                                    option.style.display = 'none';
                                    
                                    // If this option was selected but doesn't match, clear selection
                                    if (option.selected) {
                                        option.selected = false;
                                        workOrderSelect.value = '';
                                    }
                                }
                            });
                        }
                        
                        // Update Select2
                        $(workOrderSelect).trigger('change.select2');
                        console.log('Work order dropdown filtered');
                    }
                });
                
                console.log('Customer change event listener attached');
                
                // If customer is pre-selected, trigger the change event
                if (customerSelect.value) {
                    console.log('Customer pre-selected:', customerSelect.value);
                    $(customerSelect).trigger('change.select2');
                }
            } else {
                console.error('Customer select element not found!');
            }
            
            // Work order selection event
            if (workOrderSelect) {
                console.log('Found work order select element');
                
                $(workOrderSelect).on('change.select2', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    console.log('Work order selected:', selectedOption.value);
                    
                    if (selectedOption.value && selectedOption.dataset.customerId) {
                        console.log('Setting customer from work order:', selectedOption.dataset.customerId);
                        
                        // Set customer
                        if (customerSelect) {
                            customerSelect.value = selectedOption.dataset.customerId;
                            $(customerSelect).trigger('change.select2');
                            
                            // Select the vehicle if it matches the work order
                            if (vehicleSelect && selectedOption.dataset.vehicleId) {
                                // Wait a bit for the filtering to complete
                                setTimeout(() => {
                                    console.log('Setting vehicle from work order:', selectedOption.dataset.vehicleId);
                                    vehicleSelect.value = selectedOption.dataset.vehicleId;
                                    $(vehicleSelect).trigger('change.select2');
                                }, 200);
                            }
                        }
                    }
                });
                
                console.log('Work order change event listener attached');
            }
            
            console.log('=== EVENT LISTENERS ATTACHED ===');
        });
    });
</script>
@endsection
