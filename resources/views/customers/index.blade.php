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
        <div class="d-flex gap-2">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Customer
            </a>
            <button type="button" class="btn btn-success" onclick="generateCustomerForm()">
                <i class="fas fa-link me-1"></i> Generate Customer Form
            </button>
            <a href="{{ route('customers.generated-forms') }}" class="btn btn-info">
                <i class="fas fa-list me-1"></i> Generated Forms
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
                <select name="min_services" class="form-select">
                    <option value="">Min Services</option>
                    <option value="1" {{ request('min_services') == '1' ? 'selected' : '' }}>1+ services</option>
                    <option value="5" {{ request('min_services') == '5' ? 'selected' : '' }}>5+ services</option>
                    <option value="10" {{ request('min_services') == '10' ? 'selected' : '' }}>10+ services</option>
                    <option value="20" {{ request('min_services') == '20' ? 'selected' : '' }}>20+ services</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="last_service" class="form-select">
                    <option value="">Last Service</option>
                    <option value="30" {{ request('last_service') == '30' ? 'selected' : '' }}>Last 30 days</option>
                    <option value="90" {{ request('last_service') == '90' ? 'selected' : '' }}>Last 90 days</option>
                    <option value="180" {{ request('last_service') == '180' ? 'selected' : '' }}>Last 6 months</option>
                    <option value="365" {{ request('last_service') == '365' ? 'selected' : '' }}>Last year</option>
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
                            <th>Vehicles</th>
                            <th>Repairs/Services</th>
                            <th>Total Spent</th>
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
                                        <small class="text-muted">Customer since {{ $customer->customer_since?->format('M Y') ?? 'N/A' }}</small>
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
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-car text-primary me-2"></i>
                                    <div>
                                        <strong>{{ $customer->vehicles_count }}</strong> vehicles
                                        <br>
                                        <small class="text-muted">{{ $customer->upcoming_services->count() }} upcoming</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-wrench text-success me-2"></i>
                                    <div>
                                        <strong>{{ $customer->service_records_count }}</strong> total services
                                        <br>
                                        <small class="text-muted">
                                            @if($customer->serviceRecords->first())
                                                Last: {{ $customer->serviceRecords->first()->service_date->format('M d, Y') }}
                                            @else
                                                No services yet
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <strong>₱{{ number_format($customer->service_records_sum_final_amount ?? 0, 2) }}</strong>
                                <br>
                                <small class="text-muted">
                                    @if($customer->service_records_count > 0)
                                        Average: ₱{{ number_format(($customer->service_records_sum_final_amount ?? 0) / $customer->service_records_count, 2) }}
                                    @else
                                        No services
                                    @endif
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

<!-- Form Generation Modal -->
<div class="modal fade" id="formGenerationModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="fas fa-link me-2"></i>Customer Form Generated
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> Customer form has been generated successfully.
                </div>
                
                <div class="mb-4">
                    <h6 class="mb-2">Form URL:</h6>
                    <div class="input-group">
                        <input type="text" class="form-control" id="formUrl" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyFormUrl()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <small class="text-muted">This link will expire in 7 days.</small>
                </div>
                
                <div class="mb-4">
                    <h6 class="mb-2">QR Code:</h6>
                    <div class="text-center">
                        <img id="qrCodeImage" src="" alt="QR Code" class="img-fluid" style="max-width: 200px;">
                        <p class="text-muted mt-2">Scan this QR code to access the form on mobile devices</p>
                    </div>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>How to use:</strong> Send this link or QR code to customers. They can fill out the form to register themselves, which helps prevent spam submissions.
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-shield-alt me-2"></i>Security Features
                                </h6>
                                <ul class="small mb-0">
                                    <li>Unique token prevents spam</li>
                                    <li>7-day expiration</li>
                                    <li>Form submissions logged</li>
                                    <li>Rate limiting enabled</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">
                                    <i class="fas fa-user-plus me-2"></i>Benefits
                                </h6>
                                <ul class="small mb-0">
                                    <li>Reduces manual data entry</li>
                                    <li>Customers enter their own data</li>
                                    <li>24/7 customer registration</li>
                                    <li>Professional appearance</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="openFormUrl()">
                    <i class="fas fa-external-link-alt me-2"></i> Open Form
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Generating form...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function generateCustomerForm() {
        // Get modal elements
        const loadingModalElement = document.getElementById('loadingModal');
        const loadingModal = bootstrap.Modal.getOrCreateInstance(loadingModalElement);
        
        // Show loading modal
        loadingModal.show();
        
        // Generate form via AJAX
        fetch('{{ route("customers.generate-form") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(result => {
            // Hide loading modal
            loadingModal.hide();
            
            if (result.success) {
                // Populate modal with form data
                document.getElementById('formUrl').value = result.form_url;
                document.getElementById('qrCodeImage').src = result.qr_code_url;
                
                // Show the form generation modal
                const formModalElement = document.getElementById('formGenerationModal');
                const formModal = bootstrap.Modal.getOrCreateInstance(formModalElement);
                formModal.show();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(error => {
            // Hide loading modal
            loadingModal.hide();
            alert('Network error. Please try again. Error: ' + error.message);
            console.error('Error:', error);
        });
    }
    
    function copyFormUrl() {
        const formUrlInput = document.getElementById('formUrl');
        formUrlInput.select();
        formUrlInput.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            navigator.clipboard.writeText(formUrlInput.value);
            
            // Show success feedback
            const copyButton = event.target.closest('button');
            const originalHtml = copyButton.innerHTML;
            copyButton.innerHTML = '<i class="fas fa-check"></i> Copied!';
            copyButton.classList.remove('btn-outline-secondary');
            copyButton.classList.add('btn-success');
            
            setTimeout(() => {
                copyButton.innerHTML = originalHtml;
                copyButton.classList.remove('btn-success');
                copyButton.classList.add('btn-outline-secondary');
            }, 2000);
        } catch (err) {
            console.error('Failed to copy: ', err);
            alert('Failed to copy to clipboard');
        }
    }
    
    function openFormUrl() {
        const formUrl = document.getElementById('formUrl').value;
        window.open(formUrl, '_blank');
    }
</script>
@endpush