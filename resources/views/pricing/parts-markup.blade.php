@extends('layouts.app')

@section('title', 'Parts Markup Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cogs"></i> Parts Markup Management
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addPartsMarkupModal">
                            <i class="fas fa-plus"></i> Add New Rule
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                        <i class="icon fas fa-check"></i> {{ session('success') }}
                    </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Rule Name</th>
                                    <th>Type</th>
                                    <th>Value</th>
                                    <th>Category</th>
                                    <th>Supplier</th>
                                    <th>Cost Range</th>
                                    <th>Retail Range</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Effective</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($partsMarkups as $markup)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
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
                                    <td>
                                        @if($markup->minimum_cost || $markup->maximum_cost)
                                        ${{ number_format($markup->minimum_cost ?? 0, 2) }} - ${{ number_format($markup->maximum_cost ?? 0, 2) }}
                                        @else
                                        <span class="text-muted">Any</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($markup->minimum_retail || $markup->maximum_retail)
                                        ${{ number_format($markup->minimum_retail ?? 0, 2) }} - ${{ number_format($markup->maximum_retail ?? 0, 2) }}
                                        @else
                                        <span class="text-muted">Any</span>
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
                                        {{ $markup->effective_from ? $markup->effective_from->format('Y-m-d') : 'N/A' }}<br>
                                        {{ $markup->effective_to ? $markup->effective_to->format('Y-m-d') : 'N/A' }}
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editPartsMarkupModal{{ $markup->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('pricing.parts-markup.destroy', $markup) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this markup rule?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal for each markup rule -->
                                <div class="modal fade" id="editPartsMarkupModal{{ $markup->id }}" tabindex="-1" role="dialog" aria-labelledby="editPartsMarkupModalLabel{{ $markup->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editPartsMarkupModalLabel{{ $markup->id }}">Edit Markup Rule: {{ $markup->markup_name }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('pricing.parts-markup.update', $markup) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <div class="form-group">
                                                                <label for="markup_name">Rule Name *</label>
                                                                <input type="text" class="form-control" id="markup_name" name="markup_name" value="{{ $markup->markup_name }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="markup_type">Markup Type *</label>
                                                                <select class="form-control" id="markup_type" name="markup_type" required>
                                                                    <option value="percentage" {{ $markup->markup_type == 'percentage' ? 'selected' : '' }}>Percentage</option>
                                                                    <option value="fixed" {{ $markup->markup_type == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                                    <option value="tiered" {{ $markup->markup_type == 'tiered' ? 'selected' : '' }}>Tiered</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="markup_value">Markup Value *</label>
                                                                <input type="number" class="form-control" id="markup_value" name="markup_value" value="{{ $markup->markup_value }}" step="0.01" min="0" required>
                                                                <small class="form-text text-muted">
                                                                    @if($markup->markup_type == 'percentage')
                                                                    Percentage (e.g., 30 for 30%)
                                                                    @else
                                                                    Fixed amount in dollars
                                                                    @endif
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="category_id">Category</label>
                                                                <select class="form-control" id="category_id" name="category_id">
                                                                    <option value="">Select Category</option>
                                                                    @foreach($categories as $category)
                                                                    <option value="{{ $category->id }}" {{ $markup->category_id == $category->id ? 'selected' : '' }}>
                                                                        {{ $category->name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="supplier_id">Supplier</label>
                                                                <select class="form-control" id="supplier_id" name="supplier_id">
                                                                    <option value="">Select Supplier</option>
                                                                    @foreach($suppliers as $supplier)
                                                                    <option value="{{ $supplier->id }}" {{ $markup->supplier_id == $supplier->id ? 'selected' : '' }}>
                                                                        {{ $supplier->name }}
                                                                    </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="apply_to_all_categories{{ $markup->id }}" name="apply_to_all_categories" value="1" {{ $markup->apply_to_all_categories ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="apply_to_all_categories{{ $markup->id }}">All Categories</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="apply_to_all_suppliers{{ $markup->id }}" name="apply_to_all_suppliers" value="1" {{ $markup->apply_to_all_suppliers ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="apply_to_all_suppliers{{ $markup->id }}">All Suppliers</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="is_active{{ $markup->id }}" name="is_active" value="1" {{ $markup->is_active ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="is_active{{ $markup->id }}">Active</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <div class="form-group">
                                                                <label for="priority">Priority</label>
                                                                <input type="number" class="form-control" id="priority" name="priority" value="{{ $markup->priority }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="minimum_cost">Minimum Cost ($)</label>
                                                                <input type="number" class="form-control" id="minimum_cost" name="minimum_cost" value="{{ $markup->minimum_cost }}" step="0.01" min="0">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="maximum_cost">Maximum Cost ($)</label>
                                                                <input type="number" class="form-control" id="maximum_cost" name="maximum_cost" value="{{ $markup->maximum_cost }}" step="0.01" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="minimum_retail">Minimum Retail ($)</label>
                                                                <input type="number" class="form-control" id="minimum_retail" name="minimum_retail" value="{{ $markup->minimum_retail }}" step="0.01" min="0">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="maximum_retail">Maximum Retail ($)</label>
                                                                <input type="number" class="form-control" id="maximum_retail" name="maximum_retail" value="{{ $markup->maximum_retail }}" step="0.01" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="effective_from">Effective From</label>
                                                                <input type="date" class="form-control" id="effective_from" name="effective_from" value="{{ $markup->effective_from ? $markup->effective_from->format('Y-m-d') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="effective_to">Effective To</label>
                                                                <input type="date" class="form-control" id="effective_to" name="effective_to" value="{{ $markup->effective_to ? $markup->effective_to->format('Y-m-d') : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Rule</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="12" class="text-center">No markup rules found. Click "Add New Rule" to create your first markup rule.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted">Showing {{ $partsMarkups->count() }} markup rules</p>
                        </div>
                        <div class="col-md-6 text-right">
                            <small class="text-muted">Rules are applied in priority order (higher priority first)</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Parts Markup Modal -->
<div class="modal fade" id="addPartsMarkupModal" tabindex="-1" role="dialog" aria-labelledby="addPartsMarkupModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addPartsMarkupModalLabel">Add New Markup Rule</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('pricing.parts-markup.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="new_markup_name">Rule Name *</label>
                                <input type="text" class="form-control" id="new_markup_name" name="markup_name" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="new_markup_type">Markup Type *</label>
                                <select class="form-control" id="new_markup_type" name="markup_type" required>
                                    <option value="percentage">Percentage</option>
                                    <option value="fixed">Fixed Amount</option>
                                    <option value="tiered">Tiered</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="new_markup_value">Markup Value *</label>
                                <input type="number" class="form-control" id="new_markup_value" name="markup_value" step="0.01" min="0" required>
                                <small class="form-text text-muted" id="new_markup_value_help">
                                    Percentage (e.g., 30 for 30%)
                                </small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="new_category_id">Category</label>
                                <select class="form-control" id="new_category_id" name="category_id">
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="new_supplier_id">Supplier</label>
                                <select class="form-control" id="new_supplier_id" name="supplier_id">
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="new_apply_to_all_categories" name="apply_to_all_categories" value="1">
                                    <label class="custom-control-label" for="new_apply_to_all_categories">All Categories</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="new_apply_to_all_suppliers" name="apply_to_all_suppliers" value="1">
                                    <label class="custom-control-label" for="new_apply_to_all_suppliers">All Suppliers</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="new_is_active" name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="new_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="new_priority">Priority</label>
                                <input type="number" class="form-control" id="new_priority" name="priority" value="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_minimum_cost">Minimum Cost ($)</label>
                                <input type="number" class="form-control" id="new_minimum_cost" name="minimum_cost" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_maximum_cost">Maximum Cost ($)</label>
                                <input type="number" class="form-control" id="new_maximum_cost" name="maximum_cost" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_minimum_retail">Minimum Retail ($)</label>
                                <input type="number" class="form-control" id="new_minimum_retail" name="minimum_retail" step="0.01" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_maximum_retail">Maximum Retail ($)</label>
                                <input type="number" class="form-control" id="new_maximum_retail" name="maximum_retail" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_effective_from">Effective From</label>
                                <input type="date" class="form-control" id="new_effective_from" name="effective_from">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_effective_to">Effective To</label>
                                <input type="date" class="form-control" id="new_effective_to" name="effective_to">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Rule</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('styles')
<style>
    .table th {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize datepickers
    $('input[type="date"]').each(function() {
        if (!$(this).val()) {
            $(this).val(new Date().toISOString().split('T')[0]);
        }
    });

    // Update help text based on markup type
    $('#new_markup_type').change(function() {
        const type = $(this).val();
        const helpText = $('#new_markup_value_help');
        
        if (type === 'percentage') {
            helpText.text('Percentage (e.g., 30 for 30%)');
        } else if (type === 'fixed') {
            helpText.text('Fixed amount in dollars');
        } else {
            helpText.text('Tiered markup (fixed amount per tier)');
        }
    });

    // Handle "All Categories" checkbox
    $('#new_apply_to_all_categories').change(function() {
        if ($(this).is(':checked')) {
            $('#new_category_id').prop('disabled', true).val('');
        } else {
            $('#new_category_id').prop('disabled', false);
        }
    });

    // Handle "All Suppliers" checkbox
    $('#new_apply_to_all_suppliers').change(function() {
        if ($(this).is(':checked')) {
            $('#new_supplier_id').prop('disabled', true).val('');
        } else {
            $('#new_supplier_id').prop('disabled', false);
        }
    });
});
</script>
@endpush
@endsection