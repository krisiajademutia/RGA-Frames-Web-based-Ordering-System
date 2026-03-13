/**
 * Upload Logic for Frame Options
 */

let selectedFiles = []; // Used for multi-photo Frame Designs

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});

document.querySelectorAll('.opt-btn-edit').forEach(button => {
    button.addEventListener('click', function() {
        const row = this.closest('.opted-row-item');
        const recordId = row.id.replace('row-', '');
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'frame_types';
        window.location.href = `?tab=${activeTab}&action=edit&id=${recordId}#form-section`;
    });
});

window.addEventListener('load', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'edit') {
        const formElement = document.getElementById('form-section');
        if (formElement) {
            formElement.scrollIntoView({ behavior: 'smooth' });
        }
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
 * SINGLE FILE PREVIEW (Frame Types, Colors, Matboards)
 */
function handleSingleFilePreview(input, containerId, textId) {
    const container = document.getElementById(containerId);
    const textElement = document.getElementById(textId);
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            container.innerHTML = `
                <div class="preview-wrapper" style="width: 100px; height: 100px; position: relative; margin: 0 auto; pointer-events: auto;">
                    <button type="button" 
                        onclick="removeSingleImage(event, '${input.id}', '${containerId}', '${textId}')" 
                        style="position:absolute; top:-10px; right:-10px; background:#000; color:#fff; border:2px solid #fff; border-radius:50%; width:24px; height:24px; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center;">
                        &times;
                    </button>
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                </div>`;
            textElement.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeSingleImage(event, inputId, containerId, textId) {
    event.stopPropagation();
    document.getElementById(inputId).value = "";
    document.getElementById(containerId).innerHTML = "";
    document.getElementById(textId).style.display = 'block';
    document.getElementById(textId).innerText = "Click to upload photo";
}

/**
 * MULTI-FILE PREVIEW (Frame Designs Only)
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
            const reader = new FileReader();
            reader.onload = (e) => {
                const wrapper = document.createElement('div');
                wrapper.className = "preview-wrapper";
                wrapper.style.cssText = "width: 80px; height: 80px; position: relative; display: inline-block; margin: 8px; border: 1px solid #ccc; border-radius: 6px; background: white; pointer-events: auto;";

                const primaryBadge = index === 0 ? 
                    `<span style="position:absolute; bottom:0; left:0; right:0; background:rgba(6,58,50,0.9); color:white; font-size:10px; text-align:center; padding:2px 0; z-index:5; border-radius: 0 0 6px 6px;">Primary</span>` : '';

                wrapper.innerHTML = `
                    ${primaryBadge}
                    <button type="button" 
                        onclick="removeImage(event, ${index}, '${containerId}', '${textId}', '${inputId}')" 
                        style="position:absolute; top:-10px; right:-10px; background:#000; color:#fff; border:2px solid #fff; border-radius:50%; width:24px; height:24px; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center;">
                        &times;
                    </button>
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px;">
                `;
                previewContainer.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    } else {
        textElement.innerText = "Click to upload multiple photos";
    }

    const dataTransfer = new DataTransfer();
    selectedFiles.forEach(file => dataTransfer.items.add(file));
    fileInput.files = dataTransfer.files;
}

function removeImage(event, index, containerId, textId, inputId) {
    event.stopPropagation();
    selectedFiles.splice(index, 1);
    renderPreviews(containerId, textId, inputId);
}