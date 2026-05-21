let selectedFiles = [];
let removedExistingFiles = [];

// Exposed globally so updatePhotoError can call it
let updateSubmitState = function () {};

document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('.post-calc-trigger');
    const priceDisplay = document.getElementById('post_total_display');
    const isEditMode = document.querySelector('input[name="r_product_id"]') !== null;

    const widthInput = document.querySelector('input[name="width"]');
    const heightInput = document.querySelector('input[name="height"]');

    function runCalculation(isInitialLoad = false) {
        if (isEditMode && isInitialLoad) return;

        let widthValue = parseFloat(widthInput?.value) || 0;
        let heightValue = parseFloat(heightInput?.value) || 0;

        let frameDesignPrice = 0;
        let frameTypePrice = 0;

        selects.forEach(s => {
            const selectedOption = s.options[s.selectedIndex];
            const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;

            if (s.name === 'frame_design_id') frameDesignPrice = price;
            if (s.name === 'frame_type_id') frameTypePrice = price;
        });

        let total = (((widthValue + heightValue) / 6) * frameDesignPrice) + frameTypePrice;

        if (priceDisplay) {
            priceDisplay.value = total.toFixed(2);
        }
    }

    selects.forEach(s => s.addEventListener('change', () => runCalculation(false)));
    if (widthInput) widthInput.addEventListener('input', () => runCalculation(false));
    if (heightInput) heightInput.addEventListener('input', () => runCalculation(false));
    runCalculation(true);

    const postSubmitBtn = document.querySelector('button[name="add_product"], button[name="update_product"]');

    // Override the global so updatePhotoError and loadExistingPhotos can call it
    updateSubmitState = function () {
        if (!postSubmitBtn) return;

        const hasNegative = Array.from(document.querySelectorAll('input[type="number"]'))
            .some(input => parseFloat(input.value) < 0);

        const isNewPost = !!document.getElementById('post_img_input');
        const isEdit = !!document.getElementById('edit_design_imgs');

        let missingPhotos = false;

        if (isNewPost || isEdit) {
            missingPhotos = selectedFiles.length === 0;
        }

        if (hasNegative || missingPhotos) {
            postSubmitBtn.disabled = true;
            postSubmitBtn.style.opacity = '0.55';
            postSubmitBtn.style.cursor = 'not-allowed';
        } else {
            postSubmitBtn.disabled = false;
            postSubmitBtn.style.opacity = '';
            postSubmitBtn.style.cursor = '';
        }
    };

    // Show photo error only on new post form, not edit
    const isNewPostForm = !!document.getElementById('post_img_input');
    if (isNewPostForm) {
        updatePhotoError(false);
    }

    // Run initial state check
    updateSubmitState();

    function setupNegativeNumberValidation() {
        const numberInputs = document.querySelectorAll('input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('input', function () {
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
                updateSubmitState();
            });
        });
    }

    setupNegativeNumberValidation();
});

// ─── PHOTO LOADING (Edit mode) ────────────────────────────────────────────────

function loadExistingPhotos(images, containerId, textId, inputId) {
    images.forEach(img => {
        selectedFiles.push({
            isExisting: true,
            image_name: img.image_name,
            url: '/rga_frames/uploads/' + img.image_name,
            is_primary: img.is_primary == 1
        });
    });
    renderPreviews(containerId, textId, inputId);
    updateSubmitState(); // ← Unlock button after existing photos are loaded
}

// ─── FILE INPUT HANDLER ───────────────────────────────────────────────────────

function handleMultipleFilePreview(input, containerId, textId) {
    if (input.files && input.files.length > 0) {
        const newFiles = Array.from(input.files);
        selectedFiles = selectedFiles.concat(newFiles);
        input.value = "";
    }
    renderPreviews(containerId, textId, input.id);
}

// ─── PHOTO ERROR UI ───────────────────────────────────────────────────────────

function updatePhotoError(hasPhotos) {
    const uploadZone = document.querySelector('.post-upload-zone');
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

    updateSubmitState();
}

// ─── RENDER PREVIEWS ──────────────────────────────────────────────────────────

function renderPreviews(containerId, textId, inputId) {
    const previewContainer = document.getElementById(containerId);
    const textElement = document.getElementById(textId);
    const fileInput = document.getElementById(inputId);

    if (!previewContainer) return;
    previewContainer.innerHTML = '';

    if (selectedFiles.length > 0) {
        textElement.innerText = `${selectedFiles.length} images total`;
        updatePhotoError(true);

        const hasExistingPrimary = selectedFiles.some(f => f.isExisting && f.is_primary);

        selectedFiles.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "preview-wrapper";
            wrapper.style.cssText = "width: 80px; height: 80px; position: relative; display: inline-block; margin: 8px; border: 1px solid #ccc; border-radius: 6px; background: white; pointer-events: auto;";

            const isPrimary = file.is_primary || (!hasExistingPrimary && index === 0);

            const primaryBadge = isPrimary
                ? `<span style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,64,48,0.9); color:white; font-size:10px; text-align:center; padding:2px 0; z-index:5; border-radius: 0 0 6px 6px;">Primary</span>`
                : '';

            wrapper.innerHTML = `
                ${primaryBadge}
                <button type="button"
                    onclick="removeImage(event, ${index}, '${containerId}', '${textId}', '${inputId}')"
                    style="position:absolute; top:-10px; right:-10px; background:#000; color:#fff; border:2px solid #fff; border-radius:50%; width:24px; height:24px; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center; font-size:16px;">
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
        textElement.innerText = "Click to upload photos";
        updatePhotoError(false);
    }

    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        if (!file.isExisting) dataTransfer.items.add(file);
    });
    fileInput.files = dataTransfer.files;

    const removedInput = document.getElementById('removed_images');
    if (removedInput) {
        removedInput.value = JSON.stringify(removedExistingFiles);
    }
}

// ─── REMOVE IMAGE ─────────────────────────────────────────────────────────────

function removeImage(event, index, containerId, textId, inputId) {
    event.stopPropagation();
    const removedFile = selectedFiles[index];

    if (removedFile.isExisting) {
        removedExistingFiles.push(removedFile.image_name);
    }

    selectedFiles.splice(index, 1);
    renderPreviews(containerId, textId, inputId);
}

// ─── DELETE CONFIRMATION ──────────────────────────────────────────────────────

function confirmDelete(id, name) {
    Swal.fire({
        title: 'Delete this product?',
        html: `Are you sure you want to delete <strong>${name}</strong>? This action cannot be undone.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: '<i class="fas fa-trash"></i> Yes, Delete',
        cancelButtonText: 'Cancel',
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `../process/postframe_process.php?action=delete&id=${id}`;
        }
    });
}
