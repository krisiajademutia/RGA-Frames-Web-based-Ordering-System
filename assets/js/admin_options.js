/**
 * Upload Logic for Frame Options
 */

let selectedFiles = []; // Used for multi-photo Frame Designs

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

function confirmDelete(id, name, tab) {
    document.getElementById('deleteOptionName').textContent = name;
    document.getElementById('deleteOptionId').value = id;
    document.getElementById('deleteOptionTab').value = tab;
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

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
        // We push a "Proxy" object into the array
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

            // Determine if it's the primary image
            const isPrimary = file.isExisting ? file.is_primary : (index === 0 && !selectedFiles.some(f => f.is_primary));
            const primaryBadge = isPrimary ? 
                `<span style="position:absolute; bottom:0; left:0; right:0; background:rgba(6,58,50,0.9); color:white; font-size:10px; text-align:center; padding:2px 0; z-index:5; border-radius: 0 0 6px 6px;">Primary</span>` : '';

            // If existing image, add hidden input for the PHP backend
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

            // If it's a new file, read it
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

    // Sync only NEW files to the file input
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