@extends('layouts.app')

@section('title', 'Service Records - Command Center')

@section('content')
<div class="container-fluid px-3 px-md-4 fixit-sr-dash">
    <!-- ─────────────────────────────────────── -->
    <!-- PAGE HEADER                              -->
    <!-- ─────────────────────────────────────── -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold page-title">
                <i class="fas fa-server me-2 accent-icon"></i>Service Command Center
            </h4>
            <p class="mb-0 text-muted small">
                <span class="status-dot"></span>
                Full transaction workflow — from booking to payment
            </p>
        </div>
        <div class="mt-2 mt-md-0 d-flex gap-2">
            <button class="btn btn-sm btn-outline-secondary px-3" onclick="location.reload()">
                <i class="fas fa-sync-alt me-1"></i>Refresh
            </button>
        </div>
    </div>

    @php
        // ── Compute summary stats ──
        $activeJobs = 0; $waitingApproval = 0; $readyForRelease = 0;
        $unpaidJobs = 0; $completedToday = 0; $revenueToday = 0;
        $monthlyJobs = 0; $totalWorkflows = count($workflows);

        foreach ($workflows as $wf) {
            foreach ($wf['work_orders'] as $wo) {
                $s = $wo['status'] ?? '';
                if ($s === 'in_progress' || $s === 'pending') $activeJobs++;
                if ($s === 'pending_approval') $waitingApproval++;
                if ($s === 'completed') {
                    $readyForRelease++;
                    if (isset($wo['date']) && $wo['date']->isToday()) { $completedToday++; $revenueToday += $wo['estimated_total'] ?? 0; }
                }
                if (($wo['payment_status'] ?? '') !== 'paid' && $s !== 'cancelled') $unpaidJobs++;
                if (isset($wo['date']) && $wo['date']->month === now()->month) $monthlyJobs++;
            }
        }

        $stageSteps = ['booked','checked_in','inspection','estimate','approved','in_progress','ready','paid','completed'];
    @endphp

    <!-- ─────────────────────────────────────── -->
    <!-- SUMMARY CARDS (Section 10)              -->
    <!-- ─────────────────────────────────────── -->
    <div class="row g-3 mb-4" id="summaryWidgets">
        @include('service_records.partials.summary-card', ['label'=>'Active Jobs','value'=>$activeJobs,'icon'=>'wrench','color'=>'#4361ee','sublabel'=>$totalWorkflows>0?round($activeJobs/max($totalWorkflows,1)*100).'% of all records':'—'])
        @include('service_records.partials.summary-card', ['label'=>'Waiting Approval','value'=>$waitingApproval,'icon'=>'clock','color'=>'#f7a429','sublabel'=>$waitingApproval>0?'Needs attention':'All clear'])
        @include('service_records.partials.summary-card', ['label'=>'Ready to Release','value'=>$readyForRelease,'icon'=>'check-circle','color'=>'#2ec4b6','sublabel'=>$readyForRelease>0?'Call customer':'—'])
        @include('service_records.partials.summary-card', ['label'=>'Unpaid Jobs','value'=>$unpaidJobs,'icon'=>'exclamation-triangle','color'=>'#e63946','sublabel'=>$unpaidJobs>0?'Pending collection':'All paid'])
        @include('service_records.partials.summary-card', ['label'=>'Completed Today','value'=>$completedToday,'icon'=>'calendar-check','color'=>'#2ec4b6','sublabel'=>'₱'.number_format($revenueToday,0)])
        @include('service_records.partials.summary-card', ['label'=>'Monthly Jobs','value'=>$monthlyJobs,'icon'=>'chart-bar','color'=>'#4361ee','sublabel'=>now()->format('F Y')])
    </div>

    <!-- ─────────────────────────────────────── -->
    <!-- STICKY FILTER BAR (Section 4)            -->
    <!-- ─────────────────────────────────────── -->
    <div class="filter-bar mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('service-records.index') }}" id="filterForm">
                <div class="row g-2 g-md-3 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label mb-1 filter-label"><i class="fas fa-search me-1"></i>Search</label>
                        <input type="text" class="form-control form-control-sm filter-input" name="search" placeholder="Name, plate, VIN, invoice #..." value="{{ request('search') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1 filter-label"><i class="fas fa-user me-1"></i>Customer</label>
                        <select class="form-select form-select-sm filter-input" name="customer_id" onchange="this.form.submit()">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ ($customerId ?? 0)==$c->id?'selected':'' }}>{{ $c->last_name }}, {{ $c->first_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1 filter-label"><i class="fas fa-tag me-1"></i>Stage</label>
                        <select class="form-select form-select-sm filter-input" name="stage" onchange="this.form.submit()">
                            <option value="">All Stages</option>
                            @foreach(['booked','checked_in','inspection','estimate','approved','in_progress','waiting_parts','ready','paid','completed'] as $s)
                                <option value="{{ $s }}" {{ request('stage')===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                            @endforeach
                            <option value="archived" {{ request('stage')==='archived'?'selected':'' }}>Archived</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1 filter-label"><i class="fas fa-user-cog me-1"></i>Technician</label>
                        <select class="form-select form-select-sm filter-input" name="technician_id" onchange="this.form.submit()">
                            <option value="">All Techs</option>
                            @foreach($technicians ?? [] as $tech)
                                <option value="{{ $tech->id }}" {{ request('technician_id')==$tech->id?'selected':'' }}>{{ $tech->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label class="form-label mb-1 filter-label"><i class="fas fa-calendar-alt me-1"></i>Date Range</label>
                        <select class="form-select form-select-sm filter-input" name="date_range" onchange="this.form.submit()">
                            <option value="">All Time</option>
                            @foreach(['today'=>'Today','week'=>'This Week','month'=>'This Month','quarter'=>'Last 90 Days'] as $k=>$v)
                                <option value="{{ $k }}" {{ request('date_range')===$k?'selected':'' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-1 d-flex align-items-end">
                        <a href="{{ route('service-records.index') }}" class="btn btn-sm btn-clear w-100"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ─────────────────────────────────────── -->
    <!-- ALERTS BAR (Section 8)                   -->
    <!-- ─────────────────────────────────────── -->
    @php
        $alerts = [];
        foreach ($workflows as $wf) {
            $cname = ($wf['customer']->first_name ?? '').' '.($wf['customer']->last_name ?? '');
            foreach ($wf['work_orders'] as $wo) {
                if (($wo['payment_status']??'') === 'pending' && ($wo['status']??'') === 'completed') { $alerts[] = ['type'=>'payment','text'=>'Awaiting payment — '.trim($cname)]; break; }
                if (($wo['status']??'') === 'waiting_parts') { $alerts[] = ['type'=>'parts','text'=>'Parts needed — '.trim($cname)]; break; }
            }
        }
    @endphp

    @if(count($alerts) > 0)
        <div class="mb-4" id="alertsBar">
            @foreach($alerts as $alert)
                <div class="alert alert-dismissible fade show d-flex align-items-center py-2 px-3 mb-2 alert-{{ $alert['type'] }}">
                    <i class="fas fa-{{ $alert['type']==='payment'?'credit-card':'box' }} me-2"></i>
                    <span>{{ $alert['text'] }}</span>
                    <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="font-size:.7rem;"></button>
                </div>
            @endforeach
        </div>
    @endif

    <!-- ─────────────────────────────────────── -->
    <!-- WORKFLOW RECORDS TABLE (Sections 2-6)    -->
    <!-- ─────────────────────────────────────── -->
    <div id="workflowList">
        @forelse($workflows as $workflow)
            @php
                $customer = $workflow['customer'];
                $vehicle = $workflow['vehicle'];
                $hasAppts  = count($workflow['appointments']) > 0;
                $hasInsp   = count($workflow['inspections']) > 0;
                $hasEst    = count($workflow['estimates']) > 0;
                $hasWO     = count($workflow['work_orders']) > 0;

                // Determine current stage
                $currentStage = 'booked';
                if ($hasWO) {
                    $s = $workflow['work_orders'][0]['status'] ?? '';
                    $currentStage = match($s) { 'pending'=>'checked_in', 'in_progress'=>'in_progress', 'completed'=>'completed', 'cancelled'=>'cancelled', default=>'checked_in' };
                } elseif ($hasEst) {
                    $currentStage = ($workflow['estimates'][0]['status'] ?? '') === 'approved' ? 'approved' : 'estimate';
                } elseif ($hasInsp) {
                    $currentStage = 'inspection';
                } elseif ($hasAppts) {
                    $currentStage = ($workflow['appointments'][0]['status'] ?? '') === 'checked_in' ? 'checked_in' : 'booked';
                }

                $stageIdx = array_search($currentStage, $stageSteps);
                $stageIdx = $stageIdx === false ? 0 : $stageIdx;
                $stagePct = (($stageIdx + 1) / count($stageSteps)) * 100;

                $balanceDue = 0;
                foreach ($workflow['work_orders'] as $wo) { $balanceDue += $wo['balance_due'] ?? $wo['estimated_total'] ?? 0; }

                $sc = match($currentStage) {
                    'booked'       => '#4361ee',
                    'checked_in'   => '#f7a429',
                    'inspection'   => '#e63946',
                    'estimate'     => '#9b59b6',
                    'approved'     => '#2ec4b6',
                    'in_progress'  => '#4361ee',
                    'waiting_parts'=> '#e63946',
                    'ready'        => '#2ec4b6',
                    'paid'         => '#27ae60',
                    'completed'    => '#27ae60',
                    default        => '#8b9aab',
                };
            @endphp

            <!-- ── Record Card ── -->
            <div class="workflow-card mb-3">
                <!-- Header -->
                <div class="wf-header" data-bs-toggle="collapse" data-bs-target="#wfBody-{{ $loop->index }}"
                     aria-expanded="{{ $loop->index < 5 ? 'true' : 'false' }}">
                    <div class="d-flex align-items-center gap-3 flex-grow-1 min-w-0">
                        <div class="wf-avatar" style="background:{{ $sc }}12;color:{{ $sc }};">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <h6 class="mb-0 fw-bold truncate" style="color:#1a2332;">
                                    {{ $customer ? $customer->first_name.' '.$customer->last_name : 'Unknown' }}
                                </h6>
                                @if($vehicle)
                                    <span class="badge badge-vehicle"><i class="fas fa-car me-1"></i>{{ $vehicle->year }} {{ $vehicle->make }} {{ $vehicle->model }}</span>
                                    @if($vehicle->license_plate)
                                        <span class="badge badge-plate">{{ $vehicle->license_plate }}</span>
                                    @endif
                                @endif
                            </div>
                            <div class="d-flex gap-3 mt-1">
                                @if($customer && $customer->phone)
                                    <span class="wf-meta"><i class="fas fa-phone-alt"></i>{{ $customer->phone }}</span>
                                @endif
                                @if($hasAppts)
                                    <span class="wf-meta"><i class="far fa-calendar"></i>{{ \Carbon\Carbon::parse($workflow['appointments'][0]['date'])->format('M d, Y') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3 flex-shrink-0">
                        <div class="text-end d-none d-sm-block">
                            @if($balanceDue > 0)
                                <div class="fw-bold" style="color:#e63946;font-size:.9rem;">₱{{ number_format($balanceDue,2) }}</div>
                                <div class="wf-meta" style="font-size:.7rem;">Balance</div>
                            @else
                                <div class="fw-bold" style="color:#2ec4b6;font-size:.9rem;">₱0.00</div>
                                <div class="wf-meta" style="font-size:.7rem;">Balance</div>
                            @endif
                        </div>
                        <span class="stage-badge" style="background:{{ $sc }}12;color:{{ $sc }};">
                            {{ ucfirst(str_replace('_',' ',$currentStage)) }}
                        </span>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-ghost" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm wf-dropdown">
                                @if($vehicle)
                                    <li><a class="dropdown-item" href="{{ route('work-orders.create',['vehicle_id'=>$vehicle->id]) }}"><i class="fas fa-plus-circle me-2" style="color:#4361ee;"></i>New Job Order</a></li>
                                    <li><a class="dropdown-item" href="{{ route('estimates.create',['vehicle_id'=>$vehicle->id]) }}"><i class="fas fa-file-invoice me-2" style="color:#9b59b6;"></i>New Estimate</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('vehicles.show',$vehicle->id) }}"><i class="fas fa-eye me-2"></i>View Vehicle</a></li>
                                @endif
                                @if($customer)
                                    <li><a class="dropdown-item" href="{{ route('customers.show',$customer->id) }}"><i class="fas fa-user me-2"></i>View Customer</a></li>
                                @endif
                                @if($hasWO)
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="{{ route('work-orders.show',$workflow['work_orders'][0]['id']) }}"><i class="fas fa-clipboard-list me-2" style="color:#e63946;"></i>View Job Order</a></li>
                                @endif
                            </ul>
                        </div>
                        <i class="fas fa-chevron-down chevron-icon"></i>
                    </div>
                </div>

                <!-- Body -->
                <div class="collapse {{ $loop->index < 5 ? 'show' : '' }}" id="wfBody-{{ $loop->index }}">
                    <div class="wf-body">
                        <!-- Progress Stepper -->
                        <div class="stepper-wrapper mb-3">
                            @php
                                $stages = [
                                    ['k'=>'booked','l'=>'Booked','i'=>'calendar-check','c'=>'#4361ee'],
                                    ['k'=>'checked_in','l'=>'Checked In','i'=>'sign-in-alt','c'=>'#f7a429'],
                                    ['k'=>'inspection','l'=>'Inspection','i'=>'search','c'=>'#e63946'],
                                    ['k'=>'estimate','l'=>'Estimate','i'=>'file-invoice-dollar','c'=>'#9b59b6'],
                                    ['k'=>'approved','l'=>'Approved','i'=>'thumbs-up','c'=>'#2ec4b6'],
                                    ['k'=>'in_progress','l'=>'In Progress','i'=>'wrench','c'=>'#4361ee'],
                                    ['k'=>'ready','l'=>'Ready','i'=>'check-circle','c'=>'#2ec4b6'],
                                    ['k'=>'paid','l'=>'Paid','i'=>'credit-card','c'=>'#27ae60'],
                                    ['k'=>'completed','l'=>'Completed','i'=>'flag-checkered','c'=>'#27ae60'],
                                ];
                            @endphp
                            <div class="stepper-track">
                                <div class="stepper-progress" style="width:{{ $stagePct }}%"></div>
                            </div>
                            <div class="stepper-dots">
                                @foreach($stages as $i => $st)
                                    <div class="step {{ $i <= $stageIdx ? 'active' : '' }}" title="{{ $st['l'] }}" style="color:{{ $st['c'] }};">
                                        <div class="step-circle" style="border-color:{{ $i <= $stageIdx ? $st['c'] : '#d0d5dd' }};background:{{ $i <= $stageIdx ? $st['c'] : 'white' }};">
                                            <i class="fas fa-{{ $st['i'] }}"></i>
                                        </div>
                                        <span class="step-label d-none d-md-block">{{ $st['l'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Transaction Details Grid -->
                        <div class="row g-2">
                            <!-- Appointments -->
                            <div class="col-md-6 col-lg-3">
                                <div class="txn-card txn-appt">
                                    <div class="txn-header">
                                        <i class="fas fa-calendar-check me-1"></i>Appointments
                                        <span class="txn-badge">{{ count($workflow['appointments']) }}</span>
                                    </div>
                                    @forelse($workflow['appointments'] as $a)
                                        <div class="txn-item">
                                            <div class="d-flex justify-content-between">
                                                <span class="txn-label">
                                                    <span class="badge bg-primary me-1" style="font-size:.6rem;">APT-{{ str_pad($a['id'] ?? 0, 5, '0', STR_PAD_LEFT) }}</span>
                                                    {{ \Carbon\Carbon::parse($a['date'])->format('M d') }}
                                                </span>
                                                <span class="txn-status status-{{ $a['status'] }}">{{ ucfirst($a['status']) }}</span>
                                            </div>
                                            <div class="txn-desc">{{ $a['service_type'] ?? '—' }}</div>
                                        </div>
                                    @empty
                                        <div class="txn-empty">No appointments</div>
                                    @endforelse
                                </div>
                            </div>
                            <!-- Inspections -->
                            <div class="col-md-6 col-lg-3">
                                <div class="txn-card txn-insp">
                                    <div class="txn-header">
                                        <i class="fas fa-search me-1"></i>Inspections
                                        <span class="txn-badge">{{ count($workflow['inspections']) }}</span>
                                    </div>
                                    @forelse($workflow['inspections'] as $insp)
                                        <div class="txn-item">
                                            <div class="d-flex justify-content-between">
                                                <span class="txn-label">
                                                    @if(!empty($insp['transaction_id']))
                                                        <span class="badge bg-secondary me-1" style="font-size:.6rem;">{{ $insp['transaction_id'] }}</span>
                                                    @endif
                                                    {{ \Carbon\Carbon::parse($insp['date'])->format('M d') }}
                                                </span>
                                                <span class="txn-status">{{ $insp['technician'] ?? '—' }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="txn-desc">{{ $insp['inspection_type'] ?? ($insp['customer_concerns'] ?? '—') }}</span>
                                                @if(!empty($insp['is_archived']))
                                                    <span class="txn-status" style="color:#6c757d;"><i class="fas fa-archive me-1"></i>Archived</span>
                                                @endif
                                            </div>
                                        </div>
                                    @empty
                                        <div class="txn-empty">No inspections</div>
                                    @endforelse
                                </div>
                            </div>
                            <!-- Estimates -->
                            <div class="col-md-6 col-lg-3">
                                <div class="txn-card txn-est">
                                    <div class="txn-header">
                                        <i class="fas fa-file-invoice me-1"></i>Estimates
                                        <span class="txn-badge">{{ count($workflow['estimates']) }}</span>
                                    </div>
                                    @forelse($workflow['estimates'] as $e)
                                        <div class="txn-item">
                                            <div class="d-flex justify-content-between">
                                                <span class="txn-label">
                                                    <span class="badge bg-purple me-1" style="font-size:.6rem;background:#9b59b6;">EST-{{ str_pad($e['id'] ?? 0, 5, '0', STR_PAD_LEFT) }}</span>
                                                    {{ $e['estimate_number'] ?? '#'.$e['id'] }}
                                                </span>
                                                <span class="txn-status">₱{{ number_format($e['total_amount'] ?? 0, 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="txn-desc">{{ \Carbon\Carbon::parse($e['date'])->format('M d') }}</span>
                                                <span class="txn-status status-{{ $e['status'] ?? '' }}">{{ ucfirst($e['status'] ?? '—') }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="txn-empty">No estimates</div>
                                    @endforelse
                                </div>
                            </div>
                            <!-- Work Orders / Job Orders -->
                            <div class="col-md-6 col-lg-3">
                                <div class="txn-card txn-wo">
                                    <div class="txn-header">
                                        <i class="fas fa-clipboard-list me-1"></i>Job Orders
                                        <span class="txn-badge">{{ count($workflow['work_orders']) }}</span>
                                    </div>
                                    @forelse($workflow['work_orders'] as $wo)
                                        <div class="txn-item">
                                            <div class="d-flex justify-content-between">
                                                <span class="txn-label">
                                                    <span class="badge bg-danger me-1" style="font-size:.6rem;">WO-{{ str_pad($wo['id'] ?? 0, 5, '0', STR_PAD_LEFT) }}</span>
                                                    #{{ $wo['id'] }}
                                                </span>
                                                <span class="txn-status">₱{{ number_format($wo['estimated_total'] ?? 0, 0) }}</span>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span class="txn-desc">{{ isset($wo['date']) ? \Carbon\Carbon::parse($wo['date'])->format('M d') : '—' }}</span>
                                                <span class="txn-status status-{{ $wo['status'] ?? '' }}">{{ ucfirst(str_replace('_',' ',$wo['status'] ?? '—')) }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="txn-empty">No job orders</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions Footer (Section 6) -->
                        <div class="mt-3 pt-2 d-flex flex-wrap gap-2" style="border-top:1px solid #eef0f2;">
                            @if($vehicle)
                                <a href="{{ route('work-orders.create',['vehicle_id'=>$vehicle->id]) }}" class="btn btn-sm btn-action" style="color:#4361ee;border-color:#4361ee;">
                                    <i class="fas fa-plus-circle me-1"></i>Job Order
                                </a>
                                <a href="{{ route('estimates.create',['vehicle_id'=>$vehicle->id]) }}" class="btn btn-sm btn-action" style="color:#9b59b6;border-color:#9b59b6;">
                                    <i class="fas fa-file-invoice me-1"></i>Estimate
                                </a>
                            @endif
                            @if($vehicle)
                                <a href="{{ route('vehicles.show',$vehicle->id) }}" class="btn btn-sm btn-action" style="color:#6c7a8d;border-color:#d0d5dd;">
                                    <i class="fas fa-eye me-1"></i>Vehicle
                                </a>
                            @endif
                            @if($customer)
                                <a href="{{ route('customers.show',$customer->id) }}" class="btn btn-sm btn-action" style="color:#6c7a8d;border-color:#d0d5dd;">
                                    <i class="fas fa-user me-1"></i>Customer Profile
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <!-- Empty State -->
            <div class="card border-0 shadow-sm" style="border-radius:12px;">
                <div class="card-body text-center py-5">
                    <div style="width:80px;height:80px;border-radius:50%;background:#f0f2f5;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;">
                        <i class="fas fa-inbox" style="font-size:2rem;color:#c0cad8;"></i>
                    </div>
                    <h5 class="fw-bold" style="color:#1a2332;">No Service Records Found</h5>
                    <p class="text-muted mb-0">No transactions match your current filters.</p>
                    <a href="{{ route('service-records.index') }}" class="btn btn-primary btn-sm mt-3"><i class="fas fa-undo me-1"></i>Clear Filters</a>
                </div>
            </div>
        @endforelse
    </div>

    @if(count($workflows) > 0)
        <div class="text-center mt-3">
            <small class="text-muted">Showing {{ count($workflows) }} record(s)</small>
        </div>
    @endif
</div>
@endsection

@section('styles')
@push('styles')
<style>
/* ── FixIt Service Records Dashboard CSS ── */
.fixit-sr-dash{ background:#f4f6fa; min-height:100vh; padding-top:1.25rem; padding-bottom:2rem; }
.fixit-sr-dash .page-title{ color:#1a2332; font-size:1.25rem; }
.fixit-sr-dash .accent-icon{ color:#4361ee; }
.fixit-sr-dash .status-dot{ display:inline-block; width:6px;height:6px;border-radius:50%;background:#4361ee;vertical-align:middle; }

/* Summary Cards */
.summary-card{ border-radius:12px; background:white; border:0; box-shadow:0 1px 3px rgba(0,0,0,.05); transition:box-shadow .2s,transform .15s; }
.summary-card:hover{ box-shadow:0 4px 12px rgba(0,0,0,.08); transform:translateY(-1px); }
.summary-icon{ width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.summary-label{ font-size:.78rem;color:#6c7a8d;text-transform:uppercase;letter-spacing:.5px;font-weight:600; }
.summary-value{ font-size:1.6rem;font-weight:700;color:#1a2332;line-height:1.2; }
.summary-sublabel{ font-size:.7rem;color:#8b9aab; }
/* Summary Cards — Compact Professional */
.summary-card{ border-radius:10px; background:white; border:0; box-shadow:0 1px 2px rgba(0,0,0,.04); transition:box-shadow .2s,transform .15s; margin-bottom:0; }
.summary-card:hover{ box-shadow:0 3px 8px rgba(0,0,0,.06); transform:translateY(-1px); }
.summary-icon{ width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.summary-label{ font-size:.68rem;color:#6c7a8d;text-transform:uppercase;letter-spacing:.4px;font-weight:600; }
.summary-value{ font-size:1.25rem;font-weight:700;color:#1a2332;line-height:1.1;margin-bottom:2px; }
.summary-sublabel{ font-size:.65rem;color:#8b9aab; }

/* Filter Bar */
.filter-bar{ background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.05); position:sticky; top:0; z-index:100; }
.filter-label{ font-size:.78rem;font-weight:500;color:#6c7a8d; }
.filter-input{ border-radius:8px;border-color:#e0e6ed;font-size:.85rem;padding:.4rem .65rem; }
.filter-input:focus{ border-color:#4361ee;box-shadow:0 0 0 2px rgba(67,97,238,.15); }
.btn-clear{ background:#f4f6fa;color:#6c7a8d;border:1px solid #e0e6ed;border-radius:8px;padding:.45rem; }
.btn-clear:hover{ background:#e8ecf0; }

/* Alerts */
.alert-payment{ border-radius:10px;background:#fff8e7;color:#916d00; }
.alert-parts{ border-radius:10px;background:#ffecec;color:#b00020; }

/* Workflow Cards */
.workflow-card{ background:white; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.05); overflow:hidden; transition:box-shadow .2s; }
.workflow-card:hover{ box-shadow:0 3px 10px rgba(0,0,0,.08); }
.wf-header{ display:flex;align-items:center;justify-content:space-between;padding:.85rem 1rem;background:white;cursor:pointer;gap:8px; }
.wf-header:hover{ background:#fafbfc; }
.wf-avatar{ width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-size:1rem; }
.badge-vehicle{ background:#f0f2f5;color:#6c7a8d;font-weight:400;font-size:.73rem;border-radius:6px;padding:.25em .65em; }
.badge-plate{ background:#1a2332;color:white;font-weight:500;font-size:.68rem;letter-spacing:.3px;border-radius:4px;padding:.2em .5em; }
.wf-meta{ font-size:.75rem;color:#8b9aab; }
.wf-meta i{ font-size:.65rem;margin-right:4px; }
.stage-badge{ font-weight:500;font-size:.73rem;padding:.3rem .7rem;border-radius:20px;white-space:nowrap; }
.btn-ghost{ background:transparent;border:1px solid #e0e6ed;border-radius:8px;color:#6c7a8d;padding:.2rem .5rem; }
.btn-ghost:hover{ background:#f0f2f5; }
.wf-dropdown{ border-radius:10px;padding:.35rem 0;font-size:.85rem;border:0; }
.wf-dropdown .dropdown-item{ padding:.45rem 1rem; }
.wf-dropdown .dropdown-item:hover{ background:#f4f6fa; }
.chevron-icon{ color:#c0cad8;font-size:.7rem;transition:transform .25s; }
[aria-expanded="true"] .chevron-icon{ transform:rotate(180deg); }
.wf-body{ padding:.75rem 1rem 1rem; background:#fafbfc; border-top:1px solid #f0f2f5; }

/* Stepper */
.stepper-wrapper{ position:relative; padding:.3rem 0 .2rem; }
.stepper-track{ position:absolute; top:30px; left:3%; right:3%; height:3px; background:#e0e6ed; border-radius:2px; }
.stepper-progress{ height:100%; background:linear-gradient(90deg,#4361ee,#2ec4b6); border-radius:2px; transition:width .5s ease; }
.stepper-dots{ display:flex; justify-content:space-between; position:relative; }
.step{ display:flex; flex-direction:column; align-items:center; gap:4px; width:50px; cursor:default; }
.step-circle{ width:30px;height:30px;border-radius:50%;border:2.5px solid;display:flex;align-items:center;justify-content:center;font-size:.65rem;transition:all .25s; }
.step.active .step-circle{ box-shadow:0 0 0 3px rgba(67,97,238,.15); transform:scale(1.05); }
.step-label{ font-size:.65rem;color:#6c7a8d;font-weight:500;text-align:center;white-space:nowrap; }
.step.active .step-label{ color:#1a2332;font-weight:600; }

/* Transaction Cards */
.txn-card{ background:white; border-radius:10px; padding:.6rem; border:1px solid #eef0f2; height:100%; }
.txn-header{ font-size:.8rem;font-weight:600;color:#1a2332;margin-bottom:.4rem;display:flex;align-items:center;gap:6px; }
.txn-badge{ margin-left:auto;background:#f0f2f5;color:#6c7a8d;font-size:.7rem;font-weight:500;padding:.1rem .45rem;border-radius:10px;min-width:20px;text-align:center; }
.txn-item{ padding:.35rem 0; border-bottom:1px solid #f4f6fa; font-size:.78rem; }
.txn-item:last-child{ border-bottom:0; }
.txn-label{ font-weight:500;color:#1a2332; }
.txn-desc{ color:#8b9aab; font-size:.73rem; margin-top:1px; }
.txn-empty{ color:#c0cad8; font-size:.78rem; padding:.3rem 0; font-style:italic; }
.txn-status{ font-size:.73rem; font-weight:500; }
.status-booked{ color:#4361ee; }
.status-checked_in{ color:#f7a429; }
.status-pending{ color:#f7a429; }
.status-in_progress{ color:#4361ee; }
.status-completed{ color:#27ae60; }
.status-cancelled{ color:#8b9aab; }
.status-approved{ color:#2ec4b6; }
.status-paid{ color:#27ae60; }

/* Action Buttons */
.btn-action{ border-radius:8px;border:1px solid;background:transparent;padding:.3rem .65rem;font-size:.78rem; }
.btn-action:hover{ opacity:.8; }

/* Mobile */
@media(max-width:576px){
    .wf-header{ flex-wrap:wrap; gap:6px; }
    .wf-avatar{ width:36px;height:36px;font-size:.85rem; }
    .stage-badge{ font-size:.68rem;padding:.2rem .5rem; }
    .chevron-icon{ font-size:.6rem; }
}
</style>
@endpush
