/**
 * Multi-photo Upload Logic for Frame Designs
 */

let selectedFiles = [];

document.addEventListener('DOMContentLoaded', () => {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
});
document.querySelectorAll('.opt-btn-edit').forEach(button => {
    button.addEventListener('click', function() {
        // Find the record ID from the parent row
        const row = this.closest('.opted-row-item');
        const recordId = row.id.replace('row-', '');
        
        // Get the current active tab from the URL
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'frame_types';
        
        // Redirect to the edit URL
        window.location.href = `?tab=${activeTab}&action=edit&id=${recordId}#form-section`;
    });
});

// Auto-scroll if editing is active on page load
window.addEventListener('load', () => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('action') === 'edit') {
        const formElement = document.getElementById('form-section');
        if (formElement) {
            formElement.scrollIntoView({ behavior: 'smooth' });
        }
    }
});
function toggleEditForm(id) {
    const form = document.getElementById('edit-form-' + id);
    if (form) form.classList.toggle('open');
}

function confirmDelete(id, name, tab) {
    document.getElementById('deleteOptionName').textContent = name;
    document.getElementById('deleteOptionId').value = id;
    document.getElementById('deleteOptionTab').value = tab;
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

function handleMultipleFilePreview(input, containerId, textId) {
    if (input.files && input.files.length > 0) {
        const newFiles = Array.from(input.files);
        selectedFiles = selectedFiles.concat(newFiles);
        // Clear input so change event triggers even for same file
        input.value = "";
    }
    renderPreviews(containerId, textId, input.id);
}

function renderPreviews() {
    const previewContainer = document.getElementById('image_preview_container');
    const textElement = document.getElementById('opt_img_text');
    const fileInput = document.getElementById('add_design_imgs');

    if (!previewContainer) return;

    previewContainer.innerHTML = '';

    if (selectedFiles.length > 0) {
        textElement.innerText = `${selectedFiles.length} images selected`;

        selectedFiles.forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const wrapper = document.createElement('div');
                wrapper.className = "preview-wrapper";
                wrapper.style.cssText = "width: 80px; height: 80px; position: relative; display: inline-block; margin: 8px; border: 1px solid #ccc; border-radius: 6px; overflow: visible; background: white; pointer-events: auto;";

                const primaryBadge = index === 0 ? 
                    `<span style="position:absolute; bottom:0; left:0; right:0; background:rgba(6,58,50,0.9); color:white; font-size:10px; text-align:center; padding:2px 0; z-index:5; border-radius: 0 0 6px 6px; pointer-events: none;">Primary</span>` : '';

                wrapper.innerHTML = `
                    ${primaryBadge}
                    <button type="button" 
                        onclick="removeImage(event, ${index})" 
                        style="position:absolute; top:-10px; right:-10px; background:#000000; color:#FFFFFF; border:2px solid #FFFFFF; border-radius:50%; width:28px; height:28px; font-size:16px; font-weight:bold; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center; box-shadow: 0 2px 4px rgba(0,0,0,0.3);">
                        &times;
                    </button>
                    <img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 6px; pointer-events: none;">
                `;
                previewContainer.appendChild(wrapper);
            };
            reader.readAsDataURL(file);
        });
    } else {
        textElement.innerText = "Click to upload product photo";
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
