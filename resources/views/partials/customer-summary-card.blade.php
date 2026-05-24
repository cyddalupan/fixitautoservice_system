@if($selectedCustomer ?? null)
<div class="customer-summary-card">
    <div class="card-body">
        <div class="d-flex align-items-start gap-3 flex-wrap">
            <!-- Avatar -->
            <div class="customer-avatar">
                {{ strtoupper(substr($selectedCustomer->first_name, 0, 1)) }}{{ strtoupper(substr($selectedCustomer->last_name, 0, 1)) }}
            </div>
            
            <!-- Customer Info -->
            <div class="flex-grow-1" style="min-width: 200px;">
                <div class="customer-name">{{ $selectedCustomer->name }}</div>
                <div class="customer-subtitle">
                    <i class="fas fa-phone-alt me-1"></i> {{ $selectedCustomer->phone ?? 'No phone' }}
                    @if($selectedCustomer->email ?? false)
                        &nbsp;·&nbsp; <i class="fas fa-envelope me-1"></i>{{ $selectedCustomer->email }}
                    @endif
                </div>
                @if($selectedCustomer->address ?? false)
                    <div class="customer-subtitle mt-1">
                        <i class="fas fa-map-marker-alt me-1"></i>{{ $selectedCustomer->address }}
                        @if($selectedCustomer->city ?? false), {{ $selectedCustomer->city }}@endif
                    </div>
                @endif
            </div>
            
            <!-- Stats -->
            <div class="d-flex">
                <div class="stat-item">
                    <div class="stat-value">{{ ($customerVehicles ?? collect())->count() }}</div>
                    <div class="stat-label">Vehicles</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $selectedCustomer->total_service_count ?? ($customerHistory ?? collect())->count() }}</div>
                    <div class="stat-label">Visits</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ ($customerHistory ?? collect())->where('created_at', '>=', now()->subDays(30))->count() }}</div>
                    <div class="stat-label">30d</div>
                </div>
            </div>
        </div>
        
        <!-- Vehicles -->
        @if(($customerVehicles ?? collect())->isNotEmpty())
        <div class="mt-3">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fas fa-car" style="font-size: 0.75rem; opacity: 0.7;"></i>
                <span style="font-size: 0.75rem; opacity: 0.7; text-transform: uppercase; letter-spacing: 0.5px;">Vehicles</span>
            </div>
            <div class="vehicle-pills">
                @foreach($customerVehicles as $v)
                <span class="vehicle-pill {{ (isset($selectedVehicle) && $selectedVehicle && $selectedVehicle->id == $v->id) ? 'active' : '' }}"
                      onclick="document.getElementById('vehicle_id').value='{{ $v->id }}'; $('#vehicle_id').trigger('change');"
                      title="{{ $v->vin ?? '' }} {{ $v->odometer ? '· ODO: '.number_format($v->odometer).' km' : '' }} 
                             {{ $v->last_service_date ? '· Last: '.\Carbon\Carbon::parse($v->last_service_date)->format('M d, Y') : '' }}">
                    {{ $v->year }} {{ $v->make }} {{ $v->model }}
                    @if($v->license_plate) <strong>[{{ $v->license_plate }}]</strong> @endif
                </span>
                @endforeach
            </div>
        </div>
        @endif
        
        <!-- Warnings / Balance -->
        <div class="d-flex flex-wrap gap-2 mt-3">
            @if(($selectedCustomer->balance ?? 0) > 0)
            <div class="danger-badge">
                <i class="fas fa-exclamation-triangle"></i>
                Balance: ₱{{ number_format($selectedCustomer->balance, 2) }}
            </div>
            @endif
            @if($selectedCustomer->notes ?? false)
            <div class="warning-badge" title="{{ $selectedCustomer->notes }}">
                <i class="fas fa-sticky-note"></i>
                {{ Str::limit($selectedCustomer->notes, 60) }}
            </div>
            @endif
            @if(($customerVehicles->first()->last_service_date ?? null) && \Carbon\Carbon::parse($customerVehicles->first()->last_service_date)->diffInMonths(now()) > 3)
            <div class="warning-badge">
                <i class="fas fa-clock"></i>
                Last service: {{ \Carbon\Carbon::parse($customerVehicles->first()->last_service_date)->format('M d, Y') }} ({{ \Carbon\Carbon::parse($customerVehicles->first()->last_service_date)->diffForHumans() }})
            </div>
            @endif
        </div>
    </div>
</div>
@endif
