@extends('layouts.app')

@section('title', 'Labor Rates Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-tools"></i> Labor Rates Management
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" data-toggle="modal" data-target="#addLaborRateModal">
                            <i class="fas fa-plus"></i> Add New Rate
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
                                    <th>Rate Name</th>
                                    <th>Code</th>
                                    <th>Hourly Rate</th>
                                    <th>Min. Charge</th>
                                    <th>Default</th>
                                    <th>Status</th>
                                    <th>Effective From</th>
                                    <th>Effective To</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($laborRates as $rate)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $rate->rate_name }}</td>
                                    <td><span class="badge bg-secondary">{{ $rate->rate_code }}</span></td>
                                    <td>${{ number_format($rate->hourly_rate, 2) }}</td>
                                    <td>${{ number_format($rate->minimum_charge, 2) }}</td>
                                    <td>
                                        @if($rate->is_default)
                                        <span class="badge bg-success">Yes</span>
                                        @else
                                        <span class="badge bg-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($rate->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-danger">Inactive</span>
                                        @endif
                                    </td>
                                    <td>{{ $rate->effective_from ? $rate->effective_from->format('Y-m-d') : 'N/A' }}</td>
                                    <td>{{ $rate->effective_to ? $rate->effective_to->format('Y-m-d') : 'N/A' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#editLaborRateModal{{ $rate->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('pricing.labor-rates.destroy', $rate) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this labor rate?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Modal for each rate -->
                                <div class="modal fade" id="editLaborRateModal{{ $rate->id }}" tabindex="-1" role="dialog" aria-labelledby="editLaborRateModalLabel{{ $rate->id }}" aria-hidden="true">
                                    <div class="modal-dialog modal-lg" role="document">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editLaborRateModalLabel{{ $rate->id }}">Edit Labor Rate: {{ $rate->rate_name }}</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <form action="{{ route('pricing.labor-rates.update', $rate) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="rate_name">Rate Name *</label>
                                                                <input type="text" class="form-control" id="rate_name" name="rate_name" value="{{ $rate->rate_name }}" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="rate_code">Rate Code *</label>
                                                                <input type="text" class="form-control" id="rate_code" name="rate_code" value="{{ $rate->rate_code }}" required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="description">Description</label>
                                                        <textarea class="form-control" id="description" name="description" rows="2">{{ $rate->description }}</textarea>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="hourly_rate">Hourly Rate ($) *</label>
                                                                <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" value="{{ $rate->hourly_rate }}" step="0.01" min="0" required>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="minimum_charge">Minimum Charge ($)</label>
                                                                <input type="number" class="form-control" id="minimum_charge" name="minimum_charge" value="{{ $rate->minimum_charge }}" step="0.01" min="0">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="is_default{{ $rate->id }}" name="is_default" value="1" {{ $rate->is_default ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="is_default{{ $rate->id }}">Default Rate</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" class="custom-control-input" id="is_active{{ $rate->id }}" name="is_active" value="1" {{ $rate->is_active ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="is_active{{ $rate->id }}">Active</label>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <div class="form-group">
                                                                <label for="sort_order">Sort Order</label>
                                                                <input type="number" class="form-control" id="sort_order" name="sort_order" value="{{ $rate->sort_order }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="effective_from">Effective From</label>
                                                                <input type="date" class="form-control" id="effective_from" name="effective_from" value="{{ $rate->effective_from ? $rate->effective_from->format('Y-m-d') : '' }}">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="effective_to">Effective To</label>
                                                                <input type="date" class="form-control" id="effective_to" name="effective_to" value="{{ $rate->effective_to ? $rate->effective_to->format('Y-m-d') : '' }}">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="applicable_categories">Applicable Categories (JSON)</label>
                                                                <textarea class="form-control" id="applicable_categories" name="applicable_categories" rows="2" placeholder='["category1", "category2"]'>{{ $rate->applicable_categories ? json_encode($rate->applicable_categories) : '' }}</textarea>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <label for="applicable_technicians">Applicable Technicians (JSON)</label>
                                                                <textarea class="form-control" id="applicable_technicians" name="applicable_technicians" rows="2" placeholder='[1, 2, 3]'>{{ $rate->applicable_technicians ? json_encode($rate->applicable_technicians) : '' }}</textarea>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Rate</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="10" class="text-center">No labor rates found. Click "Add New Rate" to create your first labor rate.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="text-muted">Showing {{ $laborRates->count() }} labor rates</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Labor Rate Modal -->
<div class="modal fade" id="addLaborRateModal" tabindex="-1" role="dialog" aria-labelledby="addLaborRateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addLaborRateModalLabel">Add New Labor Rate</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('pricing.labor-rates.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_rate_name">Rate Name *</label>
                                <input type="text" class="form-control" id="new_rate_name" name="rate_name" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_rate_code">Rate Code *</label>
                                <input type="text" class="form-control" id="new_rate_code" name="rate_code" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="new_description">Description</label>
                        <textarea class="form-control" id="new_description" name="description" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_hourly_rate">Hourly Rate ($) *</label>
                                <input type="number" class="form-control" id="new_hourly_rate" name="hourly_rate" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_minimum_charge">Minimum Charge ($)</label>
                                <input type="number" class="form-control" id="new_minimum_charge" name="minimum_charge" step="0.01" min="0">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="new_is_default" name="is_default" value="1">
                                    <label class="custom-control-label" for="new_is_default">Default Rate</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" class="custom-control-input" id="new_is_active" name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="new_is_active">Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="new_sort_order">Sort Order</label>
                                <input type="number" class="form-control" id="new_sort_order" name="sort_order" value="0">
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_applicable_categories">Applicable Categories (JSON)</label>
                                <textarea class="form-control" id="new_applicable_categories" name="applicable_categories" rows="2" placeholder='["category1", "category2"]'></textarea>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="new_applicable_technicians">Applicable Technicians (JSON)</label>
                                <textarea class="form-control" id="new_applicable_technicians" name="applicable_technicians" rows="2" placeholder='[1, 2, 3]'></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Rate</button>
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
});
</script>
@endpush
@endsection