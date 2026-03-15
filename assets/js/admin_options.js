/**
 * Upload Logic for Frame Options
 */

let selectedFiles = []; // Used for multi-photo Frame Designs

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // --- Automatic Price Calculation Logic ---
    const widthInput = document.getElementById('fpm_width');
    const heightInput = document.getElementById('fpm_height');
    const paperSelect = document.getElementById('fpm_paper_type');
    const priceInput = document.getElementById('fpm_price');

    if (widthInput && heightInput && paperSelect && priceInput) {
        const calculateFpmPrice = () => {
            const width = parseFloat(widthInput.value) || 0;
            const height = parseFloat(heightInput.value) || 0;
            
            // Fetch multiplier from the data attribute of the selected option
            const selectedOption = paperSelect.options[paperSelect.selectedIndex];
            const multiplier = selectedOption ? parseFloat(selectedOption.getAttribute('data-multiplier')) : 0;

            if (width > 0 && height > 0 && multiplier > 0) {
                const total = width * height * multiplier;
                priceInput.value = total.toFixed(2);
            }
        };

        // Listen for changes in dimensions or paper selection
        widthInput.addEventListener('input', calculateFpmPrice);
        heightInput.addEventListener('input', calculateFpmPrice);
        paperSelect.addEventListener('change', calculateFpmPrice);
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
 * FIXED PRICE DELETE (Nested Modal Logic)
 */
function confirmDelete(id, name, tab) {
    document.getElementById('deleteOptionName').textContent = name;
    document.getElementById('deleteOptionId').value = id;
    document.getElementById('deleteOptionTab').value = tab;
    
    let nameInput = document.querySelector('input[name="option_name"]');
    if(!nameInput) {
        nameInput = document.createElement('input');
        nameInput.type = 'hidden';
        nameInput.name = 'option_name';
        document.getElementById('deleteOptionForm').appendChild(nameInput);
    }
    nameInput.value = name;

    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Fix for Bootstrap scroll lock issue when closing nested modals
document.addEventListener('DOMContentLoaded', () => {
    const deleteFpmModalEl = document.getElementById('deleteFixedPriceModal');
    if (deleteFpmModalEl) {
        deleteFpmModalEl.addEventListener('hidden.bs.modal', function () {
            if (document.querySelector('#fixedPriceModal.show')) {
                document.body.classList.add('modal-open');
            }
        });
    }
});

/**
 * SHOW EXISTING IMAGE (Used during Edit Mode for single uploads)
 */
function showExistingImage(imgUrl, containerId, textId, inputId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    const uploadZone = container.closest('.opt-upload-zone');
    
    container.style.position = 'absolute';
    container.style.top = '0';
    container.style.left = '0';
    container.style.width = '100%';
    container.style.height = '100%';
    container.style.zIndex = '10';
    container.style.backgroundColor = '#fff';
    container.style.borderRadius = '8px';
    container.style.overflow = 'hidden';

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

/**
 * LOADS EXISTING PHOTOS into the multi-previewer
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

/**
 * SINGLE FILE PREVIEW (New Uploads)
 */
function handleSingleFilePreview(input, containerId, textId) {
    const container = document.getElementById(containerId);
    const uploadZone = container.closest('.opt-upload-zone');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            container.style.position = 'absolute';
            container.style.top = '0';
            container.style.left = '0';
            container.style.width = '100%';
            container.style.height = '100%';
            container.style.zIndex = '10';
            container.style.backgroundColor = '#fff';
            container.style.borderRadius = '8px';
            container.style.overflow = 'hidden';

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
 * MULTI-FILE PREVIEW (Designs)
 */
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
        
        selectedFiles.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "preview-wrapper";
            wrapper.style.cssText = "width: 80px; height: 80px; position: relative; display: inline-block; margin: 8px; border: 1px solid #ccc; border-radius: 6px; background: white; pointer-events: auto;";

            const isPrimary = file.isExisting ? file.is_primary : (index === 0 && !selectedFiles.some(f => f.is_primary));
            const primaryBadge = isPrimary ? 
                `<span style="position:absolute; bottom:0; left:0; right:0; background:rgba(6,58,50,0.9); color:white; font-size:10px; text-align:center; padding:2px 0; z-index:5; border-radius: 0 0 6px 6px;">Primary</span>` : '';

            const hiddenInput = file.isExisting ? `<input type="hidden" name="existing_images[]" value="${file.image_name}">` : '';

            wrapper.innerHTML = `
                ${hiddenInput}
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
                    document.getElementById(`img-preview-${index}`).src = e.target.result;
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
    renderPreviews(containerId, textId, inputId);
}

/**
 * Populates the Fixed Price Management form (Edit mode)
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
    
    document.getElementById('fixedPriceForm').scrollIntoView({ behavior: 'smooth' });
}

/**
 * Resets the Fixed Price Management form
 */
function resetFpmForm() {
    document.getElementById('fixedPriceForm').reset();
    document.getElementById('fpm_action').value = 'add_fixed_price';
    document.getElementById('fpm_id').value = '';
    
    document.getElementById('form_title').innerText = 'Add New Pricing';
    document.getElementById('fpm_submit_btn').innerText = 'Add Entry';
    document.getElementById('fpm_cancel_btn').style.display = 'none';
}

/**
 * URL Cleanup: Removes success/error params from the address bar
 */
window.addEventListener('DOMContentLoaded', (event) => {
    if (window.history.replaceState) {
        const url = new URL(window.location.href);
        if (url.searchParams.has('success') || url.searchParams.has('error')) {
            url.searchParams.delete('success');
            url.searchParams.delete('error');
            window.history.replaceState(null, null, url.href);
        }
    }
});