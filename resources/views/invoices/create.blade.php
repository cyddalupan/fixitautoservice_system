@extends('layouts.app')

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

<div class="row">
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
                                            {{ $selectedVehicle && $selectedVehicle->id == $vehicle->id ? 'selected' : '' }}>
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
        
        // Add first item
        addItem();
        
        // Add item button
        document.getElementById('addItemBtn').addEventListener('click', addItem);
        
        // Common service items
        document.querySelectorAll('.add-service-item').forEach(button => {
            button.addEventListener('click', function() {
                addItem();
                const lastRow = itemsBody.lastElementChild;
                lastRow.querySelector('.item-type').value = this.dataset.type;
                lastRow.querySelector('.item-name').value = this.dataset.name;
                lastRow.querySelector('.item-price').value = this.dataset.price;
                updateItemTotal(lastRow);
                calculateTotals();
            });
        });
        
        // Customer selection
        const customerSelect = document.getElementById('customerSelect');
        const vehicleSelect = document.getElementById('vehicleSelect');
        const workOrderSelect = document.getElementById('workOrderSelect');
        const customerInfo = document.getElementById('customerInfo');
        
        if (customerSelect) {
            customerSelect.addEventListener('change', function() {
                updateCustomerInfo(this.value);
                
                // Filter vehicles for this customer
                if (vehicleSelect) {
                    const customerId = this.value;
                    Array.from(vehicleSelect.options).forEach(option => {
                        if (option.value === '') return;
                        option.style.display = 'block';
                    });
                }
                
                // Filter work orders for this customer
                if (workOrderSelect) {
                    const customerId = this.value;
                    Array.from(workOrderSelect.options).forEach(option => {
                        if (option.value === '') return;
                        const customerMatch = option.dataset.customerId === customerId;
                        option.style.display = customerMatch ? 'block' : 'none';
                    });
                }
            });
        }
        
        // Work order selection
        if (workOrderSelect) {
            workOrderSelect.addEventListener('change', function() {
                const selectedOption = this.options[this.selectedIndex];
                if (selectedOption.value && selectedOption.dataset.customerId) {
                    // Set customer
                    if (customerSelect) {
                        customerSelect.value = selectedOption.dataset.customerId;
                        updateCustomerInfo(selectedOption.dataset.customerId);
