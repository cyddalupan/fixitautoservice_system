@extends('layouts.app')

@section('title', 'Pricing Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-dollar-sign"></i> Pricing & Profit Management
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Labor Rates Summary -->
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-tools"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Labor Rates</span>
                                    <span class="info-box-number">{{ $laborRates->count() }} Active Rates</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 70%"></div>
                                    </div>
                                    <span class="progress-description">
                                        <a href="{{ route('pricing.labor-rates') }}" class="text-white">Manage Labor Rates</a>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Parts Markup Summary -->
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-cogs"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Parts Markup Rules</span>
                                    <span class="info-box-number">{{ $partsMarkups->count() }} Active Rules</span>
                                    <div class="progress">
                                        <div class="progress-bar" style="width: 50%"></div>
                                    </div>
                                    <span class="progress-description">
                                        <a href="{{ route('pricing.parts-markup') }}" class="text-white">Manage Markup Rules</a>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card card-primary">
                                <div class="card-header">
                                    <h3 class="card-title">Quick Actions</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-3 col-sm-6">
                                            <a href="{{ route('pricing.labor-rates') }}" class="btn btn-app bg-info">
                                                <i class="fas fa-tools fa-2x"></i>
                                                <span>Labor Rates</span>
                                            </a>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <a href="{{ route('pricing.parts-markup') }}" class="btn btn-app bg-success">
                                                <i class="fas fa-cogs fa-2x"></i>
                                                <span>Parts Markup</span>
                                            </a>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <a href="{{ route('profit-analysis.index') }}" class="btn btn-app bg-warning">
                                                <i class="fas fa-chart-line fa-2x"></i>
                                                <span>Profit Analysis</span>
                                            </a>
                                        </div>
                                        <div class="col-md-3 col-sm-6">
                                            <a href="#" class="btn btn-app bg-purple" data-toggle="modal" data-target="#priceCalculatorModal">
                                                <i class="fas fa-calculator fa-2x"></i>
                                                <span>Price Calculator</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Labor Rates -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Active Labor Rates</h3>
                                    <div class="card-tools">
                                        <a href="{{ route('pricing.labor-rates') }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Add New Rate
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Rate Name</th>
                                                <th>Code</th>
                                                <th>Hourly Rate</th>
                                                <th>Min. Charge</th>
                                                <th>Default</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($laborRates as $rate)
                                            <tr>
                                                <td>{{ $rate->rate_name }}</td>
                                                <td><span class="badge bg-secondary">{{ $rate->rate_code }}</span></td>
                                                <td>${{ number_format($rate->hourly_rate, 2) }}</td>
                                                <td>${{ number_format($rate->minimum_charge, 2) }}</td>
                                                <td>
                                                    @if($rate->is_default)
                                                    <span class="badge bg-success">Default</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($rate->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                    @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editLaborRateModal{{ $rate->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pricing.labor-rates.destroy', $rate) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="7" class="text-center">No labor rates found. <a href="{{ route('pricing.labor-rates') }}">Create your first labor rate</a></td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Active Parts Markup Rules -->
                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Active Parts Markup Rules</h3>
                                    <div class="card-tools">
                                        <a href="{{ route('pricing.parts-markup') }}" class="btn btn-sm btn-primary">
                                            <i class="fas fa-plus"></i> Add New Rule
                                        </a>
                                    </div>
                                </div>
                                <div class="card-body table-responsive p-0">
                                    <table class="table table-hover text-nowrap">
                                        <thead>
                                            <tr>
                                                <th>Rule Name</th>
                                                <th>Type</th>
                                                <th>Value</th>
                                                <th>Category</th>
                                                <th>Supplier</th>
                                                <th>Priority</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($partsMarkups as $markup)
                                            <tr>
                                                <td>{{ $markup->markup_name }}</td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        {{ ucfirst($markup->markup_type) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if($markup->markup_type == 'percentage')
                                                    {{ $markup->markup_value }}%
                                                    @else
                                                    ${{ number_format($markup->markup_value, 2) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($markup->apply_to_all_categories)
                                                    <span class="badge bg-success">All Categories</span>
                                                    @elseif($markup->category)
                                                    {{ $markup->category->name }}
                                                    @else
                                                    <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($markup->apply_to_all_suppliers)
                                                    <span class="badge bg-success">All Suppliers</span>
                                                    @elseif($markup->supplier)
                                                    {{ $markup->supplier->name }}
                                                    @else
                                                    <span class="text-muted">N/A</span>
                                                    @endif
                                                </td>
                                                <td>{{ $markup->priority }}</td>
                                                <td>
                                                    @if($markup->is_active)
                                                    <span class="badge bg-success">Active</span>
                                                    @else
                                                    <span class="badge bg-danger">Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="#" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editPartsMarkupModal{{ $markup->id }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('pricing.parts-markup.destroy', $markup) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @empty
                                            <tr>
                                                <td colspan="8" class="text-center">No markup rules found. <a href="{{ route('pricing.parts-markup') }}">Create your first markup rule</a></td>
                                            </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Price Calculator Modal -->
<div class="modal fade" id="priceCalculatorModal" tabindex="-1" role="dialog" aria-labelledby="priceCalculatorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="priceCalculatorModalLabel">
                    <i class="fas fa-calculator"></i> Price Calculator
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Labor Cost Calculator</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="laborHours">Labor Hours</label>
                                    <input type="number" class="form-control" id="laborHours" placeholder="Enter hours" step="0.25" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="laborRateCode">Labor Rate</label>
                                    <select class="form-control" id="laborRateCode">
                                        <option value="">Default Rate</option>
                                        @foreach($laborRates as $rate)
                                        <option value="{{ $rate->rate_code }}">{{ $rate->rate_name }} (${{ number_format($rate->hourly_rate, 2) }}/hr)</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" id="calculateLaborCost">
                                    <i class="fas fa-calculator"></i> Calculate Labor Cost
                                </button>
                                <div class="mt-3" id="laborCostResult" style="display: none;">
                                    <div class="alert alert-info">
                                        <h5>Labor Cost Calculation</h5>
                                        <p id="laborCostDetails"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Parts Price Calculator</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="partsCost">Cost Price</label>
                                    <input type="number" class="form-control" id="partsCost" placeholder="Enter cost price" step="0.01" min="0">
                                </div>
                                <div class="form-group">
                                    <label for="partsCategory">Category</label>
                                    <select class="form-control" id="partsCategory">
                                        <option value="">Select Category</option>
                                        @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="partsSupplier">Supplier</label>
                                    <select class="form-control" id="partsSupplier">
                                        <option value="">Select Supplier</option>
                                        @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" class="btn btn-primary" id="calculateRetailPrice">
                                    <i class="fas fa-calculator"></i> Calculate Retail Price
                                </button>
                                <div class="mt-3" id="retailPriceResult" style="display: none;">
                                    <div class="alert alert-success">
                                        <h5>Retail Price Calculation</h5>
                                        <p id="retailPriceDetails"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Labor cost calculation
    $('#calculateLaborCost').click(function() {
        const hours = $('#laborHours').val();
        const rateCode = $('#laborRateCode').val();
        
        if (!hours || hours <= 0) {
            alert('Please enter valid labor hours');
            return;
        }
        
        $.ajax({
            url: '{{ route("pricing.calculate-labor-cost") }}',
            method: 'POST',
            data: {
                hours: hours,
                rate_code: rateCode,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#laborCostDetails').html(`
                    <strong>Rate:</strong> ${response.rate_name}<br>
                    <strong>Hours:</strong> ${response.hours}<br>
                    <strong>Hourly Rate:</strong> $${response.hourly_rate.toFixed(2)}<br>
                    <strong>Minimum Charge:</strong> $${response.minimum_charge.toFixed(2)}<br>
                    <strong>Total Cost:</strong> <span class="font-weight-bold">$${response.total_cost.toFixed(2)}</span>
                `);
                $('#laborCostResult').show();
            },
            error: function(xhr) {
                alert('Error calculating labor cost: ' + xhr.responseJSON.error);
            }
        });
    });
    
    // Retail price calculation
    $('#calculateRetailPrice').click(function() {
        const cost = $('#partsCost').val();
        const categoryId = $('#partsCategory').val();
        const supplierId = $('#partsSupplier').val();
        
        if (!cost || cost <= 0) {
            alert('Please enter valid cost price');
            return;
        }
        
        $.ajax({
            url: '{{ route("pricing.calculate-retail-price") }}',
            method: 'POST',
            data: {
                cost_price: cost,
                category_id: categoryId,
                supplier_id: supplierId,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#retailPriceDetails').html(`
                    <strong>Cost Price:</strong> $${response.cost_price.toFixed(2)}<br>
                    <strong>Retail Price:</strong> <span class="font-weight-bold">$${response.retail_price.toFixed(2)}</span><br>
                    <strong>Markup Amount:</strong> $${response.markup_amount.toFixed(2)}<br>
                    <strong>Markup Percentage:</strong> ${response.markup_percentage.toFixed(2)}%<br>
                    <small class="text-muted">${response.applicable_rules.length} applicable rule(s)</small>
                `);
                $('#retailPriceResult').show();
            },
            error: function(xhr) {
                alert('Error calculating retail price');
            }
        });
    });
});
</script>
@endpush
@endsection