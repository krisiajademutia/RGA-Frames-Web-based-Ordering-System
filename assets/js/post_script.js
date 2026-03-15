let selectedFiles = []; // For NEW file objects
let removedExistingFiles = []; // For filenames to be deleted from DB

document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('.post-calc-trigger');
    const priceDisplay = document.getElementById('post_total_display');
    const isEditMode = document.querySelector('input[name="r_product_id"]') !== null;

    function runCalculation(isInitialLoad = false) {
        if (isEditMode && isInitialLoad) return;

        let total = 0;
        selects.forEach(s => {
            const selectedOption = s.options[s.selectedIndex];
            const price = parseFloat(selectedOption?.getAttribute('data-price')) || 0;
            total += price;
        });

        if (priceDisplay) {
            priceDisplay.value = total.toFixed(2);
        }
    }

    selects.forEach(s => {
        s.addEventListener('change', () => runCalculation(false));
    });

    runCalculation(true);

    // Trigger Success Modal
    const trigger = document.getElementById('triggerSuccessModal');
    if (trigger) {
        const successModal = new bootstrap.Modal(document.getElementById('successOperationModal'));
        successModal.show();
    }
});

/**
 * Loads images from database into the preview array
 */
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
        textElement.innerText = `${selectedFiles.length} images total`;
        
        // Reset primary logic: if no existing image is currently marked primary, 
        // the first one in the list (index 0) becomes primary.
        const hasExistingPrimary = selectedFiles.some(f => f.isExisting && f.is_primary);

        selectedFiles.forEach((file, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = "preview-wrapper";
            wrapper.style.cssText = "width: 80px; height: 80px; position: relative; display: inline-block; margin: 8px; border: 1px solid #ccc; border-radius: 6px; background: white; pointer-events: auto;";

            // LOGIC: Use existing primary status OR assign to index 0 if no primary exists
            const isPrimary = file.is_primary || (!hasExistingPrimary && index === 0);
            
            const primaryBadge = isPrimary ? 
                `<span style="position:absolute; bottom:0; left:0; right:0; background:rgba(0,64,48,0.9); color:white; font-size:10px; text-align:center; padding:2px 0; z-index:5; border-radius: 0 0 6px 6px;">Primary</span>` : '';

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
    }

    // Update hidden input for file uploads
    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => {
        if (!file.isExisting) dataTransfer.items.add(file);
    });
    fileInput.files = dataTransfer.files;

    // Update hidden input for existing image tracking
    const removedInput = document.getElementById('removed_images');
    if(removedInput) {
        removedInput.value = JSON.stringify(removedExistingFiles);
    }
}

function removeImage(event, index, containerId, textId, inputId) {
    event.stopPropagation();
    const removedFile = selectedFiles[index];

    if (removedFile.isExisting) {
        removedExistingFiles.push(removedFile.image_name);
    }

    selectedFiles.splice(index, 1);
    
    // If we removed the primary image, we need to ensure the new index 0 becomes primary
    // if there are still images left.
    renderPreviews(containerId, textId, inputId);
}

function confirmDelete(id, name) {
    const modalElement = document.getElementById('deleteConfirmModal');
    if (!modalElement) return;
    document.getElementById('deleteProductName').innerText = name;
    document.getElementById('confirmDeleteLink').href = `../process/postframe_process.php?action=delete&id=${id}`;
    
    const deleteModal = new bootstrap.Modal(modalElement);
    deleteModal.show();
}