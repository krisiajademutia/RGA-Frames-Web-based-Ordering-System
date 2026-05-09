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

            // Width validation
            const wErr = document.getElementById('width_err');
            if (widthInput.value !== '') {
                if (width < 0) {
                    wErr.innerText = `Must be a positive number.`;
                    widthInput.style.borderColor = '#dc3545';
                    isValid = false;
                } else if (width > 0 && width < minW) {
                    wErr.innerText = `below minimum width (${minW}")`;
                    widthInput.style.borderColor = '#dc3545';
                    isValid = false;
                } else if (width > maxW) {
                    wErr.innerText = `above maximum width (${maxW}")`;
                    widthInput.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    wErr.innerText = "";
                    widthInput.style.borderColor = '';
                }
            } else {
                wErr.innerText = "";
                widthInput.style.borderColor = '';
            }

            // Height validation
            const hErr = document.getElementById('height_err');
            if (heightInput.value !== '') {
                if (height < 0) {
                    hErr.innerText = `Must be a positive number.`;
                    heightInput.style.borderColor = '#dc3545';
                    isValid = false;
                } else if (height > 0 && height < minH) {
                    hErr.innerText = `below minimum height (${minH}")`;
                    heightInput.style.borderColor = '#dc3545';
                    isValid = false;
                } else if (height > maxH) {
                    hErr.innerText = `above maximum height (${maxH}")`;
                    heightInput.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    hErr.innerText = "";
                    heightInput.style.borderColor = '';
                }
            } else {
                hErr.innerText = "";
                heightInput.style.borderColor = '';
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

    const optionForms = document.querySelectorAll('form');

    function setupNegativeNumberValidation() {
        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('input', function () {
                // Skip inputs that already have their own dedicated error spans (fpm width/height)
                if (input.id === 'fpm_width' || input.id === 'fpm_height') return;

                let errorId = 'err-' + (input.name || input.id || Math.random().toString(36).substr(2, 9)).replace(/[^a-zA-Z0-9-]/g, '');
                if (!input.dataset.errId) {
                    input.dataset.errId = errorId;
                    const errDiv = document.createElement('div');
                    errDiv.id = errorId;
                    errDiv.className = 'text-danger negative-warning';
                    errDiv.style.fontSize = '12px';
                    errDiv.style.fontWeight = '600';
                    errDiv.style.marginTop = '4px';
                    errDiv.style.width = '100%';
                    errDiv.style.display = 'none';
                    errDiv.innerText = 'Must be a positive number.';
                    input.insertAdjacentElement('afterend', errDiv);
                }
                const errDiv = document.getElementById(input.dataset.errId);
                if (parseFloat(input.value) < 0) {
                    errDiv.style.display = 'block';
                    input.style.borderColor = '#dc3545';
                } else {
                    errDiv.style.display = 'none';
                    input.style.borderColor = '';
                }
                updateOptSubmitState();
            });
        });
    }
    setupNegativeNumberValidation();

    // On frame_designs new-entry form, disable submit and show error until a photo is added
    const isNewDesignForm = document.getElementById('add_design_imgs') !== null && selectedFiles.length === 0;
    if (isNewDesignForm) {
        updatePhotoError(false);
    }

    // On single-upload tabs (frame types, colors, matboard), show error on new entry forms
    const singleUploadIds = ['add_type_img', 'color_img', 'mat_img'];
    singleUploadIds.forEach(id => {
        const fileInput = document.getElementById(id);
        if (!fileInput) return;
        const uploadZone = fileInput.closest('.opt-upload-zone');
        const existingInput = document.querySelector('input[name="existing_image"]');
        const hasExisting = existingInput && existingInput.value;
        if (!hasExisting) {
            updateSinglePhotoError(uploadZone, false);
        }
    });

    optionForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            // Button is already disabled if there are errors; this is a safety net only
            const hasNegative = Array.from(form.querySelectorAll('input[type="number"]'))
                .some(input => parseFloat(input.value) < 0);
            if (hasNegative) {
                e.preventDefault();
                return;
            }
        });
    });
});


/**
 * STANDARD DELETE (For Frame Types, Colors, etc.)
 */
function confirmDelete(id, name, tab) {
    Swal.fire({
        title: 'Delete this option?',
        html: `Are you sure you want to delete <strong>${name}</strong>? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../process/posting_options.php';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="tab" value="${tab}">
                <input type="hidden" name="option_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

/**
 * FIXED PRICE DELETE
 */
function confirmDeleteFpm(id) {
    // Close the Bootstrap modal first so Swal can render on top
    const fpmModal = bootstrap.Modal.getInstance(document.getElementById('fixedPriceModal'));
    if (fpmModal) fpmModal.hide();

    setTimeout(() => {
        Swal.fire({
            title: 'Delete this price entry?',
            text: 'This action cannot be undone.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '../process/posting_options.php?tab=paper_types';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_fixed_price">
                    <input type="hidden" name="fixed_price_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            } else {

                const fpmModalEl = document.getElementById('fixedPriceModal');
                if (fpmModalEl) new bootstrap.Modal(fpmModalEl).show();
            }
        });
    }, 300);
}

/**
 * SINGLE UPLOAD PREVIEW
 */
function updateSinglePhotoError(uploadZone, hasPhoto) {
    if (!uploadZone) return;

    // Use the file input's id inside the zone as a stable unique key
    const fileInput = uploadZone.querySelector('input[type="file"]');
    const key = fileInput ? fileInput.id : Math.random().toString(36).substr(2, 9);
    const errorId = 'single-photo-error-' + key;

    let errEl = document.getElementById(errorId);
    if (!errEl) {
        errEl = document.createElement('div');
        errEl.id = errorId;
        errEl.className = 'text-danger';
        errEl.style.fontSize = '12px';
        errEl.style.fontWeight = '600';
        errEl.style.marginTop = '6px';
        errEl.innerText = 'One photo is required.';
        uploadZone.insertAdjacentElement('afterend', errEl);
    }

    if (!hasPhoto) {
        errEl.style.display = 'block';
        uploadZone.style.borderColor = '#dc3545';
    } else {
        errEl.style.display = 'none';
        uploadZone.style.borderColor = '';
    }

    updateOptSubmitState();
}

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
    updateSinglePhotoError(uploadZone, true);
}

function handleSingleFilePreview(input, containerId, textId) {
    const container = document.getElementById(containerId);
    const uploadZone = container.closest('.opt-upload-zone');

    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
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
            updateSinglePhotoError(uploadZone, true);
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
    updateSinglePhotoError(uploadZone, false);
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

function updateOptSubmitState() {
    const submitBtn = document.querySelector('button[name="add_option"], button[name="update_option"]');
    if (!submitBtn) return;
    const hasNegative = Array.from(document.querySelectorAll('input[type="number"]'))
        .some(input => parseFloat(input.value) < 0);

    // Multi-upload (frame designs)
    const missingMulti = document.getElementById('add_design_imgs') !== null && selectedFiles.length === 0;

    // Single-upload tabs: check if a file input exists but has no file and no existing image hidden value
    const singleInputIds = ['add_type_img', 'color_img', 'mat_img'];
    const missingSingle = singleInputIds.some(id => {
        const fileInput = document.getElementById(id);
        if (!fileInput) return false;
        // Has a newly selected file
        if (fileInput.files && fileInput.files.length > 0) return false;
        // Has an existing image loaded (hidden input sibling)
        const existingInput = document.querySelector('input[name="existing_image"]');
        if (existingInput && existingInput.value) return false;
        return true;
    });

    if (hasNegative || missingMulti || missingSingle) {
        submitBtn.disabled = true;
        submitBtn.style.opacity = '0.55';
        submitBtn.style.cursor = 'not-allowed';
    } else {
        submitBtn.disabled = false;
        submitBtn.style.opacity = '';
        submitBtn.style.cursor = '';
    }
}

function updatePhotoError(hasPhotos) {
    const uploadZone = document.querySelector('.opt-upload-zone');
    if (!uploadZone) return;

    let errEl = document.getElementById('photo-upload-error');
    if (!errEl) {
        errEl = document.createElement('div');
        errEl.id = 'photo-upload-error';
        errEl.className = 'text-danger';
        errEl.style.fontSize = '12px';
        errEl.style.fontWeight = '600';
        errEl.style.marginTop = '6px';
        errEl.innerText = 'At least one photo is required.';
        uploadZone.insertAdjacentElement('afterend', errEl);
    }

    if (!hasPhotos) {
        errEl.style.display = 'block';
        uploadZone.style.borderColor = '#dc3545';
    } else {
        errEl.style.display = 'none';
        uploadZone.style.borderColor = '';
    }

    updateOptSubmitState();
}

function renderPreviews(containerId, textId, inputId) {
    const previewContainer = document.getElementById(containerId);
    const textElement = document.getElementById(textId);
    const fileInput = document.getElementById(inputId);

    if (!previewContainer) return;
    previewContainer.innerHTML = '';

    if (selectedFiles.length > 0) {
        textElement.innerText = `${selectedFiles.length} images selected`;
        updatePhotoError(true);
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
        updatePhotoError(false);
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