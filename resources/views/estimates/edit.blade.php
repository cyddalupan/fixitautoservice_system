@extends('layouts.app')

@section('title', 'Edit Estimate - ' . $estimate->estimate_number)

@push('styles')
<style>
/* Autocomplete styles */
.position-relative {
    position: relative !important;
}

.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    margin-top: 2px;
    z-index: 1050;
    display: none;
    max-height: 250px; /* Reduced from 300px */
    max-width: 100%; /* Ensure doesn't exceed viewport */
    overflow-y: auto;
    overflow-x: hidden; /* Prevent horizontal scroll */
    background-color: white;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Dropdown positioned above input */
.autocomplete-dropdown.dropdown-up {
    top: auto;
    bottom: 100%;
    margin-top: 0;
    margin-bottom: 2px;
}

/* Ensure dropdown stays within viewport */
.autocomplete-dropdown.dropdown-up {
    top: auto;
    bottom: 100%;
    margin-top: 0;
    margin-bottom: 2px;
}

.autocomplete-dropdown::-webkit-scrollbar {
    width: 12px;
}

.autocomplete-dropdown::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 6px;
}

.autocomplete-dropdown::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 6px;
}

.autocomplete-dropdown::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.autocomplete-dropdown .list-group-item {
    border: none;
    border-radius: 0;
    padding: 0.5rem 1rem;
    cursor: pointer;
}

.autocomplete-dropdown .list-group-item:hover {
    background-color: #f8f9fa;
}

.autocomplete-dropdown .list-group-item-secondary {
    background-color: #e9ecef;
    font-size: 0.875rem;
    padding: 0.25rem 1rem;
    cursor: default;
}

.description-display {
    display: block;
    margin-top: 0.25rem;
    color: #6c757d;
    font-style: italic;
    min-height: 1.2em;
}

/* Ensure table cells allow dropdown overflow */
#itemsTable td {
    position: relative !important;
    overflow: visible !important;
}

#itemsTable .position-relative {
    position: relative !important;
    overflow: visible !important;
}

/* Ensure dropdown is not clipped by any parent */
#itemsTable,
#itemsTable tbody,
#itemsTable tr,
#itemsTable td,
.table-responsive {
    overflow: visible !important;
}

.item-name-display {
    display: block;
    padding: 0.375rem 0.75rem;
    font-weight: 500;
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 0.375rem;
    min-height: 38px;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit text-primary"></i> Edit Estimate #{{ $estimate->estimate_number }}
        </h1>
        <div>
            <a href="{{ route('estimates.show', $estimate->id) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="{{ route('estimates.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <form action="{{ route('estimates.update', $estimate->id) }}" method="POST" id="estimateForm">
        @csrf
        @method('PUT')

        <div class="row mb-4">
            <div class="col-md-6">
                <h5 class="text-primary mb-3"><i class="fas fa-info-circle"></i> Basic Information</h5>
                
                <div class="mb-3">
                    <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
                    <select name="customer_id" id="customer_id" class="form-select" required>
                        <option value="">Select Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ $estimate->customer_id == $customer->id ? 'selected' : '' }}>
                                {{ $customer->first_name }} {{ $customer->last_name }} - {{ $customer->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="vehicle_id" class="form-label">Vehicle <span class="text-danger">*</span></label>
                    <select name="vehicle_id" id="vehicle_id" class="form-select" required>
                        <option value="">Select Vehicle</option>
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" {{ $estimate->vehicle_id == $vehicle->id ? 'selected' : '' }}>
                                {{ $vehicle->make }} {{ $vehicle->model }} ({{ $vehicle->year }}) - {{ $vehicle->license_plate }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-md-6">
                <h5 class="text-primary mb-3"><i class="fas fa-calendar"></i> Schedule & Status</h5>
                
                <div class="mb-3">
                    <label for="estimate_date" class="form-label">Estimate Date</label>
                    <input type="date" name="issue_date" id="issue_date" class="form-control" value="{{ ($estimate->issue_date ? $estimate->issue_date->format('Y-m-d') : '') }}">
                </div>

                <div class="mb-3">
                    <label for="valid_until" class="form-label">Valid Until</label>
                    <input type="date" name="expiry_date" id="expiry_date" class="form-control" value="{{ ($estimate->expiry_date ? $estimate->expiry_date->format('Y-m-d') : '') }}">
                </div>

                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="draft" {{ $estimate->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="sent" {{ $estimate->status == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="viewed" {{ $estimate->status == 'viewed' ? 'selected' : '' }}>Viewed</option>
                        <option value="accepted" {{ $estimate->status == 'accepted' ? 'selected' : '' }}>Accepted</option>
                        <option value="rejected" {{ $estimate->status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="expired" {{ $estimate->status == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label for="technician_id" class="form-label">Technician</label>
                    <select name="technician_id" id="technician_id" class="form-select">
                        <option value="">Select Technician</option>
                        @php
                            $technicians = \App\Models\User::where('role', 'technician')->where('is_active', true)->get();
                        @endphp
                        @foreach($technicians as $tech)
                            <option value="{{ $tech->id }}" {{ $estimate->technician_id == $tech->id ? 'selected' : '' }}>
                                {{ $tech->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header bg-dark text-white">
                <h6 class="mb-0"><i class="fas fa-list"></i> Estimate Items</h6>
            </div>
            <div class="card-body">
                @php
                    $inventoryItems = $inventoryItems ?? collect();
                @endphp
                @php
                    $inventoryData = $inventoryItems->map(function($item) {
                        return [
                            'id' => $item->id,
                            'name' => $item->name,
                            'part_number' => $item->part_number,
                            'category' => $item->category_id ? ($item->category->name ?? 'Other') : 'Other',
                            'retail_price' => floatval($item->retail_price),
                            'quantity' => $item->quantity
                        ];
                    })->toArray();
                @endphp
                <script>
                    // Inventory items data for autocomplete (only items with quantity > 0)
                    const inventoryItems = @json($inventoryData);
                </script>
                <div class="table-responsive">
                    <table class="table table-bordered" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Item / Service</th>
                                <th width="100">Qty</th>
                                <th width="150">Unit Price</th>
                                <th width="150">Subtotal</th>
                                <th width="50"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            @foreach($estimate->items as $index => $item)
                            <tr id="itemRow_{{ $index }}" class="existing-item">
                                <td>
                                    <span class="item-name-display">{{ $item->inventory->name ?? 'N/A' }}</span>
                                    <input type="hidden" name="items[{{ $index }}][item_name]" value="{{ $item->inventory->name ?? '' }}">
                                    <input type="hidden" name="items[{{ $index }}][inventory_id]" value="{{ $item->inventory_id }}">
                                    <input type="hidden" name="items[{{ $index }}][description]" value="{{ $item->description }}">
                                    <input type="hidden" name="items[{{ $index }}][id]" value="{{ $item->id }}">
                                </td>
                                <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control text-center qty" value="{{ $item->quantity }}" min="1" required onchange="updateRowTotal({{ $index }})" oninput="updateRowTotal({{ $index }})"></td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" name="items[{{ $index }}][unit_price]" class="form-control unit-price-input" value="{{ $item->unit_price }}" step="0.01" min="0" required onchange="updateRowTotal({{ $index }})" oninput="updateRowTotal({{ $index }})">
                                    </div>
                                </td>
                                <td class="text-end subtotal">₱{{ number_format($item->total_price, 2) }}</td>
                                <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <button type="button" class="btn btn-success btn-sm" id="addItem">
                    <i class="fas fa-plus"></i> Add Item
                </button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0"><i class="fas fa-sticky-note"></i> Notes</h6>
                    </div>
                    <div class="card-body">
                        <textarea name="notes" class="form-control" rows="4" placeholder="Additional notes...">{{ $estimate->notes }}</textarea>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header bg-warning">
                        <h6 class="mb-0"><i class="fas fa-calculator"></i> Totals</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotalDisplay">₱{{ number_format($estimate->subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax Rate (%):</span>
                            <input type="number" name="tax_rate" id="taxRate" class="form-control w-50 text-end" value="{{ $estimate->tax_rate }}" step="0.01" min="0">
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax Amount:</span>
                            <span id="taxDisplay">₱{{ number_format($estimate->tax_amount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Discount:</span>
                            <input type="number" name="discount_amount" id="discountAmount" class="form-control w-50 text-end" value="{{ $estimate->discount_amount }}" step="0.01" min="0">
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong id="totalDisplay">₱{{ number_format($estimate->total_amount, 2) }}</strong>
                        </div>
                        <input type="hidden" name="subtotal" id="subtotalInput" value="{{ $estimate->subtotal }}">
                        <input type="hidden" name="tax_amount" id="taxInput" value="{{ $estimate->tax_amount }}">
                        <input type="hidden" name="total_amount" id="totalInput" value="{{ $estimate->total_amount }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Update Estimate
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
let itemIndex = {{ count($estimate->items) }};

function updateTotals() {
    let subtotal = 0;
    document.querySelectorAll('#itemsBody tr').forEach(row => {
        const qty = parseFloat(row.querySelector('.qty').value) || 0;
        const price = parseFloat(row.querySelector('.unit-price-input').value) || 0;
        const itemSubtotal = qty * price;
        subtotal += itemSubtotal;
        row.querySelector('.subtotal').textContent = '₱' + itemSubtotal.toFixed(2);
    });

    const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const taxAmount = subtotal * (taxRate / 100);
    const total = subtotal + taxAmount - discount;

    document.getElementById('subtotalDisplay').textContent = '₱' + subtotal.toFixed(2);
    document.getElementById('taxDisplay').textContent = '₱' + taxAmount.toFixed(2);
    document.getElementById('totalDisplay').textContent = '₱' + total.toFixed(2);
    document.getElementById('subtotalInput').value = subtotal;
    document.getElementById('taxInput').value = taxAmount;
    document.getElementById('totalInput').value = total;
}

document.getElementById('addItem').addEventListener('click', function() {
    const tbody = document.getElementById('itemsBody');
    const tr = document.createElement('tr');
    tr.id = 'itemRow_' + itemIndex;
    tr.innerHTML = `
        <td>
            <div class="position-relative">
                <input type="text" 
                       class="form-control item-name-input" 
                       name="items[${itemIndex}][item_name]" 
                       placeholder="Type item or service name..."
                       autocomplete="off"
                       required>
                <input type="hidden" name="items[${itemIndex}][inventory_id]" class="inventory-id-input">
                <input type="hidden" name="items[${itemIndex}][description]" class="description-input">
                
                <!-- Autocomplete dropdown -->
                <div class="autocomplete-dropdown">
                    <div class="list-group"></div>
                </div>
            </div>
            <small class="text-muted description-display"></small>
        </td>
        <td><input type="number" name="items[${itemIndex}][quantity]" class="form-control text-center qty" value="1" min="1" required onchange="updateRowTotal(itemIndex)" oninput="updateRowTotal(itemIndex)"></td>
        <td>
            <div class="input-group">
                <span class="input-group-text">₱</span>
                <input type="number" name="items[${itemIndex}][unit_price]" class="form-control unit-price-input" value="0" step="0.01" min="0" required onchange="updateRowTotal(itemIndex)" oninput="updateRowTotal(itemIndex)">
            </div>
        </td>
        <td class="text-end subtotal">₱0.00</td>
        <td><button type="button" class="btn btn-danger btn-sm remove-item"><i class="fas fa-trash"></i></button></td>
    `;
    tbody.appendChild(tr);
    
    // Initialize autocomplete for this row
    initAutocomplete(itemIndex);
    
    itemIndex++;
    updateTotals();
});

// Initialize autocomplete for an item row
function initAutocomplete(rowId) {
    const row = document.getElementById('itemRow_' + rowId);
    const input = row.querySelector('.item-name-input');
    const dropdown = row.querySelector('.autocomplete-dropdown');
    const listGroup = dropdown.querySelector('.list-group');
    
    // Show suggestions on focus/click
    input.addEventListener('focus', function() {
        showSuggestions(rowId);
    });
    
    // Filter as user types
    input.addEventListener('input', function() {
        const query = this.value.toLowerCase();
        
        if (query.length < 2) {
            dropdown.style.display = 'none';
            return;
        }
        
        showSuggestions(rowId, query);
    });
    
    // Function to position dropdown within viewport
    function positionDropdown(dropdown, input) {
        // First make dropdown visible temporarily to measure it
        dropdown.style.display = 'block';
        dropdown.style.visibility = 'hidden';
        
        const inputRect = input.getBoundingClientRect();
        const dropdownHeight = dropdown.offsetHeight;
        const viewportHeight = window.innerHeight;
        
        // Hide dropdown again
        dropdown.style.display = 'none';
        dropdown.style.visibility = 'visible';
        
        // Check if there's enough space below
        const spaceBelow = viewportHeight - inputRect.bottom - 10; // 10px margin
        
        if (spaceBelow >= dropdownHeight || spaceBelow >= 150) {
            // Enough space below, show below
            dropdown.style.top = '100%';
            dropdown.style.bottom = 'auto';
            dropdown.classList.remove('dropdown-up');
        } else {
            // Not enough space below, show above
            dropdown.style.top = 'auto';
            dropdown.style.bottom = '100%';
            dropdown.classList.add('dropdown-up');
        }
        
        // Ensure dropdown doesn't go off-screen horizontally
        if (inputRect.left + dropdown.offsetWidth > window.innerWidth) {
            dropdown.style.left = 'auto';
            dropdown.style.right = '0';
        } else {
            dropdown.style.left = '0';
            dropdown.style.right = 'auto';
        }
    }
    
    // Function to show suggestions
    function showSuggestions(rowId, query = '') {
        let matches;
        
        if (query === '') {
            // Show all items when focused without typing
            matches = inventoryItems;
        } else {
            // Filter inventory items based on query
            matches = inventoryItems.filter(item => 
                item.name.toLowerCase().includes(query) ||
                (item.part_number && item.part_number.toLowerCase().includes(query))
            );
        }
        
        if (matches.length === 0) {
            listGroup.innerHTML = '<div class="list-group-item text-muted text-center py-3">No items found</div>';
            positionDropdown(dropdown, input);
            dropdown.style.display = 'block';
            return;
        }
        
        // Group by category
        const grouped = {};
        matches.forEach(item => {
            const cat = item.category || 'Other';
            if (!grouped[cat]) grouped[cat] = [];
            grouped[cat].push(item);
        });
        
        let html = '';
        for (const [category, items] of Object.entries(grouped)) {
            html += `<div class="list-group-item-secondary">${category}</div>`;
            items.forEach(item => {
                html += `<button type="button" class="list-group-item list-group-item-action" onclick="selectItem(${rowId}, ${item.id}, '${item.name.replace(/'/g, "\\'")}', ${item.retail_price}, '${(item.category || '').replace(/'/g, "\\'")}')">
                        <div class="d-flex justify-content-between">
                            <div>
                                <strong>${item.name}</strong>
                                <br>
                                <small class="text-muted">${item.part_number || 'No Part #'} | ${item.manufacturer || 'No Manufacturer'}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-success">₱${item.retail_price.toFixed(2)}</span>
                                <br>
                                <small class="text-muted">Stock: ${item.quantity}</small>
                            </div>
                        </div>
                    </button>`;
            });
        }
        
        listGroup.innerHTML = html;
        positionDropdown(dropdown, input);
        dropdown.style.display = 'block';
    }
    
    // Hide dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!row.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });
}

// Select item from autocomplete
function selectItem(rowId, itemId, itemName, price, category) {
    const row = document.getElementById('itemRow_' + rowId);
    const input = row.querySelector('.item-name-input');
    const inventoryIdInput = row.querySelector('.inventory-id-input');
    const descriptionInput = row.querySelector('.description-input');
    const descriptionDisplay = row.querySelector('.description-display');
    const unitPriceInput = row.querySelector('.unit-price-input');
    
    input.value = itemName;
    inventoryIdInput.value = itemId;
    descriptionInput.value = category;
    descriptionDisplay.textContent = category;
    unitPriceInput.value = price.toFixed(2);
    
    // Hide dropdown
    row.querySelector('.autocomplete-dropdown').style.display = 'none';
    
    updateRowTotal(rowId);
}

// Update row total
function updateRowTotal(rowId) {
    const row = document.getElementById('itemRow_' + rowId);
    const qty = parseFloat(row.querySelector('.qty').value) || 0;
    const price = parseFloat(row.querySelector('.unit-price-input').value) || 0;
    const subtotal = qty * price;
    
    row.querySelector('.subtotal').textContent = '₱' + subtotal.toFixed(2);
    updateTotals();
}

document.addEventListener('click', function(e) {
    if (e.target.closest('.remove-item')) {
        e.target.closest('tr').remove();
        updateTotals();
    }
});

document.addEventListener('input', function(e) {
    if (e.target.classList.contains('qty') || e.target.classList.contains('unit-price-input') || 
        e.target.id === 'taxRate' || e.target.id === 'discountAmount') {
        updateTotals();
    }
});

// Initial calculation
updateTotals();
</script>
@endpush
@endsection
