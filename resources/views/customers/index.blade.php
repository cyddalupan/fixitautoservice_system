@extends('layouts.app')

@section('title', 'Customers - Fix-It Auto Services')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-users me-2"></i>Customers
            </h1>
            <p class="text-muted mb-0">Manage your customer database and relationships</p>
        </div>
        <div>
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Customer
            </a>
        </div>
    </div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search customers..." 
                       value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="customer_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="individual" {{ request('customer_type') == 'individual' ? 'selected' : '' }}>Individual</option>
                    <option value="commercial" {{ request('customer_type') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                    <option value="fleet" {{ request('customer_type') == 'fleet' ? 'selected' : '' }}>Fleet</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="segment" class="form-select">
                    <option value="">All Segments</option>
                    <option value="premium" {{ request('segment') == 'premium' ? 'selected' : '' }}>Premium</option>
                    <option value="regular" {{ request('segment') == 'regular' ? 'selected' : '' }}>Regular</option>
                    <option value="commercial" {{ request('segment') == 'commercial' ? 'selected' : '' }}>Commercial</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="is_active" class="form-select">
                    <option value="">All Status</option>
                    <option value="active" {{ request('is_active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('is_active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        @if($customers->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <a href="{{ route('customers.index', array_merge(request()->all(), ['sort' => 'first_name', 'direction' => request('direction') == 'asc' ? 'desc' : 'asc'])) }}">
                                    Customer
                                    @if(request('sort') == 'first_name')
                                        <i class="fas fa-sort-{{ request('direction') == 'asc' ? 'up' : 'down' }}"></i>
                                    @else
                                        <i class="fas fa-sort"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Contact</th>
                            <th>Type</th>
                            <th>Vehicles</th>
                            <th>Total Spent</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customers as $customer)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="customer-avatar me-3">
                                        {{ substr($customer->first_name, 0, 1) }}{{ substr($customer->last_name, 0, 1) }}
                                    </div>
                                    <div>
                                        <strong>{{ $customer->full_name }}</strong>
                                        @if($customer->company_name)
                                            <br>
                                            <small class="text-muted">{{ $customer->company_name }}</small>
                                        @endif
                                        <br>
                                        <small class="text-muted">Customer since {{ $customer->customer_since->format('M Y') }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <i class="fas fa-envelope me-1 text-muted"></i>
                                    <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                                    <br>
                                    <i class="fas fa-phone me-1 text-muted"></i>
                                    {{ $customer->phone ?? 'N/A' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $customer->customer_type == 'individual' ? 'primary' : ($customer->customer_type == 'commercial' ? 'success' : 'warning') }}">
                                    {{ ucfirst($customer->customer_type) }}
                                </span>
                                @if($customer->segment)
                                    <br>
                                    <small class="text-muted">{{ ucfirst($customer->segment) }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-car text-primary me-2"></i>
                                    <div>
                                        <strong>{{ $customer->total_vehicles }}</strong> vehicles
                                        <br>
                                        <small class="text-muted">{{ $customer->upcoming_services->count() }} upcoming</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>₱{{ number_format($customer->total_spent, 2) }}</strong>
                                <br>
                                <small class="text-muted">{{ $customer->total_services }} services</small>
                            </td>
                            <td>
                                @if($customer->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Inactive</span>
                                @endif
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-star text-warning"></i>
                                    {{ $customer->loyalty_points }} points
                                </small>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-outline-primary" 
                                       data-bs-toggle="tooltip" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-sm btn-outline-secondary"
                                       data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $customer->id }}"
                                            data-bs-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Delete Modal -->
                                <div class="modal fade" id="deleteModal{{ $customer->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Delete Customer</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                Are you sure you want to delete <strong>{{ $customer->full_name }}</strong>?
                                                <br>
                                                <small class="text-danger">This action cannot be undone.</small>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <form action="{{ route('customers.destroy', $customer) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Delete</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers
                </div>
                <div>
                    {{ $customers->links() }}
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-users fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No customers found</h4>
                <p class="text-muted">Try adjusting your filters or add a new customer</p>
                <a href="{{ route('customers.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Add First Customer
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="text-primary">{{ $customers->total() }}</h3>
                <p class="text-muted mb-0">Total Customers</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="text-success">{{ $customers->where('customer_type', 'individual')->count() }}</h3>
                <p class="text-muted mb-0">Individual Customers</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="text-warning">{{ $customers->whereIn('customer_type', ['commercial', 'fleet'])->count() }}</h3>
                <p class="text-muted mb-0">Business Customers</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-light">
            <div class="card-body text-center">
                <h3 class="text-danger">{{ $customers->where('is_active', false)->count() }}</h3>
                <p class="text-muted mb-0">Inactive Customers</p>
            </div>
        </div>
    </div>
</div>
@endsection