<?php 
include '../includes/customer_header.php'; 
include_once __DIR__ . '/../config/db_connect.php';

$paper_type_query = "SELECT DISTINCT paper_name FROM tbl_paper_type WHERE is_active = 1 ORDER BY paper_name ASC";
$paper_type_result = mysqli_query($conn, $paper_type_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printing Services - RGA Frames</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="ps-body">
    <div class="ps-page-wrapper">
        <div class="container py-5">
            <div class="ps-header mb-4">
                <h1 class="ps-main-title">Printing Services</h1>
            </div>

            <div class="ps-card mb-4">
                <div class="ps-card-header">UPLOAD YOUR IMAGE</div>
                <div class="ps-card-body">
                    <div class="ps-upload-area" id="upload-area">
                        <div class="ps-upload-content" id="upload-content">
                            <i class="fas fa-image ps-upload-icon"></i>
                            <p class="ps-upload-text">Click to upload image</p>
                        </div>
                        <div class="ps-preview-wrapper d-none" id="preview-wrapper">
                            <img src="#" alt="Preview" class="ps-image-preview" id="image-preview">
                            <div class="ps-preview-instruction-overlay">
                                <i class="fas fa-sync-alt"></i>
                                <span>Click to change uploaded photo</span>
                            </div>
                        </div>
                       <input type="file" class="ps-file-input" id="ps-file-input" accept="image/*">
                       <span id="file-error" class="text-danger small d-none">Please select an image file to proceed.</span>
                    </div>
                </div>
            </div>

            <div class="ps-card ps-controls-card mb-4">
                <div class="ps-card-header">PAPER TYPE & SIZE</div>
                <div class="ps-card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="ps-label">PAPER NAME</label>
                            <select class="form-select ps-select" id="paper-type-select">
                                <option selected disabled>Select paper name</option>
                                <?php while($row = mysqli_fetch_assoc($paper_type_result)): ?>
                                    <option value="<?= htmlspecialchars($row['paper_name']) ?>"><?= htmlspecialchars($row['paper_name']) ?></option>
                                <?php endwhile; ?>
                            </select>
                            <span id="paper-error" class="text-danger small d-none">Please select a paper type.</span>
                        </div>
                        <div class="col-md-6">
                            <label class="ps-label">SIZE</label>
                            <select class="form-select ps-select mb-3" id="size-select" disabled>
                                <option selected disabled>Select a paper name first</option>
                            </select>
                            
                            <div id="dimension-fields" class="row g-2">
                                <div class="col-4">
                                    <label class="small text-muted">Width (in)</label>
                                    <input type="number" class="form-control" id="custom-width" placeholder="W" step="0.1" readonly>
                                </div>
                                <div class="col-4">
                                    <label class="small text-muted">Height (in)</label>
                                    <input type="number" class="form-control" id="custom-height" placeholder="H" step="0.1" readonly>
                                </div>
                                <div class="col-4">
                                    <label class="small text-muted">Total Inch</label>
                                    <input type="number" class="form-control" id="total-inch-input" placeholder="Total" step="0.1" readonly style="background-color: #f8f9fa;">
                                </div>
                            </div>
                            <span id="dim-error" class="text-danger small d-none">Please provide valid dimensions.</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="ps-label">QUANTITY</label>
                        <div class="ps-quantity-control">
                            <button type="button" class="ps-qty-btn" id="minus-btn"><i class="fas fa-minus"></i></button>
                            <input type="number" class="ps-qty-input" id="qty-input" value="1" min="1" readonly>
                            <button type="button" class="ps-qty-btn" id="plus-btn"><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ps-total-container d-flex justify-content-between align-items-center">
                <span class="ps-total-label">TOTAL</span>
                <div class="ps-total-amount" id="display-total">₱0.00</div>
                <button class="ps-btn-cart"><i class="fas fa-cart-plus me-2"></i> Add to Cart</button>
            </div>
        </div>
    </div>

<script>
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
    const uploadArea = document.getElementById('upload-area');


    function showToast(message, type = 'success') {
    Swal.fire({
        toast: true,
        position: 'bottom-end', 
        icon: type,
        title: message,
        showConfirmButton: false,
        timer: 4000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer);
            toast.addEventListener('mouseleave', Swal.resumeTimer);
        }
    });
}

    // Inline Validation Helper
    function validateForm() {
        let isValid = true;
        // Reset visibility for all error messages
        document.querySelectorAll('.text-danger').forEach(el => el.classList.add('d-none'));

        if (!fileInput.files[0]) {
            document.getElementById('file-error').classList.remove('d-none');
            isValid = false;
        }
        if (paperSelect.selectedIndex === 0) {
            document.getElementById('paper-error').classList.remove('d-none');
            isValid = false;
        }
        if (!customW.value || !customH.value || parseFloat(customW.value) <= 0 || parseFloat(customH.value) <= 0) {
            document.getElementById('dim-error').classList.remove('d-none');
            isValid = false;
        }
        return isValid;
    }

    // Helper: Calculate Area (Total Inch)
    function calculateArea() {
        const w = parseFloat(customW.value) || 0;
        const h = parseFloat(customH.value) || 0;
        const area = (w * h).toFixed(2);
        totalInchInput.value = area > 0 ? area : '';
    }

    // 1. Price Calculation Logic
    function updatePrice() {
        const type = paperSelect.value;
        const size = sizeSelect.value;
        const qty = parseInt(qtyInput.value) || 1;
        
        if (!type || !size) return;

        const formData = new URLSearchParams();
        formData.append('type', type);
        formData.append('size', size);
        formData.append('width', customW.value || 0);
        formData.append('height', customH.value || 0);

        fetch('../process/get_print_price.php', { method: 'POST', body: formData })
        .then(response => response.text())
        .then(price => {
            const total = parseFloat(price) * qty;
            document.getElementById('display-total').innerText = '₱' + (isNaN(total) ? "0.00" : total.toFixed(2));
        })
        .catch(error => console.error('Error fetching price:', error));
    }

   // 2. Updated Add to Cart Handler with Professional Feedback
btnCart.addEventListener('click', function() {
        if (!validateForm()) return;

        const formData = new FormData();
        formData.append('image', fileInput.files[0]);
        formData.append('type', paperSelect.value);
        formData.append('size', sizeSelect.value);
        formData.append('qty', qtyInput.value);
        formData.append('width', customW.value);
        formData.append('height', customH.value);
        const priceText = document.getElementById('display-total').innerText.replace(/[^\d.]/g, '');
        formData.append('total_price', parseFloat(priceText) || 0);

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch('../process/add_to_cart.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                showToast('Item successfully added to cart.');

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
                showToast(data.message, 'error');
            }
        })
        .catch(() => showToast('A connection error occurred.', 'error'))
        .finally(() => {
            this.disabled = false;
            this.innerHTML = '<i class="fas fa-cart-plus me-2"></i> Add to Cart';
        });
    });

    // 3. Event Listeners for Price
    [paperSelect, sizeSelect].forEach(el => el.addEventListener('change', updatePrice));
    
    // 4. Dynamic Input for Custom Sizes
    [customW, customH].forEach(el => {
        el.addEventListener('input', () => {
            calculateArea();
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

    // 5. Paper Type Selection
    paperSelect.addEventListener('change', function() {
        fetch('../process/fetch_sizes.php', {
            method: 'POST',
            body: 'paper_name=' + encodeURIComponent(this.value),
            headers: {'Content-Type': 'application/x-www-form-urlencoded'}
        })
        .then(res => res.text())
        .then(html => { 
            sizeSelect.innerHTML = html; 
            sizeSelect.disabled = false; 
            // Reset dimensions on paper change
            customW.value = '';
            customH.value = '';
            totalInchInput.value = '';
        });
    });

    // 6. Size Selection Logic (The "Smart" Toggle)
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
            
            // Get values from the data attributes in fetch_sizes.php
            const w = selectedOption.getAttribute('data-width');
            const h = selectedOption.getAttribute('data-height');
            
            customW.value = w || 0;
            customH.value = h || 0;
            calculateArea();
        }
        updatePrice();
    });

    // 7. Image Preview
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
    // Only trigger if we aren't clicking the file input directly
    if (e.target !== fileInput) {
        fileInput.click();
    }
});

// Prevent the file input's own click from bubbling up to the upload area
fileInput.addEventListener('click', function(e) {
    e.stopPropagation();
});
</script>
</body>
</html>