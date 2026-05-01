<!-- ══════════════════════════════════════════════════════════
     AVATAR CROP MODAL - Partial
     Reusable crop/adjust modal for profile photos.
     Requires Cropper.js (cropperjs).
     
     Usage in parent form:
       1. Include this partial
       2. Add file input: <input type="file" name="profile_photo" id="profilePhotoInput" accept="image/*">
       3. Add hidden input: <input type="hidden" name="cropped_image" id="croppedImageInput">
       4. Add preview container with class "avatar-preview-wrap" containing an <img> or initials
       5. Wire click on trigger to: document.getElementById('profilePhotoInput').click();
       6. Wire file input change: handleAvatarFileSelect(event)
     
     The modal outputs a base64 cropped image into the hidden input.
     On the backend, decode and save it.
     ══════════════════════════════════════════════════════════ -->
<div class="modal fade" id="avatarCropModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius:14px;overflow:hidden;">
            <div class="modal-header border-0 pb-0" style="padding:1rem 1.25rem 0;">
                <h6 class="modal-title fw-bold" style="font-size:.9rem;">
                    <i class="fas fa-crop-alt me-1" style="color:#4361ee;"></i> Adjust Profile Photo
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="row g-0">
                    <!-- Crop area -->
                    <div class="col-md-8" style="background:#1a1a2e;min-height:350px;display:flex;align-items:center;justify-content:center;position:relative;">
                        <div style="width:100%;padding:1rem;">
                            <img id="cropImage" src="" alt="Crop" style="max-width:100%;display:block;">
                        </div>
                    </div>
                    <!-- Preview + controls -->
                    <div class="col-md-4 d-flex flex-column" style="padding:1.25rem;background:#fff;">
                        <!-- Circular preview -->
                        <div style="text-align:center;margin-bottom:1rem;">
                            <label style="font-size:.7rem;font-weight:600;color:#6c7a8d;text-transform:uppercase;letter-spacing:.3px;display:block;margin-bottom:.5rem;">Preview</label>
                            <div id="cropPreview" style="width:120px;height:120px;border-radius:50%;overflow:hidden;margin:0 auto;border:3px solid #eef0f3;box-shadow:0 2px 8px rgba(0,0,0,.08);background:#f4f6fa;">
                                <img id="cropPreviewImg" src="" alt="" style="width:100%;height:100%;object-fit:cover;display:none;">
                                <div id="cropPreviewPlaceholder" style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:600;color:#4361ee;">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Zoom slider -->
                        <div style="margin-bottom:1rem;">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label style="font-size:.72rem;font-weight:500;color:#374151;">
                                    <i class="fas fa-search-plus me-1" style="font-size:.65rem;"></i> Zoom
                                </label>
                                <span id="zoomLevel" style="font-size:.68rem;color:#6c7a8d;">100%</span>
                            </div>
                            <input type="range" id="zoomSlider" class="form-range" min="0.1" max="3" step="0.01" value="1" style="height:4px;">
                        </div>
                        
                        <!-- Action buttons -->
                        <div style="margin-top:auto;">
                            <div class="d-grid gap-2">
                                <button type="button" id="resetCropBtn" class="btn btn-sm btn-outline-secondary" style="font-size:.75rem;">
                                    <i class="fas fa-undo me-1"></i> Reset
                                </button>
                                <button type="button" id="applyCropBtn" class="btn btn-sm" style="font-size:.78rem;background:#4361ee;color:#fff;border:none;padding:.4rem;">
                                    <i class="fas fa-check me-1"></i> Apply & Save
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal" style="font-size:.75rem;">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
/**
 * Global Avatar Crop handler
 * Attached to window so it works across pages.
 */
let cropperInstance = null;
let cropFileInput = null;
let cropPreviewWrap = null;
let cropPreviewImg = null;
let cropHiddenInput = null;

function initAvatarCrop(config) {
    /**
     * config = {
     *   fileInput: HTMLElement | selector string,
     *   previewWrap: HTMLElement | selector string,  // the preview container
     *   hiddenInput: HTMLElement | selector string,  // hidden input for cropped base64
     *   previewImg: HTMLElement | selector string,   // the <img> inside preview
     *   aspectRatio: 1,  // 1:1 square
     * }
     */
    cropFileInput = typeof config.fileInput === 'string' 
        ? document.querySelector(config.fileInput) 
        : config.fileInput;
    cropPreviewWrap = typeof config.previewWrap === 'string'
        ? document.querySelector(config.previewWrap)
        : config.previewWrap;
    cropPreviewImg = typeof config.previewImg === 'string'
        ? document.querySelector(config.previewImg)
        : config.previewImg;
    cropHiddenInput = typeof config.hiddenInput === 'string'
        ? document.querySelector(config.hiddenInput)
        : config.hiddenInput;
    
    if (!cropFileInput) return;
    
    cropFileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (!file) return;
        
        const reader = new FileReader();
        reader.onload = function(ev) {
            const imgSrc = ev.target.result;
            openCropModal(imgSrc, config.aspectRatio || 1);
        };
        reader.readAsDataURL(file);
        
        // Reset file input so re-selecting same file triggers change
        cropFileInput.value = '';
    });
}

function openCropModal(imgSrc, aspectRatio) {
    const cropImg = document.getElementById('cropImage');
    const modalEl = document.getElementById('avatarCropModal');
    
    if (!cropImg || !modalEl) return;
    
    cropImg.src = imgSrc;
    
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
    
    // Need to wait for modal + image to render
    cropImg.onload = function() {
        initCropper(cropImg, aspectRatio);
    };
    // If already loaded
    if (cropImg.complete && cropImg.naturalWidth) {
        initCropper(cropImg, aspectRatio);
    }
}

function initCropper(img, aspectRatio) {
    // Destroy previous instance
    if (cropperInstance) {
        cropperInstance.destroy();
        cropperInstance = null;
    }
    
    cropperInstance = new Cropper(img, {
        aspectRatio: aspectRatio || 1,
        viewMode: 1,
        dragMode: 'move',
        autoCropArea: 0.8,
        restore: false,
        guides: false,
        center: true,
        highlight: false,
        cropBoxMovable: false,
        cropBoxResizable: false,
        toggleDragModeOnDblclick: false,
        background: false,
        ready: function() {
            updatePreview();
        },
        crop: function() {
            updatePreview();
        }
    });
    
    // Wire zoom slider
    const slider = document.getElementById('zoomSlider');
    const zoomLabel = document.getElementById('zoomLevel');
    if (slider) {
        slider.value = 1;
        slider.oninput = function() {
            if (cropperInstance) {
                cropperInstance.zoomTo(parseFloat(this.value));
            }
            if (zoomLabel) {
                zoomLabel.textContent = Math.round(parseFloat(this.value) * 100) + '%';
            }
        };
    }
    
    // Wire reset
    const resetBtn = document.getElementById('resetCropBtn');
    if (resetBtn) {
        resetBtn.onclick = function() {
            if (cropperInstance) {
                cropperInstance.reset();
                if (slider) slider.value = 1;
                if (zoomLabel) zoomLabel.textContent = '100%';
            }
        };
    }
    
    // Wire apply
    const applyBtn = document.getElementById('applyCropBtn');
    if (applyBtn) {
        applyBtn.onclick = function() {
            applyCrop();
        };
    }
}

function updatePreview() {
    if (!cropperInstance) return;
    
    const previewEl = document.getElementById('cropPreviewImg');
    const placeholder = document.getElementById('cropPreviewPlaceholder');
    
    try {
        const canvas = cropperInstance.getCroppedCanvas({
            width: 300,
            height: 300,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        if (previewEl) {
            previewEl.src = canvas.toDataURL('image/jpeg', 0.9);
            previewEl.style.display = 'block';
        }
        if (placeholder) {
            placeholder.style.display = 'none';
        }
    } catch(e) {
        console.warn('Preview update failed:', e);
    }
}

function applyCrop() {
    if (!cropperInstance) return;
    
    try {
        const canvas = cropperInstance.getCroppedCanvas({
            width: 512,
            height: 512,
            imageSmoothingEnabled: true,
            imageSmoothingQuality: 'high',
        });
        
        const croppedDataUrl = canvas.toDataURL('image/jpeg', 0.85);
        
        // Update preview in the parent form
        if (cropPreviewWrap) {
            // Remove any existing icon/placeholder children
            var existingImg = cropPreviewWrap.querySelector('img');
            var icon = cropPreviewWrap.querySelector('i.fas');
            var initials = cropPreviewWrap.querySelector('.avatar-initials');
            var placeholder = cropPreviewWrap.querySelector('.prsnl-photo-placeholder');
            
            if (existingImg) {
                existingImg.src = croppedDataUrl;
                existingImg.style.display = 'block';
            } else {
                // No img tag yet — create one
                var newImg = document.createElement('img');
                newImg.src = croppedDataUrl;
                newImg.alt = 'Profile photo';
                newImg.style.width = '100%';
                newImg.style.height = '100%';
                newImg.style.objectFit = 'cover';
                cropPreviewWrap.appendChild(newImg);
                // Track this img for future updates
                cropPreviewImg = newImg;
            }
            if (icon) icon.style.display = 'none';
            if (initials) initials.style.display = 'none';
            if (placeholder) placeholder.style.display = 'none';
        }
        
        // Set hidden input value
        if (cropHiddenInput) {
            cropHiddenInput.value = croppedDataUrl;
        }
        
        // Close modal
        var modalEl = document.getElementById('avatarCropModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        
        // Cleanup
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
    } catch(e) {
        console.error('Crop apply failed:', e);
        alert('Failed to process image. Please try again.');
    }
}

// Cleanup cropper on modal close
document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('avatarCropModal');
    if (!modalEl) return;
    
    modalEl.addEventListener('hidden.bs.modal', function() {
        if (cropperInstance) {
            cropperInstance.destroy();
            cropperInstance = null;
        }
    });
});
</script>
@endpush
