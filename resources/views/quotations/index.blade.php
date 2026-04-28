@extends('layouts.app')

@section('title', 'Quotations')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-file-invoice-dollar text-primary"></i> Quotations
                        <span class="badge bg-danger ms-2" id="newLeadsBadge" style="{{ $quotations->where('status', 'new_lead')->count() > 0 ? '' : 'display:none;' }}">
                            {{ $quotations->where('status', 'new_lead')->count() }} new
                        </span>
                    </h5>
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 300px;">
                            <input type="text" class="form-control" placeholder="Search quotations..." id="searchInput">
                            <button class="btn btn-outline-secondary" type="button" id="searchButton">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <a href="{{ route('quotations.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> New Quotation
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Status Filter Tabs -->
                    <ul class="nav nav-tabs mb-3" id="statusTabs">
                        <li class="nav-item">
                            <a class="nav-link active" href="#" data-filter="all">All</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-filter="new_lead">New Leads</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-filter="contacted">Contacted</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-filter="converted_to_customer">Converted</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-filter="appointment_booked">Appt Booked</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-filter="won">Won</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-filter="lost">Lost</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-filter="archived">Archived</a>
                        </li>
                    </ul>
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Customer</th>
                                    <th>Vehicle</th>
                                    <th>License Plate</th>
                                    <th>Concern</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotations as $quotation)
                                    <tr data-status="{{ $quotation->status }}" data-name="{{ strtolower($quotation->name) }}" data-email="{{ strtolower($quotation->email) }}" data-phone="{{ $quotation->phone }}">
                                        <td>#{{ str_pad($quotation->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $quotation->name }}</div>
                                            <small class="text-muted">{{ $quotation->email }}</small><br>
                                            <small class="text-muted">{{ $quotation->phone }}</small>
                                            @if($quotation->customer)
                                                <br><span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 mt-1">
                                                    <i class="fas fa-check-circle"></i> Customer #{{ $quotation->customer->id }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $quotation->vehicle_make }} {{ $quotation->vehicle_model }}</div>
                                            <small class="text-muted">Year: {{ $quotation->vehicle_year }}</small>
                                        </td>
                                        <td>
                                            @if($quotation->license_plate)
                                                <span class="badge bg-dark">{{ $quotation->license_plate }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $quotation->service_description }}">
                                                {{ Str::limit($quotation->service_description, 40) }}
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'new_lead' => 'warning',
                                                    'contacted' => 'info',
                                                    'converted_to_customer' => 'success',
                                                    'appointment_booked' => 'primary',
                                                    'won' => 'success',
                                                    'lost' => 'danger',
                                                    'archived' => 'secondary'
                                                ];
                                                
                                                $statusLabels = [
                                                    'new_lead' => 'New Lead',
                                                    'contacted' => 'Contacted',
                                                    'converted_to_customer' => 'Converted to Customer',
                                                    'appointment_booked' => 'Appointment Booked',
                                                    'won' => 'Won',
                                                    'lost' => 'Lost',
                                                    'archived' => 'Archived'
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$quotation->status] ?? 'secondary' }}">
                                                {{ $statusLabels[$quotation->status] ?? ucfirst($quotation->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>{{ $quotation->created_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $quotation->created_at->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                                    <i class="fas fa-cog"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" style="min-width: 220px;">
                                                    <!-- View / Edit -->
                                                    <li><a class="dropdown-item" href="{{ route('quotations.show', $quotation->id) }}"><i class="fas fa-eye text-primary me-2"></i> View Details</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('quotations.edit', $quotation->id) }}"><i class="fas fa-edit text-warning me-2"></i> Edit</a></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    
                                                    <!-- CRM Actions -->
                                                    @if(!$quotation->customer)
                                                        <li>
                                                            <form action="{{ route('quotations.convert-to-customer', $quotation->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="dropdown-item">
                                                                    <i class="fas fa-user-plus text-success me-2"></i> Convert to Customer
                                                                </button>
                                                            </form>
                                                        </li>
                                                    @else
                                                        <li><a class="dropdown-item" href="{{ route('customers.show', $quotation->customer_id) }}"><i class="fas fa-user text-success me-2"></i> View Customer Profile</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('appointments.create', ['customer_id' => $quotation->customer_id, 'quotation_id' => $quotation->id]) }}"><i class="fas fa-calendar-plus text-primary me-2"></i> Create Appointment</a></li>
                                                        <li><a class="dropdown-item" href="{{ route('estimates.create', ['customer_id' => $quotation->customer_id, 'quotation_id' => $quotation->id]) }}"><i class="fas fa-file-invoice text-info me-2"></i> Create Estimate</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                    @endif
                                                    
                                                    <!-- Status Updates -->
                                                    @if($quotation->status === 'new_lead')
                                                        <li>
                                                            <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="contacted">
                                                                <button type="submit" class="dropdown-item"><i class="fas fa-phone text-info me-2"></i> Mark Contacted</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if(in_array($quotation->status, ['new_lead', 'contacted']))
                                                        <li>
                                                            <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="archived">
                                                                <button type="submit" class="dropdown-item"><i class="fas fa-archive text-secondary me-2"></i> Archive Lead</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    @if(in_array($quotation->status, ['converted_to_customer', 'appointment_booked']))
                                                        <li>
                                                            <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="won">
                                                                <button type="submit" class="dropdown-item"><i class="fas fa-trophy text-success me-2"></i> Mark as Won</button>
                                                            </form>
                                                        </li>
                                                        <li>
                                                            <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this lead as Lost?');">
                                                                @csrf @method('PATCH')
                                                                <input type="hidden" name="status" value="lost">
                                                                <button type="submit" class="dropdown-item"><i class="fas fa-times-circle text-danger me-2"></i> Mark as Lost</button>
                                                            </form>
                                                        </li>
                                                    @endif
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <form action="{{ route('quotations.destroy', $quotation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quotation?');">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger"><i class="fas fa-trash me-2"></i> Delete</button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-inbox fa-2x mb-3"></i>
                                                <h5>No quotations found</h5>
                                                <p>When customers submit quote requests from the website, they will appear here.</p>
                                                <a href="{{ route('quotations.create') }}" class="btn btn-primary mt-2">
                                                    <i class="fas fa-plus"></i> Create First Quotation
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    @if($quotations->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $quotations->links() }}
                        </div>
                    @endif

                    <div class="mt-4">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="card border-primary">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-1">Total</h6>
                                        <h4 class="text-primary mb-0">{{ \App\Models\Quotation::count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card border-warning">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-1">New Leads</h6>
                                        <h4 class="text-warning mb-0">{{ \App\Models\Quotation::where('status', 'new_lead')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card border-info">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-1">Contacted</h6>
                                        <h4 class="text-info mb-0">{{ \App\Models\Quotation::where('status', 'contacted')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card border-success">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-1">Converted</h6>
                                        <h4 class="text-success mb-0">{{ \App\Models\Quotation::whereIn('status', ['converted_to_customer','appointment_booked','won'])->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card border-danger">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-1">Lost</h6>
                                        <h4 class="text-danger mb-0">{{ \App\Models\Quotation::where('status', 'lost')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="card border-secondary">
                                    <div class="card-body text-center py-2">
                                        <h6 class="text-muted mb-1">Archived</h6>
                                        <h4 class="text-secondary mb-0">{{ \App\Models\Quotation::where('status', 'archived')->count() }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Status filter tabs
    document.querySelectorAll('#statusTabs .nav-link').forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll('#statusTabs .nav-link').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            document.querySelectorAll('tbody tr').forEach(row => {
                if (filter === 'all' || row.dataset.status === filter) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    });
    
    // Search functionality
    document.getElementById('searchButton').addEventListener('click', function() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');
        
        rows.forEach(row => {
            if (row.style.display === 'none') return;
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
    
    document.getElementById('searchInput').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            document.getElementById('searchButton').click();
        }
    });
</script>
@endsection
