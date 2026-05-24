@if($activeTransaction ?? null)
@php
    $at = $activeTransaction;
@endphp
<div class="modal fade" id="duplicateTransactionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #e63946, #c1121f);">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white d-flex align-items-center justify-content-center" style="width:48px;height:48px;color:#e63946;font-size:1.5rem;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 text-white fw-bold">Vehicle Already Has Active Transaction</h5>
                        <small class="text-white-50">This vehicle currently has an ongoing service record</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-3">This vehicle already has an ongoing service. Please complete or cancel the existing transaction before creating a new one.</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <tbody>
                            <tr><th class="bg-light" style="width:140px;">Customer</th><td>{{ $at['customer'] ?? 'N/A' }}</td></tr>
                            <tr><th class="bg-light">Stage</th><td><span class="badge bg-warning text-dark">{{ $at['stage'] }}</span></td></tr>
                            <tr><th class="bg-light">Reference No.</th><td><code>{{ $at['reference_number'] }}</code></td></tr>
                            <tr><th class="bg-light">Status</th><td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $at['status'])) }}</span></td></tr>
                            <tr><th class="bg-light">Created</th><td>{{ \Carbon\Carbon::parse($at['created_at'])->format('M d, Y h:i A') }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-0 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <div>
                    <a href="{{ $at['route'] }}" class="btn btn-primary me-2">
                        <i class="fas fa-eye me-1"></i> View Current Record
                    </a>
                    <button type="button" class="btn btn-danger" onclick="var inp = document.getElementById('overrideDuplicate'); if(!inp) { inp = document.createElement('input'); inp.type='hidden'; inp.name='override_duplicate'; inp.id='overrideDuplicate'; document.getElementById('creationForm')?.appendChild(inp); } inp.value='1'; document.getElementById('creationForm')?.submit(); $(this).closest('.modal').modal('hide');">
                        <i class="fas fa-shield-alt me-1"></i> Continue Anyway
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = new bootstrap.Modal(document.getElementById('duplicateTransactionModal'));
    modal.show();
});
</script>
@endif
