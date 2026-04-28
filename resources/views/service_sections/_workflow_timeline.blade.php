<!-- Complete Workflows (for context) -->
<div class="row mt-4">
    <div class="col-12">
        <div class="card border-secondary">
            <div class="card-header bg-secondary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-history me-2"></i>Complete Service Workflows
                    <span class="badge bg-light text-dark ms-2">{{ count($workflows) }} vehicle(s) with service history</span>
                </h5>
            </div>
            <div class="card-body">
                @if(count($workflows) > 0)
                    @foreach($workflows as $workflow)
                        <div class="card mb-4 border shadow-sm">
                            <div class="card-header bg-light">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <h5 class="mb-0">
                                            <i class="fas fa-car me-2"></i>
                                            {{ $workflow['vehicle']->year }} {{ $workflow['vehicle']->make }} {{ $workflow['vehicle']->model }}
                                            <small class="text-muted">({{ $workflow['vehicle']->license_plate }})</small>
                                        </h5>
                                        <p class="mb-0">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $workflow['customer']->first_name }} {{ $workflow['customer']->last_name }}
                                            • {{ $workflow['customer']->phone }}
                                        </p>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <div class="btn-group">
                                            <a href="{{ route('vehicles.show', $workflow['vehicle']->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-car me-1"></i> Vehicle Details
                                            </a>
                                            <a href="{{ route('customers.show', $workflow['customer']->id) }}" class="btn btn-sm btn-outline-info">
                                                <i class="fas fa-user me-1"></i> Customer Profile
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- Workflow Timeline -->
                                <div class="row">
                                    @php
                                        $sections = [
                                            'appointments' => ['icon' => 'calendar-check', 'color' => 'primary', 'title' => 'Appointment'],
                                            'inspections' => ['icon' => 'tools', 'color' => 'warning', 'title' => 'Repair Order'],
                                            'estimates' => ['icon' => 'file-invoice-dollar', 'color' => 'success', 'title' => 'Estimate'],
                                            'work_orders' => ['icon' => 'clipboard-check', 'color' => 'danger', 'title' => 'Job Order'],
                                            'payments' => ['icon' => 'credit-card', 'color' => 'purple', 'title' => 'Payment'],
                                            'complete' => ['icon' => 'flag-checkered', 'color' => 'secondary', 'title' => 'Complete']
                                        ];
                                    @endphp
                                    
                                    @foreach($sections as $sectionKey => $sectionInfo)
                                        <div class="col-md-2 col-6 mb-3">
                                            <div class="card h-100 {{ count($workflow[$sectionKey] ?? []) > 0 ? 'border-' . $sectionInfo['color'] : 'border-secondary' }}">
                                                <div class="card-header {{ count($workflow[$sectionKey] ?? []) > 0 ? 'bg-' . $sectionInfo['color'] . ' ' . ($sectionInfo['color'] == 'warning' ? 'text-dark' : 'text-white') : 'bg-light' }}">
                                                    <h6 class="mb-0">
                                                        <i class="fas fa-{{ $sectionInfo['icon'] }} me-1"></i> {{ $sectionInfo['title'] }}
                                                    </h6>
                                                </div>
                                                <div class="card-body text-center">
                                                    @if($sectionKey == 'complete')
                                                        @if(!empty($workflow['work_orders']) && array_reduce($workflow['work_orders'], function($carry, $wo) { return $carry + count($wo['payments']); }, 0) > 0)
                                                            <div class="text-success">
                                                                <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                                                <small>Complete</small>
                                                            </div>
                                                        @else
                                                            <div class="text-muted py-3">
                                                                <i class="fas fa-spinner fa-2x mb-2"></i><br>
                                                                <small>In Progress</small>
                                                            </div>
                                                        @endif
                                                    @elseif(count($workflow[$sectionKey] ?? []) > 0)
                                                        <div class="text-success">
                                                            <i class="fas fa-check-circle fa-2x mb-2"></i><br>
                                                            <small>Exists</small>
                                                        </div>
                                                    @else
                                                        <div class="text-muted py-3">
                                                            <i class="fas fa-times fa-2x mb-2"></i><br>
                                                            <small>None</small>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-car fa-3x text-muted mb-3"></i>
                        <h4>No Service Workflows Found</h4>
                        <p class="text-muted">No vehicles with service history match your current filters.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>