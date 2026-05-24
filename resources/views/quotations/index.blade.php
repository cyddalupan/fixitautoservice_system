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
                                    <th style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotations as $quotation)
                                    <tr data-status="{{ $quotation->status }}" data-name="{{ strtolower($quotation->name) }}" data-email="{{ strtolower($quotation->email) }}" data-phone="{{ $quotation->phone }}" class="cursor-pointer" onclick="window.location='{{ route('quotations.show', $quotation->id) }}'">
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
                                        <td class="actions-cell" onclick="event.stopPropagation();">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <!-- View -->
                                                <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-sm btn-outline-primary" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <!-- Edit -->
                                                <a href="{{ route('quotations.edit', $quotation->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <!-- Convert to Customer / View Customer -->
                                                @if(!$quotation->customer)
                                                    <form action="{{ route('quotations.convert-to-customer', $quotation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Convert this lead to a customer?')">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Convert to Customer">
                                                            <i class="fas fa-user-plus"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <a href="{{ route('customers.show', $quotation->customer_id) }}" class="btn btn-sm btn-outline-success" title="View Customer Profile">
                                                        <i class="fas fa-user"></i>
                                                    </a>
                                                @endif

                                                <!-- Create Appointment (only if customer exists) -->
                                                @if($quotation->customer)
                                                    <a href="{{ route('appointments.create', ['customer_id' => $quotation->customer_id, 'quotation_id' => $quotation->id]) }}" class="btn btn-sm btn-outline-primary" title="Create Appointment">
                                                        <i class="fas fa-calendar-plus"></i>
                                                    </a>
                                                @endif

                                                <!-- Create Estimate (only if customer exists) -->
                                                @if($quotation->customer)
                                                    <a href="{{ route('estimates.create', ['customer_id' => $quotation->customer_id, 'quotation_id' => $quotation->id]) }}" class="btn btn-sm btn-outline-info" title="Create Estimate">
                                                        <i class="fas fa-file-invoice"></i>
                                                    </a>
                                                @endif

                                                <!-- Status: Mark Contacted (new_lead only) -->
                                                @if($quotation->status === 'new_lead')
                                                    <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="contacted">
                                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Mark Contacted">
                                                            <i class="fas fa-phone"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Status: Won/Lost (converted/appointment only) -->
                                                @if(in_array($quotation->status, ['converted_to_customer', 'appointment_booked']))
                                                    <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="won">
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Mark as Won">
                                                            <i class="fas fa-trophy"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Mark this lead as Lost?');">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="lost">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Mark as Lost">
                                                            <i class="fas fa-times-circle"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Archive (new_lead/contacted only) -->
                                                @if(in_array($quotation->status, ['new_lead', 'contacted']))
                                                    <form action="{{ route('quotations.update-status', $quotation->id) }}" method="POST" class="d-inline">
                                                        @csrf @method('PATCH')
                                                        <input type="hidden" name="status" value="archived">
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Archive">
                                                            <i class="fas fa-archive"></i>
                                                        </button>
                                                    </form>
                                                @endif

                                                <!-- Delete -->
                                                <form action="{{ route('quotations.destroy', $quotation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this quotation?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
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
@endsection

@push('styles')
<style>
    .table tbody tr {
        cursor: pointer;
        transition: background-color 0.15s ease;
    }
    .table tbody tr:hover {
        background-color: rgba(0,0,0,0.03) !important;
    }
    .table tbody tr td.actions-cell .d-flex {
        gap: 0.25rem !important;
        flex-wrap: nowrap;
    }
    .table tbody tr td.actions-cell .btn {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 6px;
        font-size: 0.8rem;
        transition: all 0.15s ease;
    }
    .table tbody tr td.actions-cell .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table tbody tr td.actions-cell form {
        display: inline;
    }
</style>
@endpush

@push('scripts')
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
@endpush
