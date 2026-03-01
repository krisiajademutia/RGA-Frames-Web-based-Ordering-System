/**
 * RGA Frames - Admin Post Management Script
 */

document.addEventListener('DOMContentLoaded', () => {
    // --- Price Calculation Logic ---
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

function handlePostFileChange(input) {
    const textElement = document.getElementById('post_img_text');
    if (input.files && input.files[0]) {
        textElement.innerHTML = `<span style="color: #0F473A; font-weight: 600;">Selected: ${input.files[0].name}</span>`;
    } else {
        textElement.innerText = "Click to upload product photo";
    }
}

/**
 * Triggers the Custom Styled Delete Confirmation Modal
 */
function confirmDelete(id, name) {
    const modalElement = document.getElementById('deleteConfirmModal');
    if (!modalElement) return;

    // Initialize the modal
    const deleteModal = new bootstrap.Modal(modalElement);
    
    // Inject data
    document.getElementById('deleteProductName').innerText = name;
    document.getElementById('confirmDeleteLink').href = `/rga_frames/process/postframe_process.php?action=delete&id=${id}`;
    
    // Show the modal
    deleteModal.show();
}