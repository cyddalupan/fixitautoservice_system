@extends('layouts.app')

@section('title', 'Low Stock Inventory')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">
                    <i class="fas fa-exclamation-triangle text-warning"></i> Low Stock Inventory
                </h1>
                <div>
                    <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Inventory
                    </a>
                    <a href="{{ route('inventory.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Item
                    </a>
                </div>
            </div>
            <p class="text-muted mb-0">Items that are at or below their reorder point</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Low Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_low_stock']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Out of Stock Items</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['total_out_of_stock']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Value at Risk</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">₱{{ number_format($stats['total_value_at_risk'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-money-bill-wave fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Action Required</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if($stats['total_low_stock'] > 0)
                                    <span class="text-danger">Urgent</span>
                                @else
                                    <span class="text-success">None</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-bell fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Low Stock Items Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-warning">
                <i class="fas fa-list"></i> Low Stock Items ({{ $inventory->total() }})
            </h6>
            <div class="btn-group">
                <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-download"></i> Export
                </button>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="#"><i class="fas fa-file-csv"></i> CSV</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-file-excel"></i> Excel</a>
                    <a class="dropdown-item" href="#"><i class="fas fa-file-pdf"></i> PDF</a>
                </div>
                <button class="btn btn-sm btn-outline-warning" data-toggle="modal" data-target="#reorderModal">
                    <i class="fas fa-shopping-cart"></i> Generate Reorder List
                </button>
            </div>
        </div>
        <div class="card-body">
            @if($inventory->isEmpty())
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h4 class="text-success">No Low Stock Items!</h4>
                    <p class="text-muted">All inventory items are above their reorder points.</p>
                    <a href="{{ route('inventory.index') }}" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Back to Full Inventory
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-bordered table-hover" id="lowStockTable" width="100%" cellspacing="0">
                        <thead class="bg-warning text-dark">
                            <tr>
                                <th>Part Number</th>
                                <th>Item Name</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th class="text-center">Current Qty</th>
                                <th class="text-center">Min Stock</th>
                                <th class="text-center">Reorder Point</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Cost Value</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($inventory as $item)
                                @php
                                    $statusClass = '';
                                    if ($item->quantity <= 0) {
                                        $statusClass = 'bg-danger text-white';
                                    } elseif ($item->quantity <= $item->reorder_point) {
                                        $statusClass = 'bg-warning text-dark';
                                    }
                                    
                                    $costValue = $item->quantity * $item->cost_price;
                                @endphp
                                <tr class="{{ $statusClass }}">
                                    <td>
                                        <strong>{{ $item->part_number }}</strong>
                                        @if($item->oem_number)
                                            <br><small class="text-muted">OEM: {{ $item->oem_number }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $item->name }}</strong>
                                        @if($item->description)
                                            <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->category)
                                            <span class="badge badge-primary">{{ $item->category->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">Uncategorized</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->supplier)
                                            <span class="badge badge-info">{{ $item->supplier->name }}</span>
                                        @else
                                            <span class="badge badge-secondary">No Supplier</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="font-weight-bold {{ $item->quantity <= 0 ? 'text-danger' : ($item->quantity <= $item->reorder_point ? 'text-warning' : 'text-success') }}">
                                            {{ number_format($item->quantity) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($item->minimum_stock) }}
                                    </td>
                                    <td class="text-center">
                                        {{ number_format($item->reorder_point) }}
                                    </td>
                                    <td class="text-center">
                                        @if($item->quantity <= 0)
                                            <span class="badge badge-danger">Out of Stock</span>
                                        @elseif($item->quantity <= $item->minimum_stock)
                                            <span class="badge badge-danger">Critical</span>
                                        @elseif($item->quantity <= $item->reorder_point)
                                            <span class="badge badge-warning">Low Stock</span>
                                        @else
                                            <span class="badge badge-success">Adequate</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold">
                                        ₱{{ number_format($costValue, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('inventory.show', $item->id) }}" class="btn btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="btn btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('inventory.adjust-quantity', $item->id) }}" class="btn btn-primary" title="Adjust Quantity">
                                                <i class="fas fa-plus-minus"></i>
                                            </a>
                                            <button type="button" class="btn btn-success" title="Reorder" data-toggle="modal" data-target="#reorderItemModal{{ $item->id }}">
                                                <i class="fas fa-shopping-cart"></i>
                                            </button>
                                        </div>
                                        
                                        <!-- Reorder Item Modal -->
                                        <div class="modal fade" id="reorderItemModal{{ $item->id }}" tabindex="-1" role="dialog" aria-labelledby="reorderItemModalLabel{{ $item->id }}" aria-hidden="true">
                                            <div class="modal-dialog" role="document">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="reorderItemModalLabel{{ $item->id }}">
                                                            <i class="fas fa-shopping-cart text-success"></i> Reorder Item
                                                        </h5>
                                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <h6>{{ $item->name }}</h6>
                                                        <p class="text-muted">{{ $item->part_number }}</p>
                                                        
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            <strong>Current Stock:</strong> {{ $item->quantity }} units
                                                            <br>
                                                            <strong>Reorder Point:</strong> {{ $item->reorder_point }} units
                                                            <br>
                                                            <strong>Deficit:</strong> {{ max(0, $item->reorder_point - $item->quantity) }} units
                                                        </div>
                                                        
                                                        <form id="reorderForm{{ $item->id }}">
                                                            @csrf
                                                            <div class="form-group">
                                                                <label for="reorderQuantity{{ $item->id }}">Quantity to Order</label>
                                                                <input type="number" class="form-control" id="reorderQuantity{{ $item->id }}" 
                                                                       name="quantity" min="1" value="{{ max(10, $item->reorder_point - $item->quantity) }}" required>
                                                                <small class="form-text text-muted">Suggested: {{ max(10, $item->reorder_point - $item->quantity) }} units</small>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="supplier{{ $item->id }}">Supplier</label>
                                                                <select class="form-control" id="supplier{{ $item->id }}" name="supplier_id" required>
                                                                    @if($item->supplier)
                                                                        <option value="{{ $item->supplier->id }}" selected>{{ $item->supplier->name }}</option>
                                                                    @endif
                                                                    <!-- Additional suppliers would be loaded here -->
                                                                </select>
                                                            </div>
                                                            <div class="form-group">
                                                                <label for="expectedDate{{ $item->id }}">Expected Delivery Date</label>
                                                                <input type="date" class="form-control" id="expectedDate{{ $item->id }}" 
                                                                       name="expected_date" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                        <button type="button" class="btn btn-success" onclick="submitReorder({{ $item->id }})">
                                                            <i class="fas fa-paper-plane"></i> Submit Order
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td colspan="8" class="text-right font-weight-bold">Total Value at Risk:</td>
                                <td class="text-center font-weight-bold text-danger">₱{{ number_format($stats['total_value_at_risk'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $inventory->firstItem() }} to {{ $inventory->lastItem() }} of {{ $inventory->total() }} items
                    </div>
                    <div>
                        {{ $inventory->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Reorder List Modal -->
    <div class="modal fade" id="reorderModal" tabindex="-1" role="dialog" aria-labelledby="reorderModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="reorderModalLabel">
                        <i class="fas fa-shopping-cart text-success"></i> Generate Reorder List
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> This will generate a purchase order list for all low stock items.
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Current Qty</th>
                                    <th>Reorder Point</th>
                                    <th>Deficit</th>
                                    <th>Suggested Order</th>
                                    <th>Supplier</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($inventory as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->part_number }}</strong><br>
                                            <small>{{ Str::limit($item->name, 30) }}</small>
                                        </td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-center">{{ $item->reorder_point }}</td>
                                        <td class="text-center text-danger font-weight-bold">
                                            {{ max(0, $item->reorder_point - $item->quantity) }}
                                        </td>
                                        <td class="text-center">
                                            {{ max(10, $item->reorder_point - $item->quantity) }}
                                        </td>
                                        <td>
                                            @if($item->supplier)
                                                {{ $item->supplier->name }}
                                            @else
                                                <span class="text-muted">No Supplier</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="printReorderList()">
                        <i class="fas fa-print"></i> Print List
                    </button>
                    <button type="button" class="btn btn-success" onclick="generatePurchaseOrder()">
                        <i class="fas fa-file-invoice"></i> Create Purchase Order
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    function submitReorder(itemId) {
        const form = document.getElementById('reorderForm' + itemId);
        const formData = new FormData(form);
        
        // Add item_id to form data
        formData.append('item_id', itemId);
        
        fetch('/inventory/' + itemId + '/reorder', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#reorderItemModal' + itemId).modal('hide');
                showToast('success', 'Reorder submitted successfully!');
                // Reload page after 2 seconds
                setTimeout(() => location.reload(), 2000);
            } else {
                showToast('error', data.message || 'Error submitting reorder');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('error', 'Network error. Please try again.');
        });
    }
    
    function printReorderList() {
        const printContent = document.getElementById('reorderModal').querySelector('.modal-body').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Reorder List - Fixit Auto Services</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    h1 { color: #333; border-bottom: 2px solid #333; padding-bottom: 10px; }
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    .total { font-weight: bold; background-color: #f8f9fa; }
                    .header { text-align: center; margin-bottom: 30px; }
                    .date { float: right; }
                    @media print {
                        .no-print { display: none; }
                        body { margin: 0; }
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Fixit Auto Services - Reorder List</h1>
                    <p>Generated on: ${new Date().toLocaleDateString()}</p>
                    <p class="date">Low Stock Items Report</p>
                </div>
                ${printContent}
                <div class="no-print" style="margin-top: 20px; text-align: center;">
                    <button onclick="window.print()" class="btn btn-primary">Print</button>
                    <button onclick="document.body.innerHTML = originalContent; location.reload();" class="btn btn-secondary">Close</button>
                </div>
            </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }
    
    function generatePurchaseOrder() {
        // Collect all low stock items
        const items = [];
        document.querySelectorAll('#lowStockTable tbody tr').forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 9) {
                items.push({
                    part_number: cells[0].querySelector('strong').textContent.trim(),
                    name: cells[1].querySelector('strong').textContent.trim(),
                    current_qty: parseInt(cells[4].textContent.trim().replace(/,/g, '')),
                    reorder_point: parseInt(cells[6].textContent.trim().replace(/,/g, '')),
                    supplier: cells[3].querySelector('.badge').textContent.trim()
                });
            }
        });
        
        // Create purchase order data
        const poData = {
            items: items,
            date: new Date().toISOString().split('T')[0],
            total_items: items.length
        };
        
        // Save to localStorage for the purchase order page
        localStorage.setItem('purchaseOrderData', JSON.stringify(poData));
        
        // Redirect to purchase order page
        window.location.href = '/purchase-orders/create?from=low-stock';
    }
    
    function showToast(type, message) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');
        
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Add to toast container
        const toastContainer = document.getElementById('toastContainer');
        if (!toastContainer) {
            const container = document.createElement('div');
            container.id = 'toastContainer';
            container.className = 'toast-container position-fixed top-0 end-0 p-3';
            container.style.zIndex = '9999';
            document.body.appendChild(container);
        }
        
        document.getElementById('toastContainer').appendChild(toast);
        
        // Initialize and show toast
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.addEventListener('hidden.bs.toast', function () {
            toast.remove();
        });
    }
    
    // Initialize DataTable if table exists
    document.addEventListener('DOMContentLoaded', function() {
        const table = document.getElementById('lowStockTable');
        if (table) {
            // Simple sorting functionality
            const headers = table.querySelectorAll('thead th');
            headers.forEach((header, index) => {
                header.style.cursor = 'pointer';
                header.addEventListener('click', function() {
                    sortTable(index);
                });
            });
        }
    });
    
    function sortTable(columnIndex) {
        const table = document.getElementById('lowStockTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        // Determine sort direction
        const currentDir = table.getAttribute('data-sort-dir') || 'asc';
        const newDir = currentDir === 'asc' ? 'desc' : 'asc';
        table.setAttribute('data-sort-dir', newDir);
        
        rows.sort((a, b) => {
            const aCell = a.querySelectorAll('td')[columnIndex];
            const bCell = b.querySelectorAll('td')[columnIndex];
            
            let aValue = aCell.textContent.trim();
            let bValue = bCell.textContent.trim();
            
            // Try to parse as number
            const aNum = parseFloat(aValue.replace(/[^0-9.-]+/g, ''));
            const bNum = parseFloat(bValue.replace(/[^0-9.-]+/g, ''));
            
            if (!isNaN(aNum) && !isNaN(bNum)) {
                aValue = aNum;
                bValue = bNum;
            }
            
            if (newDir === 'asc') {
                return aValue > bValue ? 1 : -1;
            } else {
                return aValue < bValue ? 1 : -1;
            }
        });
        
        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
        
        // Update sort indicators
        const headers = table.querySelectorAll('thead th');
        headers.forEach((header, index) => {
            header.classList.remove('sort-asc', 'sort-desc');
            if (index === columnIndex) {
                header.classList.add(`sort-${newDir}`);
            }
        });
    }
</script>
@endsection