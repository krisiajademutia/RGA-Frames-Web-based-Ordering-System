let selectedFiles = [];

document.addEventListener('DOMContentLoaded', () => {
    const selects = document.querySelectorAll('.post-calc-trigger');
    const priceDisplay = document.getElementById('post_total_display');

    function runCalculation() {
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
        s.addEventListener('change', runCalculation);
    });

    runCalculation();
});

function handleMultipleFilePreview(input) {
    if (input.files && input.files.length > 0) {
        const newFiles = Array.from(input.files);
        selectedFiles = selectedFiles.concat(newFiles);
        input.value = "";
    }
    renderPreviews();
}

function renderPreviews() {
    const previewContainer = document.getElementById('image_preview_container');
    const textElement = document.getElementById('post_img_text');
    const fileInput = document.getElementById('post_img_input');

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

function removeImage(event, index) {
    event.stopPropagation();
    selectedFiles.splice(index, 1);
    renderPreviews();
}

function confirmDelete(id, name) {
    const modalElement = document.getElementById('deleteConfirmModal');
    if (!modalElement) return;

    const deleteModal = new bootstrap.Modal(modalElement);
    document.getElementById('deleteProductName').innerText = name;
    document.getElementById('confirmDeleteLink').href = `/rga_frames/process/postframe_process.php?action=delete&id=${id}`;
    deleteModal.show();
}