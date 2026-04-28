@extends('layouts.app')

@section('title', 'Customers - Fix-It Auto Services')

@push('styles')
<style>
/* ============================================
   SMART CUSTOMER SEARCH — Premium CRM UI
   ============================================ */

/* --- Skeleton Loading --- */
.customer-skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: skeleton-pulse 1.5s ease-in-out infinite;
    border-radius: 8px;
}
@keyframes skeleton-pulse {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
.dark-mode .customer-skeleton {
    background: linear-gradient(90deg, #2a2a2a 25%, #3a3a3a 50%, #2a2a2a 75%);
    background-size: 200% 100%;
}

/* --- Search Bar --- */
.smart-search-wrapper {
    position: relative;
}
.smart-search-bar {
    height: 48px;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
    padding-left: 44px;
    padding-right: 120px;
    font-size: 15px;
    transition: all 0.2s ease;
    background: #fff;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.smart-search-bar:focus {
    border-color: #4f46e5;
    box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.12);
    outline: none;
}
.smart-search-icon {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 16px;
    pointer-events: none;
}
.smart-search-badge {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 11px;
    background: #e2e8f0;
    color: #64748b;
    padding: 3px 10px;
    border-radius: 6px;
    font-weight: 600;
    letter-spacing: 0.3px;
    pointer-events: none;
}

/* --- Autocomplete Dropdown --- */
.autocomplete-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    z-index: 1050;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.10);
    margin-top: 4px;
    max-height: 280px;
    overflow-y: auto;
    display: none;
}
.autocomplete-dropdown.show {
    display: block;
}
.autocomplete-item {
    display: flex;
    align-items: center;
    padding: 10px 16px;
    cursor: pointer;
    transition: background 0.1s;
    gap: 10px;
}
.autocomplete-item:hover {
    background: #f1f5f9;
}
.autocomplete-item:first-child {
    border-radius: 12px 12px 0 0;
}
.autocomplete-item:last-child {
    border-radius: 0 0 12px 12px;
}
.autocomplete-type-badge {
    font-size: 10px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 2px 8px;
    border-radius: 4px;
    background: #eef2ff;
    color: #4f46e5;
    white-space: nowrap;
    flex-shrink: 0;
}
.autocomplete-item .value {
    font-size: 14px;
    color: #1e293b;
    flex: 1;
}

/* --- Tabs / Quick Filters --- */
.filter-tabs {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.filter-tab {
    padding: 6px 14px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    border: 1.5px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
}
.filter-tab:hover {
    border-color: #4f46e5;
    color: #4f46e5;
    background: #eef2ff;
}
.filter-tab.active {
    border-color: #4f46e5;
    background: #4f46e5;
    color: #fff;
}
.filter-tab .badge {
    font-size: 10px;
    margin-left: 4px;
    background: rgba(255,255,255,0.2);
}

/* --- Advanced Filters Panel --- */
.advanced-filters-panel {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    display: none;
    margin-top: 8px;
}
.advanced-filters-panel.show {
    display: block;
}
.dark-mode .advanced-filters-panel {
    background: #1e293b;
    border-color: #334155;
}

/* --- Customer Cards (modern result layout) --- */
.customer-result-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 18px;
    margin-bottom: 8px;
    transition: all 0.2s ease;
    cursor: pointer;
    position: relative;
}
.customer-result-card:hover {
    border-color: #4f46e5;
    box-shadow: 0 4px 16px rgba(79, 70, 229, 0.10);
    transform: translateY(-1px);
}
.customer-result-card:last-child {
    margin-bottom: 0;
}
.dark-mode .customer-result-card {
    background: #1e293b;
    border-color: #334155;
}

.customer-avatar-circle {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 16px;
    color: #fff;
    flex-shrink: 0;
    background: linear-gradient(135deg, #4f46e5, #7c3aed);
}
.customer-avatar-circle img {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
}

/* Vehicle badges */
.vehicle-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 6px;
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    white-space: nowrap;
    margin-right: 4px;
    margin-bottom: 4px;
}
.dark-mode .vehicle-badge {
    background: #334155;
    color: #e2e8f0;
    border-color: #475569;
}

/* Status labels */
.status-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 20px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.status-active {
    background: #dcfce7;
    color: #16a34a;
}
.status-inactive {
    background: #f1f5f9;
    color: #64748b;
}
.status-unpaid {
    background: #fef2f2;
    color: #dc2626;
}

/* Plate number highlight */
.plate-highlight {
    font-family: 'Courier New', monospace;
    font-weight: 700;
    font-size: 13px;
    background: #fef9c3;
    color: #a16207;
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid #fde68a;
    display: inline-block;
}
.dark-mode .plate-highlight {
    background: #713f12;
    color: #fef9c3;
    border-color: #a16207;
}

/* Archive button on card */
.archive-btn {
    padding: 2px 8px;
    font-size: 12px;
    border-radius: 4px;
    opacity: 0.6;
    transition: opacity 0.2s, background 0.2s;
    margin-bottom: 8px;
}
.archive-btn:hover {
    opacity: 1;
}
.customer-result-card:hover .archive-btn {
    opacity: 0.9;
}

/* Last visit badge */
.last-visit-badge {
    font-size: 12px;
    color: #64748b;
    background: #f1f5f9;
    padding: 4px 10px;
    border-radius: 6px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.dark-mode .last-visit-badge {
    background: #334155;
    color: #94a3b8;
}

/* Price highlight */
.price-highlight {
    font-weight: 700;
    color: #059669;
}
.price-unpaid {
    color: #dc2626;
}

/* Customer name link */
.customer-name-link {
    font-weight: 700;
    font-size: 15px;
    color: #1e293b;
    text-decoration: none;
}
.customer-name-link:hover {
    color: #4f46e5;
}
.dark-mode .customer-name-link {
    color: #e2e8f0;
}

/* --- Counter / Info --- */
.search-meta {
    font-size: 13px;
    color: #64748b;
    margin-bottom: 12px;
}
.search-meta strong {
    color: #1e293b;
}
.dark-mode .search-meta strong {
    color: #e2e8f0;
}

/* Quick stats row */
.quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 10px;
    margin-bottom: 16px;
}
.stat-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 12px;
    text-align: center;
}
.dark-mode .stat-item {
    background: #1e293b;
    border-color: #334155;
}
.stat-item .stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #4f46e5;
}
.stat-item .stat-label {
    font-size: 11px;
    color: #64748b;
    margin-top: 2px;
}

/* --- Load More --- */
.load-more-container {
    text-align: center;
    padding: 24px 0;
}
.load-more-btn {
    padding: 10px 36px;
    border-radius: 30px;
    font-weight: 600;
    border: 2px solid #e2e8f0;
    background: #fff;
    color: #4f46e5;
    transition: all 0.2s;
    cursor: pointer;
}
.load-more-btn:hover {
    border-color: #4f46e5;
    background: #eef2ff;
}
.load-more-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 3px solid #e2e8f0;
    border-top-color: #4f46e5;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
}

/* --- Empty State --- */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state .empty-icon {
    font-size: 48px;
    color: #cbd5e1;
    margin-bottom: 16px;
}
.empty-state h5 {
    color: #475569;
    font-weight: 600;
    margin-bottom: 6px;
}
.empty-state p {
    color: #94a3b8;
    font-size: 14px;
    max-width: 360px;
    margin: 0 auto;
}
.dark-mode .empty-state .empty-icon {
    color: #475569;
}
.dark-mode .empty-state h5 {
    color: #94a3b8;
}

/* --- No results suggestions --- */
.search-suggestions {
    display: flex;
    gap: 6px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 12px;
}
.search-suggestion-chip {
    padding: 4px 14px;
    border-radius: 20px;
    font-size: 12px;
    background: #f1f5f9;
    color: #4f46e5;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    transition: all 0.1s;
}
.search-suggestion-chip:hover {
    background: #eef2ff;
    border-color: #4f46e5;
}

/* --- Responsive --- */
@media (max-width: 768px) {
    .smart-search-bar {
        padding-right: 16px;
        font-size: 14px;
    }
    .smart-search-badge {
        display: none;
    }
    .filter-tabs {
        gap: 4px;
    }
    .filter-tab {
        font-size: 11px;
        padding: 5px 10px;
    }
    .customer-result-card {
        padding: 14px;
    }
    .quick-stats {
        grid-template-columns: repeat(2, 1fr);
    }
    .customer-info-grid {
        flex-direction: column;
    }
}

/* Sticky header */
.sticky-header {
    position: sticky;
    top: 60px;
    z-index: 100;
    background: #f1f5f9;
    padding: 12px 0 8px;
}
.dark-mode .sticky-header {
    background: #0f172a;
}

/* Transition animations */
.customer-result-card {
    animation: fadeInUp 0.25s ease-out;
}
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Scroll offset for results container */
#customer-results {
    min-height: 200px;
}
</style>
@endpush

@section('content')
<div class="page-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-users me-2"></i>Customers
            </h1>
            <p class="text-muted mb-0">Smart search, filter, and manage your customer database</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add New Customer
            </a>
            <button type="button" class="btn btn-success" onclick="generateCustomerForm()">
                <i class="fas fa-link me-1"></i> Generate Form
            </button>
            <a href="{{ route('customers.generated-forms') }}" class="btn btn-info">
                <i class="fas fa-list me-1"></i> Generated Forms
            </a>
            @if(in_array(Auth::user()->role, ['super_admin', 'admin']))
            <button type="button" class="btn btn-outline-danger" onclick="showArchiveModal()">
                <i class="fas fa-archive me-1"></i> Archive
            </button>
            @endif
        </div>
    </div>
</div>

<!-- ======== STICKY SEARCH + FILTERS ======== -->
<div class="sticky-header">
    <div class="smart-search-wrapper">
        <i class="fas fa-search smart-search-icon"></i>
        <input type="text"
               id="customer-search"
               class="form-control smart-search-bar"
               placeholder="Search customers by name, plate #, vehicle, phone, email, location..."
               autocomplete="off"
               spellcheck="false">
        <span class="smart-search-badge">SEARCH</span>

        <!-- Autocomplete dropdown -->
        <div class="autocomplete-dropdown" id="autocomplete-dropdown"></div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
        <!-- Quick Filter Tabs -->
        <div class="filter-tabs" id="quick-filter-tabs">
            <span class="filter-tab active" data-filter="all">All</span>
            <span class="filter-tab" data-filter="active">Active</span>
            <span class="filter-tab" data-filter="inactive">Inactive</span>
            <span class="filter-tab" data-filter="unpaid">Unpaid Balance</span>
            <span class="filter-tab" data-filter="frequent">Frequent</span>
            <span class="filter-tab" data-filter="new">New (30d)</span>
            <span class="filter-tab" data-filter="recent-service">Recent Service</span>
        </div>

        <button class="btn btn-sm btn-outline-secondary" id="advanced-filter-toggle" type="button">
            <i class="fas fa-sliders-h me-1"></i> Filters
        </button>
    </div>

    <!-- Advanced Filters Panel -->
    <div class="advanced-filters-panel" id="advanced-filters-panel">
        <div class="row g-3">
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold small">Vehicle Brand</label>
                <select class="form-select form-select-sm" id="filter-brand">
                    <option value="">All Brands</option>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold small">Vehicle Model</label>
                <select class="form-select form-select-sm" id="filter-model">
                    <option value="">All Models</option>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold small">Location</label>
                <select class="form-select form-select-sm" id="filter-location">
                    <option value="">All Locations</option>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label fw-semibold small">Last Service</label>
                <select class="form-select form-select-sm" id="filter-last-service">
                    <option value="">Any Time</option>
                    <option value="30">Last 30 Days</option>
                    <option value="90">Last 90 Days</option>
                    <option value="180">Last 6 Months</option>
                    <option value="365">Last Year</option>
                </select>
            </div>
        </div>
        <div class="text-end mt-2">
            <button class="btn btn-sm btn-primary" id="apply-filters-btn">
                <i class="fas fa-check me-1"></i> Apply Filters
            </button>
            <button class="btn btn-sm btn-outline-secondary" id="clear-filters-btn">
                <i class="fas fa-times me-1"></i> Clear
            </button>
        </div>
    </div>
</div>

<!-- ======== QUICK STATS ======== -->
<div class="quick-stats" id="quick-stats">
    <div class="stat-item">
        <div class="stat-value" id="stat-total">—</div>
        <div class="stat-label">Total Customers</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" id="stat-active">—</div>
        <div class="stat-label">Active</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" id="stat-services">—</div>
        <div class="stat-label">Total Services</div>
    </div>
    <div class="stat-item">
        <div class="stat-value" id="stat-unpaid">—</div>
        <div class="stat-label">Unpaid</div>
    </div>
</div>

<!-- ======== RESULTS ======== -->
<div id="customer-results">
    <!-- Skeleton loading shown by JS on initial load -->
    <div id="skeleton-container"></div>
    <div id="results-container"></div>
    <div id="empty-state-container"></div>
</div>

<!-- ======== LOAD MORE ======== -->
<div class="load-more-container" id="load-more-container" style="display:none;">
    <button class="load-more-btn" id="load-more-btn">
        <span id="load-more-text">Load More Customers</span>
        <span id="load-more-spinner" class="loading-spinner" style="display:none;"></span>
    </button>
</div>

<!-- ======== ARCHIVE MODAL ======== -->
@if(in_array(Auth::user()->role, ['super_admin', 'admin']))
<div class="modal fade" id="archiveModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-archive me-2"></i>Archive Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="archiveForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to archive this customer?</p>
                    <p class="mb-0" id="archive-customer-name"></p>
                    <p class="text-muted small mt-2 mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Archived customers can be restored from the Archives page (coming soon).
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-archive me-1"></i> Archive Customer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection

<!-- ======== INLINE SCRIPT ======== -->
@push('scripts')
<script>
(function() {
    'use strict';

    // ─── State ───────────────────────────────────────────────────
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;
    let searchTerm = '';
    let currentFilters = {};
    let allCustomers = [];       // accumulated results
    let totalCount = 0;
    let debounceTimer = null;
    let userCanArchive = {{ in_array(Auth::user()->role, ['super_admin', 'admin']) ? 'true' : 'false' }};

    // ─── DOM Refs ────────────────────────────────────────────────
    const searchInput = document.getElementById('customer-search');
    const resultsContainer = document.getElementById('results-container');
    const skeletonContainer = document.getElementById('skeleton-container');
    const emptyContainer = document.getElementById('empty-state-container');
    const loadMoreBtn = document.getElementById('load-more-btn');
    const loadMoreContainer = document.getElementById('load-more-container');
    const loadMoreText = document.getElementById('load-more-text');
    const loadMoreSpinner = document.getElementById('load-more-spinner');
    const autocompleteDropdown = document.getElementById('autocomplete-dropdown');

    // Stats
    const statTotal = document.getElementById('stat-total');
    const statActive = document.getElementById('stat-active');
    const statServices = document.getElementById('stat-services');
    const statUnpaid = document.getElementById('stat-unpaid');

    // Filters
    const advancedFilterToggle = document.getElementById('advanced-filter-toggle');
    const advancedPanel = document.getElementById('advanced-filters-panel');
    const filterBrand = document.getElementById('filter-brand');
    const filterModel = document.getElementById('filter-model');
    const filterLocation = document.getElementById('filter-location');
    const filterLastService = document.getElementById('filter-last-service');
    const applyFiltersBtn = document.getElementById('apply-filters-btn');
    const clearFiltersBtn = document.getElementById('clear-filters-btn');
    const quickFilterTabs = document.querySelectorAll('.filter-tab');

    // ─── Helpers ────────────────────────────────────────────────
    function getInitials(first, last) {
        return (first?.[0] || '') + (last?.[0] || '');
    }

    function getAvatarColors(name) {
        const colors = [
            ['#4f46e5','#7c3aed'], ['#059669','#34d399'], ['#d97706','#f59e0b'],
            ['#dc2626','#f87171'], ['#2563eb','#60a5fa'], ['#7c3aed','#a78bfa'],
            ['#0891b2','#22d3ee'], ['#be185d','#f472b6'], ['#65a30d','#a3e635'],
            ['#c026d3','#d946ef']
        ];
        let hash = 0;
        for (let i = 0; i < name.length; i++) {
            hash = name.charCodeAt(i) + ((hash << 5) - hash);
        }
        return colors[Math.abs(hash) % colors.length];
    }

    function formatDate(dateStr) {
        if (!dateStr) return '—';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function daysAgo(dateStr) {
        if (!dateStr) return null;
        const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 86400000);
        if (diff === 0) return 'Today';
        if (diff === 1) return 'Yesterday';
        if (diff < 7) return diff + 'd ago';
        if (diff < 30) return Math.floor(diff / 7) + 'w ago';
        return formatDate(dateStr);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function highlightMatch(text, term) {
        if (!text || !term) return escapeHtml(text);
        const escaped = escapeHtml(String(text));
        const terms = term.trim().split(/\s+/).filter(t => t.length > 0);
        let result = escaped;
        terms.forEach(t => {
            const re = new RegExp('(' + t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
            result = result.replace(re, '<mark style="background:#fef08a;padding:0 2px;border-radius:2px;">$1</mark>');
        });
        return result;
    }

    // ─── Skeleton UI ────────────────────────────────────────────
    function renderSkeletons(count = 5) {
        let html = '';
        for (let i = 0; i < count; i++) {
            html += `
                <div class="customer-result-card">
                    <div class="d-flex align-items-center gap-3">
                        <div class="customer-skeleton" style="width:44px;height:44px;border-radius:50%;"></div>
                        <div style="flex:1;">
                            <div class="customer-skeleton" style="width:180px;height:16px;margin-bottom:8px;"></div>
                            <div class="customer-skeleton" style="width:120px;height:12px;"></div>
                        </div>
                        <div class="customer-skeleton" style="width:100px;height:30px;border-radius:6px;"></div>
                    </div>
                </div>`;
        }
        skeletonContainer.innerHTML = html;
    }

    function hideSkeletons() {
        skeletonContainer.innerHTML = '';
    }

    // ─── Render a single customer card ──────────────────────────
    function renderCustomerCard(c, term) {
        const initials = getInitials(c.first_name, c.last_name);
        const colors = getAvatarColors(c.full_name);
        const lastVisit = daysAgo(c.last_service_date);
        const plate = c.vehicles?.[0]?.license_plate;
        const primaryVehicle = c.vehicles?.[0];
        const vehicleDisplay = primaryVehicle
            ? `${primaryVehicle.year} ${primaryVehicle.make} ${primaryVehicle.model}`
            : 'No vehicle';

        // Status badge
        let statusHtml = '';
        if (c.has_unpaid) {
            statusHtml = '<span class="status-badge status-unpaid"><i class="fas fa-exclamation-circle"></i> Unpaid</span>';
        } else if (c.is_active) {
            statusHtml = '<span class="status-badge status-active"><i class="fas fa-check-circle"></i> Active</span>';
        } else {
            statusHtml = '<span class="status-badge status-inactive"><i class="fas fa-minus-circle"></i> Inactive</span>';
        }

        // Vehicle badges
        let vehicleBadges = '';
        if (c.vehicles && c.vehicles.length > 0) {
            c.vehicles.forEach(v => {
                if (v.make) {
                    vehicleBadges += `<span class="vehicle-badge"><i class="fas fa-car"></i> ${escapeHtml(v.make)} ${escapeHtml(v.model || '')}</span>`;
                }
            });
        }

        // Plate highlighted
        let plateHtml = '';
        if (plate) {
            plateHtml = `<span class="plate-highlight">${highlightMatch(plate, term)}</span>`;
        }

        // Archive button (admin/super_admin only, prevent card click)
        let archiveBtnHtml = '';
        if (userCanArchive) {
            archiveBtnHtml = `<button class="btn btn-sm btn-outline-danger archive-btn" onclick="event.stopPropagation(); openArchiveCustomer('${c.id}', '${escapeHtml(c.full_name)}')" title="Archive customer"><i class="fas fa-archive"></i></button>`;
        }

        return `
            <div class="customer-result-card" onclick="window.location.href='${c.show_url}'">
                <div class="d-flex align-items-start gap-3">
                    <!-- Avatar -->
                    <div class="customer-avatar-circle" style="background:linear-gradient(135deg, ${colors[0]}, ${colors[1]});">
                        ${c.has_profile_picture ? `<img src="${escapeHtml(c.avatar)}" alt="">` : initials}
                    </div>

                    <!-- Main info -->
                    <div style="flex:1;min-width:0;">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                            <a href="${c.show_url}" class="customer-name-link">
                                ${highlightMatch(c.full_name, term)}
                            </a>
                            ${statusHtml}
                        </div>
                        <div class="customer-info-grid" style="display:flex;flex-wrap:wrap;gap:4px 16px;font-size:13px;">
                            <span><i class="fas fa-phone text-muted me-1" style="width:14px;"></i> ${c.phone ? escapeHtml(c.phone) : '—'}</span>
                            <span><i class="fas fa-envelope text-muted me-1" style="width:14px;"></i> ${c.email ? escapeHtml(c.email) : '—'}</span>
                            ${c.city ? `<span><i class="fas fa-map-marker-alt text-muted me-1" style="width:14px;"></i> ${escapeHtml(c.city)}</span>` : ''}
                        </div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                            ${vehicleBadges}
                            ${plateHtml}
                        </div>
                    </div>

                    <!-- Right side stats -->
                    <div style="text-align:right;flex-shrink:0;">
                        ${archiveBtnHtml}
                        <div style="font-size:13px;font-weight:600;">${c.service_records_count} visits</div>
                        <div class="${c.has_unpaid ? 'price-unpaid' : 'price-highlight'}">
                            ₱${c.total_spent.toLocaleString('en-PH', {minimumFractionDigits: 2})}
                        </div>
                        ${lastVisit ? `<div class="last-visit-badge mt-1"><i class="far fa-clock"></i> ${lastVisit}</div>` : '<div class="last-visit-badge mt-1" style="opacity:0.5;">No visits</div>'}
                    </div>
                </div>
            </div>
        `;
    }

    // ─── Render all accumulated customers ──────────────────────
    function renderResults(term) {
        if (allCustomers.length === 0) {
            resultsContainer.innerHTML = '';
            emptyContainer.style.display = 'block';
            loadMoreContainer.style.display = 'none';
            renderEmptyState(term);
            return;
        }

        emptyContainer.innerHTML = '';
        emptyContainer.style.display = 'none';

        let html = '';

        // Search meta
        html += `<div class="search-meta"><strong>${totalCount}</strong> customer${totalCount !== 1 ? 's' : ''} found${term ? ' for <strong>"' + escapeHtml(term) + '"</strong>' : ''}</div>`;

        // Results
        allCustomers.forEach(c => {
            html += renderCustomerCard(c, term);
        });

        resultsContainer.innerHTML = html;

        // Load more
        if (hasMore) {
            loadMoreContainer.style.display = 'block';
        } else {
            loadMoreContainer.style.display = 'none';
        }

        // Stats
        statTotal.textContent = totalCount;

        const activeCount = allCustomers.filter(c => c.is_active).length;
        const unpaidCount = allCustomers.filter(c => c.has_unpaid).length;
        const totalServices = allCustomers.reduce((sum, c) => sum + (c.service_records_count || 0), 0);

        statActive.textContent = activeCount;
        statServices.textContent = totalServices;
        statUnpaid.textContent = unpaidCount;
    }

    // ─── Empty State ────────────────────────────────────────────
    function renderEmptyState(term) {
        if (term) {
            emptyContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-search"></i></div>
                    <h5>No customers found for "${escapeHtml(term)}"</h5>
                    <p>Try a different search term — check the name, plate number, vehicle brand, or location</p>
                    <div class="search-suggestions">
                        <span class="search-suggestion-chip" onclick="document.getElementById('customer-search').value='Toyota';triggerSearch()">Toyota</span>
                        <span class="search-suggestion-chip" onclick="document.getElementById('customer-search').value='Vios';triggerSearch()">Vios</span>
                        <span class="search-suggestion-chip" onclick="document.getElementById('customer-search').value='Quezon';triggerSearch()">Quezon City</span>
                        <span class="search-suggestion-chip" onclick="document.getElementById('customer-search').value='Active';triggerSearch()">Active</span>
                        <span class="search-suggestion-chip" onclick="document.getElementById('customer-search').value='';triggerSearch()">Clear Search</span>
                    </div>
                </div>`;
        } else {
            emptyContainer.innerHTML = `
                <div class="empty-state">
                    <div class="empty-icon"><i class="fas fa-users"></i></div>
                    <h5>No customers yet</h5>
                    <p>Add your first customer to start building your database</p>
                </div>`;
        }
    }

    // ─── Fetch customers API ────────────────────────────────────
    async function fetchCustomers(append = false) {
        if (isLoading) return;
        isLoading = true;

        if (!append) {
            // Fresh load — show skeletons
            renderSkeletons();
            resultsContainer.innerHTML = '';
            allCustomers = [];
            currentPage = 1;
        } else {
            loadMoreText.textContent = 'Loading...';
            loadMoreSpinner.style.display = 'inline-block';
            loadMoreBtn.disabled = true;
        }

        const params = new URLSearchParams();
        params.set('q', searchTerm);
        params.set('page', currentPage);

        if (Object.keys(currentFilters).length > 0) {
            params.set('filters', JSON.stringify(currentFilters));
        }

        try {
            const res = await fetch('{{ route("api.customers.search") }}?' + params.toString());
            const data = await res.json();

            hideSkeletons();

            if (append) {
                allCustomers = allCustomers.concat(data.customers);
            } else {
                allCustomers = data.customers;
            }

            hasMore = data.has_more;
            totalCount = data.total;

            renderResults(searchTerm);

            if (hasMore) {
                currentPage++;
            }

        } catch (err) {
            hideSkeletons();
            resultsContainer.innerHTML = `<div class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Failed to load customers. Please try again.</div>`;
        } finally {
            isLoading = false;
            loadMoreText.textContent = 'Load More Customers';
            loadMoreSpinner.style.display = 'none';
            loadMoreBtn.disabled = false;
        }
    }

    // ─── Load More ──────────────────────────────────────────────
    loadMoreBtn.addEventListener('click', function() {
        fetchCustomers(true);
    });

    // ─── Autocomplete ───────────────────────────────────────────
    async function fetchAutocomplete(term) {
        if (term.length < 1) {
            autocompleteDropdown.classList.remove('show');
            return;
        }
        try {
            const res = await fetch('{{ route("api.customers.autocomplete") }}?q=' + encodeURIComponent(term));
            const data = await res.json();
            if (data.length === 0) {
                autocompleteDropdown.classList.remove('show');
                return;
            }
            let html = '';
            data.forEach(item => {
                const icons = {name:'fa-user', plate:'fa-car', model:'fa-car-side', location:'fa-map-marker-alt'};
                html += `<div class="autocomplete-item" data-value="${escapeHtml(item.value)}" data-type="${item.type}">
                    <i class="fas ${icons[item.type] || 'fa-search'}" style="color:#94a3b8;width:16px;"></i>
                    <span class="value">${escapeHtml(item.value)}</span>
                    <span class="autocomplete-type-badge">${item.label}</span>
                </div>`;
            });
            autocompleteDropdown.innerHTML = html;
            autocompleteDropdown.classList.add('show');

            // Click handler
            autocompleteDropdown.querySelectorAll('.autocomplete-item').forEach(el => {
                el.addEventListener('click', function() {
                    searchInput.value = this.dataset.value;
                    autocompleteDropdown.classList.remove('show');
                    triggerSearch();
                });
            });
        } catch(e) {
            autocompleteDropdown.classList.remove('show');
        }
    }

    // ─── Search Trigger ─────────────────────────────────────────
    function triggerSearch() {
        searchTerm = searchInput.value.trim();
        currentPage = 1;
        fetchCustomers(false);
    }

    // ─── Debounced Input ────────────────────────────────────────
    searchInput.addEventListener('input', function() {
        const val = this.value.trim();
        
        // Autocomplete
        if (val.length >= 1) {
            fetchAutocomplete(val);
        } else {
            autocompleteDropdown.classList.remove('show');
        }

        // Debounced search
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() {
            triggerSearch();
        }, 300);
    });

    // Close autocomplete on blur (with delay for click)
    searchInput.addEventListener('blur', function() {
        setTimeout(function() {
            autocompleteDropdown.classList.remove('show');
        }, 200);
    });

    // Keyboard: Enter triggers search + close autocomplete
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            autocompleteDropdown.classList.remove('show');
            triggerSearch();
        }
        if (e.key === 'Escape') {
            autocompleteDropdown.classList.remove('show');
        }
    });

    // ─── Quick Filter Tabs ─────────────────────────────────────
    quickFilterTabs.forEach(tab => {
        tab.addEventListener('click', function() {
            quickFilterTabs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');

            const filter = this.dataset.filter;
            currentFilters = {};

            switch(filter) {
                case 'active':
                    currentFilters.is_active = true;
                    break;
                case 'inactive':
                    currentFilters.is_active = false;
                    break;
                case 'unpaid':
                    currentFilters.unpaid_balance = true;
                    break;
                case 'frequent':
                    currentFilters.frequent = true;
                    break;
                case 'new':
                    currentFilters.new_customers = true;
                    break;
                case 'recent-service':
                    currentFilters.last_service_days = '30';
                    break;
                case 'all':
                default:
                    break;
            }

            triggerSearch();
        });
    });

    // ─── Advanced Filters Panel Toggle ─────────────────────────
    advancedFilterToggle.addEventListener('click', function() {
        advancedPanel.classList.toggle('show');
    });

    // ─── Populate Filter Selects ───────────────────────────────
    async function loadFilterOptions() {
        try {
            const res = await fetch('{{ route("api.customers.filter-options") }}');
            const data = await res.json();

            // Brands
            filterBrand.innerHTML = '<option value="">All Brands</option>' +
                data.brands.map(b => `<option value="${escapeHtml(b.value)}">${escapeHtml(b.label)}</option>`).join('');

            // Models
            filterModel.innerHTML = '<option value="">All Models</option>' +
                data.models.map(m => `<option value="${escapeHtml(m.value)}">${escapeHtml(m.label)}</option>`).join('');

            // Locations
            filterLocation.innerHTML = '<option value="">All Locations</option>' +
                data.locations.map(l => `<option value="${escapeHtml(l.value)}">${escapeHtml(l.label)}</option>`).join('');

        } catch(e) {
            console.log('Failed to load filter options');
        }
    }
    loadFilterOptions();

    // ─── Apply Filters Button ──────────────────────────────────
    applyFiltersBtn.addEventListener('click', function() {
        currentFilters = {};
        if (filterBrand.value) currentFilters.brand = filterBrand.value;
        if (filterModel.value) currentFilters.model = filterModel.value;
        if (filterLocation.value) currentFilters.location = filterLocation.value;
        if (filterLastService.value) currentFilters.last_service_days = filterLastService.value;

        // Reset quick tabs
        quickFilterTabs.forEach(t => t.classList.remove('active'));
        document.querySelector('.filter-tab[data-filter="all"]')?.classList.add('active');

        triggerSearch();
    });

    // ─── Clear Filters Button ──────────────────────────────────
    clearFiltersBtn.addEventListener('click', function() {
        filterBrand.value = '';
        filterModel.value = '';
        filterLocation.value = '';
        filterLastService.value = '';
        currentFilters = {};

        quickFilterTabs.forEach(t => t.classList.remove('active'));
        document.querySelector('.filter-tab[data-filter="all"]')?.classList.add('active');

        searchInput.value = '';
        searchTerm = '';
        triggerSearch();
    });

    // ─── Intersection Observer for infinite scroll ─────────────
    let observer = null;
    function setupInfiniteScroll() {
        if (observer) observer.disconnect();
        
        const sentinel = document.createElement('div');
        sentinel.id = 'scroll-sentinel';
        sentinel.style.height = '1px';
        loadMoreContainer.parentNode.insertBefore(sentinel, loadMoreContainer);

        observer = new IntersectionObserver(function(entries) {
            if (entries[0].isIntersecting && hasMore && !isLoading) {
                fetchCustomers(true);
            }
        }, { rootMargin: '200px' });

        observer.observe(sentinel);
    }

    // ─── Init ───────────────────────────────────────────────────
    // Start with fresh load after a tiny delay to let the page render
    setTimeout(function() {
        fetchCustomers(false);
        setupInfiniteScroll();
    }, 100);

    // Expose triggerSearch globally for suggestion chips
    window.triggerSearch = triggerSearch;

    // ─── Archive Functions ──────────────────────────────────────
    window.openArchiveCustomer = function(id, name) {
        if (!userCanArchive) return;
        document.getElementById('archive-customer-name').textContent = 'Customer: ' + name;
        document.getElementById('archiveForm').action = '/customers/' + id + '/archive';
        var modal = new bootstrap.Modal(document.getElementById('archiveModal'));
        modal.show();
    };

    window.showArchiveModal = function() {
        // Navigate to archives page
        window.location.href = '/archives';
    };

})();
</script>
@endpush
