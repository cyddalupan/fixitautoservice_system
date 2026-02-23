@extends('layouts.app')

@section('title', 'Edit Parts Order - FixIt Auto Services')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-edit me-2"></i>Edit Parts Order #{{ $partsOrder->order_number }}
                </h1>
                <div>
                    <a href="{{ route('parts-procurement.show', $partsOrder) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                </div>
            </div>
            <p class="text-muted mb-0">Update parts order details and items</p>
        </div>
    </div>

    <form action="{{ route('parts-procurement.update', $partsOrder) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Left Column: Order Details -->
            <div class="col-lg-8">
                <!-- Basic Information Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2"></i>Order Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vendor_id" class="form-label fw-bold">
                                        <i class="fas fa-store me-1"></i>Vendor
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('vendor_id') is-invalid @enderror" 
                                            id="vendor_id" 
                                            name="vendor_id" 
                                            required>
                                        <option value="">Select Vendor</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" 
                                                    {{ old('vendor_id', $partsOrder->vendor_id) == $vendor->id ? 'selected' : '' }}>
                                                {{ $vendor->name }}
                                                @if($vendor->contact_phone)
                                                    ({{ $vendor->contact_phone }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vendor_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="order_type" class="form-label fw-bold">
                                        <i class="fas fa-tag me-1"></i>Order Type
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('order_type') is-invalid @enderror" 
                                            id="order_type" 
                                            name="order_type" 
                                            required>
                                        <option value="standard" {{ old('order_type', $partsOrder->order_type) == 'standard' ? 'selected' : '' }}>Standard</option>
                                        <option value="emergency" {{ old('order_type', $partsOrder->order_type) == 'emergency' ? 'selected' : '' }}>Emergency/Rush</option>
                                        <option value="warranty" {{ old('order_type', $partsOrder->order_type) == 'warranty' ? 'selected' : '' }}>Warranty</option>
                                        <option value="stock" {{ old('order_type', $partsOrder->order_type) == 'stock' ? 'selected' : '' }}>Stock Replenishment</option>
                                    </select>
                                    @error('order_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="priority" class="form-label fw-bold">
                                        <i class="fas fa-exclamation-circle me-1"></i>Priority
                                        <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select @error('priority') is-invalid @enderror" 
                                            id="priority" 
                                            name="priority" 
                                            required>
                                        <option value="low" {{ old('priority', $partsOrder->priority) == 'low' ? 'selected' : '' }}>Low</option>
                                        <option value="normal" {{ old('priority', $partsOrder->priority) == 'normal' ? 'selected' : '' }}>Normal</option>
                                        <option value="high" {{ old('priority', $partsOrder->priority) == 'high' ? 'selected' : '' }}>High</option>
                                        <option value="urgent" {{ old('priority', $partsOrder->priority) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="work_order_id" class="form-label fw-bold">
                                        <i class="fas fa-wrench me-1"></i>Work Order (Optional)
                                    </label>
                                    <select class="form-select @error('work_order_id') is-invalid @enderror" 
                                            id="work_order_id" 
                                            name="work_order_id">
                                        <option value="">Not Linked</option>
                                        @foreach($workOrders as $workOrder)
                                            <option value="{{ $workOrder->id }}" 
                                                    {{ old('work_order_id', $partsOrder->work_order_id) == $workOrder->id ? 'selected' : '' }}>
                                                #{{ $workOrder->work_order_number }} - 
                                                {{ $workOrder->customer->name ?? 'Unknown' }} - 
                                                {{ $workOrder->vehicle->make ?? '' }} {{ $workOrder->vehicle->model ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('work_order_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="vehicle_id" class="form-label fw-bold">
                                        <i class="fas fa-car me-1"></i>Vehicle (Optional)
                                    </label>
                                    <select class="form-select @error('vehicle_id') is-invalid @enderror" 
                                            id="vehicle_id" 
                                            name="vehicle_id">
                                        <option value="">Not Specified</option>
                                        @foreach($vehicles as $vehicle)
                                            <option value="{{ $vehicle->id }}" 
                                                    {{ old('vehicle_id', $partsOrder->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                                {{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}
                                                @if($vehicle->vin)
                                                    (VIN: {{ $vehicle->vin }})
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('vehicle_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="shipping_method" class="form-label fw-bold">
                                        <i class="fas fa-truck me-1"></i>Shipping Method
                                    </label>
                                    <select class="form-select @error('shipping_method') is-invalid @enderror" 
                                            id="shipping_method" 
                                            name="shipping_method">
                                        <option value="">Standard Shipping</option>
                                        <option value="ground" {{ old('shipping_method', $partsOrder->shipping_method) == 'ground' ? 'selected' : '' }}>Ground</option>
                                        <option value="express" {{ old('shipping_method', $partsOrder->shipping_method) == 'express' ? 'selected' : '' }}>Express</option>
                                        <option value="overnight" {{ old('shipping_method', $partsOrder->shipping_method) == 'overnight' ? 'selected' : '' }}>Overnight</option>
                                        <option value="pickup" {{ old('shipping_method', $partsOrder->shipping_method) == 'pickup' ? 'selected' : '' }}>Local Pickup</option>
                                    </select>
                                    @error('shipping_method')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label fw-bold">
                                        <i class="fas fa-sticky-note me-1"></i>Notes (Optional)
                                    </label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" 
                                              name="notes" 
                                              rows="3" 
                                              placeholder="Add any notes about this order...">{{ old('notes', $partsOrder->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="special_instructions" class="form-label fw-bold">
                                        <i class="fas fa-exclamation-circle me-1"></i>Special Instructions (Optional)
                                    </label>
                                    <textarea class="form-control @error('special_instructions') is-invalid @enderror" 
                                              id="special_instructions" 
                                              name="special_instructions" 
                                              rows="2" 
                                              placeholder="Any special instructions for the vendor...">{{ old('special_instructions', $partsOrder->special_instructions) }}</textarea>
                                    @error('special_instructions')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Items Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="fas fa-list me-2"></i>Order Items
                            </h5>
                            <button type="button" class="btn btn-light btn-sm" onclick="addItemRow()">
                                <i class="fas fa-plus me-1"></i> Add Item
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="items-container">
                            @if(old('items') && count(old('items')) > 0)
                                @foreach(old('items') as $index => $item)
                                    @include('parts-procurement.partials.item-row', [
                                        'index' => $index,
                                        'item' => (object) $item
                                    ])
                                @endforeach
                            @elseif($partsOrder->items && count($partsOrder->items) > 0)
                                @foreach($partsOrder->items as $index => $item)
                                    @include('parts-procurement.partials.item-row', [
                                        'index' => $index,
                                        'item' => $item
                                    ])
                                @endforeach
                            @else
                                @include('parts-procurement.partials.item-row', ['index' => 0])
                            @endif
                        </div>
                        
                        <div class="row mt-4">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th class="text-end">Subtotal:</th>
                                        <td class="text-end" width="150">
                                            $<span id="subtotal">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-end">Tax ({{ $taxRate }}%):</th>
                                        <td class="text-end">
                                            $<span id="tax">0.00</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="text-end">Shipping:</th>
                                        <td class="text-end">
                                            $<span id="shipping">0.00</span>
                                        </td>
                                    </tr>
                                    <tr class="table-active">
                                        <th class="text-end fs-5">Total:</th>
                                        <td class="text-end fs-5">
                                            $<span id="total">0.00</span>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Actions & Help -->
            <div class="col-lg-4">
                <!-- Actions Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0">
                            <i class="fas fa-cogs me-2"></i>Actions
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Changes
                            </button>
                            
                            @if($partsOrder->status == 'draft')
                            <button type="submit" name="action" value="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-1"></i> Save & Submit for Approval
                            </button>
                            @endif
                            
                            <a href="{{ route('parts-procurement.show', $partsOrder) }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i> Cancel
                            </a>
                            
                            <button type="button" class="btn btn-outline-danger" 
                                    onclick="if(confirm('Are you sure you want to delete this order?')) { document.getElementById('delete-form').submit(); }">
                                <i class="fas fa-trash me-1"></i> Delete Order
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i>Quick Help
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <p class="mb-2">
                                <strong>Vendor:</strong> Select the supplier for this order.
                            </p>
                            <p class="mb-2">
                                <strong>Order Type:</strong>
                            </p>
                            <ul class="mb-2 ps-3">
                                <li><strong>Standard:</strong> Regular parts order</li>
                                <li><strong>Emergency:</strong> Rush order for urgent repairs</li>
                                <li><strong>Warranty:</strong> Warranty claim parts</li>
                                <li><strong>Stock:</strong> Inventory replenishment</li>
                            </ul>
                            <p class="mb-2">
                                <strong>Priority:</strong> Set urgency level for processing.
                            </p>
                            <p class="mb-0">
                                <strong>Items:</strong> Add all parts needed. Prices will be calculated automatically.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Part Lookup Card -->
                <div class="card shadow-sm">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-search me-2"></i>Quick Part Lookup
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="input-group mb-2">
                            <input type="text" 
                                   class="form-control" 
                                   id="part-search" 
                                   placeholder="Search part number or name...">
                            <button class="btn btn-outline-success" type="button" onclick="searchPart()">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <div id="part-search-results" class="small"></div>
                        <div class="mt-3">
                            <a href="{{ route('parts-procurement.lookup') }}" class="btn btn-outline-success btn-sm w-100">
                                <i class="fas fa-external-link-alt me-1"></i> Advanced Parts Lookup
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Delete Form (Hidden) -->
    <form id="delete-form" action="{{ route('parts-procurement.destroy', $partsOrder) }}" method="POST" class="d-none">
        @csrf
        @method('DELETE')
    </form>
</div>

<!-- Item Row Template (Hidden) -->
<template id="item-row-template">
    @include('parts-procurement.partials.item-row', ['index' => '__INDEX__'])
</template>

@section('scripts')
<script>
    let itemCount = {{ old('items') ? count(old('items')) : ($partsOrder->items ? count($partsOrder->items) : 1) }};
    const taxRate = {{ $taxRate }};
    
    function addItemRow() {
        const template = document.getElementById('item-row-template').innerHTML;
        const newRow = template.replace(/__INDEX__/g, itemCount);
        
        const container = document.getElementById('items-container');
        container.insertAdjacentHTML('beforeend', newRow);
        
        itemCount++;
        updateTotals();
        attachItemEventListeners(itemCount - 1);
    }
    
    function removeItemRow(button) {
        const row = button.closest('.item-row');
        if (document.querySelectorAll('.item-row').length > 1) {
            row.remove();
            updateTotals();
        } else {
            alert('At least one item is required.');
        }
    }
    
    function updateItemTotal(input) {
        const row = input.closest('.item-row');
        const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
        const total = quantity * unitPrice;
        
        row.querySelector('.item-total').textContent = total.toFixed(2);
        updateTotals();
    }
    
    function updateTotals() {
        let subtotal = 0;
        
        document.querySelectorAll('.item-row').forEach(row => {
            const quantity = parseFloat(row.querySelector('.item-quantity').value) || 0;
            const unitPrice = parseFloat(row.querySelector('.item-unit-price').value) || 0;
            subtotal += quantity * unitPrice;
        });
        
        const tax = subtotal * (taxRate / 100);
        const shipping = parseFloat(document.getElementById('shipping_amount').value) || 0;
        const total = subtotal + tax + shipping;
        
        document.getElementById('subtotal').textContent = subtotal.toFixed(2);
        document.getElementById('tax').textContent = tax.toFixed(2);
        document.getElementById('shipping').textContent = shipping.toFixed(2);
        document.getElementById('total').textContent = total.toFixed(2);
        
        // Update hidden total field
        document.getElementById('total_amount').value = total;
        document.getElementById('subtotal_amount').value = subtotal;
        document.getElementById('tax_amount').value = tax;
    }
    
    function attachItemEventListeners(index) {
        const row = document.querySelector(`.item-row[data-index="${index}"]`);
        if (row) {
            row.querySelector('.item-quantity').addEventListener('input', function() {
                updateItemTotal(this);
            });
            row.querySelector('.item-unit-price').addEventListener('input', function() {
                updateItemTotal(this);
            });
            row.querySelector('.remove-item').addEventListener('click', function() {
                removeItemRow(this);
            });
        }
    }
    
    function searchPart() {
        const searchTerm = document.getElementById('part-search').value.trim();
        if (!searchTerm) return;
        
        const resultsDiv = document.getElementById('part-search-results');
        resultsDiv.innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</div>';
        
        // This would normally be an API call
        // For now, we'll simulate with sample data
        setTimeout(() => {
            const sampleParts = [
                { part_number: 'ABC123', name: 'Brake Rotor', price: 89.99 },
                { part_number: 'DEF456', name: 'Brake Pad Set', price: 45.50 },
                { part_number: 'GHI789', name: 'Oil Filter', price: 12.99 },
            ];
            
            let html = '<div class="list-group list-group-flush">';
            sampleParts.forEach(part => {
                if (part.part_number.includes(searchTerm) || part.name.toLowerCase().includes(searchTerm.toLowerCase())) {
                    html += `
                        <div class="list-group-item border-0 px-0 py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold">${part.name}</div>
                                    <div class="small text-muted">${part.part_number}</div>
                                </div>
                                <div>
                                    <div class="text-end">$${part.price.toFixed(2)}</div>
                                    <button type="button" class="btn btn-sm btn-outline-success mt-1" onclick="addPartToOrder('${part.part_number}', '${part.name}', ${part.price})">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                }
            });
            
            if (html === '<div class="list-group list-group-flush">') {
                html += '<div class="text-center text-muted py-2">No parts found</div>';
            }
            
            html += '</div>';
            resultsDiv.innerHTML = html;
        }, 500);
    }
    
    function addPartToOrder(partNumber, partName, price) {
        // Add a new item row with the selected part
        addItemRow();
        
        // Get the last added row
        const rows = document.querySelectorAll('.item-row');
        const lastRow = rows[rows.length - 1];
        
        // Fill in the part details
        lastRow.querySelector('.item-part-number').value = partNumber;
        lastRow.querySelector('.item-part-name').value = partName;
        lastRow.querySelector('.item-unit-price').value = price.toFixed(2);
        
        // Update totals
        updateItemTotal(lastRow.querySelector('.item-unit-price'));
        
        // Clear search
        document.getElementById('part-search').value = '';
        document.getElementById('part-search-results').innerHTML = '';
    }
    
    // Initialize event listeners on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Attach event listeners to existing items
        document.querySelectorAll('.item-row').forEach((row, index) => {
            attachItemEventListeners(index);
        });
        
        // Initialize totals
        updateTotals();
        
        // Shipping amount listener
        document.getElementById('shipping_amount').addEventListener('input', updateTotals);
        
        // Enter key in search field
        document.getElementById('part-search').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                searchPart();
            }
        });
    });
</script>
@endsection