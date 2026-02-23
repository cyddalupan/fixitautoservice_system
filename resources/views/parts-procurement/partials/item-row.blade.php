<div class="item-row border rounded p-3 mb-3" data-index="{{ $index }}">
    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label class="form-label fw-bold small mb-1">
                    Part Number
                    <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control form-control-sm item-part-number" 
                       name="items[{{ $index }}][part_number]" 
                       value="{{ old("items.$index.part_number", $item->part_number ?? '') }}" 
                       required>
            </div>
        </div>
        <div class="col-md-5">
            <div class="mb-3">
                <label class="form-label fw-bold small mb-1">
                    Part Name
                    <span class="text-danger">*</span>
                </label>
                <input type="text" 
                       class="form-control form-control-sm item-part-name" 
                       name="items[{{ $index }}][part_name]" 
                       value="{{ old("items.$index.part_name", $item->part_name ?? '') }}" 
                       required>
            </div>
        </div>
        <div class="col-md-3">
            <div class="mb-3">
                <label class="form-label fw-bold small mb-1">
                    Manufacturer
                </label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       name="items[{{ $index }}][manufacturer]" 
                       value="{{ old("items.$index.manufacturer", $item->manufacturer ?? '') }}">
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label class="form-label fw-bold small mb-1">
                    Description (Optional)
                </label>
                <input type="text" 
                       class="form-control form-control-sm" 
                       name="items[{{ $index }}][description]" 
                       value="{{ old("items.$index.description", $item->description ?? '') }}">
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label fw-bold small mb-1">
                    Quantity
                    <span class="text-danger">*</span>
                </label>
                <input type="number" 
                       class="form-control form-control-sm item-quantity" 
                       name="items[{{ $index }}][quantity]" 
                       value="{{ old("items.$index.quantity", $item->quantity ?? 1) }}" 
                       min="1" 
                       step="1" 
                       required>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label fw-bold small mb-1">
                    Unit Price
                    <span class="text-danger">*</span>
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">$</span>
                    <input type="number" 
                           class="form-control form-control-sm item-unit-price" 
                           name="items[{{ $index }}][unit_price]" 
                           value="{{ old("items.$index.unit_price", $item->unit_price ?? 0) }}" 
                           min="0" 
                           step="0.01" 
                           required>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="mb-3">
                <label class="form-label fw-bold small mb-1">
                    Total
                                </label>
                <div class="form-control form-control-sm bg-light text-end">
                    $<span class="item-total">
                        @php
                            $quantity = old("items.$index.quantity", $item->quantity ?? 1);
                            $unitPrice = old("items.$index.unit_price", $item->unit_price ?? 0);
                            echo number_format($quantity * $unitPrice, 2);
                        @endphp
                    </span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-end">
                <button type="button" class="btn btn-outline-danger btn-sm remove-item" onclick="removeItemRow(this)">
                    <i class="fas fa-trash me-1"></i> Remove
                </button>
            </div>
        </div>
    </div>
    
    <!-- Hidden fields for calculations -->
    <input type="hidden" name="items[{{ $index }}][total]" value="{{ old("items.$index.total", $item->total ?? 0) }}">
</div>