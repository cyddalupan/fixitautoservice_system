<div class="card mb-4 border-light shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h6 class="mb-0 text-dark">
            <i class="fas fa-stream me-2 text-muted"></i>Service Workflow Progress
        </h6>
    </div>
    <div class="card-body p-3">
        @if($progress)
            <!-- Progress Indicator -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Current Progress</span>
                    <span class="fw-semibold text-dark">{{ $progress->calculateContextAwareProgress($currentStage ?? $progress->current_stage) }}% Complete</span>
                </div>
                <div class="progress" style="height: 10px; background-color: #e9ecef;">
                    <div class="progress-bar bg-primary" 
                         role="progressbar" 
                         style="width: {{ $progress->calculateContextAwareProgress($currentStage ?? $progress->current_stage) }}%"
                         aria-valuenow="{{ $progress->calculateContextAwareProgress($currentStage ?? $progress->current_stage) }}" 
                         aria-valuemin="0" 
                         aria-valuemax="100">
                    </div>
                </div>
            </div>
            
            <!-- Clean Stage Timeline -->
            <div class="stages-container">
                @php
                    // Get dynamic stages based on actual service flow
                    $stages = [];
                    $stageConfig = [
                        'appointment' => ['name' => 'Appointment', 'icon' => 'fa-calendar-check'],
                        'inspection' => ['name' => 'Inspection', 'icon' => 'fa-search'],
                        'estimate' => ['name' => 'Estimate', 'icon' => 'fa-file-alt'],
                        'work_order' => ['name' => 'Work Order', 'icon' => 'fa-tools'],
                        'invoice' => ['name' => 'Invoice', 'icon' => 'fa-file-invoice'],
                        'payment' => ['name' => 'Payment', 'icon' => 'fa-receipt'],
                    ];
                    
                    // Determine which stages to show based on service type and actual flow
                    if ($progress->service_type === 'parts_purchase') {
                        $stages = ['invoice', 'payment'];
                    } else {
                        // Full service - show appointment first
                        $stages = ['appointment'];
                        
                        // Only show inspection if it exists or was used
                        if ($progress->has_inspection || $progress->inspection_id) {
                            $stages[] = 'inspection';
                        }
                        
                        // Always show these stages for full service
                        $stages[] = 'estimate';
                        $stages[] = 'work_order';
                        $stages[] = 'invoice';
                        $stages[] = 'payment';
                    }
                    
                    $stageCount = count($stages);
                @endphp
                
                <!-- Timeline-style stages -->
                <div class="position-relative">
                    <!-- Timeline line -->
                    <div class="timeline-line position-absolute top-50 start-0 end-0" style="height: 2px; background-color: #e9ecef; transform: translateY(-50%);"></div>
                    
                    <!-- Stages -->
                    <div class="d-flex justify-content-between position-relative">
                        @foreach($stages as $index => $stage)
                            @php
                                // Check if stage is marked as completed in database
                                $dbCompleted = $progress->{"has_" . $stage};
                                // Use provided currentStage override if available, otherwise use progress->current_stage
                                $effectiveCurrentStage = $currentStage ?? $progress->current_stage;
                                $isCurrent = $effectiveCurrentStage === $stage;
                                
                                // Get all stages to determine position
                                $allStages = $progress->getStages();
                                $currentStageIndex = array_search($effectiveCurrentStage, $allStages);
                                $stageIndex = array_search($stage, $allStages);
                                
                                // Determine visual state:
                                // - Current stage: always highlighted (black)
                                // - Stages before current: completed (gray) if entity exists
                                // - Stages after current: future (light gray) even if entity exists
                                if ($isCurrent) {
                                    $isCompleted = false;
                                    $isFuture = false;
                                } elseif ($stageIndex < $currentStageIndex) {
                                    // Stage is before current stage
                                    $isCompleted = $dbCompleted; // Show as completed if entity exists
                                    $isFuture = false;
                                } else {
                                    // Stage is after current stage (future)
                                    $isCompleted = false;
                                    $isFuture = true;
                                }
                                
                                $timestampField = $stage . '_created_at';
                                $timestamp = $progress->$timestampField ?? null;
                            @endphp
                            <div class="text-center" style="width: {{ 100/$stageCount }}%">
                                <!-- Stage indicator -->
                                <div class="position-relative mb-2">
                                    <div class="mx-auto rounded-circle d-flex align-items-center justify-content-center" 
                                         style="width: 32px; height: 32px; background-color: {{ $isCurrent ? '#212529' : ($isCompleted ? '#6c757d' : '#e9ecef') }}; border: 1px solid #dee2e6; z-index: 2; position: relative;">
                                        <i class="fas {{ $stageConfig[$stage]['icon'] }} fa-sm {{ $isCurrent || $isCompleted ? 'text-white' : 'text-muted' }}"></i>
                                    </div>
                                </div>
                                
                                <!-- Stage label -->
                                <div class="stage-label">
                                    <div class="small fw-medium {{ $isCurrent ? 'text-dark' : ($isCompleted ? 'text-dark' : 'text-muted') }}">
                                        {{ $stageConfig[$stage]['name'] }}
                                    </div>
                                    @if($timestamp)
                                        <div class="extra-small text-muted mt-1">
                                            {{ $timestamp->format('M d') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Compact Status Summary -->
            <div class="mt-4 pt-3 border-top">
                <div class="row">
                    <div class="col-6">
                        <div class="small text-muted mb-1">Type</div>
                        <div class="fw-medium">{{ ucfirst(str_replace('_', ' ', $progress->service_type)) }}</div>
                    </div>
                    <div class="col-6">
                        <div class="small text-muted mb-1">Status</div>
                        <div class="fw-medium {{ $progress->completed_at ? 'text-success' : 'text-primary' }}">
                            {{ $progress->completed_at ? 'Completed' : ucfirst(str_replace('_', ' ', $currentStage ?? $progress->current_stage)) }}
                        </div>
                    </div>
                </div>
                @if($progress->started_at || $progress->completed_at)
                <div class="row mt-2">
                    @if($progress->started_at)
                    <div class="col-6">
                        <div class="small text-muted mb-1">Started</div>
                        <div class="extra-small">{{ $progress->started_at->format('M d, Y') }}</div>
                    </div>
                    @endif
                    @if($progress->completed_at)
                    <div class="col-6">
                        <div class="small text-muted mb-1">Completed</div>
                        <div class="extra-small">{{ $progress->completed_at->format('M d, Y') }}</div>
                    </div>
                    @endif
                </div>
                @endif
            </div>
        @else
            <div class="text-center py-3">
                <i class="fas fa-info-circle fa-lg text-muted mb-2"></i>
                <p class="text-muted small mb-0">No workflow tracking available</p>
                <p class="extra-small text-muted">Tracking begins with first service action</p>
            </div>
        @endif
    </div>
</div>

<style>
    .extra-small {
        font-size: 0.75rem;
    }
    
    .timeline-line {
        z-index: 1;
    }
    
    .stage-label {
        min-height: 40px;
    }
    
    .progress-bar {
        transition: width 0.3s ease;
    }
</style>