{{--
==========================================================
 MODERN REDESIGN CSS — Fix-It Auto System v2.0
 Module Color System + Shared Layout Improvements
==========================================================
--}}
<style>
/* ============================================================
   MODULE COLOR SYSTEM
   ============================================================ */
:root {
    /* Module Colors */
    --module-inspections: #0ea5e9;
    --module-inspections-dark: #0284c7;
    --module-inspections-light: #e0f2fe;

    --module-estimates: #8b5cf6;
    --module-estimates-dark: #7c3aed;
    --module-estimates-light: #ede9fe;

    --module-work-orders: #f97316;
    --module-work-orders-dark: #ea580c;
    --module-work-orders-light: #fff7ed;

    --module-invoices: #10b981;
    --module-invoices-dark: #059669;
    --module-invoices-light: #ecfdf5;

    /* Global Layout */
    --page-bg: #f8fafc;
    --card-bg: #ffffff;
    --card-shadow: 0 1px 3px 0 rgba(0,0,0,0.06), 0 1px 2px -1px rgba(0,0,0,0.1);
    --card-shadow-hover: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -2px rgba(0,0,0,0.1);
    --text-primary: #0f172a;
    --text-secondary: #334155;
    --text-muted: #64748b;
    --border-color: #e2e8f0;
}

/* --- Module-specific body classes --- */

/* Inspections */
body.page-inspections {
    --module-active: var(--module-inspections);
    --module-active-dark: var(--module-inspections-dark);
    --module-active-light: var(--module-inspections-light);
}

/* Estimates */
body.page-estimates {
    --module-active: var(--module-estimates);
    --module-active-dark: var(--module-estimates-dark);
    --module-active-light: var(--module-estimates-light);
}

/* Work Orders */
body.page-work-orders {
    --module-active: var(--module-work-orders);
    --module-active-dark: var(--module-work-orders-dark);
    --module-active-light: var(--module-work-orders-light);
}

/* Invoices */
body.page-invoices {
    --module-active: var(--module-invoices);
    --module-active-dark: var(--module-invoices-dark);
    --module-active-light: var(--module-invoices-light);
}

/* ============================================================
   SIDEBAR — IMPROVED DESIGN
   ============================================================ */
.sidebar {
    background: #1e293b !important;
    min-height: calc(100vh - 56px) !important;
    position: sticky !important;
    top: 56px !important;
    height: calc(100vh - 56px) !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    z-index: 100 !important;
    border-right: 1px solid rgba(255,255,255,0.05) !important;
    scrollbar-width: thin !important;
    scrollbar-color: #475569 transparent !important;
}

.sidebar::-webkit-scrollbar {
    width: 4px !important;
}

.sidebar::-webkit-scrollbar-thumb {
    background: #475569 !important;
    border-radius: 4px !important;
}

/* Sidebar Inner Navigation */
.sidebar-inner {
    padding: 0.5rem 0 !important;
}

.sidebar .nav-link {
    color: #94a3b8 !important;
    padding: 0.625rem 1.25rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
    transition: all 0.2s ease !important;
    border-left: 3px solid transparent !important;
    margin: 1px 0 !important;
    white-space: nowrap !important;
    position: relative !important;
}

.sidebar .nav-link i {
    width: 1.25rem !important;
    text-align: center !important;
    font-size: 1rem !important;
    flex-shrink: 0 !important;
    color: #64748b !important;
    transition: color 0.2s ease !important;
}

.sidebar .nav-link:hover {
    color: #e2e8f0 !important;
    background: rgba(255,255,255,0.05) !important;
    border-left-color: rgba(255,255,255,0.2) !important;
}

.sidebar .nav-link:hover i {
    color: #94a3b8 !important;
}

.sidebar .nav-link.active {
    color: #ffffff !important;
    background: linear-gradient(90deg, rgba(255,255,255,0.08) 0%, transparent 100%) !important;
    border-left-color: var(--module-active, #0ea5e9) !important;
}

.sidebar .nav-link.active i {
    color: var(--module-active, #0ea5e9) !important;
}

/* Section toggle - collapsible parent */
.sidebar .nav-link.section-toggle {
    font-weight: 600 !important;
    text-transform: uppercase !important;
    font-size: 0.75rem !important;
    letter-spacing: 0.5px !important;
    color: #64748b !important;
    padding-top: 1rem !important;
    padding-bottom: 0.5rem !important;
    cursor: pointer !important;
}

.sidebar .nav-link.section-toggle:hover {
    color: #94a3b8 !important;
    background: transparent !important;
}

.sidebar .nav-chevron {
    margin-left: auto !important;
    font-size: 0.65rem !important;
    transition: transform 0.3s ease !important;
    opacity: 0.5 !important;
}

/* Sub-navigation (nested items) */
.sidebar .nav-sub .nav-link {
    padding-left: 2.75rem !important;
    font-size: 0.825rem !important;
    font-weight: 400 !important;
}

.sidebar .nav-sub .nav-link i {
    font-size: 0.75rem !important;
    width: 1rem !important;
}

/* Badge inside sidebar */
.sidebar .badge-sm {
    font-size: 0.6rem !important;
    padding: 0.125rem 0.4rem !important;
    font-weight: 600 !important;
    border-radius: 999px !important;
}

.bg-emerald {
    background-color: #10b981 !important;
}

/* Quotation counter badge */
.quotation-counter {
    position: absolute !important;
    top: 6px !important;
    right: 8px !important;
    font-size: 0.55rem !important;
    padding: 0.125rem 0.3rem !important;
    min-width: 16px !important;
    height: 16px !important;
    line-height: 1.2 !important;
    border-radius: 8px !important;
    border: 1.5px solid #1e293b !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3) !important;
    z-index: 100 !important;
    background-color: #dc3545 !important;
    color: white !important;
    font-weight: bold !important;
}

/* Sidebar total count badge (gray/blue) */
.sidebar-badge {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.6rem !important;
    font-weight: 700 !important;
    padding: 0.1rem 0.35rem !important;
    min-width: 18px !important;
    height: 16px !important;
    line-height: 1 !important;
    border-radius: 8px !important;
    background: rgba(148, 163, 184, 0.15) !important;
    color: #94a3b8 !important;
    margin-left: auto !important;
    flex-shrink: 0 !important;
}

/* Sidebar red notification badge for new/unseen items */
.sidebar-badge-danger {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-size: 0.55rem !important;
    font-weight: 700 !important;
    padding: 0.1rem 0.3rem !important;
    min-width: 16px !important;
    height: 14px !important;
    line-height: 1 !important;
    border-radius: 7px !important;
    background: #dc3545 !important;
    color: #fff !important;
    margin-left: 4px !important;
    flex-shrink: 0 !important;
    border: 1px solid rgba(255,255,255,0.1) !important;
    box-shadow: 0 1px 3px rgba(220,53,69,0.4) !important;
    animation: sidebar-pulse 2s infinite !important;
}

@keyframes sidebar-pulse {
    0%, 100% { box-shadow: 0 1px 3px rgba(220,53,69,0.4); }
    50% { box-shadow: 0 1px 6px rgba(220,53,69,0.7); }
}

/* Wrapper for nav label + badge flex layout */
.sidebar .nav-link {
    display: flex !important;
    align-items: center !important;
    gap: 0.75rem !important;
}

/* Sidebar Footer (Quick Stats) */
.sidebar-footer {
    padding: 1rem 1.25rem !important;
    border-top: 1px solid rgba(255,255,255,0.06) !important;
    margin-top: 1rem !important;
}

.sidebar-footer .quick-stats-label {
    font-size: 0.6rem !important;
    font-weight: 700 !important;
    letter-spacing: 1px !important;
    color: #475569 !important;
    text-transform: uppercase !important;
}

.sidebar-footer .quick-stats-list {
    margin-top: 0.75rem !important;
}

.sidebar-footer .quick-stat-item {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    padding: 0.25rem 0 !important;
}

.sidebar-footer .quick-stat-item .stat-label {
    font-size: 0.75rem !important;
    color: #64748b !important;
}

.sidebar-footer .quick-stat-item .stat-value {
    font-size: 0.8125rem !important;
    font-weight: 700 !important;
    color: #94a3b8 !important;
}

/* ============================================================
   PAGE LAYOUT — Clean Professional
   ============================================================ */
body {
    background-color: var(--page-bg) !important;
    font-family: system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif !important;
    color: var(--text-secondary) !important;
}

.module-page {
    padding: 1.5rem !important;
}

/* --- Page Header --- */
.module-header {
    display: flex !important;
    align-items: flex-start !important;
    justify-content: space-between !important;
    margin-bottom: 1.5rem !important;
    flex-wrap: wrap !important;
    gap: 1rem !important;
}

.module-header .header-left {
    flex: 1 !important;
    min-width: 200px !important;
}

.module-header .header-left h1 {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    color: var(--text-primary) !important;
    margin: 0 0 0.25rem 0 !important;
    letter-spacing: -0.025em !important;
}

.module-header .header-left .breadcrumb-bar {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    font-size: 0.8125rem !important;
    color: var(--text-muted) !important;
}

.module-header .header-left .breadcrumb-bar a {
    color: var(--module-active, var(--text-muted)) !important;
    text-decoration: none !important;
}

.module-header .header-left .breadcrumb-bar a:hover {
    text-decoration: underline !important;
}

.module-header .header-left .breadcrumb-bar .separator {
    color: #cbd5e1 !important;
}

.module-header .action-bar {
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    flex-shrink: 0 !important;
}

/* --- Sticky Action Bar --- */
.sticky-action-bar {
    position: sticky !important;
    top: 56px !important;
    z-index: 99 !important;
    background: rgba(255,255,255,0.9) !important;
    backdrop-filter: blur(8px) !important;
    -webkit-backdrop-filter: blur(8px) !important;
    padding: 0.75rem 0 !important;
    margin-bottom: 1.5rem !important;
    border-bottom: 1px solid var(--border-color) !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
}

.sticky-action-bar .sticky-title {
    font-weight: 600 !important;
    color: var(--text-primary) !important;
    display: flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
}

.sticky-action-bar .sticky-title i {
    color: var(--module-active, #0ea5e9) !important;
}

/* ============================================================
   CARD COMPONENTS
   ============================================================ */
.card {
    background: var(--card-bg) !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 0.75rem !important;
    box-shadow: var(--card-shadow) !important;
    transition: box-shadow 0.2s ease, transform 0.2s ease !important;
    overflow: hidden !important;
}

.card:hover {
    box-shadow: var(--card-shadow-hover) !important;
}

.card-header {
    background: transparent !important;
    border-bottom: 1px solid var(--border-color) !important;
    padding: 1rem 1.25rem !important;
    font-weight: 600 !important;
}

.card-body {
    padding: 1.25rem !important;
}

/* --- Module Stat Cards --- */
.module-stat-card {
    border-radius: 0.75rem !important;
    padding: 1.25rem !important;
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    background: var(--card-bg) !important;
    border: 1px solid var(--border-color) !important;
    box-shadow: var(--card-shadow) !important;
    transition: all 0.2s ease !important;
    overflow: hidden !important;
    position: relative !important;
}

.module-stat-card::before {
    content: '' !important;
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 4px !important;
    height: 100% !important;
    border-radius: 0.75rem 0 0 0.75rem !important;
}

.module-stat-card:hover {
    transform: translateY(-2px) !important;
    box-shadow: var(--card-shadow-hover) !important;
}

.module-stat-card .stat-info h3 {
    font-size: 1.5rem !important;
    font-weight: 700 !important;
    margin: 0 0 0.25rem 0 !important;
    color: var(--text-primary) !important;
}

.module-stat-card .stat-info p {
    font-size: 0.8125rem !important;
    margin: 0 !important;
    color: var(--text-muted) !important;
}

.module-stat-card .stat-icon {
    font-size: 1.75rem !important;
    opacity: 0.15 !important;
}

/* Stat card module color variants */
.stat-inspections::before { background: var(--module-inspections) !important; }
.stat-inspections .stat-info h3 { color: var(--module-inspections-dark) !important; }
.stat-estimates::before { background: var(--module-estimates) !important; }
.stat-estimates .stat-info h3 { color: var(--module-estimates-dark) !important; }
.stat-work-orders::before { background: var(--module-work-orders) !important; }
.stat-work-orders .stat-info h3 { color: var(--module-work-orders-dark) !important; }
.stat-invoices::before { background: var(--module-invoices) !important; }
.stat-invoices .stat-info h3 { color: var(--module-invoices-dark) !important; }

/* ============================================================
   STATUS BADGES — Professional Pills
   ============================================================ */
.status-badge {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.375rem !important;
    padding: 0.25rem 0.75rem !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    border-radius: 999px !important;
    white-space: nowrap !important;
    line-height: 1.25rem !important;
}

.status-badge i {
    font-size: 0.625rem !important;
}

/* Status colors */
.status-draft { background: #f1f5f9 !important; color: #475569 !important; }
.status-pending { background: #fef3c7 !important; color: #b45309 !important; }
.status-pending i { color: #d97706 !important; }
.status-in-progress { background: #dbeafe !important; color: #1d4ed8 !important; }
.status-in-progress i { color: #2563eb !important; }
.status-repairing { background: #fff7ed !important; color: #c2410c !important; }
.status-repairing i { color: #ea580c !important; }
.status-completed { background: #d1fae5 !important; color: #065f46 !important; }
.status-completed i { color: #059669 !important; }
.status-approved { background: #d1fae5 !important; color: #065f46 !important; }
.status-approved i { color: #059669 !important; }
.status-approved, .stat-paid { background: #d1fae5 !important; color: #065f46 !important; }
.status-sent { background: #dbeafe !important; color: #1d4ed8 !important; }
.status-partial { background: #fef3c7 !important; color: #92400e !important; }
.status-paid { background: #d1fae5 !important; color: #065f46 !important; }
.status-overdue { background: #fee2e2 !important; color: #991b1b !important; }
.status-cancelled { background: #f1f5f9 !important; color: #475569 !important; }
.status-rejected { background: #fee2e2 !important; color: #991b1b !important; }
.status-expired { background: #f1f5f9 !important; color: #334155 !important; }
.status-released { background: #d1fae5 !important; color: #065f46 !important; }
.status-waiting-parts { background: #fef3c7 !important; color: #92400e !important; }
.status-archived { background: #f1f5f9 !important; color: #475569 !important; }

/* ============================================================
   TABLES
   ============================================================ */
.modern-table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}

.modern-table thead th {
    background: #f8fafc !important;
    color: var(--text-secondary) !important;
    font-size: 0.75rem !important;
    font-weight: 600 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    padding: 0.75rem 1rem !important;
    border-bottom: 2px solid var(--border-color) !important;
    white-space: nowrap !important;
}

.modern-table thead th i {
    margin-right: 0.375rem !important;
    opacity: 0.7 !important;
}

.modern-table tbody tr {
    transition: background 0.15s ease !important;
}

.modern-table tbody tr:hover {
    background: #f1f5f9 !important;
}

.modern-table tbody td {
    padding: 0.875rem 1rem !important;
    border-bottom: 1px solid #f1f5f9 !important;
    vertical-align: middle !important;
    font-size: 0.875rem !important;
}

.modern-table tbody tr:last-child td {
    border-bottom: none !important;
}

/* Table action buttons */
.table-actions {
    display: flex !important;
    align-items: center !important;
    gap: 0.25rem !important;
}

.action-btn {
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    width: 32px !important;
    height: 32px !important;
    border-radius: 0.5rem !important;
    border: 1px solid var(--border-color) !important;
    background: transparent !important;
    color: var(--text-muted) !important;
    font-size: 0.8125rem !important;
    cursor: pointer !important;
    transition: all 0.15s ease !important;
    text-decoration: none !important;
    padding: 0 !important;
}

.action-btn:hover {
    background: #f1f5f9 !important;
    color: var(--text-primary) !important;
    border-color: #cbd5e1 !important;
}

.action-btn.btn-view:hover { color: var(--module-active, #0ea5e9) !important; border-color: var(--module-active, #0ea5e9) !important; }
.action-btn.btn-edit:hover { color: #f59e0b !important; border-color: #f59e0b !important; }
.action-btn.btn-delete:hover { color: #ef4444 !important; border-color: #ef4444 !important; }

/* Module-colored table header */
.module-inspections .modern-table thead th { color: var(--module-inspections-dark) !important; border-bottom-color: var(--module-inspections) !important; }
.module-estimates .modern-table thead th { color: var(--module-estimates-dark) !important; border-bottom-color: var(--module-estimates) !important; }
.module-work-orders .modern-table thead th { color: var(--module-work-orders-dark) !important; border-bottom-color: var(--module-work-orders) !important; }
.module-invoices .modern-table thead th { color: var(--module-invoices-dark) !important; border-bottom-color: var(--module-invoices) !important; }

/* ============================================================
   FILTER / SEARCH AREA
   ============================================================ */
.filter-bar {
    display: flex !important;
    align-items: flex-end !important;
    gap: 0.75rem !important;
    flex-wrap: wrap !important;
    padding: 1rem 1.25rem !important;
}

.filter-bar .filter-group {
    display: flex !important;
    flex-direction: column !important;
    gap: 0.25rem !important;
}

.filter-bar .filter-group label {
    font-size: 0.6875rem !important;
    font-weight: 600 !important;
    color: var(--text-muted) !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
}

.filter-bar .form-control,
.filter-bar .form-select {
    font-size: 0.8125rem !important;
    padding: 0.5rem 0.75rem !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 0.5rem !important;
    background: #fff !important;
    min-height: 38px !important;
}

.filter-bar .form-control:focus,
.filter-bar .form-select:focus {
    border-color: var(--module-active, #0ea5e9) !important;
    box-shadow: 0 0 0 3px rgba(var(--module-active-rgb, 14, 165, 233), 0.1) !important;
    outline: none !important;
}

.filter-bar .btn-filter {
    font-size: 0.8125rem !important;
    padding: 0.5rem 1rem !important;
    border-radius: 0.5rem !important;
    font-weight: 500 !important;
    min-height: 38px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.375rem !important;
    border: 1px solid transparent !important;
    transition: all 0.15s ease !important;
}

.btn-module {
    background: var(--module-active, #0ea5e9) !important;
    color: white !important;
}

.btn-module:hover {
    filter: brightness(1.1) !important;
    color: white !important;
}

.btn-module-outline {
    background: transparent !important;
    color: var(--module-active, #0ea5e9) !important;
    border-color: var(--module-active, #0ea5e9) !important;
}

.btn-module-outline:hover {
    background: var(--module-active, #0ea5e9) !important;
    color: white !important;
}

/* ============================================================
   EMPTY STATE
   ============================================================ */
.empty-state {
    text-align: center !important;
    padding: 4rem 2rem !important;
}

.empty-state .empty-icon {
    font-size: 3.5rem !important;
    color: var(--border-color) !important;
    margin-bottom: 1rem !important;
}

.empty-state h4 {
    font-size: 1.125rem !important;
    font-weight: 600 !important;
    color: var(--text-primary) !important;
    margin-bottom: 0.5rem !important;
}

.empty-state p {
    font-size: 0.875rem !important;
    color: var(--text-muted) !important;
    margin-bottom: 1.5rem !important;
}

/* ============================================================
   FORMS
   ============================================================ */
.form-group {
    margin-bottom: 1rem !important;
}

.form-label {
    font-size: 0.8125rem !important;
    font-weight: 500 !important;
    color: var(--text-secondary) !important;
    margin-bottom: 0.375rem !important;
}

.form-control, .form-select {
    font-size: 0.875rem !important;
    padding: 0.5rem 0.75rem !important;
    border: 1px solid var(--border-color) !important;
    border-radius: 0.5rem !important;
    transition: border-color 0.15s ease !important;
}

.form-control:focus, .form-select:focus {
    border-color: var(--module-active, #0ea5e9) !important;
    box-shadow: 0 0 0 3px rgba(var(--module-active-rgb, 14, 165, 233), 0.1) !important;
}

/* ============================================================
   PAGINATION
   ============================================================ */
.pagination-modern {
    display: flex !important;
    align-items: center !important;
    justify-content: space-between !important;
    padding: 1rem 0 !important;
    flex-wrap: wrap !important;
    gap: 0.75rem !important;
}

.pagination-modern .info {
    font-size: 0.8125rem !important;
    color: var(--text-muted) !important;
}

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 767.98px) {
    .sidebar {
        position: fixed !important;
        top: 56px !important;
        left: -280px !important;
        width: 280px !important;
        transition: left 0.3s ease !important;
        z-index: 1050 !important;
        box-shadow: 0 10px 40px rgba(0,0,0,0.3) !important;
    }

    .sidebar.show {
        left: 0 !important;
    }

    .module-page {
        padding: 1rem !important;
    }

    .module-header {
        flex-direction: column !important;
    }

    .module-header .action-bar {
        width: 100% !important;
    }

    .module-header .action-bar .btn {
        flex: 1 !important;
    }

    .filter-bar {
        flex-direction: column !important;
        align-items: stretch !important;
    }

    .sticky-action-bar {
        flex-direction: column !important;
        gap: 0.5rem !important;
        align-items: flex-start !important;
    }

    .stat-cards-row {
        grid-template-columns: repeat(2, 1fr) !important;
    }
}

/* ============================================================
   UTILITIES
   ============================================================ */
.text-module {
    color: var(--module-active, #0ea5e9) !important;
}

.bg-module-light {
    background: var(--module-active-light, #e0f2fe) !important;
}

.border-module {
    border-color: var(--module-active, #0ea5e9) !important;
}

/* Module-style button */
.btn-module-primary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.5rem 1.25rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    border-radius: 0.5rem !important;
    background: var(--module-active, #0ea5e9) !important;
    color: white !important;
    border: none !important;
    text-decoration: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}

.btn-module-primary:hover {
    filter: brightness(1.1) !important;
    color: white !important;
    transform: translateY(-1px) !important;
    box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1) !important;
}

.btn-module-outline-primary {
    display: inline-flex !important;
    align-items: center !important;
    gap: 0.5rem !important;
    padding: 0.5rem 1.25rem !important;
    font-size: 0.875rem !important;
    font-weight: 500 !important;
    border-radius: 0.5rem !important;
    background: transparent !important;
    color: var(--module-active, #0ea5e9) !important;
    border: 1px solid var(--module-active, #0ea5e9) !important;
    text-decoration: none !important;
    transition: all 0.15s ease !important;
    cursor: pointer !important;
}

.btn-module-outline-primary:hover {
    background: var(--module-active, #0ea5e9) !important;
    color: white !important;
}

/* Module dropdown */
.dropdown-module {
    border-color: var(--module-active, #0ea5e9) !important;
    color: var(--module-active, #0ea5e9) !important;
}

/* Stat cards grid */
.stat-cards-row {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 1rem !important;
    margin-bottom: 1.5rem !important;
}

/* Table-responsive improvements */
.table-responsive {
    border-radius: 0.5rem !important;
}

/* ============================================================
   NEW UNIFIED MODULE COMPONENTS — page-module-header, stat-card, etc.
   ============================================================ */

/* Page Module Header */
.page-module-header {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
    padding: 1.25rem 1.5rem;
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}

.module-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: var(--module-primary-light);
    color: var(--module-primary);
    font-size: 1.25rem;
    flex-shrink: 0;
}

.module-title {
    font-size: 1.35rem !important;
    font-weight: 700 !important;
    color: #0f172a !important;
    margin: 0 !important;
    letter-spacing: -0.01em !important;
}

.module-subtitle {
    font-size: 0.85rem !important;
    color: #64748b !important;
    margin: 2px 0 0 0 !important;
}

/* Stats Row */
.stats-row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

/* Stat Card */
.stat-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    transition: box-shadow 0.15s, transform 0.15s;
    border-left: 4px solid var(--module-primary);
}
.stat-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}
.stat-card-body {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
}
.stat-card-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 10px;
    background: var(--module-primary-light);
    color: var(--module-primary);
    font-size: 1.15rem;
    flex-shrink: 0;
}
.stat-card-info h3 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    line-height: 1.2;
}
.stat-card-info p {
    font-size: 0.75rem;
    color: #64748b;
    margin: 2px 0 0 0;
    text-transform: uppercase;
    letter-spacing: 0.03em;
    font-weight: 500;
}

/* Main Card (Table container) */
.main-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    overflow: hidden;
}
.main-card-body {
    padding: 0;
}

/* Modern Table */
.table-fixit {
    width: 100%;
    border-collapse: collapse;
}
.table-fixit thead th {
    background: #f8fafc;
    padding: 0.75rem 1rem;
    font-size: 0.72rem;
    font-weight: 600;
    color: #475569;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    border-bottom: 2px solid var(--module-primary);
    white-space: nowrap;
}
.table-fixit tbody td {
    padding: 0.85rem 1rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
    font-size: 0.875rem;
}
.table-fixit tbody tr:last-child td {
    border-bottom: none;
}
.table-fixit tbody tr:hover {
    background: #f8fafc;
}

/* Action Buttons */
.action-group {
    display: flex;
    align-items: center;
    gap: 3px;
}
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-size: 0.8rem;
    cursor: pointer;
    transition: all 0.12s;
    text-decoration: none;
    padding: 0;
}
.btn-action:hover {
    border-color: #94a3b8;
    color: #334155;
    background: #f1f5f9;
}

/* Primary Create Button */
.btn-create {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1.25rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 8px;
    background: var(--module-primary);
    color: #fff;
    border: none;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.15s;
}
.btn-create:hover {
    filter: brightness(1.1);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.12);
}

/* Secondary Action Button */
.btn-secondary-action {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    font-weight: 500;
    border-radius: 8px;
    background: #fff;
    color: #475569;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.12s;
}
.btn-secondary-action:hover {
    background: #f1f5f9;
    color: #0f172a;
    border-color: #cbd5e1;
}

/* Filter Buttons */
.btn-filter-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 7px;
    background: var(--module-primary);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: all 0.12s;
    min-height: 38px;
}
.btn-filter-primary:hover {
    filter: brightness(1.1);
}
.btn-filter-outline {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 7px;
    background: #fff;
    color: #64748b;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.12s;
    min-height: 38px;
}
.btn-filter-outline:hover {
    background: #f1f5f9;
    color: #334155;
}

/* Status Badge (second version with module color) */
.status-badge-warning { background: #fef3c7; color: #92400e; }
.status-badge-info { background: #dbeafe; color: #1d40af; }
.status-badge-success { background: #d1fae5; color: #065f46; }
.status-badge-danger { background: #fee2e2; color: #991b1b; }
.status-badge-secondary { background: #f1f5f9; color: #475569; }

/* Empty State (module version) */
.empty-state-module {
    text-align: center;
    padding: 4rem 2rem;
}
.empty-state-icon {
    font-size: 3rem;
    color: #cbd5e1;
    margin-bottom: 1rem;
}
.empty-state-module h4 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.5rem;
}
.empty-state-module p {
    font-size: 0.85rem;
    color: #94a3b8;
    margin-bottom: 1.25rem;
}

/* Pagination Info */
.pagination-info {
    font-size: 0.8rem;
    color: #64748b;
}

/* Sub-card for work orders */
.sub-card-header {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid #e2e8f0;
    font-weight: 600;
    font-size: 0.85rem;
    color: #334155;
    background: #fafafa;
}
.sub-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
}
.sub-card-body {
    padding: 0.85rem 1rem;
}
</style>
