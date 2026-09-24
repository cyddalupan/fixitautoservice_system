{{-- Shared Add/Edit Finding modal. Include OUTSIDE any <form> to avoid nested forms. --}}
        <!-- Findings Add/Edit Modal -->
        <div class="modal fade" id="findingEditModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius:12px;border:none;box-shadow:0 10px 40px rgba(0,0,0,.15);">
                    <div class="modal-header" style="background:linear-gradient(135deg,#1a237e,#283593);color:#fff;border-radius:12px 12px 0 0;">
                        <h5 class="modal-title"><i class="fas fa-clipboard-list me-2"></i><span id="findingModalTitle">Add Finding</span></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <form id="findingForm">
                            <input type="hidden" id="editFindingId" value="">
                            <input type="hidden" id="editDetailedNotes" value="">
                            <input type="hidden" id="editRecommendedAction" value="">
                            <input type="hidden" id="editSeverity" value="medium">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Category</label>
                                    <select id="editCategory" class="form-select form-select-sm">
                                        <option value="Engine">Engine</option>
                                        <option value="Brakes">Brakes</option>
                                        <option value="Suspension">Suspension</option>
                                        <option value="Electrical">Electrical</option>
                                        <option value="Cooling">Cooling</option>
                                        <option value="Transmission">Transmission</option>
                                        <option value="Tires">Tires</option>
                                        <option value="Aircon">Aircon</option>
                                        <option value="Steering">Steering</option>
                                        <option value="Body / Exterior">Body / Exterior</option>
                                        <option value="Safety">Safety</option>
                                        <option value="Maintenance">Maintenance</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Parts</label>
                                    <div class="position-relative">
                                        <input type="text" id="editIssueTitle" class="form-control form-control-sm" placeholder="e.g. Oil Leak" autocomplete="off">
                                        <div id="modal-autosuggest-results" class="list-group position-absolute w-100 shadow-sm" style="z-index: 1050; display: none; max-height: 250px; overflow-y: auto;"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Remarks</label>
                                    <input type="text" id="editRemarks" class="form-control form-control-sm" placeholder="worn out / damage / leak / for replacement">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Quantity</label>
                                    <input type="number" id="editQuantity" class="form-control form-control-sm" min="0" step="0.01" value="1">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-semibold">Unit Price (₱)</label>
                                    <input type="number" id="editUnitPrice" class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Urgency</label>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary btn-sm urgency-btn px-3" data-value="routine">Routine</button>
                                        <button type="button" class="btn btn-outline-info btn-sm urgency-btn px-3 active" data-value="soon">Soon</button>
                                        <button type="button" class="btn btn-outline-warning btn-sm urgency-btn px-3" data-value="urgent">Urgent</button>
                                        <button type="button" class="btn btn-outline-danger btn-sm urgency-btn px-3" data-value="immediate">Immediate</button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Est. Cost (₱)</label>
                                    <input type="number" id="editEstimatedCost" class="form-control form-control-sm" min="0" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="saveFindingFromModal()">
                            <i class="fas fa-save me-1"></i>Save Finding
                        </button>
                    </div>
                </div>
            </div>
        </div>
