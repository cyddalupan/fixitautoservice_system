@extends('layouts.app')

@section('title', 'Record New Expense')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Record New Expense</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('expenses.index') }}">Expenses</a></li>
                        <li class="breadcrumb-item active">New Expense</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Expense Details</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('expenses.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date" class="form-label">Date *</label>
                                <input type="date" class="form-control @error('date') is-invalid @enderror" 
                                       id="date" name="date" value="{{ old('date', date('Y-m-d')) }}" required>
                                @error('date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="category" class="form-label">Category *</label>
                                <select class="form-select @error('category') is-invalid @enderror" 
                                        id="category" name="category" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $key => $label)
                                        <option value="{{ $key }}" {{ old('category') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('category')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">Description *</label>
                            <input type="text" class="form-control @error('description') is-invalid @enderror" 
                                   id="description" name="description" value="{{ old('description') }}" 
                                   placeholder="e.g., Office supplies purchase, Tool replacement, Utility bill payment" required>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="amount" class="form-label">Amount (₱) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">₱</span>
                                    <input type="number" class="form-control @error('amount') is-invalid @enderror" 
                                           id="amount" name="amount" value="{{ old('amount') }}" 
                                           step="0.01" min="0" placeholder="0.00" required>
                                </div>
                                @error('amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="vendor" class="form-label">Vendor / Supplier</label>
                                <input type="text" class="form-control @error('vendor') is-invalid @enderror" 
                                       id="vendor" name="vendor" value="{{ old('vendor') }}" 
                                       placeholder="e.g., Office Depot, Hardware Store, Meralco">
                                @error('vendor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="payment_method" class="form-label">Payment Method</label>
                                <select class="form-select @error('payment_method') is-invalid @enderror" 
                                        id="payment_method" name="payment_method">
                                    <option value="">Select Method</option>
                                    @foreach($paymentMethods as $key => $label)
                                        <option value="{{ $key }}" {{ old('payment_method') == $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('payment_method')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="reference_number" class="form-label">Reference Number</label>
                                <input type="text" class="form-control @error('reference_number') is-invalid @enderror" 
                                       id="reference_number" name="reference_number" value="{{ old('reference_number') }}" 
                                       placeholder="e.g., Check #1234, Transaction ID">
                                @error('reference_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Additional details about this expense">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="receipt" class="form-label">Receipt (Optional)</label>
                            <input type="file" class="form-control @error('receipt') is-invalid @enderror" 
                                   id="receipt" name="receipt" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">
                                Upload receipt image or PDF (max: 2MB). Supported formats: JPG, PNG, PDF.
                            </div>
                            @error('receipt')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('expenses.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Back to Expenses
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Record Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Expense Guidelines -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Expense Guidelines</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>Best Practices:</h6>
                        <ul class="mb-0">
                            <li>Record expenses as soon as they occur</li>
                            <li>Always attach receipts when available</li>
                            <li>Use clear, descriptive descriptions</li>
                            <li>Categorize expenses correctly</li>
                        </ul>
                    </div>
                    
                    <h6 class="mt-3">Category Definitions:</h6>
                    <div class="small">
                        <p><strong>Office Supplies:</strong> Paper, pens, printer ink, etc.</p>
                        <p><strong>Tools & Equipment:</strong> Wrenches, diagnostic tools, etc.</p>
                        <p><strong>Utilities:</strong> Electricity, water, internet bills</p>
                        <p><strong>Rent:</strong> Shop/office rental payments</p>
                        <p><strong>Shop Supplies:</strong> Cleaning materials, lubricants, etc.</p>
                        <p><strong>Vehicle Expenses:</strong> Fuel, maintenance, repairs for company vehicles</p>
                        <p><strong>Other:</strong> Any expense that doesn't fit other categories</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-chart-bar me-2"></i>This Month's Expenses</h5>
                </div>
                <div class="card-body">
                    @php
                        $monthlyExpenses = App\Models\Expense::thisMonth()->sum('amount');
                        $pendingCount = App\Models\Expense::pending()->count();
                    @endphp
                    <div class="text-center">
                        <h3 class="text-danger">₱{{ number_format($monthlyExpenses, 2) }}</h3>
                        <p class="text-muted">Total expenses this month</p>
                        
                        @if($pendingCount > 0)
                            <div class="alert alert-warning small mb-0">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ $pendingCount }} expense(s) pending approval
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Set today's date as default
        const dateInput = document.getElementById('date');
        if (dateInput && !dateInput.value) {
            dateInput.value = new Date().toISOString().split('T')[0];
        }
        
        // Format amount input
        const amountInput = document.getElementById('amount');
        amountInput?.addEventListener('blur', function() {
            if (this.value) {
                this.value = parseFloat(this.value).toFixed(2);
            }
        });
    });
</script>
@endpush