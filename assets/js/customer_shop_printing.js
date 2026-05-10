// assets/js/customer_shop_printing.js

const paperSelect = document.getElementById('paper-type-select');
const sizeSelect = document.getElementById('size-select');
const customW = document.getElementById('custom-width');
const customH = document.getElementById('custom-height');
const totalInchInput = document.getElementById('total-inch-input');
const qtyInput = document.getElementById('qty-input');
const fileInput = document.getElementById('ps-file-input');
const imagePreview = document.getElementById('image-preview');
const uploadContent = document.getElementById('upload-content');
const previewWrapper = document.getElementById('preview-wrapper');
const btnCart = document.querySelector('.ps-btn-cart');
const btnBuyNow = document.querySelector('.ps-btn-buy-now');
const uploadArea = document.getElementById('upload-area');
const driveArea = document.getElementById('drive-area');
const driveLinkInput = document.getElementById('ps-drive-link');
const optUpload = document.getElementById('opt-upload');
const optDrive = document.getElementById('opt-drive');

// Toggle UI
if(optUpload && optDrive) {
    [optUpload, optDrive].forEach(radio => {
        radio.addEventListener('change', function() {
            if(optUpload.checked) {
                uploadArea.classList.remove('d-none');
                driveArea.classList.add('d-none');
            } else {
                uploadArea.classList.add('d-none');
                driveArea.classList.remove('d-none');
            }
        });
    });
}

function showToast(message, type = 'success') {
    const existing = document.querySelector('#csc-toast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.id = 'csc-toast';
    
    // Using left/right 0 and margin auto perfectly centers it without fighting the animation
    toast.style.cssText = [
        'position:fixed', 
        'bottom:3rem', 
        'left:0', 
        'right:0',
        'margin:0 auto',
        'width:max-content',
        'max-width:90%',
        'z-index:9999',
        'background:' + (type === 'success' ? '#0F473A' : '#ef4444'),
        'color:#fff', 
        'padding:0.85rem 1.5rem', 
        'border-radius:10px',
        'font-size:0.9rem', 
        'font-weight:600',
        'box-shadow:0 4px 16px rgba(0,0,0,0.15)',
        'animation:fadeInUp 0.3s ease',
        'text-align:center'
    ].join(';');
    
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => toast.remove(), 3500);
}

function validateForm(showToastMsg = false) {
    let isValid = true;
    let errorMessage = "";

    const wErr = document.getElementById('width-error');
    const hErr = document.getElementById('height-error');
    const fileErr = document.getElementById('file-error');
    const driveErr = document.getElementById('drive-error');
    const paperErr = document.getElementById('paper-error');

    [wErr, hErr, fileErr, driveErr, paperErr].forEach(el => {
        if(el) {
            el.innerText = "";
            el.classList.add('d-none');
        }
    });
    customW.style.borderColor = '';
    customH.style.borderColor = '';

    if (optUpload && optUpload.checked) {
        if (!fileInput.files[0]) {
            fileErr.innerText = "Please select an image.";
            fileErr.classList.remove('d-none');
            isValid = false;
        }
    } else if (optDrive && optDrive.checked) {
        const linkVal = driveLinkInput.value.trim();
        if (!linkVal || !linkVal.startsWith('http')) {
            driveErr.innerText = "Please enter a valid Google Drive URL.";
            driveErr.classList.remove('d-none');
            isValid = false;
        }
    }

    if (paperSelect.selectedIndex === 0) {
        paperErr.innerText = "Please select a paper type.";
        paperErr.classList.remove('d-none');
        isValid = false;
    }

    const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
    const maxWidth = parseFloat(selectedOption?.dataset.maxwidth) || 0;
    const maxHeight = parseFloat(selectedOption?.dataset.maxheight) || 0;
    const userW = parseFloat(customW.value);
    const userH = parseFloat(customH.value);
    const paperNameText = paperSelect.selectedIndex > 0 ? paperSelect.options[paperSelect.selectedIndex].text.toLowerCase() : '';

   // 3. MAXIMUM WIDTH CHECK
    if (!customW.value || userW <= 0) {
        if(wErr) { wErr.innerText = "Required"; wErr.classList.remove('d-none'); }
        customW.style.borderColor = '#ef4444'; // Red Border
        isValid = false;
        if(!errorMessage) errorMessage = "Please enter a valid width.";
    } else if (maxWidth > 0 && userW > maxWidth) {
        if(wErr) { wErr.innerText = `Maximum Print Size is ${maxWidth}x${maxHeight}`; wErr.classList.remove('d-none'); }
        customW.style.borderColor = '#ef4444'; // Red Border
        isValid = false;
        if(!errorMessage) errorMessage = `Maximum Print Size is ${maxWidth}x${maxHeight} inches.`;
    }

    // 4. MAXIMUM HEIGHT CHECK
    if (!customH.value || userH <= 0) {
        if(hErr) { hErr.innerText = "Required"; hErr.classList.remove('d-none'); }
        customH.style.borderColor = '#ef4444'; // Red Border
        isValid = false;
        if(!errorMessage) errorMessage = "Please enter a valid height.";
    } else if (maxHeight > 0 && userH > maxHeight) {
        if(hErr) { hErr.innerText = `Maximum Print Size is ${maxWidth}x${maxHeight}`; hErr.classList.remove('d-none'); }
        customH.style.borderColor = '#ef4444'; // Red Border
        isValid = false;
        if(!errorMessage) errorMessage = `Maximum Print Size is ${maxWidth}x${maxHeight} inches.`;
    }

    if (isValid && paperNameText.includes('canvas')) {
        const shortSide = Math.min(userW, userH);
        const longSide = Math.max(userW, userH);

        if (shortSide < 12 || longSide < 18) {
            const errorMsg = "Canvas minimum is 12x18";
            if(wErr) { wErr.innerText = errorMsg; wErr.classList.remove('d-none'); }
            if(hErr) { hErr.innerText = errorMsg; hErr.classList.remove('d-none'); }
            
            // Turn both fields red!
            customW.style.borderColor = '#ef4444'; 
            customH.style.borderColor = '#ef4444';
            
            isValid = false;
            if(!errorMessage) errorMessage = "Canvas minimum size allowed is 12x18 inches.";
        }
    }

    if (!isValid && showToastMsg && errorMessage) {
        showToast(errorMessage, 'error');
    }   

    return isValid;
}

function calculateArea() {
    const w = parseFloat(customW.value) || 0;
    const h = parseFloat(customH.value) || 0;
    const area = (w * h).toFixed(2);
    totalInchInput.value = area > 0 ? area : '';
}

function updatePrice() {
    const typeId = paperSelect.value; 
    const size = sizeSelect.value;
    const qty = parseInt(qtyInput.value) || 1;
    
    if (!typeId || size === 'Select a paper name first') return;

    const formData = new URLSearchParams();
    formData.append('type', typeId);
    formData.append('size', size);
    formData.append('width', customW.value || 0);
    formData.append('height', customH.value || 0);

    fetch('../process/get_print_price.php', { method: 'POST', body: formData })
    .then(res => res.text())
    .then(price => {
        const total = parseFloat(price) * qty;
        document.getElementById('display-total').innerText = '₱' + (isNaN(total) ? "0.00" : total.toFixed(2));
    });

    savePrintingState();
}

function savePrintingState() {
    sessionStorage.setItem('printingState', JSON.stringify({
        paper: paperSelect.value,
        size: sizeSelect.value,
        w: customW.value,
        h: customH.value,
        qty: qtyInput.value
    }));
}

// --- ADD TO CART LOGIC ---
btnCart.addEventListener('click', function() {
    if (!validateForm(true)) return;

    const formData = new FormData();
    
    if (optUpload.checked) {
        formData.append('image', fileInput.files[0]);
    } else {
        formData.append('drive_link', driveLinkInput.value.trim());
    }
    formData.append('type', paperSelect.value);
    formData.append('size', sizeSelect.value);
    formData.append('qty', qtyInput.value);
    formData.append('width', customW.value);
    formData.append('height', customH.value);
    const priceText = document.getElementById('display-total').innerText.replace(/[^\d.]/g, '');
    formData.append('total_price', parseFloat(priceText) || 0);
    
    // 🟢 NEW: Tell the backend this is an Add to Cart action!
    formData.append('action', 'add_to_cart');

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    // 🟢 CHANGED URL
    fetch('../process/printing_process.php', { method: 'POST', body: formData })
    .then(res => res.json())
    .then(data => {
            if(data.success) {
            sessionStorage.removeItem('printingState'); // Clear state on successful add to cart
            showToast('Item successfully added to cart.');
            
            // Update cart badge dynamically
            const addedQty = parseInt(qtyInput.value) || 1;
            const desktopBadge = document.getElementById('cart-badge-desktop');
            const mobileBadge = document.getElementById('cart-badge-mobile');
            if (desktopBadge) {
                desktopBadge.textContent = parseInt(desktopBadge.textContent || 0) + addedQty;
                desktopBadge.style.display = 'inline-block';
            }
            if (mobileBadge) {
                mobileBadge.textContent = parseInt(mobileBadge.textContent || 0) + addedQty;
                mobileBadge.style.display = 'inline-block';
            }

            fileInput.value = ''; 
            paperSelect.selectedIndex = 0; 
            sizeSelect.innerHTML = '<option selected disabled>Select size</option>'; 
            sizeSelect.disabled = true;
            qtyInput.value = 1; 
            customW.value = ''; 
            customH.value = ''; 
            totalInchInput.value = ''; 
            document.getElementById('display-total').innerText = '₱0.00'; 
            imagePreview.src = '#';
            uploadContent.classList.remove('d-none');
            previewWrapper.classList.add('d-none');
        } else {
            if (data.message && !data.message.includes("dimensions")) {
                showToast(data.message, 'error');
            }
        }
    })
    .catch(() => showToast('A connection error occurred.', 'error'))
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-cart-plus me-2"></i> Add to Cart';
    });
});

// --- BUY NOW LOGIC ---
btnBuyNow.addEventListener('click', function() {
    if (!validateForm(true)) return;

    const formData = new FormData();
    
    if (optUpload.checked) {
        formData.append('image', fileInput.files[0]);
    } else {
        formData.append('drive_link', driveLinkInput.value.trim());
    }
    formData.append('type', paperSelect.value);
    
    const paperNameText = paperSelect.selectedIndex > 0 ? paperSelect.options[paperSelect.selectedIndex].text : 'Standard';
    formData.append('paper_name', paperNameText);

    formData.append('size', sizeSelect.value);
    formData.append('qty', qtyInput.value);
    formData.append('width', customW.value);
    formData.append('height', customH.value);
    const priceText = document.getElementById('display-total').innerText.replace(/[^\d.]/g, '');
    formData.append('total_price', parseFloat(priceText) || 0);
    
    // 🔥 RESTORED: Your original checkout flag!
    formData.append('is_buy_now', 1); 
    
    // 🟢 NEW: Tell the backend this is a Buy Now action!
    formData.append('action', 'buy_now'); 

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    // 🟢 CHANGED URL
    fetch('../process/printing_process.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                window.location.href = 'customer_checkout.php';
            } else {
                showToast(data.message || 'Failed to process Buy Now.', 'error');
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-bolt me-2"></i> Buy Now';
            }
        })
        .catch(() => {
            showToast('A connection error occurred.', 'error');
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-bolt me-2"></i> Buy Now';
        });
});

[paperSelect, sizeSelect].forEach(el => el.addEventListener('change', updatePrice));
[customW, customH].forEach(el => {
    el.addEventListener('input', () => {
        calculateArea();
        validateForm();
        updatePrice();
    });
});

document.getElementById('plus-btn').addEventListener('click', () => { 
    qtyInput.value = parseInt(qtyInput.value) + 1; 
    updatePrice(); 
});
    
document.getElementById('minus-btn').addEventListener('click', () => { 
    if(qtyInput.value > 1) { 
        qtyInput.value = parseInt(qtyInput.value) - 1; 
        updatePrice(); 
    } 
});

paperSelect.addEventListener('change', function() {
    fetch('../process/fetch_print_sizes.php', {
        method: 'POST',
        body: 'paper_id=' + encodeURIComponent(this.value), 
        headers: {'Content-Type': 'application/x-www-form-urlencoded'}
    })
    .then(res => res.text())
    .then(html => { 
        sizeSelect.innerHTML = html; 
        sizeSelect.disabled = false; 
        customW.value = '';
        customH.value = '';
        totalInchInput.value = '';
        validateForm();
    });
});

sizeSelect.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    if (this.value === 'Other') {
        customW.readOnly = false;
        customH.readOnly = false;
        customW.value = '';
        customH.value = '';
        totalInchInput.value = '';
        customW.focus();
    } else {
        customW.readOnly = true;
        customH.readOnly = true;
        customW.value = selectedOption.getAttribute('data-width') || 0;
        customH.value = selectedOption.getAttribute('data-height') || 0;
        calculateArea();
    }
    updatePrice();
    validateForm();
});

fileInput.addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.src = e.target.result;
            uploadContent.classList.add('d-none');
            previewWrapper.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    }
});

uploadArea.addEventListener('click', function(e) {
    if (e.target !== fileInput) fileInput.click();
});

fileInput.addEventListener('click', function(e) {
    e.stopPropagation();
});

// ── Restore state from sessionStorage ─────────────────────
window.addEventListener('DOMContentLoaded', () => {
    const saved = sessionStorage.getItem('printingState');
    if (saved) {
        try {
            const s = JSON.parse(saved);
            if (s.paper) {
                paperSelect.value = s.paper;
                
                // Fetch sizes for this paper
                fetch('../process/fetch_print_sizes.php', {
                    method: 'POST',
                    body: 'paper_id=' + encodeURIComponent(s.paper), 
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'}
                })
                .then(res => res.text())
                .then(html => { 
                    sizeSelect.innerHTML = html; 
                    sizeSelect.disabled = false; 
                    
                    if (s.size) {
                        sizeSelect.value = s.size;
                    }
                    
                    if (s.size === 'Other') {
                        customW.readOnly = false;
                        customH.readOnly = false;
                        customW.value = s.w || '';
                        customH.value = s.h || '';
                    } else {
                        customW.readOnly = true;
                        customH.readOnly = true;
                        if(sizeSelect.selectedIndex > 0) {
                            const opt = sizeSelect.options[sizeSelect.selectedIndex];
                            customW.value = opt.getAttribute('data-width') || 0;
                            customH.value = opt.getAttribute('data-height') || 0;
                        }
                    }
                    
                    if (s.qty) qtyInput.value = s.qty;
                    calculateArea();
                    updatePrice();
                    validateForm();
                });
            }
        } catch (e) {
            console.error('Failed to restore printing state:', e);
            sessionStorage.removeItem('printingState');
        }
    }
});