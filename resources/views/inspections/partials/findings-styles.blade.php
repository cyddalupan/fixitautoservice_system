{{-- Shared Findings/Quotation styles (include inside @push('styles')). --}}
<style>
/* Findings System Styles */
.findings-quick-add{border:1px solid #d0d9f0!important;position:relative;z-index:10}
#quick-issue-title{font-size:.875rem}
#autosuggest-results .list-group-item,
#modal-autosuggest-results .list-group-item{padding:.35rem .75rem;cursor:pointer;border-left:3px solid transparent;font-size:.8rem}
#autosuggest-results .list-group-item:hover,#autosuggest-results .list-group-item.active,
#modal-autosuggest-results .list-group-item:hover,#modal-autosuggest-results .list-group-item.active{border-left-color:#1a237e;background:#f0f4ff}
#autosuggest-results .list-group-item .category-hint,
#modal-autosuggest-results .list-group-item .category-hint{font-size:.65rem;color:#94a3b8}
.severity-btn.active,.urgency-btn.active{box-shadow:0 0 0 2px rgba(26,35,126,.25)}
.btn-outline-info.active{background:#0dcaf0!important;color:#fff!important;border-color:#0dcaf0!important}
.btn-outline-warning.active{background:#ffc107!important;color:#000!important;border-color:#ffc107!important}
.btn-outline-danger.active{background:#dc3545!important;color:#fff!important;border-color:#dc3545!important}
.btn-outline-dark.active{background:#212529!important;color:#fff!important;border-color:#212529!important}
.btn-outline-secondary.active{background:#6c757d!important;color:#fff!important;border-color:#6c757d!important}
.btn-outline-info.active{background:#0dcaf0!important;color:#fff!important;border-color:#0dcaf0!important}
.finding-card{transition:all .15s ease}
.finding-card .card:hover{border-color:#c8d6e5!important;box-shadow:0 2px 8px rgba(0,0,0,.06)!important}
.finding-title{color:#1e293b;font-size:.9rem}
.finding-notes{font-size:.82rem;color:#64748b}
.finding-action{font-size:.82rem;color:#475569}
.finding-cost{font-size:.95rem}
/* Animate new findings */
@keyframes fadeSlideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
.finding-new{animation:fadeSlideDown .3s ease}
/* Animate removal */
@keyframes fadeScaleOut{from{opacity:1;transform:scale(1)}to{opacity:0;transform:scale(.95)}}
.finding-removing{animation:fadeScaleOut .2s ease}
/* Modify modal styling for premium look */
#findingEditModal .modal-header{background:linear-gradient(135deg,#1a237e,#283593);color:#fff;border-radius:.375rem .375rem 0 0}
#findingEditModal .modal-header .btn-close{filter:brightness(0) invert(1)}
.bulk-add-btn{transition:all .1s ease;cursor:pointer}
.bulk-add-btn:hover{transform:translateY(-1px);box-shadow:0 2px 4px rgba(0,0,0,.1)}
/* ===== Findings column layout (compact + readable) ===== */
.finding-headrow,.finding-card .finding-grid{
    display:grid;
    grid-template-columns:24px 104px minmax(0,1.7fr) minmax(0,1.1fr) 88px 58px 90px 96px 60px;
    align-items:center;
}
.finding-headrow{padding:4px 6px;font-size:10.5px;letter-spacing:.4px;text-transform:uppercase;color:#94a3b8;font-weight:600;border-bottom:1px solid #e6ebf3;background:#f8fafc;}
.finding-headrow span{padding:0 6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.finding-card .finding-grid{font-size:13px;line-height:1.5;}
.finding-card .fcell{padding:3px 6px;border-right:1px solid #eef2f7;min-width:0;display:flex;align-items:center;white-space:nowrap;overflow:hidden;}
.finding-card .finding-grid > .fcell:last-child{border-right:0;}
.finding-card .finding-title{font-size:14.5px;font-weight:700;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.finding-card .fc-parts{gap:6px;}
.finding-card .finding-remarks{font-size:12.5px;color:#607d8b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;width:100%;}
.finding-card .finding-notes{font-size:12px;color:#90a4ae;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.finding-card .urgency-badge{font-size:11px;padding:3px 9px;border-radius:10px;}
.finding-card .finding-price{font-size:13px;color:#37474f;}
.finding-card .finding-line-total{font-size:14px;color:#1a237e;}
.finding-card .finding-qty-wrap{font-size:13px;color:#546e7a;}
.finding-card .card{border-radius:8px;overflow:hidden;}
/* floating / lifted look while dragging */
.finding-chosen .card{border-color:#1a237e!important;box-shadow:0 10px 26px rgba(26,35,126,.22)!important;}
.sortable-fallback{opacity:.97!important;box-shadow:0 22px 46px rgba(16,24,40,.32)!important;transform:rotate(1.4deg) scale(1.02)!important;border-radius:10px!important;background:#fff!important;cursor:grabbing!important;pointer-events:none;z-index:99999!important;}
.sortable-fallback .card{border-radius:10px!important;}

.finding-ghost{opacity:.5;border:1.5px dashed #1a237e!important;background:#eef3ff!important;border-radius:9px!important;min-height:34px;}
/* kanban-style drop feedback */
.finding-dropzone{transition:background .15s ease,border-color .15s ease,box-shadow .15s ease;}
.finding-dropzone.dz-over{background:#eef3ff!important;border-color:#1a237e!important;box-shadow:inset 0 0 0 2px rgba(26,35,126,.30);}
.finding-group.group-over > .finding-group-head{box-shadow:0 0 0 2.5px #1a237e;border-radius:10px 10px 0 0;}
.finding-chosen{z-index:5;}
.finding-drag{opacity:.35!important;}
body.fixit-dragging{-webkit-user-select:none;user-select:none;}
body.fixit-dragging *{cursor:grabbing!important;}
.drag-handle:hover,.drag-handle:active{color:#1a237e!important;cursor:grab;}
/* long text wrap (overview email/address etc.) */
.form-section-body p{overflow-wrap:anywhere;word-break:break-word;}
.finding-card .card:hover{border-color:#c8d6e5!important;box-shadow:0 2px 10px rgba(26,35,126,.10)!important;}
.finding-dropzone .finding-headrow:first-child{margin:-.5rem -.5rem .25rem;border-radius:0;}
/* quick-add urgency buttons: never overflow into the Qty field */
.findings-quick-add .urgency-group{flex-wrap:wrap;gap:.3rem!important;}
.findings-quick-add .urgency-btn{flex:0 1 auto;padding:.26rem .45rem;font-size:.72rem;line-height:1.15;white-space:nowrap;}
@media (pointer:coarse){.findings-quick-add .urgency-btn{padding:.42rem .3rem;font-size:.74rem;}}
/* ===== Beautified toast notifications ===== */
.fixit-toast-stack{position:fixed;top:18px;right:18px;z-index:21000;display:flex;flex-direction:column;gap:10px;width:340px;max-width:calc(100vw - 32px);pointer-events:none;}
.fixit-toast{pointer-events:auto;position:relative;display:flex;align-items:flex-start;gap:11px;padding:12px 14px;border-radius:14px;background:#fff;border:1px solid #eef2f7;box-shadow:0 14px 34px rgba(16,24,40,.16),0 2px 6px rgba(16,24,40,.06);overflow:hidden;animation:fixitToastIn .3s cubic-bezier(.22,1,.36,1);}
.fixit-toast.fixit-out{animation:fixitToastOut .22s ease forwards}
.fixit-toast .ft-ico{width:36px;height:36px;border-radius:11px;display:flex;align-items:center;justify-content:center;color:#fff;flex:0 0 auto;font-size:15px;box-shadow:0 6px 14px rgba(0,0,0,.16);}
.fixit-toast .ft-body{min-width:0;flex:1 1 auto}
.fixit-toast .ft-title{font-weight:800;font-size:13.5px;color:#1e293b;line-height:1.25;letter-spacing:.2px;}
.fixit-toast .ft-msg{font-size:12.5px;color:#64748b;line-height:1.4;margin-top:1px;word-break:break-word;}
.fixit-toast .ft-x{margin-left:auto;background:transparent;border:0;color:#cbd5e1;font-size:15px;line-height:1;cursor:pointer;padding:2px 4px;transition:color .15s;}
.fixit-toast .ft-x:hover{color:#64748b}
.fixit-toast .ft-bar{position:absolute;left:0;bottom:0;height:3px;width:100%;transform-origin:left;animation:fixitBar 3.6s linear forwards;}
.fixit-toast.ft-success{border-left:4px solid #059669}
.fixit-toast.ft-error{border-left:4px solid #dc3545}
.fixit-toast.ft-warn{border-left:4px solid #f59e0b}
.fixit-toast.ft-info{border-left:4px solid #1a237e}
@keyframes fixitToastIn{from{opacity:0;transform:translateX(46px) scale(.98)}to{opacity:1;transform:none}}
@keyframes fixitToastOut{to{opacity:0;transform:translateX(46px) scale(.98)}}
@keyframes fixitBar{from{transform:scaleX(1)}to{transform:scaleX(0)}}
[data-theme="dark"] .fixit-toast{background:var(--dark-card,#1e293b);border-color:var(--dark-border,#334155)}
[data-theme="dark"] .fixit-toast .ft-title{color:#f1f5f9}
[data-theme="dark"] .fixit-toast .ft-msg{color:#94a3b8}
/* ===== Beautified confirm dialog ===== */
.fixit-confirm-backdrop{position:fixed;inset:0;background:rgba(15,23,42,.55);-webkit-backdrop-filter:blur(3px);backdrop-filter:blur(3px);z-index:21500;display:flex;align-items:center;justify-content:center;padding:20px;animation:fixitFadeIn .18s ease;}
.fixit-confirm{width:100%;max-width:400px;background:#fff;border-radius:18px;box-shadow:0 24px 60px rgba(0,0,0,.28);overflow:hidden;animation:fixitPop .26s cubic-bezier(.22,1,.36,1);}
.fixit-confirm .fc-top{padding:24px 22px 6px;text-align:center;}
.fixit-confirm .fc-ico{width:58px;height:58px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 13px;font-size:23px;color:#fff;box-shadow:0 10px 22px rgba(0,0,0,.18);}
.fixit-confirm h6{font-weight:800;font-size:17px;color:#1e293b;margin:0 0 7px;}
.fixit-confirm p{font-size:13px;color:#64748b;margin:0;line-height:1.5;}
.fixit-confirm .fc-actions{display:flex;gap:10px;padding:20px 22px 22px;}
.fixit-confirm .fc-btn{flex:1;border:0;border-radius:11px;padding:11px;font-weight:700;font-size:13.5px;cursor:pointer;transition:all .15s;}
.fixit-confirm .fc-cancel{background:#f1f5f9;color:#475569;}
.fixit-confirm .fc-cancel:hover{background:#e2e8f0;}
.fixit-confirm .fc-ok{color:#fff;}
.fixit-confirm .fc-ok.danger{background:linear-gradient(135deg,#dc3545,#ef4444);box-shadow:0 8px 18px rgba(220,53,69,.28);}
.fixit-confirm .fc-ok.primary{background:linear-gradient(135deg,#1a237e,#3949ab);box-shadow:0 8px 18px rgba(26,35,126,.28);}
.fixit-confirm .fc-btn:active{transform:scale(.97)}
@keyframes fixitFadeIn{from{opacity:0}to{opacity:1}}
@keyframes fixitPop{from{opacity:0;transform:translateY(14px) scale(.96)}to{opacity:1;transform:none}}
[data-theme="dark"] .fixit-confirm{background:var(--dark-card,#1e293b)}
[data-theme="dark"] .fixit-confirm h6{color:#f1f5f9}
[data-theme="dark"] .fixit-confirm p{color:#94a3b8}
[data-theme="dark"] .fixit-confirm .fc-cancel{background:#334155;color:#cbd5e1}


/* ===== Responsive: tablet / small screens + touch dragging ===== */
.drag-handle{touch-action:none;-webkit-user-select:none;user-select:none;}
.finding-inline-editing{background:#fff8e1;border-radius:8px;box-shadow:inset 0 0 0 1.5px #f0b429;}
.finding-card .inline-qty,.finding-card .inline-price{border:1px solid #cbd5e1!important;background:#fff!important;}
@media (max-width:1100px), (pointer:coarse){
    /* compact jam-packed card, all info still readable (tablet / iPad, portrait + landscape) */
    .finding-headrow{display:none!important;}
    .finding-card .finding-grid{display:flex!important;flex-wrap:wrap;align-items:center;gap:3px 6px;padding:6px 9px;font-size:12.5px;line-height:1.35;}
    .finding-card .fcell{border-right:0!important;padding:0;min-width:0;}
    .finding-card .finding-grid > .drag-handle{flex:0 0 auto;font-size:17px;padding:0 4px!important;}
    .finding-card .finding-grid > .fcell:nth-child(2){flex:0 0 auto;}
    .finding-card .finding-grid > .fcell:nth-child(3){flex:1 1 150px;}
    .finding-card .finding-grid > .fcell:nth-child(4){flex:1 1 170px;}
    .finding-card .finding-grid > .fcell:nth-child(3),
    .finding-card .finding-grid > .fcell:nth-child(4){white-space:normal;overflow:visible;}
    .finding-card .finding-grid > .fcell:nth-child(3) .finding-title,
    .finding-card .finding-grid > .fcell:nth-child(4) .finding-remarks{white-space:normal!important;overflow:visible!important;text-overflow:clip!important;min-width:0;}
    .finding-card .finding-grid > .fcell:nth-child(5),
    .finding-card .finding-grid > .fcell:nth-child(6),
    .finding-card .finding-grid > .fcell:nth-child(7),
    .finding-card .finding-grid > .fcell:nth-child(8),
    .finding-card .finding-grid > .fcell:nth-child(9){flex:0 0 auto;}
    .finding-card .finding-title{font-size:13px;white-space:normal;overflow:visible;text-overflow:clip;line-height:1.3;word-break:break-word;}
    .finding-card .finding-remarks{white-space:normal;overflow:visible;text-overflow:clip;width:auto;word-break:break-word;}
    .finding-card .finding-notes{white-space:normal;overflow:visible;text-overflow:clip;}
    .finding-card .finding-grid > .fcell:nth-child(3)::before{content:'Parts ';font-size:9.5px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;}
    .finding-card .finding-grid > .fcell:nth-child(4)::before{content:'Remarks ';font-size:9.5px;text-transform:uppercase;letter-spacing:.4px;color:#94a3b8;font-weight:700;}
    .finding-card .finding-line-total{margin-left:auto;}
    .finding-card .finding-grid > .fcell:last-child{margin-left:0;}
}
@media (max-width:760px){
    .findings-quick-add{position:static!important;}
    .finding-card .fc-parts{flex:1 1 100%;}
    .finding-card .finding-grid > .fcell:nth-child(4){flex:1 1 100%;}
}
@media (pointer:coarse){
    .finding-card .drag-handle{font-size:19px;padding:3px 10px!important;background:#f1f5f9;border-radius:7px;}
    .finding-card .finding-grid{gap:6px 10px;padding:10px 9px;}
    .finding-card .btn{padding:.28rem .5rem;}
    .group-name-input,.group-labor-input{max-width:100%!important;}
}


    /* ===== Dark mode overrides (auto-swept) ===== */
    [data-theme="dark"] .finding-action {
        background-color: var(--dark-card) !important;
        color: var(--dark-text) !important;
        border-color: var(--dark-border) !important;
    }
    [data-theme="dark"] .finding-notes {
        background-color: var(--dark-card) !important;
        color: var(--dark-text) !important;
        border-color: var(--dark-border) !important;
    }
    [data-theme="dark"] .finding-title {
        background-color: var(--dark-card) !important;
        color: var(--dark-text) !important;
        border-color: var(--dark-border) !important;
    }
</style>
