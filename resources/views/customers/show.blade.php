@extends('layouts.app')

@section('title', $customer->first_name . ' ' . $customer->last_name . ' - Fix-It Auto Services')
@section('body-class', 'page-customers')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-module-header">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="module-icon">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h1 class="h4 mb-1" style="font-weight: 700;">{{ $customer->first_name }} {{ $customer->last_name }}</h1>
                    <ol class="breadcrumb m-0 p-0" style="background: none; font-size: 0.8rem;">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('customers.index') }}">Customers</a></li>
                        <li class="breadcrumb-item active">{{ $customer->first_name }} {{ $customer->last_name }}</li>
                    </ol>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('customers.edit', $customer) }}" class="btn-filter-primary">
                    <i class="fas fa-edit me-1"></i> Edit Customer
                </a>
                <a href="{{ route('customers.index') }}" class="btn-filter-outline">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </a>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column: Profile & Quick Actions -->
        <div class="col-lg-4">
            <!-- Profile Card -->
            <div class="main-card">
                <div class="profile-card-header">
                    <div class="profile-avatar-wrapper">
                        @if($customer->hasProfilePicture)
                            <img src="{{ $customer->avatar }}" 
                                 alt="{{ $customer->first_name }} {{ $customer->last_name }}"
                                 class="profile-avatar"
                                 id="profile-picture-img">
                        @else
                            <div class="profile-avatar profile-avatar-initials" id="profile-picture-initials">
                                {{ $customer->avatar }}
                            </div>
                        @endif
                        <button type="button" 
                                class="profile-avatar-upload-btn"
                                data-bs-toggle="modal" 
                                data-bs-target="#profilePictureModal"
                                title="Update profile picture">
                            <i class="fas fa-camera"></i>
                        </button>
                    </div>
                    <h5 class="mb-1 fw-bold">{{ $customer->first_name }} {{ $customer->last_name }}</h5>
                    <div class="d-flex flex-wrap justify-content-center gap-1 mb-0">
                        <span class="badge-custom badge-success">{{ $customer->is_active ? 'Active' : 'Inactive' }}</span>
                        <span class="badge-custom badge-primary">{{ ucfirst($customer->customer_type) }}</span>
                        @if($customer->segment)
                            <span class="badge-custom badge-secondary">{{ ucfirst($customer->segment) }}</span>
                        @endif
                    </div>
                </div>
                <div class="main-card-body px-3 pb-3">
                    <!-- Contact Info -->
                    <div class="customer-details-section">
                        <div class="detail-label">Contact Information</div>
                        @if($customer->email)
                            <div class="detail-row">
                                <i class="fas fa-envelope detail-icon"></i>
                                <a href="mailto:{{ $customer->email }}">{{ $customer->email }}</a>
                            </div>
                        @endif
                        @if($customer->phone)
                            <div class="detail-row">
                                <i class="fas fa-phone detail-icon"></i>
                                <a href="tel:{{ $customer->phone }}">{{ $customer->phone }}</a>
                            </div>
                        @endif
                        @if($customer->preferred_contact_method)
                            <div class="detail-row">
                                <i class="fas fa-comment detail-icon"></i>
                                <small>Prefers {{ ucfirst($customer->preferred_contact_method) }}</small>
                            </div>
                        @endif
                    </div>

                    <!-- Address -->
                    @if($customer->address || $customer->city || $customer->state || $customer->zip_code)
                    <div class="customer-details-section">
                        <div class="detail-label">Address</div>
                        <div class="detail-row">
                            <i class="fas fa-map-marker-alt detail-icon detail-icon-start"></i>
                            <div>
                                @if($customer->address)<div>{{ $customer->address }}</div>@endif
                                @if($customer->city || $customer->state || $customer->zip_code)
                                    <div>{{ $customer->city }}{{ $customer->city && $customer->state ? ', ' : '' }}{{ $customer->state }} {{ $customer->zip_code }}</div>
                                @endif
                                @if($customer->country)<div>{{ $customer->country }}</div>@endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Company -->
                    @if($customer->company_name)
                    <div class="customer-details-section">
                        <div class="detail-label">Company</div>
                        <div class="detail-row">
                            <i class="fas fa-building detail-icon"></i>
                            <div>
                                <strong>{{ $customer->company_name }}</strong>
                                @if($customer->tax_id)
                                    <div class="text-muted small">Tax ID: {{ $customer->tax_id }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Customer Since & Loyalty -->
                    <div class="customer-details-section">
                        <div class="detail-label">Membership</div>
                        <div class="detail-row">
                            <i class="fas fa-calendar detail-icon"></i>
                            <span>Customer since {{ $customer->created_at->format('F j, Y') }}</span>
                        </div>
                        <div class="detail-row">
                            <i class="fas fa-star detail-icon" style="color: #f59e0b;"></i>
                            <div>
                                <strong>{{ number_format($customer->loyalty_points) }} points</strong>
                                @if($customer->loyalty_points >= 1000)
                                    <span class="badge-custom badge-gold ms-2">Gold Member</span>
                                @elseif($customer->loyalty_points >= 500)
                                    <span class="badge-custom badge-silver ms-2">Silver Member</span>
                                @else
                                    <span class="badge-custom badge-secondary ms-2">Bronze Member</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="main-card mt-4">
                <div class="main-card-header">
                    <i class="fas fa-bolt me-2" style="color: var(--module-active);"></i> Quick Actions
                </div>
                <div class="main-card-body px-3 pb-3">
                    <div class="quick-actions-grid">
                        <a href="{{ route('appointments.create') }}?customer_id={{ $customer->id }}" class="quick-action-btn" style="border-left-color: #f59e0b;">
                            <i class="fas fa-calendar-plus" style="color: #f59e0b;"></i>
                            <span>Appointment</span>
                        </a>
                        <a href="{{ route('inspections.create') }}?customer_id={{ $customer->id }}" class="quick-action-btn" style="border-left-color: var(--module-active);">
                            <i class="fas fa-tools" style="color: var(--module-active);"></i>
                            <span>Repair Order</span>
                        </a>
                        <a href="{{ route('estimates.create') }}?customer_id={{ $customer->id }}" class="quick-action-btn" style="border-left-color: #0ea5e9;">
                            <i class="fas fa-file-invoice-dollar" style="color: #0ea5e9;"></i>
                            <span>Estimate</span>
                        </a>
                        <a href="{{ route('work-orders.create') }}?customer_id={{ $customer->id }}" class="quick-action-btn" style="border-left-color: #ef4444;">
                            <i class="fas fa-wrench" style="color: #ef4444;"></i>
                            <span>Work Order</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Tabs with Vehicles, Services, Notes, Quotations -->
        <div class="col-lg-8">
            <!-- Tab Navigation -->
            <div class="main-card mb-4">
                <div class="tabs-nav-wrapper">
                    <ul class="tabs-nav" id="customerTabs" role="tablist">
                        <li class="tab-item active" role="presentation">
                            <button class="tab-link" id="vehicles-tab" data-bs-toggle="tab" data-bs-target="#vehicles" type="button" role="tab" aria-selected="true">
                                <i class="fas fa-car me-1"></i> Vehicles
                                @if($customer->vehicles && $customer->vehicles->count() > 0)
                                    <span class="badge-tab">{{ $customer->vehicles->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="tab-item" role="presentation">
                            <button class="tab-link" id="services-tab" data-bs-toggle="tab" data-bs-target="#services" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-wrench me-1"></i> Service History
                                @if(isset($unifiedHistory) && count($unifiedHistory) > 0)
                                    <span class="badge-tab">{{ count($unifiedHistory) }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="tab-item" role="presentation">
                            <button class="tab-link" id="quotations-tab" data-bs-toggle="tab" data-bs-target="#quotations" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-file-invoice-dollar me-1"></i> Quotations
                                @if($customer->quotations && $customer->quotations->count() > 0)
                                    <span class="badge-tab">{{ $customer->quotations->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="tab-item" role="presentation">
                            <button class="tab-link" id="notes-tab" data-bs-toggle="tab" data-bs-target="#notes" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-sticky-note me-1"></i> Notes
                                @if($customer->notes && $customer->notes->count() > 0)
                                    <span class="badge-tab">{{ $customer->notes->count() }}</span>
                                @endif
                            </button>
                        </li>
                        <li class="tab-item" role="presentation">
                            <button class="tab-link" id="archived-tab" data-bs-toggle="tab" data-bs-target="#archived" type="button" role="tab" aria-selected="false">
                                <i class="fas fa-archive me-1"></i> Archived
                                @if(isset($archivedInspections) && $archivedInspections->count() > 0)
                                    <span class="badge-tab">{{ $archivedInspections->count() }}</span>
                                @endif
                            </button>
                        </li>
                    </ul>
                    <div class="tabs-nav-extra">
                        <button type="button" class="btn-filter-outline btn-sm" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                            <i class="fas fa-plus me-1"></i> Add Note
                        </button>
                    </div>
                </div>

                <!-- Tab Content -->
                <div class="tab-content p-0">
                    <!-- Vehicles Tab -->
                    <div class="tab-pane fade show active" id="vehicles" role="tabpanel">
                        @if($customer->vehicles && $customer->vehicles->count() > 0)
                            <div class="table-responsive">
                                <table class="table-fixit">
                                    <thead>
                                        <tr>
                                            <th>Vehicle</th>
                                            <th>License Plate</th>
                                            <th>VIN / Engine</th>
                                            <th>Last Service</th>
                                            <th class="text-end" style="width: 60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->vehicles->take(5) as $vehicle)
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div style="width: 34px; height: 34px; background: var(--module-active-light); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: var(--module-active); font-size: 0.9rem;">
                                                            <i class="fas fa-car"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold" style="font-size: 0.88rem;">{{ $vehicle->make }} {{ $vehicle->model }}</div>
                                                            <small class="text-muted">{{ $vehicle->year }} {{ $vehicle->trim ? '/ '.$vehicle->trim : '' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code style="font-size: 0.75rem; background: #f1f5f9; padding: 2px 8px; border-radius: 4px; color: #334155;">{{ $vehicle->license_plate ?? 'N/A' }}</code>
                                                </td>
                                                <td>
                                                    <div style="font-size: 0.78rem; line-height: 1.5;">
                                                        <div>VIN: <span class="text-muted">{{ $vehicle->vin ? substr($vehicle->vin, 0, 8).'...' : 'N/A' }}</span></div>
                                                        <div>Engine: <span class="text-muted">{{ $vehicle->engine_no ?? 'N/A' }}</span></div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($vehicle->last_service_date)
                                                        <span style="font-size: 0.8rem;">{{ $vehicle->last_service_date->format('M j, Y') }}</span>
                                                    @else
                                                        <span class="text-muted" style="font-size: 0.8rem;">Never</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; padding: 0.25rem 0.5rem;">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($customer->vehicles->count() > 5)
                                <div class="text-center py-3 border-top">
                                    <a href="{{ route('customers.vehicles', $customer) }}" class="btn-filter-outline btn-sm">
                                        View All {{ $customer->vehicles->count() }} Vehicles
                                        <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="empty-state-module py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-car-side"></i>
                                </div>
                                <h5>No Vehicles</h5>
                                <p>No vehicles registered for this customer.</p>
                                <a href="{{ route('vehicles.create') }}?customer_id={{ $customer->id }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus me-1"></i> Add Vehicle
                                </a>
                            </div>
                        @endif
                    </div>

                    <!-- Service History Tab -->
                    <div class="tab-pane fade" id="services" role="tabpanel">
                        @if(isset($unifiedHistory) && count($unifiedHistory) > 0)
                            <div class="table-responsive">
                                <table class="table-fixit">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Service</th>
                                            <th>Vehicle</th>
                                            <th class="text-end">Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach(array_slice($unifiedHistory, 0, 50) as $tx)
                                            <tr>
                                                <td><span class="badge" style="font-size:.7rem; background: var(--module-active);">{{ $tx->ref_number }}</span></td>
                                                <td><span style="font-size: 0.85rem;">@if($tx->created_at ?? $tx->archived_at){{ \Carbon\Carbon::parse($tx->created_at ?? $tx->archived_at)->format('M j, Y h:i A') }}@else—@endif</span></td>
                                                <td>
                                                    <span class="badge" style="font-size:.7rem; background: var(--module-bg); color: var(--module-active);">
                                                        @switch($tx->type)
                                                            @case('appointment') <i class="fas fa-calendar"></i> Appt @break
                                                            @case('inspection') <i class="fas fa-clipboard-check"></i> Inspection @break
                                                            @case('work_order') <i class="fas fa-wrench"></i> Work Order @break
                                                            @case('estimate') <i class="fas fa-file-invoice-dollar"></i> Estimate @break
                                                            @case('invoice') <i class="fas fa-receipt"></i> Invoice @break
                                                            @case('archived_inspection') <i class="fas fa-archive"></i> Archived @break
                                                            @default {{ ucfirst(str_replace('_', ' ', $tx->type)) }}
                                                        @endswitch
                                                    </span>
                                                </td>
                                                <td><span style="font-size: 0.85rem; font-weight: 500;">{{ $tx->service_type ?: 'General' }}</span></td>
                                                <td><small class="text-muted">{{ $tx->vehicle_label ?? 'N/A' }}</small></td>
                                                <td class="text-end">
                                                    @if($tx->total_amount)
                                                        <span style="font-weight: 600;">${{ number_format($tx->total_amount, 2) }}</span>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $color = \App\Services\TransactionHistoryService::statusColor($tx->type, $tx->status ?? 'pending');
                                                    @endphp
                                                    <span class="status-badge {{ $color }}">{{ ucfirst($tx->status ?? 'pending') }}</span>
                                                    @if($tx->type === 'archived_inspection')
                                                        <span class="status-badge bg-secondary">Archived</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if(count($unifiedHistory) > 50)
                                <div class="text-center py-3 border-top">
                                    <span class="text-muted">Showing 50 of {{ count($unifiedHistory) }} transactions</span>
                                </div>
                            @endif
                        @else
                            <div class="empty-state-module py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-tools"></i>
                                </div>
                                <h5>No Service History</h5>
                                <p>No transactions recorded for this customer yet.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Archived Records Tab -->
                    <div class="tab-pane fade" id="archived" role="tabpanel">
                        @if(isset($archivedInspections) && $archivedInspections->count() > 0)
                            <div class="table-responsive">
                                <table class="table-fixit">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Date Archived</th>
                                            <th>Type</th>
                                            <th>Vehicle</th>
                                            <th>Concern</th>
                                            <th>Archived By</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($archivedInspections as $archive)
                                            @php
                                                $origData = $archive->original_data;
                                                $concerns = $origData['customer_concerns'] ?? '—';
                                                $inspType = $origData['inspection_type'] ?? '—';
                                                $archiver = $archive->archivedBy ? $archive->archivedBy->name : 'Unknown';
                                                $vehicleMake = $origData['vehicle_make'] ?? '';
                                                $vehicleModel = $origData['vehicle_model'] ?? '';
                                                $vehicleDisplay = $vehicleMake ? $vehicleMake . ' ' . $vehicleModel : 'Vehicle #' . ($origData['vehicle_id'] ?? '?');
                                            @endphp
                                            <tr style="background: #f8f9fa;">
                                                <td><span class="badge bg-secondary">INS-{{ str_pad($origData['id'] ?? $archive->archivable_id, 5, '0', STR_PAD_LEFT) }}</span></td>
                                                <td>{{ $archive->archived_at->format('M d, Y h:i A') }}</td>
                                                <td>{{ ucfirst(str_replace('_', ' ', $inspType)) }}</td>
                                                <td><small class="text-muted">{{ $vehicleDisplay }}</small></td>
                                                <td>{{ Str::limit($concerns, 40) }}</td>
                                                <td>{{ $archiver }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="empty-state-module py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-archive"></i>
                                </div>
                                <h5>No Archived Records</h5>
                                <p>No archived transactions for this customer.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Quotations Tab -->
                    <div class="tab-pane fade" id="quotations" role="tabpanel">
                        @php
                            $statusLabels = [
                                'new_lead' => 'New Lead',
                                'contacted' => 'Contacted',
                                'converted_to_customer' => 'Converted to Customer',
                                'appointment_booked' => 'Appointment Booked',
                                'won' => 'Won',
                                'lost' => 'Lost',
                                'archived' => 'Archived',
                            ];
                            $statusBadges = [
                                'new_lead' => 'badge-warning',
                                'contacted' => 'badge-info',
                                'converted_to_customer' => 'badge-success',
                                'appointment_booked' => 'badge-primary',
                                'won' => 'badge-success',
                                'lost' => 'badge-danger',
                                'archived' => 'badge-secondary',
                            ];
                        @endphp
                        @if($customer->quotations && $customer->quotations->count() > 0)
                            <div class="table-responsive">
                                <table class="table-fixit">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Concern</th>
                                            <th>Budget</th>
                                            <th>Status</th>
                                            <th class="text-end" style="width: 60px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($customer->quotations as $quotation)
                                            <tr>
                                                <td><span style="font-size: 0.85rem;">{{ $quotation->created_at->format('M j, Y') }}</span></td>
                                                <td>
                                                    <div style="max-width: 220px; font-size: 0.85rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="{{ $quotation->service_description }}">
                                                        {{ Str::limit($quotation->service_description, 50) }}
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($quotation->budget_min || $quotation->budget_max)
                                                        <span style="font-size: 0.85rem; font-weight: 500;">
                                                            @if($quotation->budget_min && $quotation->budget_max)
                                                                ₱{{ number_format($quotation->budget_min) }} – ₱{{ number_format($quotation->budget_max) }}
                                                            @elseif($quotation->budget_min)
                                                                ₱{{ number_format($quotation->budget_min) }}+
                                                            @else
                                                                up to ₱{{ number_format($quotation->budget_max) }}
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span class="text-muted" style="font-size: 0.82rem;">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="status-badge {{ $statusBadges[$quotation->status] ?? 'badge-secondary' }}">
                                                        {{ $statusLabels[$quotation->status] ?? ucfirst($quotation->status) }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('quotations.show', $quotation->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius: 6px; padding: 0.25rem 0.5rem;" title="View Quotation #{{ $quotation->id }}">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @if($customer->quotations->count() > 5)
                                <div class="text-center py-3 border-top">
                                    <a href="{{ route('quotations.index', ['customer_id' => $customer->id]) }}" class="btn-filter-outline btn-sm">
                                        View All {{ $customer->quotations->count() }} Quotations
                                        <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="empty-state-module py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-file-invoice"></i>
                                </div>
                                <h5>No Quotations</h5>
                                <p>No quotation history for this customer.</p>
                            </div>
                        @endif
                    </div>

                    <!-- Notes Tab -->
                    <div class="tab-pane fade" id="notes" role="tabpanel">
                        @if($customer->notes && $customer->notes->count() > 0)
                            <div class="px-3 py-3">
                                @foreach($customer->notes->take(10) as $note)
                                    <div class="note-card">
                                        <div class="note-card-header">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="badge-custom badge-note-type">{{ $note->note_type ?? 'General' }}</span>
                                                <small class="text-muted">{{ $note->created_at->format('M j, Y g:i A') }}</small>
                                            </div>
                                        </div>
                                        <div class="note-card-body">
                                            {{ $note->content }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            @if($customer->notes->count() > 5)
                                <div class="text-center py-3 border-top">
                                    <span class="text-muted" style="font-size: 0.85rem;">
                                        Showing {{ min(10, $customer->notes->count()) }} of {{ $customer->notes->count() }} notes
                                    </span>
                                </div>
                            @endif
                        @else
                            <div class="empty-state-module py-4">
                                <div class="empty-state-icon">
                                    <i class="fas fa-sticky-note"></i>
                                </div>
                                <h5>No Notes</h5>
                                <p>No notes added for this customer yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Customer Notes Section (if global notes exist) -->
            @if($customer->notes)
            <div class="main-card mb-4">
                <div class="main-card-header">
                    <i class="fas fa-info-circle me-2" style="color: var(--module-active);"></i> Customer Notes
                </div>
                <div class="main-card-body px-3 py-3">
                    <p class="mb-0" style="font-size: 0.9rem; color: #334155;">{{ $customer->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Add Note Modal -->
<div class="modal fade" id="addNoteModal" tabindex="-1" aria-labelledby="addNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('customers.notes.store', $customer) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addNoteModalLabel">
                        <i class="fas fa-plus-circle me-2" style="color: var(--module-active);"></i> Add Customer Note
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="note_type" class="form-label">Note Type</label>
                        <select class="form-select" id="note_type" name="note_type">
                            <option value="general">General</option>
                            <option value="preference">Preference</option>
                            <option value="issue">Issue</option>
                            <option value="follow_up">Follow-up</option>
                            <option value="reminder">Reminder</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Note Content</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Note</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Profile Picture Upload Modal -->
<div class="modal fade" id="profilePictureModal" tabindex="-1" aria-labelledby="profilePictureModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="profilePictureModalLabel">
                    <i class="fas fa-camera me-2" style="color: var(--module-active);"></i> Update Profile Picture
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="profilePictureForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="profile_picture" class="form-label">Choose a new profile picture</label>
                        <input type="file" class="form-control" id="profile_picture" name="profile_picture" accept="image/*" required>
                        <div class="form-text">Supported formats: JPEG, PNG, GIF. Max size: 2MB</div>
                    </div>
                    
                    <!-- Preview -->
                    <div class="mb-3 text-center">
                        <img id="imagePreview" src="#" alt="Preview" class="img-fluid rounded d-none" style="max-height: 200px;">
                    </div>
                    
                    @if($customer->hasProfilePicture)
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-danger w-100" id="removeProfilePictureBtn">
                            <i class="fas fa-trash me-2"></i> Remove Current Picture
                        </button>
                    </div>
                    @endif
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="uploadProfilePictureBtn">Upload Picture</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Image preview functionality
        $('#profile_picture').change(function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagePreview').attr('src', e.target.result).removeClass('d-none');
                }
                reader.readAsDataURL(file);
            }
        });

        // Upload profile picture
        $('#uploadProfilePictureBtn').click(function(e) {
            e.preventDefault();
            
            const fileInput = $('#profile_picture')[0];
            if (!fileInput.files || fileInput.files.length === 0) {
                showToast('error', 'Please select a picture to upload.');
                return;
            }
            
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');
            
            const formData = new FormData($('#profilePictureForm')[0]);
            
            $.ajax({
                url: '{{ route("customers.upload-profile-picture", $customer) }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        if ($('#profile-picture-img').length) {
                            $('#profile-picture-img').attr('src', response.profile_picture_url + '?' + new Date().getTime());
                        } else {
                            $('#profile-picture-initials').replaceWith(
                                '<img src="' + response.profile_picture_url + '" ' +
                                'alt="{{ $customer->first_name }} {{ $customer->last_name }}" ' +
                                'class="profile-avatar" id="profile-picture-img">'
                            );
                        }
                        showToast('success', 'Profile picture updated successfully!');
                        $('#profilePictureModal').modal('hide');
                        $('#profilePictureForm')[0].reset();
                        $('#imagePreview').addClass('d-none').attr('src', '#');
                    } else {
                        showToast('error', response.message || 'Upload failed');
                    }
                    $btn.prop('disabled', false).html('Upload Picture');
                },
                error: function(xhr) {
                    let errorMessage = 'An error occurred. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = Object.values(xhr.responseJSON.errors).join('<br>');
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.status === 413) {
                        errorMessage = 'File too large. Maximum size is 2MB.';
                    } else if (xhr.status === 422) {
                        errorMessage = 'Validation error. Please check file format and size.';
                    }
                    showToast('error', errorMessage);
                    $btn.prop('disabled', false).html('Upload Picture');
                }
            });
        });

        // Remove profile picture
        $('#removeProfilePictureBtn').click(function() {
            if (!confirm('Are you sure you want to remove the profile picture?')) {
                return;
            }

            $.ajax({
                url: '{{ route("customers.remove-profile-picture", $customer) }}',
                type: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        const initials = '{{ strtoupper(substr($customer->first_name, 0, 1) . substr($customer->last_name, 0, 1)) }}';
                        $('#profile-picture-img').replaceWith(
                            '<div class="profile-avatar profile-avatar-initials" id="profile-picture-initials">' + initials + '</div>'
                        );
                        $('#removeProfilePictureBtn').remove();
                        showToast('success', 'Profile picture removed successfully!');
                        $('#profilePictureModal').modal('hide');
                    }
                },
                error: function(xhr) {
                    showToast('error', 'Failed to remove profile picture. Please try again.');
                }
            });
        });

        // Toast notification function
        function showToast(type, message) {
            const toastHtml = `
                <div class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            ${message}
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            `;
            
            let toastContainer = $('#toast-container');
            if (toastContainer.length === 0) {
                $('body').append('<div id="toast-container" class="toast-container position-fixed top-0 end-0 p-3"></div>');
                toastContainer = $('#toast-container');
            }
            
            toastContainer.append(toastHtml);
            const toastElement = toastContainer.find('.toast:last-child');
            const toast = new bootstrap.Toast(toastElement[0]);
            toast.show();
            
            toastElement.on('hidden.bs.toast', function () {
                $(this).remove();
            });
        }
    });
</script>
@endpush
<!-- END customers/show.blade.php -->
