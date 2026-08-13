@extends('layouts.app')

@section('title', 'My Work Orders - Customer Portal')

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list me-2"></i>My Work Orders
            </h1>
            <p class="text-muted mb-0">View your service and repair work orders</p>
        </div>
        <div>
            <a href="{{ route('portal.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- Work Order Summary -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Total Work Orders</h6>
                        <h3 class="mb-0">{{ $workOrders->total() }}</h3>
                    </div>
                    <i class="fas fa-clipboard-list fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Completed</h6>
                        <h3 class="mb-0">{{ $workOrders->where('status', 'completed')->count() }}</h3>
                    </div>
                    <i class="fas fa-check-circle fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">In Progress</h6>
                        <h3 class="mb-0">{{ $workOrders->where('status', 'in_progress')->count() }}</h3>
                    </div>
                    <i class="fas fa-tools fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 mb-0">Total Spent</h6>
                        <h3 class="mb-0">${{ number_format($workOrders->sum('total_cost'), 2) }}</h3>
                    </div>
                    <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Work Orders List -->
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">
            <i class="fas fa-list me-2"></i>Work Orders
        </h6>
        <div class="btn-group">
            <button class="btn btn-sm btn-outline-secondary active" id="allBtn">All</button>
            <button class="btn btn-sm btn-outline-secondary" id="pendingBtn">Pending</button>
            <button class="btn btn-sm btn-outline-secondary" id="inProgressBtn">In Progress</button>
            <button class="btn btn-sm btn-outline-secondary" id="completedBtn">Completed</button>
        </div>
    </div>
    
    <div class="card-body">
        @if($workOrders->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Work Order #</th>
                            <th>Vehicle</th>
                            <th>Service Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Total Cost</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workOrders as $workOrder)
                            <tr class="work-order-row" data-status="{{ $workOrder->status }}">
                                <td>
                                    <strong>WO-{{ $workOrder->id }}</strong>
                                    @if($workOrder->is_urgent)
                                        <span class="badge bg-danger ms-1">Urgent</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}
                                    <br>
                                    <small class="text-muted">{{ $workOrder->vehicle->license_plate ?? 'No Plate' }}</small>
                                </td>
                                <td>{{ $workOrder->service_type }}</td>
                                <td>
                                    {{ $workOrder->created_at->format('M j, Y') }}
                                    <br>
                                    <small class="text-muted">{{ $workOrder->created_at->format('g:i A') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $workOrder->status === 'completed' ? 'success' : 
                                                              ($workOrder->status === 'in_progress' ? 'warning' : 
                                                              ($workOrder->status === 'pending_approval' ? 'info' : 'secondary')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $workOrder->status)) }}
                                    </span>
                                    @if($workOrder->status === 'pending_approval')
                                        <br>
                                        <small class="text-muted">Awaiting approval</small>
                                    @endif
                                </td>
                                <td>
                                    <strong>${{ number_format($workOrder->total_cost, 2) }}</strong>
                                    @if($workOrder->status === 'pending_approval')
                                        <br>
                                        <small class="text-muted">Estimate</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('portal.work-orders.show', $workOrder) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        @if($workOrder->status === 'pending_approval')
                                            <button type="button" class="btn btn-outline-success" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#approveModal{{ $workOrder->id }}"
                                                    title="Approve Estimate">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        @if($workOrder->status === 'completed')
                                            <a href="{{ route('portal.work-orders.invoice', $workOrder) }}" 
                                               class="btn btn-outline-info" title="View Invoice">
                                                <i class="fas fa-file-invoice-dollar"></i>
                                            </a>
                                        @endif
                                    </div>
                                    
                                    <!-- Approve Modal -->
                                    @if($workOrder->status === 'pending_approval')
                                        <div class="modal fade" id="approveModal{{ $workOrder->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Approve Work Order Estimate</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info">
                                                            <i class="fas fa-info-circle me-2"></i>
                                                            By approving this estimate, you authorize Fix-It Auto Services to proceed with the work.
                                                        </div>
                                                        
                                                        <h6>Work Order Details</h6>
                                                        <p><strong>WO-{{ $workOrder->id }}</strong></p>
                                                        <p><strong>Vehicle:</strong> {{ $workOrder->vehicle->year }} {{ $workOrder->vehicle->make }} {{ $workOrder->vehicle->model }}</p>
                                                        <p><strong>Service Type:</strong> {{ $workOrder->service_type }}</p>
                                                        <p><strong>Estimated Total:</strong> ${{ number_format($workOrder->total_cost, 2) }}</p>
                                                        
                                                        <hr>
                                                        
                                                        <form id="approveForm{{ $workOrder->id }}" 
                                                              action="{{ route('portal.work-orders.approve', $workOrder) }}" 
                                                              method="POST">
                                                            @csrf
                                                            <div class="mb-3">
                                                                <label class="form-label">Digital Signature</label>
                                                                <div class="signature-pad border rounded p-3 mb-3" 
                                                                     style="height: 150px; background: white;">
                                                                    <canvas id="signatureCanvas{{ $workOrder->id }}" 
                                                                            style="width: 100%; height: 100%;"></canvas>
                                                                </div>
                                                                <input type="hidden" name="signature_data" 
                                                                       id="signatureData{{ $workOrder->id }}">
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                                            onclick="clearSignature({{ $workOrder->id }})">
                                                                        Clear
                                                                    </button>
                                                                    <small class="text-muted ms-auto">
                                                                        Draw your signature above
                                                                    </small>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="form-check mb-3">
                                                                <input class="form-check-input" type="checkbox" 
                                                                       id="terms{{ $workOrder->id }}" name="terms" required>
                                                                <label class="form-check-label" for="terms{{ $workOrder->id }}">
                                                                    I authorize the work described above and agree to pay the estimated amount upon completion.
                                                                </label>
                                                            </div>
                                                        </form>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" form="approveForm{{ $workOrder->id }}" 
                                                                class="btn btn-success">Approve & Authorize</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($workOrders->hasPages())
                <div class="mt-4">
                    {{ $workOrders->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-4x text-muted mb-4"></i>
                <h4 class="text-muted mb-3">No Work Orders Found</h4>
                <p class="text-muted mb-4">
                    You don't have any work orders yet.
                </p>
                <a href="{{ route('portal.appointments.create') }}" class="btn btn-primary">
                    <i class="fas fa-calendar-plus me-1"></i> Schedule Service
                </a>
            </div>
        @endif
    </div>
</div>

<style>
    .work-order-row:hover {
        background-color: #f8f9fa;
    }
    
    .signature-pad {
        cursor: crosshair;
        touch-action: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter buttons
        const allBtn = document.getElementById('allBtn');
        const pendingBtn = document.getElementById('pendingBtn');
        const inProgressBtn = document.getElementById('inProgressBtn');
        const completedBtn = document.getElementById('completedBtn');
        const workOrderRows = document.querySelectorAll('.work-order-row');
        
        function filterWorkOrders(status) {
            workOrderRows.forEach(row => {
                if (status === 'all' || row.dataset.status === status) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
            
            // Update button states
            [allBtn, pendingBtn, inProgressBtn, completedBtn].forEach(btn => {
                btn.classList.remove('active');
            });
            
            if (status === 'all') allBtn.classList.add('active');
            if (status === 'pending_approval') pendingBtn.classList.add('active');
            if (status === 'in_progress') inProgressBtn.classList.add('active');
            if (status === 'completed') completedBtn.classList.add('active');
        }
        
        allBtn.addEventListener('click', () => filterWorkOrders('all'));
        pendingBtn.addEventListener('click', () => filterWorkOrders('pending_approval'));
        inProgressBtn.addEventListener('click', () => filterWorkOrders('in_progress'));
        completedBtn.addEventListener('click', () => filterWorkOrders('completed'));
        
        // Signature pad functionality
        const signatureCanvases = {};
        const signaturePads = {};
        
        function initSignaturePad(workOrderId) {
            const canvas = document.getElementById(`signatureCanvas${workOrderId}`);
            if (!canvas) return;
            
            const ctx = canvas.getContext('2d');
            let isDrawing = false;
            let lastX = 0;
            let lastY = 0;
            
            // Set canvas size
            canvas.width = canvas.offsetWidth;
            canvas.height = canvas.offsetHeight;
            
            // Clear canvas
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            ctx.lineWidth = 2;
            ctx.lineCap = 'round';
            ctx.strokeStyle = '#000';
            
            // Drawing functions
            function startDrawing(e) {
                isDrawing = true;
                [lastX, lastY] = getCoordinates(e);
            }
            
            function draw(e) {
                if (!isDrawing) return;
                
                const [x, y] = getCoordinates(e);
                
                ctx.beginPath();
                ctx.moveTo(lastX, lastY);
                ctx.lineTo(x, y);
                ctx.stroke();
                
                [lastX, lastY] = [x, y];
                
                // Save signature data
                saveSignature(workOrderId);
            }
            
            function stopDrawing() {
                isDrawing = false;
                saveSignature(workOrderId);
            }
            
            function getCoordinates(e) {
                const rect = canvas.getBoundingClientRect();
                const clientX = e.type.includes('touch') ? e.touches[0].clientX : e.clientX;
                const clientY = e.type.includes('touch') ? e.touches[0].clientY : e.clientY;
                return [
                    clientX - rect.left,
                    clientY - rect.top
                ];
            }
            
            // Event listeners
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);
            
            canvas.addEventListener('touchstart', (e) => {
                e.preventDefault();
                startDrawing(e);
            });
            canvas.addEventListener('touchmove', (e) => {
                e.preventDefault();
                draw(e);
            });
            canvas.addEventListener('touchend', (e) => {
                e.preventDefault();
                stopDrawing();
            });
            
            signatureCanvases[workOrderId] = canvas;
            signaturePads[workOrderId] = { ctx, canvas };
        }
        
        function saveSignature(workOrderId) {
            const canvas = signatureCanvases[workOrderId];
            if (!canvas) return;
            
            const dataUrl = canvas.toDataURL();
            document.getElementById(`signatureData${workOrderId}`).value = dataUrl;
        }
        
        window.clearSignature = function(workOrderId) {
            const pad = signaturePads[workOrderId];
            if (!pad) return;
            
            pad.ctx.clearRect(0, 0, pad.canvas.width, pad.canvas.height);
            document.getElementById(`signatureData${workOrderId}`).value = '';
        };
        
        // Initialize signature pads when modals are shown
        document.querySelectorAll('[id^="approveModal"]').forEach(modal => {
            const modalEl = new bootstrap.Modal(modal);
            modal.addEventListener('shown.bs.modal', function() {
                const workOrderId = this.id.replace('approveModal', '');
                initSignaturePad(workOrderId);
            });
        });
    });
</script>
@endsection