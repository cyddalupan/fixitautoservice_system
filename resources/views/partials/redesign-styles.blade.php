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
   DARK MODE OVERRIDES — New Components
   ============================================================ */
[data-theme="dark"] .page-module-header {
    border-bottom-color: #2d3748;
}

[data-theme="dark"] .stat-card.gradient {
    background: #1a202c;
    border-color: #2d3748;
}

[data-theme="dark"] .stat-card-info h3 {
    color: #e2e8f0;
}

[data-theme="dark"] .stat-card-info p {
    color: #718096;
}

[data-theme="dark"] .filter-bar {
    background: #1a202c;
    border-color: #2d3748;
}

[data-theme="dark"] .filter-search input {
    background: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}

[data-theme="dark"] .filter-search input:focus {
    background: #1a202c;
    border-color: var(--module-active);
}

[data-theme="dark"] .filter-select {
    background-color: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23718096' stroke-width='2'%3E%3Cpath d='m6 9 6 6 6-6'/%3E%3C/svg%3E");
}

[data-theme="dark"] .btn-filter-outline {
    background: #2d3748;
    border-color: #4a5568;
    color: #a0aec0;
}

[data-theme="dark"] .btn-filter-outline:hover {
    background: #4a5568;
    color: #e2e8f0;
}

[data-theme="dark"] .main-card {
    background: #1a202c;
    border-color: #2d3748;
}

[data-theme="dark"] .main-card-header {
    background: #2d3748;
    border-bottom-color: #4a5568;
    color: #e2e8f0;
}

[data-theme="dark"] .table-fixit thead th {
    background: #2d3748;
    color: #a0aec0;
    border-bottom-color: #4a5568;
}

[data-theme="dark"] .table-fixit tbody td {
    border-bottom-color: #2d3748;
    color: #e2e8f0;
}

[data-theme="dark"] .table-fixit tbody tr:hover {
    background: #2d3748;
}

[data-theme="dark"] .filter-tab {
    background: #2d3748;
    border-color: #4a5568;
    color: #a0aec0;
}

[data-theme="dark"] .filter-tab:hover {
    background: #4a5568;
}

[data-theme="dark"] .filter-tab.active {
    background: var(--module-active-dark);
    color: #e2e8f0;
    border-color: var(--module-active);
}

[data-theme="dark"] .profile-card-header {
    background: linear-gradient(180deg, rgba(var(--module-active), 0.15) 0%, rgba(26, 32, 44, 0) 100%);
}

[data-theme="dark"] .customer-details-section {
    border-bottom-color: #2d3748;
}

[data-theme="dark"] .detail-row {
    color: #e2e8f0;
}

[data-theme="dark"] .quick-action-btn {
    background: #1a202c;
    border-color: #2d3748;
    color: #a0aec0;
}

[data-theme="dark"] .quick-action-btn:hover {
    background: #2d3748;
    color: #e2e8f0;
}

[data-theme="dark"] .note-card {
    border-color: #2d3748;
}

[data-theme="dark"] .note-card-header {
    background: #2d3748;
    border-bottom-color: #4a5568;
}

[data-theme="dark"] .note-card-body {
    color: #e2e8f0;
}

[data-theme="dark"] .tabs-nav-wrapper {
    background: #2d3748;
    border-bottom-color: #4a5568;
}

[data-theme="dark"] .tab-link {
    color: #a0aec0;
}

[data-theme="dark"] .tab-link:hover {
    color: #e2e8f0;
    background: rgba(255,255,255,0.05);
}

[data-theme="dark"] .tab-item.active .tab-link {
    color: var(--module-active);
}

[data-theme="dark"] .input-group-fixit .input-group-text {
    background: #2d3748;
    border-color: #4a5568;
    color: #a0aec0;
}

[data-theme="dark"] .main-card .form-control,
[data-theme="dark"] .main-card .form-select {
    background: #2d3748;
    border-color: #4a5568;
    color: #e2e8f0;
}

[data-theme="dark"] .main-card .form-control:focus,
[data-theme="dark"] .main-card .form-select:focus {
    background: #1a202c;
}

[data-theme="dark"] .profile-avatar-upload-btn {
    border-color: #1a202c;
}

[data-theme="dark"] .empty-state-module h4,
[data-theme="dark"] .empty-state-module h5 {
    color: #e2e8f0;
}

[data-theme="dark"] .empty-state-icon {
    color: #4a5568;
}

[data-theme="dark"] .pagination-info {
    color: #a0aec0;
}

[data-theme="dark"] .sub-card {
    border-color: #2d3748;
    background: #1a202c;
}

[data-theme="dark"] .sub-card-header {
    background: #2d3748;
    border-bottom-color: #4a5568;
    color: #e2e8f0;
}
</style>
