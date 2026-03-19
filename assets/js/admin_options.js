/**
 * Upload Logic for Frame Options
 */

let selectedFiles = []; // Used for multi-photo Frame Designs

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // --- Automatic Price Calculation & Validation Logic ---
    const widthInput = document.getElementById('fpm_width');
    const heightInput = document.getElementById('fpm_height');
    const paperSelect = document.getElementById('fpm_paper_type');
    const priceInput = document.getElementById('fpm_price');
    const submitBtn = document.getElementById('fpm_submit_btn');

    if (widthInput && heightInput && paperSelect && priceInput) {
        const calculateAndValidate = () => {
            const width = parseFloat(widthInput.value) || 0;
            const height = parseFloat(heightInput.value) || 0;
            const selected = paperSelect.options[paperSelect.selectedIndex];
            
            if (!selected || selected.value === "") return;

            // Get range values from dataset (passed from PHP)
            const minW = parseFloat(selected.dataset.minW) || 0;
            const maxW = parseFloat(selected.dataset.maxW) || 0;
            const minH = parseFloat(selected.dataset.minH) || 0;
            const maxH = parseFloat(selected.dataset.maxH) || 0;
            const multiplier = parseFloat(selected.dataset.multiplier) || 0;

            let isValid = true;

            // Width Debug Logic
            const wErr = document.getElementById('width_err');
            if (width > 0) {
                if (width < minW) {
                    wErr.innerText = `below minimum width (${minW}")`;
                    isValid = false;
                } else if (width > maxW) {
                    wErr.innerText = `above maximum width (${maxW}")`;
                    isValid = false;
                } else {
                    wErr.innerText = "";
                }
            } else {
                wErr.innerText = "";
            }

            // Height Debug Logic
            const hErr = document.getElementById('height_err');
            if (height > 0) {
                if (height < minH) {
                    hErr.innerText = `below minimum height (${minH}")`;
                    isValid = false;
                } else if (height > maxH) {
                    hErr.innerText = `above maximum height (${maxH}")`;
                    isValid = false;
                } else {
                    hErr.innerText = "";
                }
            } else {
                hErr.innerText = "";
            }

            // --- BUTTON STATE LOGIC (GRAY AND UNCLICKABLE) ---
            if (submitBtn) {
                if (!isValid) {
                    submitBtn.disabled = true;
                    submitBtn.style.backgroundColor = "#cccccc"; 
                    submitBtn.style.borderColor = "#cccccc";
                    submitBtn.style.color = "#666666";
                    submitBtn.style.cursor = "not-allowed";
                    submitBtn.style.opacity = "0.7";
                    priceInput.value = ""; // Clear price if dimensions are invalid
                } else {
                    submitBtn.disabled = false;
                    submitBtn.style.backgroundColor = ""; // Reset to CSS defaults
                    submitBtn.style.borderColor = "";
                    submitBtn.style.color = "";
                    submitBtn.style.cursor = "pointer";
                    submitBtn.style.opacity = "1";
                    
                    // Calculate price only if both dimensions exist and are valid
                    if (width > 0 && height > 0 && multiplier > 0) {
                        const total = width * height * multiplier;
                        priceInput.value = total.toFixed(2);
                    }
                }
            }
        };

        widthInput.addEventListener('input', calculateAndValidate);
        heightInput.addEventListener('input', calculateAndValidate);
        paperSelect.addEventListener('change', calculateAndValidate);
    }

    // Modal scroll fix for nested modals
    const deleteFpmModalEl = document.getElementById('deleteFixedPriceModal');
    if (deleteFpmModalEl) {
        deleteFpmModalEl.addEventListener('hidden.bs.modal', function () {
            if (document.querySelector('#fixedPriceModal.show')) {
                document.body.classList.add('modal-open');
                document.body.style.overflow = 'hidden';
            }
        });
    }

    // Clean URL params after notifications
    if (window.history.replaceState) {
        const url = new URL(window.location.href);
        if (url.searchParams.has('success') || url.searchParams.has('error')) {
            url.searchParams.delete('success');
            url.searchParams.delete('error');
            window.history.replaceState(null, null, url.href);
        }
    }
});

/**
 * STANDARD DELETE (For Frame Types, Colors, etc.)
 */
function confirmDelete(id, name, tab) {
    document.getElementById('deleteOptionName').textContent = name;
    document.getElementById('deleteOptionId').value = id;
    document.getElementById('deleteOptionTab').value = tab;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

/**
 * FIXED PRICE DELETE
 */
function confirmDeleteFpm(id) {
    const idInput = document.getElementById('delete_fpm_id');
    if (idInput) {
        idInput.value = id;
    }
    const modal = new bootstrap.Modal(document.getElementById('deleteFixedPriceModal'));
    modal.show();
}

/**
 * SINGLE UPLOAD PREVIEW
 */
function showExistingImage(imgUrl, containerId, textId, inputId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const uploadZone = container.closest('.opt-upload-zone');
    
    container.style.cssText = "position:absolute; top:0; left:0; width:100%; height:100%; z-index:10; background:#fff; border-radius:8px; overflow:hidden;";

    container.innerHTML = `
        <div class="preview-wrapper" style="width: 100%; height: 100%; position: relative; pointer-events: auto;">
            <button type="button" 
                onclick="removeSingleImage(event, '${inputId}', '${containerId}', '${textId}')" 
                style="position:absolute; top:8px; right:8px; background:rgba(0,0,0,0.6); color:#fff; border:1px solid rgba(255,255,255,0.3); border-radius:50%; width:28px; height:28px; cursor:pointer; z-index:20; display:flex; align-items:center; justify-content:center; font-size: 18px;">
                &times;
            </button>
            <img src="${imgUrl}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
        </div>`;

    const uploadContent = uploadZone.querySelector('.upload-content');
    if (uploadContent) uploadContent.style.visibility = 'hidden';
}

function handleSingleFilePreview(input, containerId, textId) {
    const container = document.getElementById(containerId);
    const uploadZone = container.closest('.opt-upload-zone');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            container.style.cssText = "position:absolute; top:0; left:0; width:100%; height:100%; z-index:10; background:#fff; border-radius:8px; overflow:hidden;";
            container.innerHTML = `
                <div class="preview-wrapper" style="width: 100%; height: 100%; position: relative; pointer-events: auto;">
                    <button type="button" 
                        onclick="removeSingleImage(event, '${input.id}', '${containerId}', '${textId}')" 
                        style="position:absolute; top:8px; right:8px; background:rgba(0,0,0,0.6); color:#fff; border:1px solid rgba(255,255,255,0.3); border-radius:50%; width:28px; height:28px; cursor:pointer; z-index:20; display:flex; align-items:center; justify-content:center; font-size: 18px;">
                        &times;
                    </button>
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                </div>`;
            
            const uploadContent = uploadZone.querySelector('.upload-content');
            if (uploadContent) uploadContent.style.visibility = 'hidden';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeSingleImage(event, inputId, containerId, textId) {
    event.stopPropagation();
    const container = document.getElementById(containerId);
    const uploadZone = container.closest('.opt-upload-zone');
    
    document.getElementById(inputId).value = "";
    container.innerHTML = "";
    container.style.zIndex = '-1'; 

    const uploadContent = uploadZone.querySelector('.upload-content');
    if (uploadContent) uploadContent.style.visibility = 'visible';
}

/**
 * MULTI-FILE LOGIC (Frame Designs)
 */
function loadExistingPhotos(images, containerId, textId, inputId) {
    images.forEach(img => {
        selectedFiles.push({
            isExisting: true,
            image_name: img.image_name,
            url: '../uploads/' + img.image_name,
            is_primary: img.is_primary == 1
        });
    });
    renderPreviews(containerId, textId, inputId);
}

function handleMultipleFilePreview(input, containerId, textId) {
    if (input.files && input.files.length > 0) {
        const newFiles = Array.from(input.files);
        selectedFiles = selectedFiles.concat(newFiles);
        input.value = "";
    }
    renderPreviews(containerId, textId, input.id);
}

function renderPreviews(containerId, textId, inputId) {
    const previewContainer = document.getElementById(containerId);
    const textElement = document.getElementById(textId);
    const fileInput = document.getElementById(inputId);

    if (!previewContainer) return;
    previewContainer.innerHTML = '';

    if (selectedFiles.length > 0) {
        textElement.innerText = `${selectedFiles.length} images selected`;
        const hasExistingPrimary = selectedFiles.some(f => f.is_primary === true);

        selectedFiles.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "preview-wrapper";
            wrapper.style.cssText = "width: 80px; height: 80px; position: relative; display: inline-block; margin: 8px; border: 1px solid #ccc; border-radius: 6px; background: white; pointer-events: auto;";

            const isPrimary = file.is_primary || (!hasExistingPrimary && index === 0);
            file.is_primary = isPrimary;

            const primaryBadge = isPrimary ? 
                `<span style="position:absolute; bottom:0; left:0; right:0; background:rgba(6,58,50,0.9); color:white; font-size:10px; text-align:center; padding:2px 0; z-index:5; border-radius: 0 0 6px 6px;">Primary</span>` : '';

            const primaryInput = isPrimary && file.isExisting ? `<input type="hidden" name="primary_existing_image" value="${file.image_name}">` : '';
            const hiddenInput = file.isExisting ? `<input type="hidden" name="existing_images[]" value="${file.image_name}">` : '';

            wrapper.innerHTML = `
                ${hiddenInput}
                ${primaryInput}
                ${primaryBadge}
                <button type="button" 
                    onclick="removeImage(event, ${index}, '${containerId}', '${textId}', '${inputId}')" 
                    style="position:absolute; top:-10px; right:-10px; background:#000; color:#fff; border:2px solid #fff; border-radius:50%; width:24px; height:24px; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center;">
                    &times;
                </button>
                <img src="${file.isExisting ? file.url : ''}" id="img-preview-${index}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
            `;
            
            previewContainer.appendChild(wrapper);

            if (!file.isExisting) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = document.getElementById(`img-preview-${index}`);
                    if (img) img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    } else {
        textElement.innerText = "Click to upload multiple photos";
    }

    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        if (!file.isExisting) dataTransfer.items.add(file);
    });
    fileInput.files = dataTransfer.files;
}

function removeImage(event, index, containerId, textId, inputId) {
    event.stopPropagation();
    selectedFiles.splice(index, 1);
    
    if (selectedFiles.length > 0 && !selectedFiles.some(f => f.is_primary === true)) {
        selectedFiles[0].is_primary = true;
    }
    renderPreviews(containerId, textId, inputId);
}

/**
 * FIXED PRICE MANAGEMENT
 */
function editFpm(data) {
    document.getElementById('fpm_action').value = 'update_fixed_price';
    document.getElementById('fpm_id').value = data.fixed_price_id;
    document.getElementById('fpm_paper_type').value = data.paper_type_id;
    document.getElementById('fpm_dimension').value = data.dimension;
    document.getElementById('fpm_width').value = data.width_inch;
    document.getElementById('fpm_height').value = data.height_inch;
    document.getElementById('fpm_price').value = data.fixed_price;
    document.getElementById('form_title').innerText = 'Edit Pricing Record';
    document.getElementById('fpm_submit_btn').innerText = 'Update Price';
    document.getElementById('fpm_cancel_btn').style.display = 'block';
    
    // Trigger calculation/validation to ensure correct button state for the loaded record
    const event = new Event('input');
    document.getElementById('fpm_width').dispatchEvent(event);
    
    document.getElementById('fixedPriceForm').scrollIntoView({ behavior: 'smooth' });
}

function resetFpmForm() {
    document.getElementById('fixedPriceForm').reset();
    document.getElementById('fpm_action').value = 'add_fixed_price';
    document.getElementById('fpm_id').value = '';
    document.getElementById('form_title').innerText = 'Add New Pricing';
    document.getElementById('fpm_submit_btn').innerText = 'Add Entry';
    document.getElementById('fpm_cancel_btn').style.display = 'none';
    
    // Reset validation errors
    document.getElementById('width_err').innerText = "";
    document.getElementById('height_err').innerText = "";
    
    // Reset button state
    const btn = document.getElementById('fpm_submit_btn');
    btn.disabled = false;
    btn.style.backgroundColor = "";
    btn.style.opacity = "1";
    btn.style.cursor = "pointer";
}