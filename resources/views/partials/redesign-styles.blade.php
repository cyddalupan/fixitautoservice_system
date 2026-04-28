{{--
==========================================================
 MODERN REDESIGN CSS — Fix-It Auto System v2.0
 Module Color System + Shared Layout Improvements
==========================================================
--}}

<style>
/* ============================================================
   CSS VARIABLES — Module Color System
   ============================================================ */
:root {
    /* Primary Brand */
    --module-primary: #2563eb;
    --module-primary-dark: #1d4ed8;
    --module-primary-light: #dbeafe;

    /* Inspections */
    --module-inspections: #0891b2;
    --module-inspections-dark: #0e7490;
    --module-inspections-light: #cffafe;

    /* Estimates */
    --module-estimates: #2563eb;
    --module-estimates-dark: #1d4ed8;
    --module-estimates-light: #dbeafe;

    /* Work Orders */
    --module-work-orders: #9333ea;
    --module-work-orders-dark: #7e22ce;
    --module-work-orders-light: #f3e8ff;

    /* Invoices */
    --module-invoices: #ca8a04;
    --module-invoices-dark: #a16207;
    --module-invoices-light: #fef9c3;

    /* Stat card gradients */
    --stat-blue: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    --stat-green: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
    --stat-orange: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
    --stat-purple: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
    --stat-teal: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
    --stat-amber: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    --stat-red: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    --stat-indigo: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
}

/* Module Colors */
body.page-inspections {
    --module-active: var(--module-inspections);
    --module-active-dark: var(--module-inspections-dark);
    --module-active-light: var(--module-inspections-light);
}

body.page-estimates {
    --module-active: var(--module-estimates);
    --module-active-dark: var(--module-estimates-dark);
    --module-active-light: var(--module-estimates-light);
}

body.page-work-orders {
    --module-active: var(--module-work-orders);
    --module-active-dark: var(--module-work-orders-dark);
    --module-active-light: var(--module-work-orders-light);
}

body.page-invoices {
    --module-active: var(--module-invoices);
    --module-active-dark: var(--module-invoices-dark);
    --module-active-light: var(--module-invoices-light);
}

/* ============================================================
   NEW MODULE COLORS — Vehicles, Expenses, Customers
   ============================================================ */
:root {
    --module-vehicles: #dc2626;
    --module-vehicles-dark: #b91c1c;
    --module-vehicles-light: #fee2e2;
    --module-vehicles-gradient: linear-gradient(135deg, #dc2626, #b91c1c);

    --module-expenses: #059669;
    --module-expenses-dark: #047857;
    --module-expenses-light: #d1fae5;
    --module-expenses-gradient: linear-gradient(135deg, #059669, #047857);

    --module-customers: #2563eb;
    --module-customers-dark: #1d4ed8;
    --module-customers-light: #dbeafe;
    --module-customers-gradient: linear-gradient(135deg, #2563eb, #1d4ed8);
}

body.page-vehicles {
    --module-active: var(--module-vehicles);
    --module-active-dark: var(--module-vehicles-dark);
    --module-active-light: var(--module-vehicles-light);
    --module-gradient: var(--module-vehicles-gradient);
}

body.page-expenses {
    --module-active: var(--module-expenses);
    --module-active-dark: var(--module-expenses-dark);
    --module-active-light: var(--module-expenses-light);
    --module-gradient: var(--module-expenses-gradient);
}

body.page-customers {
    --module-active: var(--module-customers);
    --module-active-dark: var(--module-customers-dark);
    --module-active-light: var(--module-customers-light);
    --module-gradient: var(--module-customers-gradient);
}

/* ============================================================
   PAGE MODULE HEADER (consistent across all modules)
   ============================================================ */
.page-module-header {
    padding: 1.25rem 0 0.75rem;
    margin-bottom: 1rem;
    border-bottom: 1px solid #e2e8f0;
}

.page-module-header .module-icon {
    width: 42px;
    height: 42px;
    background: var(--module-active-light);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    color: var(--module-active);
    flex-shrink: 0;
}

/* ============================================================
   STAT CARDS (gradient variant)
   ============================================================ */
.stat-cards-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1rem;
    margin-bottom: 1.25rem;
}

.stat-card {
    border-radius: 10px;
    overflow: hidden;
}

.stat-card.gradient {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.stat-card-body {
    display: flex;
    align-items: center;
    gap: 0.85rem;
    padding: 1rem 1.15rem;
}

.stat-card-icon {
    width: 42px;
    height: 42px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.stat-card-info h3 {
    font-size: 1.35rem;
    font-weight: 700;
    margin: 0;
    line-height: 1.2;
    color: #0f172a;
}

.stat-card-info p {
    font-size: 0.75rem;
    font-weight: 500;
    margin: 0;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* ============================================================
   STAT CARD GRADIENT (light mode — used by vehicles, expenses)
   ============================================================ */
.stat-card-gradient {
    border-radius: 12px;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    transition: box-shadow 0.2s ease, transform 0.2s ease;
}

.stat-card-gradient:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    transform: translateY(-1px);
}

.stat-card-gradient h3,
.stat-card-gradient h4,
.stat-card-gradient .mb-0 {
    font-weight: 700;
    margin: 0;
    color: #0f172a;
}

.stat-card-gradient p,
.stat-card-gradient .mb-1 {
    margin: 0;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
}

.stat-card-gradient .stat-icon {
    opacity: 0.15 !important;
}

/* Gradient card variants */
.stat-gradient-blue { background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%) !important; border-color: #bfdbfe !important; }
.stat-gradient-green { background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%) !important; border-color: #bbf7d0 !important; }
.stat-gradient-orange { background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%) !important; border-color: #fed7aa !important; }
.stat-gradient-purple { background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%) !important; border-color: #e9d5ff !important; }
.stat-gradient-teal { background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%) !important; border-color: #99f6e4 !important; }
.stat-gradient-amber { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%) !important; border-color: #fde68a !important; }

/* Background helpers for stat card icons */
.bg-primary-light { background: #dbeafe; color: #2563eb; }
.bg-success-light { background: #d1fae5; color: #16a34a; }
.bg-warning-light { background: #fef3c7; color: #d97706; }
.bg-info-light { background: #cffafe; color: #0891b2; }
.bg-danger-light { background: #fee2e2; color: #dc2626; }
.bg-purple-light { background: #f3e8ff; color: #9333ea; }
.bg-pink-light { background: #fce7f3; color: #db2777; }

/* Stat card specific variants (colored left borders) */
.stat-card-total { border-left: 3px solid #2563eb; }
.stat-card-active { border-left: 3px solid #22c55e; }
.stat-card-vin { border-left: 3px solid #0891b2; }
.stat-card-engine { border-left: 3px solid #f59e0b; }
.stat-card-month { border-left: 3px solid #16a34a; }
.stat-card-pending { border-left: 3px solid #f59e0b; }
.stat-card-records { border-left: 3px solid #6366f1; }
.stat-card-overdue { border-left: 3px solid #ef4444; }
.stat-card-completed { border-left: 3px solid #22c55e; }
.stat-card-wip { border-left: 3px solid #f97316; }
.stat-card-draft { border-left: 3px solid #94a3b8; }

/* ============================================================
   FILTER BAR
   ============================================================ */
.filter-bar {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 0.75rem 1rem;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.filter-search {
    position: relative;
    flex: 1;
    min-width: 200px;
}

.filter-search i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.8rem;
}

.filter-search input {
    width: 100%;
    padding: 0.45rem 0.75rem 0.45rem 2rem;
    font-size: 0.8rem;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #f8fafc;
    color: #0f172a;
    min-height: 38px;
}

.filter-search input:focus {
    outline: none;
    border-color: var(--module-active);
    background: #fff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.filter-select {
    padding: 0.45rem 1.75rem 0.45rem 0.75rem;
    font-size: 0.8rem;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E") no-repeat right 8px center;
    color: #334155;
    cursor: pointer;
    appearance: none;
    min-height: 38px;
}

.filter-select:focus {
    outline: none;
    border-color: var(--module-active);
    background-color: #fff;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Filter buttons */
.btn-filter-primary {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.45rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
    border-radius: 7px;
    background: var(--module-active);
    color: #fff;
    border: none;
    cursor: pointer;
    transition: all 0.12s;
    min-height: 38px;
    text-decoration: none;
}

.btn-filter-primary:hover {
    filter: brightness(1.1);
    color: #fff;
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

.btn-sm {
    min-height: 30px;
    padding: 0.3rem 0.75rem;
    font-size: 0.75rem;
}

/* ============================================================
   FILTER TABS (quick filter pills)
   ============================================================ */
.filter-tabs {
    display: flex;
    gap: 0.35rem;
    flex-wrap: wrap;
    margin-bottom: 1rem;
}

.filter-tab {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.85rem;
    font-size: 0.78rem;
    font-weight: 500;
    border-radius: 6px;
    background: #f8fafc;
    color: #64748b;
    border: 1px solid #e2e8f0;
    text-decoration: none;
    transition: all 0.12s;
    cursor: pointer;
}

.filter-tab:hover {
    background: #f1f5f9;
    color: #334155;
    text-decoration: none;
}

.filter-tab.active {
    background: var(--module-active-light);
    color: var(--module-active);
    border-color: var(--module-active);
}

.badge-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: 0.65rem;
    font-weight: 700;
    border-radius: 9px;
    background: var(--module-active);
    color: #fff;
    margin-left: 4px;
}

.filter-tab .badge-tab {
    background: var(--module-active-light);
    color: var(--module-active);
}

.filter-tab.active .badge-tab {
    background: var(--module-active);
    color: #fff;
}

/* ============================================================
   MAIN CARD
   ============================================================ */
.main-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
}

.main-card-header {
    padding: 0.85rem 1.15rem;
    font-weight: 600;
    font-size: 0.85rem;
    color: #334155;
    border-bottom: 1px solid #e2e8f0;
    background: #fafafa;
    display: flex;
    align-items: center;
}

.main-card-body {
    padding: 0;
}

.table-fixit {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.85rem;
}

.table-fixit thead th {
    padding: 0.7rem 0.85rem;
    font-size: 0.72rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #64748b;
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
    text-align: left;
    white-space: nowrap;
}

.table-fixit tbody td {
    padding: 0.7rem 0.85rem;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}

.table-fixit tbody tr:last-child td {
    border-bottom: none;
}

.table-fixit tbody tr:hover {
    background: #f8fafc;
}

/* ============================================================
   BADGES & STATUS
   ============================================================ */
.badge-custom {
    display: inline-flex;
    align-items: center;
    padding: 0.15rem 0.55rem;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 4px;
    line-height: 1.4;
}

.badge-success { background: #d1fae5; color: #065f46; }
.badge-primary { background: #dbeafe; color: #1d40af; }
.badge-warning { background: #fef3c7; color: #92400e; }
.badge-danger { background: #fee2e2; color: #991b1b; }
.badge-secondary { background: #f1f5f9; color: #475569; }
.badge-info { background: #cffafe; color: #0e7490; }
.badge-purple { background: #f3e8ff; color: #6d28d9; }
.badge-gold { background: #fef3c7; color: #b45309; border: 1px solid #fcd34d; }
.badge-silver { background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; }

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.2rem 0.6rem;
    font-size: 0.7rem;
    font-weight: 600;
    border-radius: 4px;
    white-space: nowrap;
}

.badge-note-type {
    background: var(--module-active-light);
    color: var(--module-active);
}

/* ============================================================
   EMPTY STATE
   ============================================================ */
.empty-state-module {
    text-align: center;
    padding: 3rem 2rem;
}

.empty-state-icon {
    font-size: 2.5rem;
    color: #cbd5e1;
    margin-bottom: 0.75rem;
}

.empty-state-module h4, .empty-state-module h5 {
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 0.4rem;
}

.empty-state-module p {
    font-size: 0.85rem;
    color: #94a3b8;
    margin-bottom: 1rem;
}

/* ============================================================
   PAGINATION
   ============================================================ */
.pagination-info {
    font-size: 0.8rem;
    color: #64748b;
}

/* ============================================================
   PROFILE CARD (Customer Show Page)
   ============================================================ */
.profile-card-header {
    text-align: center;
    padding: 1.5rem 1rem 1rem;
    background: linear-gradient(180deg, var(--module-active-light) 0%, rgba(248, 250, 252, 0) 100%);
    position: relative;
}

.profile-avatar-wrapper {
    position: relative;
    display: inline-block;
    margin-bottom: 0.75rem;
}

.profile-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #fff;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.profile-avatar-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    font-weight: 700;
    background: var(--module-active);
    color: #fff;
}

.profile-avatar-upload-btn {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: var(--module-active);
    color: #fff;
    border: 2px solid #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 0.75rem;
    transition: all 0.15s;
    box-shadow: 0 2px 6px rgba(0,0,0,0.15);
}

.profile-avatar-upload-btn:hover {
    transform: scale(1.1);
    background: var(--module-active-dark);
}

/* ============================================================
   CUSTOMER DETAILS SECTIONS
   ============================================================ */
.customer-details-section {
    margin-bottom: 1rem;
    padding-bottom: 0.85rem;
    border-bottom: 1px solid #f1f5f9;
}

.customer-details-section:last-child {
    margin-bottom: 0;
    padding-bottom: 0;
    border-bottom: none;
}

.detail-label {
    font-size: 0.65rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #94a3b8;
    margin-bottom: 0.5rem;
}

.detail-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.3rem 0;
    font-size: 0.85rem;
    color: #334155;
}

.detail-icon {
    width: 16px;
    color: #94a3b8;
    font-size: 0.8rem;
    flex-shrink: 0;
    text-align: center;
}

.detail-icon-start {
    align-self: flex-start;
    margin-top: 3px;
}

/* ============================================================
   QUICK ACTIONS (Grid)
   ============================================================ */
.quick-actions-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.5rem;
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.35rem;
    padding: 0.7rem 0.5rem;
    border: 1px solid #e2e8f0;
    border-left: 3px solid #94a3b8;
    border-radius: 8px;
    background: #fff;
    text-decoration: none;
    font-size: 0.72rem;
    font-weight: 500;
    color: #475569;
    transition: all 0.12s;
}

.quick-action-btn i {
    font-size: 1.15rem;
}

.quick-action-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.04);
}

/* ============================================================
   TABS
   ============================================================ */
.tabs-nav-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #e2e8f0;
    padding-right: 0.75rem;
    background: #fafafa;
    overflow-x: auto;
}

.tabs-nav {
    display: flex;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0;
}

.tab-item {
    margin: 0;
}

.tab-link {
    display: inline-flex;
    align-items: center;
    padding: 0.7rem 1rem;
    font-size: 0.8rem;
    font-weight: 500;
    color: #64748b;
    background: none;
    border: none;
    border-bottom: 2px solid transparent;
    cursor: pointer;
    transition: all 0.12s;
    white-space: nowrap;
}

.tab-link:hover {
    color: #334155;
    background: rgba(0,0,0,0.02);
}

.tab-item.active .tab-link {
    color: var(--module-active);
    border-bottom-color: var(--module-active);
    background: transparent;
}

.tab-content {
    border: none;
}

/* ============================================================
   NOTE CARDS
   ============================================================ */
.note-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    margin-bottom: 0.75rem;
    overflow: hidden;
}

.note-card-header {
    padding: 0.5rem 0.75rem;
    background: #f8fafc;
    border-bottom: 1px solid #f1f5f9;
}

.note-card-body {
    padding: 0.65rem 0.75rem;
    font-size: 0.85rem;
    color: #334155;
    line-height: 1.5;
}

/* ============================================================
   INPUT GROUP (Edit Page)
   ============================================================ */
.input-group-fixit {
    display: flex;
    align-items: stretch;
}

.input-group-fixit .input-group-text {
    display: flex;
    align-items: center;
    padding: 0.45rem 0.75rem;
    font-size: 0.8rem;
    color: #64748b;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-right: none;
    border-radius: 7px 0 0 7px;
}

.input-group-fixit .form-control {
    border-radius: 0 7px 7px 0;
    border-left: none;
}

.input-group-fixit .form-control:focus {
    border-color: var(--module-active);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

/* Form controls consistent styling */
.main-card .form-control,
.main-card .form-select {
    font-size: 0.85rem;
    padding: 0.45rem 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 7px;
    background: #fff;
    color: #0f172a;
    min-height: 38px;
}

.main-card .form-control:focus,
.main-card .form-select:focus {
    border-color: var(--module-active);
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
}

.main-card .form-text {
    font-size: 0.75rem;
    color: #94a3b8;
    margin-top: 0.25rem;
}

/* ============================================================
   COMPREHENSIVE DARK MODE — Full System Theme
   All rules use centralized --dark-* CSS variables defined in app.blade.php
   Rich dark backgrounds (not pure black), premium feel, full Bootstrap coverage
   ============================================================ */

/* Smooth transitions when toggling themes */
html {
    transition: background-color 0.2s ease, color 0.2s ease;
}

html *,
html *::before,
html *::after {
    transition: background-color 0.15s ease, color 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
}

/* Theme toggle button */
.theme-toggle-btn {
    background: none !important;
    border: 1px solid rgba(255,255,255,0.25) !important;
    color: rgba(255,255,255,0.8) !important;
    border-radius: 50% !important;
    width: 36px !important;
    height: 36px !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    font-size: 16px !important;
    transition: all 0.3s ease !important;
    padding: 0 !important;
}

.theme-toggle-btn:hover {
    background-color: rgba(255,255,255,0.1) !important;
    color: #ffffff !important;
    border-color: rgba(255,255,255,0.5) !important;
}

[data-theme="dark"] .theme-toggle-btn {
    border-color: rgba(255,255,255,0.15) !important;
}

[data-theme="dark"] .theme-toggle-btn:hover {
    background-color: rgba(255,255,255,0.08) !important;
}

/* ============================================================
   LAYOUT & BODY
   ============================================================ */
[data-theme="dark"] {
    --dark-bg: #1a1d23;
    --dark-surface: #22262e;
    --dark-card: #2a2f38;
    --dark-text: #e4e7eb;
    --dark-text-secondary: #9ca3af;
    --dark-border: #374151;
    --dark-hover: #333842;
    --dark-input-bg: #1f232b;
    --dark-sidebar-bg: #16181e;
    --dark-navbar-bg: #16181e;
}

[data-theme="dark"] body {
    background-color: var(--dark-bg) !important;
    color: var(--dark-text) !important;
}

[data-theme="dark"] .main-content-area {
    background-color: var(--dark-bg) !important;
}

[data-theme="dark"] .navbar {
    background-color: var(--dark-navbar-bg) !important;
}

[data-theme="dark"] .sidebar {
    background-color: var(--dark-sidebar-bg) !important;
}

[data-theme="dark"] .sidebar .nav-link {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .sidebar .nav-link:hover,
[data-theme="dark"] .sidebar .nav-link.active {
    color: var(--dark-text) !important;
    background-color: var(--dark-hover) !important;
}

/* ============================================================
   PAGE MODULE HEADER
   ============================================================ */
[data-theme="dark"] .page-module-header {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .page-module-header .module-icon {
    background: rgba(255,255,255,0.08);
    color: var(--dark-text);
}

/* ============================================================
   STAT CARDS
   ============================================================ */
[data-theme="dark"] .stat-card.gradient {
    background: var(--dark-card);
    border-color: var(--dark-border);
}

[data-theme="dark"] .stat-card-info h3 {
    color: var(--dark-text);
}

[data-theme="dark"] .stat-card-info p {
    color: var(--dark-text-secondary);
}

/* Bootstrap stat cards - adjusted gradients */
[data-theme="dark"] .stat-card.bg-primary { background: linear-gradient(135deg, #1a2a4a 0%, #4a2560 100%) !important; }
[data-theme="dark"] .stat-card.bg-success { background: linear-gradient(135deg, #0a5a4e 0%, #1a6a3a 100%) !important; }
[data-theme="dark"] .stat-card.bg-warning { background: linear-gradient(135deg, #7a4a0e 0%, #8a6a00 100%) !important; }
[data-theme="dark"] .stat-card.bg-danger { background: linear-gradient(135deg, #7a2136 0%, #7a2525 100%) !important; }
[data-theme="dark"] .stat-card.bg-info { background: linear-gradient(135deg, #005a7a 0%, #003a7a 100%) !important; }
[data-theme="dark"] .stat-card.bg-secondary { background: linear-gradient(135deg, #4a4a5a 0%, #3a3a4a 100%) !important; }

/* stat-card body background */
[data-theme="dark"] .stat-card-body {
    background: transparent;
}

/* ============================================================
   STAT CARD GRADIENT (used by vehicles, expenses etc.)
   ============================================================ */
[data-theme="dark"] .stat-card-gradient {
    background: var(--dark-card) !important;
    border: 1px solid var(--dark-border) !important;
}

[data-theme="dark"] .stat-card-gradient h3,
[data-theme="dark"] .stat-card-gradient h4,
[data-theme="dark"] .stat-card-gradient .mb-0 {
    color: var(--dark-text) !important;
}

[data-theme="dark"] .stat-card-gradient p,
[data-theme="dark"] .stat-card-gradient .mb-1 {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .stat-card-gradient .stat-icon {
    opacity: 0.15 !important;
}

/* Specific gradient variants in dark mode */
[data-theme="dark"] .stat-gradient-blue { background: linear-gradient(135deg, #1a2a4a 0%, #4a2560 100%) !important; border-color: transparent !important; }
[data-theme="dark"] .stat-gradient-green { background: linear-gradient(135deg, #0a4a2a 0%, #0a5a4e 100%) !important; border-color: transparent !important; }
[data-theme="dark"] .stat-gradient-orange { background: linear-gradient(135deg, #5a3a0a 0%, #7a4a0e 100%) !important; border-color: transparent !important; }
[data-theme="dark"] .stat-gradient-purple { background: linear-gradient(135deg, #2a1a4a 0%, #4a2560 100%) !important; border-color: transparent !important; }
[data-theme="dark"] .stat-gradient-teal { background: linear-gradient(135deg, #0a4a4a 0%, #004a5a 100%) !important; border-color: transparent !important; }
[data-theme="dark"] .stat-gradient-amber { background: linear-gradient(135deg, #5a3a0a 0%, #6a5a00 100%) !important; border-color: transparent !important; }

/* ============================================================
   GLOBAL WHITE BACKGROUND KILLER
   Catch-all for inline bg-white, bg-light, bg="#fff", style="background: #fff", etc.
   ============================================================ */
[data-theme="dark"] [style*="background:#fff"],
[data-theme="dark"] [style*="background: #fff"],
[data-theme="dark"] [style*="background-color:#fff"],
[data-theme="dark"] [style*="background-color: #fff"],
[data-theme="dark"] [style*="background:white"],
[data-theme="dark"] [style*="background: white"],
[data-theme="dark"] [style*="background-color:white"],
[data-theme="dark"] [style*="background-color: white"] {
    background: var(--dark-card) !important;
}

[data-theme="dark"] .card[style*="background: #fff"],
[data-theme="dark"] .card[style*="background:#fff"] {
    background: var(--dark-card) !important;
}

/* Catch inline bg-white utility inline style */
[data-theme="dark"] [style*="bg-white"],
[data-theme="dark"] [style*="bg-white;"] {
    background: var(--dark-card) !important;
}

/* ============================================================
   FILTER BAR & SEARCH
   ============================================================ */
[data-theme="dark"] .filter-bar {
    background: var(--dark-card);
    border-color: var(--dark-border);
}

[data-theme="dark"] .filter-search input {
    background: var(--dark-input-bg);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .filter-search input::placeholder {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .filter-search input:focus {
    background: var(--dark-card);
    border-color: var(--module-active);
}

[data-theme="dark"] .filter-search i {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .filter-select {
    background-color: var(--dark-input-bg);
    border-color: var(--dark-border);
    color: var(--dark-text);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
}

[data-theme="dark"] .btn-filter-primary {
    color: #fff;
}

[data-theme="dark"] .btn-filter-outline {
    background: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .btn-filter-outline:hover {
    background: var(--dark-hover);
    color: var(--dark-text);
}

/* ============================================================
   FILTER TABS
   ============================================================ */
[data-theme="dark"] .filter-tab {
    background: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .filter-tab:hover {
    background: var(--dark-hover);
    color: var(--dark-text);
}

[data-theme="dark"] .filter-tab.active {
    background: var(--module-active-dark);
    color: var(--dark-text);
    border-color: var(--module-active);
}

[data-theme="dark"] .filter-tab .badge-tab {
    background: rgba(255,255,255,0.1);
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .filter-tab.active .badge-tab {
    background: rgba(255,255,255,0.2);
    color: var(--dark-text);
}

/* ============================================================
   MAIN CARD & SUB CARD
   ============================================================ */
[data-theme="dark"] .main-card {
    background: var(--dark-card);
    border-color: var(--dark-border);
    box-shadow: 0 1px 3px rgba(0,0,0,0.2);
}

[data-theme="dark"] .main-card-header {
    background: var(--dark-surface);
    border-bottom-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .sub-card {
    border-color: var(--dark-border);
    background: var(--dark-card);
}

[data-theme="dark"] .sub-card-header {
    background: var(--dark-surface);
    border-bottom-color: var(--dark-border);
    color: var(--dark-text);
}

/* ============================================================
   TABLES (Bootstrap .table + custom .table-fixit)
   ============================================================ */
[data-theme="dark"] .table {
    color: var(--dark-text);
}

[data-theme="dark"] .table thead th {
    color: var(--dark-text-secondary);
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .table td {
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .table-hover tbody tr:hover {
    background-color: var(--dark-hover);
    color: var(--dark-text);
}

[data-theme="dark"] .table-striped > tbody > tr:nth-of-type(odd) {
    background-color: rgba(255,255,255,0.03);
}

[data-theme="dark"] .table-fixit thead th {
    background: var(--dark-surface);
    color: var(--dark-text-secondary);
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .table-fixit tbody td {
    border-bottom-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .table-fixit tbody tr:hover {
    background: var(--dark-hover);
}

/* Table row bg variants */
[data-theme="dark"] .table-light {
    background-color: var(--dark-surface);
}

[data-theme="dark"] .table-dark {
    background-color: var(--dark-sidebar-bg);
}

[data-theme="dark"] .table-active {
    background-color: var(--dark-hover);
}

/* ============================================================
   FORMS & INPUTS
   ============================================================ */
[data-theme="dark"] .form-control {
    background-color: var(--dark-input-bg);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .form-control::placeholder {
    color: var(--dark-text-secondary);
    opacity: 0.7;
}

[data-theme="dark"] .form-control:focus {
    background-color: var(--dark-card);
    border-color: #4f9cf7;
    color: var(--dark-text);
    box-shadow: 0 0 0 0.2rem rgba(79, 156, 247, 0.25);
}

[data-theme="dark"] .form-select {
    background-color: var(--dark-input-bg);
    border-color: var(--dark-border);
    color: var(--dark-text);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%239ca3af' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
}

[data-theme="dark"] .form-select:focus {
    background-color: var(--dark-card);
    border-color: #4f9cf7;
    color: var(--dark-text);
    box-shadow: 0 0 0 0.2rem rgba(79, 156, 247, 0.25);
}

[data-theme="dark"] input,
[data-theme="dark"] select,
[data-theme="dark"] textarea {
    background-color: var(--dark-input-bg);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] input:focus,
[data-theme="dark"] select:focus,
[data-theme="dark"] textarea:focus {
    background-color: var(--dark-card);
    border-color: #4f9cf7;
    color: var(--dark-text);
    box-shadow: 0 0 0 0.2rem rgba(79, 156, 247, 0.25);
}

[data-theme="dark"] .input-group-text {
    background-color: var(--dark-surface);
    border-color: var(--dark-border);
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .input-group-fixit .input-group-text {
    background: var(--dark-surface);
    border-color: var(--dark-border);
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .main-card .form-control,
[data-theme="dark"] .main-card .form-select {
    background: var(--dark-input-bg);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .main-card .form-control:focus,
[data-theme="dark"] .main-card .form-select:focus {
    background: var(--dark-card);
}

[data-theme="dark"] .main-card .form-text {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .form-label {
    color: var(--dark-text);
}

[data-theme="dark"] .form-check-label {
    color: var(--dark-text);
}

[data-theme="dark"] .form-check-input {
    background-color: var(--dark-input-bg);
    border-color: var(--dark-border);
}

[data-theme="dark"] .form-range::-webkit-slider-runnable-track {
    background-color: var(--dark-surface);
}

[data-theme="dark"] .form-range::-webkit-slider-thumb {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
}

/* ============================================================
   BOOTSTRAP CARDS (.card class, not .main-card)
   ============================================================ */
[data-theme="dark"] .card {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .card-header {
    background-color: var(--dark-surface);
    border-bottom-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .card-body {
    background-color: var(--dark-card);
    color: var(--dark-text);
}

[data-theme="dark"] .card-footer {
    background-color: var(--dark-surface);
    border-top-color: var(--dark-border);
    color: var(--dark-text-secondary);
}

/* ============================================================
   MODALS
   ============================================================ */
[data-theme="dark"] .modal-content {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .modal-header {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .modal-footer {
    border-top-color: var(--dark-border);
}

[data-theme="dark"] .modal-title {
    color: var(--dark-text);
}

[data-theme="dark"] .modal-backdrop {
    background-color: rgba(0, 0, 0, 0.7);
}

[data-theme="dark"] .btn-close {
    filter: invert(1) grayscale(1);
}

/* ============================================================
   DROPDOWNS
   ============================================================ */
[data-theme="dark"] .dropdown-menu {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
}

[data-theme="dark"] .dropdown-item {
    color: var(--dark-text);
}

[data-theme="dark"] .dropdown-item:hover {
    background-color: var(--dark-hover);
    color: var(--dark-text);
}

[data-theme="dark"] .dropdown-divider {
    border-color: var(--dark-border);
}

[data-theme="dark"] .dropdown-header {
    color: var(--dark-text-secondary);
}

/* ============================================================
   ALERTS
   ============================================================ */
[data-theme="dark"] .alert {
    background-color: var(--dark-surface);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .alert-success {
    background-color: #1a3a2a;
    border-color: #2d6a4f;
    color: #95d5b2;
}

[data-theme="dark"] .alert-danger {
    background-color: #3a1a1a;
    border-color: #6a2d2d;
    color: #f5a5a5;
}

[data-theme="dark"] .alert-warning {
    background-color: #3a2e1a;
    border-color: #6a5a2d;
    color: #f0d58c;
}

[data-theme="dark"] .alert-info {
    background-color: #1a2a3a;
    border-color: #2d4a6a;
    color: #95c5f0;
}

[data-theme="dark"] .alert-primary {
    background-color: #1a2260;
    border-color: #2d3a8a;
    color: #a5b8f0;
}

/* ============================================================
   NAV & TABS
   ============================================================ */
[data-theme="dark"] .nav-tabs {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .nav-tabs .nav-link {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .nav-tabs .nav-link.active {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    border-bottom-color: var(--dark-card);
    color: var(--dark-text);
}

[data-theme="dark"] .nav-tabs .nav-link:hover {
    border-color: var(--dark-border);
    background-color: var(--dark-hover);
}

[data-theme="dark"] .nav-link {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .nav-link:hover {
    color: var(--dark-text);
}

/* ============================================================
   PAGINATION
   ============================================================ */
[data-theme="dark"] .pagination .page-link {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .pagination .page-link:hover {
    background-color: var(--dark-hover);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .pagination .page-item.active .page-link {
    background-color: #4a5568;
    border-color: #4a5568;
    color: var(--dark-text);
}

[data-theme="dark"] .pagination .page-item.disabled .page-link {
    background-color: var(--dark-surface);
    color: var(--dark-text-secondary);
    border-color: var(--dark-border);
}

/* ============================================================
   BADGES
   ============================================================ */
[data-theme="dark"] .badge.bg-light {
    background-color: var(--dark-surface) !important;
    color: var(--dark-text);
}

[data-theme="dark"] .badge.bg-white {
    background-color: var(--dark-card) !important;
    color: var(--dark-text);
}

/* Custom badges */
[data-theme="dark"] .badge-success { background: #1a3a2a; color: #95d5b2; }
[data-theme="dark"] .badge-primary { background: #1a2a4a; color: #95b8f0; }
[data-theme="dark"] .badge-warning { background: #3a2e1a; color: #f0d58c; }
[data-theme="dark"] .badge-danger { background: #3a1a1a; color: #f5a5a5; }
[data-theme="dark"] .badge-secondary { background: var(--dark-surface); color: var(--dark-text-secondary); }
[data-theme="dark"] .badge-info { background: #1a2a3a; color: #95c5f0; }
[data-theme="dark"] .badge-purple { background: #2a1a3a; color: #c5a5f0; }
[data-theme="dark"] .badge-gold { background: #3a2e1a; border-color: #6a5a2d; color: #f0d58c; }
[data-theme="dark"] .badge-silver { background: var(--dark-surface); border-color: var(--dark-border); color: var(--dark-text-secondary); }

[data-theme="dark"] .badge-note-type {
    background: rgba(255,255,255,0.1);
    color: var(--dark-text);
}

[data-theme="dark"] .status-badge {
    color: var(--dark-text);
}

/* ============================================================
   LIST GROUP
   ============================================================ */
[data-theme="dark"] .list-group-item {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .list-group-item:hover {
    background-color: var(--dark-hover);
}

[data-theme="dark"] .list-group-item.active {
    background-color: #4f9cf7;
    border-color: #4f9cf7;
}

/* ============================================================
   PROGRESS BARS
   ============================================================ */
[data-theme="dark"] .progress {
    background-color: var(--dark-surface);
}

[data-theme="dark"] .chart-container {
    background-color: var(--dark-card);
}

/* ============================================================
   SWEETALERT2 DARK MODE
   ============================================================ */
[data-theme="dark"] .swal2-popup {
    background: var(--dark-card) !important;
    color: var(--dark-text) !important;
}

[data-theme="dark"] .swal2-title {
    color: var(--dark-text) !important;
}

[data-theme="dark"] .swal2-html-container {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .swal2-icon {
    border-color: var(--dark-border) !important;
}

/* ============================================================
   TOAST
   ============================================================ */
[data-theme="dark"] .toast {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .toast-header {
    background-color: var(--dark-surface);
    border-bottom-color: var(--dark-border);
    color: var(--dark-text);
}

/* ============================================================
   OFF-CANVAS
   ============================================================ */
[data-theme="dark"] .offcanvas {
    background-color: var(--dark-card);
    color: var(--dark-text);
}

[data-theme="dark"] .offcanvas-header {
    border-bottom-color: var(--dark-border);
}

/* ============================================================
   ACCORDION
   ============================================================ */
[data-theme="dark"] .accordion-item {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
}

[data-theme="dark"] .accordion-button {
    background-color: var(--dark-surface);
    color: var(--dark-text);
}

[data-theme="dark"] .accordion-button:not(.collapsed) {
    background-color: var(--dark-hover);
    color: var(--dark-text);
}

[data-theme="dark"] .accordion-button:focus {
    box-shadow: 0 0 0 0.1rem rgba(79, 156, 247, 0.25);
}

[data-theme="dark"] .accordion-body {
    background-color: var(--dark-card);
    color: var(--dark-text);
}

[data-theme="dark"] .accordion-header {
    border-color: var(--dark-border);
}

/* ============================================================
   TOOLTIPS & POPOVERS
   ============================================================ */
[data-theme="dark"] .tooltip-inner {
    background-color: var(--dark-surface);
    color: var(--dark-text);
}

[data-theme="dark"] .tooltip .tooltip-arrow::before {
    border-top-color: var(--dark-surface);
}

[data-theme="dark"] .popover {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
}

[data-theme="dark"] .popover-header {
    background-color: var(--dark-surface);
    border-bottom-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .popover-body {
    color: var(--dark-text);
}

[data-theme="dark"] .popover .bs-popover-top .popover-arrow::after,
[data-theme="dark"] .popover .bs-popover-top .popover-arrow::before {
    border-top-color: var(--dark-card);
}

/* ============================================================
   BOOTSTRAP UTILITY OVERRIDES
   ============================================================ */
[data-theme="dark"] .bg-white {
    background-color: var(--dark-card) !important;
}

[data-theme="dark"] .bg-light {
    background-color: var(--dark-surface) !important;
}

[data-theme="dark"] .text-dark {
    color: var(--dark-text) !important;
}

[data-theme="dark"] .text-body {
    color: var(--dark-text) !important;
}

[data-theme="dark"] .text-muted {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .text-gray-600,
[data-theme="dark"] .text-gray-500 {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .border {
    border-color: var(--dark-border) !important;
}

[data-theme="dark"] .border-top {
    border-top-color: var(--dark-border) !important;
}

[data-theme="dark"] .border-bottom {
    border-bottom-color: var(--dark-border) !important;
}

[data-theme="dark"] .border-left,
[data-theme="dark"] .border-start {
    border-left-color: var(--dark-border) !important;
}

[data-theme="dark"] .border-right,
[data-theme="dark"] .border-end {
    border-right-color: var(--dark-border) !important;
}

/* ============================================================
   PAGE HEADER & SECTION HEADERS
   ============================================================ */
[data-theme="dark"] .page-header {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .section-header {
    border-bottom-color: var(--dark-border);
}

/* ============================================================
   CUSTOMER PROFILE SPECIFICS
   ============================================================ */
[data-theme="dark"] .profile-card-header {
    background: linear-gradient(180deg, rgba(var(--module-active), 0.15) 0%, rgba(26, 32, 44, 0) 100%);
}

[data-theme="dark"] .customer-details-section {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .detail-row {
    color: var(--dark-text);
}

[data-theme="dark"] .quick-action-btn {
    background: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .quick-action-btn:hover {
    background: var(--dark-hover);
    color: var(--dark-text);
}

[data-theme="dark"] .note-card {
    border-color: var(--dark-border);
}

[data-theme="dark"] .note-card-header {
    background: var(--dark-surface);
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .note-card-body {
    color: var(--dark-text);
}

[data-theme="dark"] .tabs-nav-wrapper {
    background: var(--dark-surface);
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .tab-link {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .tab-link:hover {
    color: var(--dark-text);
    background: rgba(255,255,255,0.05);
}

[data-theme="dark"] .tab-item.active .tab-link {
    color: var(--module-active);
}

[data-theme="dark"] .profile-avatar-upload-btn {
    border-color: var(--dark-card);
}

/* ============================================================
   EMPTY STATES
   ============================================================ */
[data-theme="dark"] .empty-state-module h4,
[data-theme="dark"] .empty-state-module h5 {
    color: var(--dark-text);
}

[data-theme="dark"] .empty-state-module p {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .empty-state-icon {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .pagination-info {
    color: var(--dark-text-secondary);
}

/* ============================================================
   INVENTORY & HR-PAYROLL SPECIFIC
   ============================================================ */
[data-theme="dark"] .inventory-content {
    background-color: var(--dark-card);
}

[data-theme="dark"] .hr-payroll-content {
    background-color: var(--dark-card);
}

/* ============================================================
   SIDEBAR EXTRAS
   ============================================================ */
[data-theme="dark"] .sidebar-resize-handle {
    background-color: var(--dark-border);
}

[data-theme="dark"] .sidebar .badge {
    color: var(--dark-text);
}

/* ============================================================
   BTN VARIANTS (for bg-light etc buttons)
   ============================================================ */
[data-theme="dark"] .btn-light {
    background-color: var(--dark-surface);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .btn-light:hover {
    background-color: var(--dark-hover);
    color: var(--dark-text);
}

[data-theme="dark"] .btn-outline-light {
    border-color: var(--dark-border);
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .btn-outline-light:hover {
    background-color: var(--dark-surface);
    color: var(--dark-text);
}

[data-theme="dark"] .btn-white {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .btn-white:hover {
    background-color: var(--dark-hover);
}

[data-theme="dark"] .btn-secondary {
    background-color: var(--dark-surface);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .btn-secondary:hover {
    background-color: var(--dark-hover);
}

/* ============================================================
   CALENDAR / DATEPICKER (Bootstrap Datepicker)
   ============================================================ */
[data-theme="dark"] .datepicker {
    background-color: var(--dark-card);
    border-color: var(--dark-border);
    color: var(--dark-text);
}

[data-theme="dark"] .datepicker table tr td,
[data-theme="dark"] .datepicker table tr th {
    color: var(--dark-text);
    background-color: transparent;
}

[data-theme="dark"] .datepicker table tr td.day:hover,
[data-theme="dark"] .datepicker table tr td.focused {
    background-color: var(--dark-hover);
}

[data-theme="dark"] .datepicker table tr td.active,
[data-theme="dark"] .datepicker table tr td.active:hover {
    background-color: #4a5568;
    color: var(--dark-text);
}

[data-theme="dark"] .datepicker table tr td.new,
[data-theme="dark"] .datepicker table tr td.old {
    color: var(--dark-text-secondary);
}

[data-theme="dark"] .datepicker .datepicker-switch {
    color: var(--dark-text);
}

[data-theme="dark"] .datepicker .datepicker-switch:hover,
[data-theme="dark"] .datepicker .prev:hover,
[data-theme="dark"] .datepicker .next:hover {
    background-color: var(--dark-hover);
}

/* ============================================================
   SELECT2
   ============================================================ */
[data-theme="dark"] .select2-container--default .select2-selection--single,
[data-theme="dark"] .select2-container--default .select2-selection--multiple {
    background-color: var(--dark-input-bg) !important;
    border-color: var(--dark-border) !important;
}

[data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__rendered {
    color: var(--dark-text) !important;
}

[data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__placeholder {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .select2-container--default .select2-selection--single .select2-selection__arrow b {
    border-color: var(--dark-text-secondary) transparent transparent transparent !important;
}

[data-theme="dark"] .select2-dropdown {
    background-color: var(--dark-card) !important;
    border-color: var(--dark-border) !important;
}

[data-theme="dark"] .select2-container--default .select2-results__option {
    color: var(--dark-text) !important;
}

[data-theme="dark"] .select2-container--default .select2-results__option--highlighted {
    background-color: var(--dark-hover) !important;
}

[data-theme="dark"] .select2-container--default .select2-results__option[aria-selected="true"] {
    background-color: var(--dark-hover) !important;
}

[data-theme="dark"] .select2-search--dropdown .select2-search__field {
    background-color: var(--dark-input-bg) !important;
    border-color: var(--dark-border) !important;
    color: var(--dark-text) !important;
}

/* ============================================================
   FLATPICKR
   ============================================================ */
[data-theme="dark"] .flatpickr-calendar {
    background: var(--dark-card) !important;
    border-color: var(--dark-border) !important;
}

[data-theme="dark"] .flatpickr-calendar.arrowTop::after,
[data-theme="dark"] .flatpickr-calendar.arrowTop::before {
    border-bottom-color: var(--dark-card) !important;
}

[data-theme="dark"] .flatpickr-months .flatpickr-month {
    background: var(--dark-card) !important;
    color: var(--dark-text) !important;
}

[data-theme="dark"] .flatpickr-current-month .flatpickr-monthDropdown-months {
    background: var(--dark-card) !important;
    color: var(--dark-text) !important;
}

[data-theme="dark"] .flatpickr-weekday {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .flatpickr-day {
    color: var(--dark-text) !important;
}

[data-theme="dark"] .flatpickr-day.today {
    border-color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .flatpickr-day:hover {
    background: var(--dark-hover) !important;
}

[data-theme="dark"] .flatpickr-day.selected {
    background: #4a5568 !important;
    border-color: #4a5568 !important;
}

[data-theme="dark"] .flatpickr-day.inRange {
    background: var(--dark-hover) !important;
    box-shadow: -5px 0 0 var(--dark-hover), 5px 0 0 var(--dark-hover);
}

[data-theme="dark"] .flatpickr-day.disabled {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] .flatpickr-time {
    border-top-color: var(--dark-border) !important;
}

[data-theme="dark"] .flatpickr-time input {
    color: var(--dark-text) !important;
}

/* ============================================================
   WEBKIT / SCROLLBAR STYLING
   ============================================================ */
[data-theme="dark"] ::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

[data-theme="dark"] ::-webkit-scrollbar-track {
    background: var(--dark-bg);
}

[data-theme="dark"] ::-webkit-scrollbar-thumb {
    background: var(--dark-border);
    border-radius: 4px;
}

[data-theme="dark"] ::-webkit-scrollbar-thumb:hover {
    background: var(--dark-text-secondary);
}

/* ============================================================
   DASHBOARD / BI SPECIFICS
   ============================================================ */
[data-theme="dark"] .dashboard-stat {
    background: var(--dark-card);
    border-color: var(--dark-border);
}

[data-theme="dark"] .card.border-left-primary,
[data-theme="dark"] .card.border-left-success,
[data-theme="dark"] .card.border-left-info,
[data-theme="dark"] .card.border-left-warning,
[data-theme="dark"] .card.border-left-danger {
    background-color: var(--dark-card);
    border-left-color: var(--dark-text-secondary);
}

/* ============================================================
   PLACEHOLDER TEXT FALLBACK (any element)
   ============================================================ */
[data-theme="dark"] ::placeholder {
    color: var(--dark-text-secondary) !important;
    opacity: 0.7 !important;
}

[data-theme="dark"] :-ms-input-placeholder {
    color: var(--dark-text-secondary) !important;
}

[data-theme="dark"] ::-ms-input-placeholder {
    color: var(--dark-text-secondary) !important;
}

/* ============================================================
   PRINT PREVIEW PANELS
   ============================================================ */
[data-theme="dark"] .print-preview {
    background: var(--dark-card);
    border-color: var(--dark-border);
}

/* ============================================================
   NOTIFICATION BELL / BADGES
   ============================================================ */
[data-theme="dark"] .notification-item {
    border-bottom-color: var(--dark-border);
}

[data-theme="dark"] .notification-item:hover {
    background-color: var(--dark-hover);
}

[data-theme="dark"] .notification-time {
    color: var(--dark-text-secondary);
}
</style>
