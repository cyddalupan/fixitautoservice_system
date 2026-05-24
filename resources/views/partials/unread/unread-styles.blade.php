{{--
==========================================================
 UNREAD RECORD VISUAL SYSTEM — Fix-It Auto System v2.0
 Gmail-style unread indicators with full light/dark mode
==========================================================
--}}

<style>
/* ============================================================
   CSS VARIABLES — Unread System
   ============================================================ */
:root {
    /* ---- Light Mode Defaults ---- */
    --unread-bg: #f0f4ff;
    --unread-bg-hover: #e8eefe;
    --unread-left-border: var(--module-active, #2563eb);
    --unread-text-primary: #0f172a;
    --unread-text-secondary: #1e293b;
    --unread-accent-bar-width: 3px;

    --read-text-primary: #1e293b;
    --read-text-weight: 400;

    --new-badge-bg: #2563eb;
    --new-badge-text: #fff;

    --mark-read-btn-bg: #eef2ff;
    --mark-read-btn-color: #4f46e5;
    --mark-read-btn-hover: #e0e7ff;

    --unread-dot-color: #2563eb;
    --unread-dot-size: 8px;
}

/* ============================================================
   DARK MODE — Unread System
   ============================================================ */
[data-theme="dark"] {
    --unread-bg: #2e3340;
    --unread-bg-hover: #363b4a;
    --unread-left-border: var(--module-active, #60a5fa);
    --unread-text-primary: #f1f5f9;
    --unread-text-secondary: #e2e8f0;

    --read-text-primary: #cbd5e1;

    --new-badge-bg: #3b82f6;
    --new-badge-text: #fff;

    --mark-read-btn-bg: #2a2f3a;
    --mark-read-btn-color: #93a3f8;
    --mark-read-btn-hover: #333842;

    --unread-dot-color: #60a5fa;
}

/* ============================================================
   UNREAD ROW — Applied to <tr> when record is unread
   ============================================================ */
.tr-unread {
    background-color: var(--unread-bg) !important;
    position: relative;
}

/* Left accent bar — thin colored border on the leftmost cell */
.tr-unread td:first-child {
    position: relative;
    box-shadow: inset var(--unread-accent-bar-width) 0 0 0 var(--unread-left-border);
}

/* Row hover state */
.tr-unread:hover {
    background-color: var(--unread-bg-hover) !important;
}

/* Bold primary text for unread rows */
.tr-unread td .unread-primary-text {
    font-weight: 700;
    color: var(--unread-text-primary);
}

.tr-unread td .unread-secondary-text {
    color: var(--unread-text-secondary);
    font-weight: 600;
}

/* Normal styling for read rows */
.td-read-text {
    font-weight: var(--read-text-weight);
    color: var(--read-text-primary);
}

/* ============================================================
   "NEW" BADGE — Small, clean badge for unread records
   ============================================================ */
.badge-new-record {
    display: inline-flex;
    align-items: center;
    padding: 2px 7px;
    font-size: 0.62rem;
    font-weight: 700;
    border-radius: 3px;
    background: var(--new-badge-bg);
    color: var(--new-badge-text);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
    margin-left: 6px;
    vertical-align: middle;
    animation: badge-new-pulse 2s ease-in-out 1;
}

@keyframes badge-new-pulse {
    0% { transform: scale(0.85); opacity: 0.7; }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

/* ============================================================
   UNREAD DOT — Small dot indicator for unread rows
   ============================================================ */
.unread-dot {
    display: inline-block;
    width: var(--unread-dot-size);
    height: var(--unread-dot-size);
    border-radius: 50%;
    background: var(--unread-dot-color);
    margin-right: 6px;
    vertical-align: middle;
    flex-shrink: 0;
}

/* ============================================================
   "Mark All as Read" Button
   ============================================================ */
.btn-mark-all-read {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    font-size: 0.78rem;
    font-weight: 600;
    border-radius: 7px;
    background: var(--mark-read-btn-bg);
    color: var(--mark-read-btn-color);
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.12s ease;
    white-space: nowrap;
}

.btn-mark-all-read:hover {
    background: var(--mark-read-btn-hover);
    filter: brightness(1.05);
}

.btn-mark-all-read:active {
    transform: scale(0.97);
}

/* ============================================================
   "Unread Only" Filter Tab
   ============================================================ */
.filter-tab-unread {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 6px 12px;
    font-size: 0.78rem;
    font-weight: 500;
    border-radius: 6px;
    background: var(--mark-read-btn-bg);
    color: var(--mark-read-btn-color);
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.12s ease;
    text-decoration: none;
    white-space: nowrap;
}

.filter-tab-unread:hover,
.filter-tab-unread.active {
    background: var(--unread-bg);
    border-color: var(--unread-left-border);
    color: var(--unread-text-primary);
}

.filter-tab-unread .unread-dot-mini {
    display: inline-block;
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--unread-dot-color);
}

/* ============================================================
   SMOOTH TRANSITIONS
   ============================================================ */
.tr-unread,
.tr-unread td,
.tr-unread td:first-child {
    transition: background-color 0.25s ease, box-shadow 0.25s ease;
}

.table-fixit tbody tr.tr-unread td .unread-primary-text,
.table-fixit tbody tr.tr-unread td .unread-secondary-text,
.table-fixit tbody tr td {
    transition: color 0.2s ease, font-weight 0.25s ease;
}

/* ============================================================
   UNREAD BADGE COUNTER (for filter tabs)
   ============================================================ */
.badge-unread-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    font-size: 0.62rem;
    font-weight: 700;
    border-radius: 9px;
    background: #ef4444;
    color: #fff;
    margin-left: 4px;
}
</style>
