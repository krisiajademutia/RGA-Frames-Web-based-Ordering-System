<?php 
include '../includes/customer_header.php'; 
include_once __DIR__ . '/../config/db_connect.php';

$paper_type_query = "SELECT paper_type_id, paper_name FROM tbl_paper_type WHERE is_active = 1 ORDER BY paper_name ASC";
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
                                    <option value="<?= $row['paper_type_id'] ?>"><?= htmlspecialchars($row['paper_name']) ?></option>
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
                                    <span id="width-error" class="text-danger" style="font-size: 0.7rem; color: #dc3545; display: block; min-height: 1rem; font-weight: bold;"></span>
                                </div>
                                <div class="col-4">
                                    <label class="small text-muted">Height (in)</label>
                                    <input type="number" class="form-control" id="custom-height" placeholder="H" step="0.1" readonly>
                                    <span id="height-error" class="text-danger" style="font-size: 0.7rem; color: #dc3545; display: block; min-height: 1rem; font-weight: bold;"></span>
                                </div>
                                <div class="col-4">
                                    <label class="small text-muted">Total Inch</label>
                                    <input type="number" class="form-control" id="total-inch-input" placeholder="Total" step="0.1" readonly style="background-color: #f8f9fa;">
                                </div>
                            </div>
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
                <div class="d-flex gap-2">
                    <button class="ps-btn-cart"><i class="fas fa-cart-plus me-2"></i> Add to Cart</button>
                    <button class="ps-btn-buy-now" style="background-color: #0f3d33; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold;"><i class="fas fa-bolt me-2"></i> Buy Now</button>
                </div>
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
    const btnBuyNow = document.querySelector('.ps-btn-buy-now');
    const uploadArea = document.getElementById('upload-area');

    function showToast(message, type = 'success') {
        Swal.fire({
            toast: true,
            position: 'bottom-end', 
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    }

    function validateForm() {
        let isValid = true;
        const wErr = document.getElementById('width-error');
        const hErr = document.getElementById('height-error');
        const fileErr = document.getElementById('file-error');
        const paperErr = document.getElementById('paper-error');

        [wErr, hErr, fileErr, paperErr].forEach(el => {
            el.innerText = "";
            el.classList.add('d-none');
        });

        if (!fileInput.files[0]) {
            fileErr.innerText = "Please select an image.";
            fileErr.classList.remove('d-none');
            isValid = false;
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

        if (!customW.value || userW <= 0) {
            wErr.innerText = "Required";
            wErr.classList.remove('d-none');
            isValid = false;
        } else if (maxWidth > 0 && userW > maxWidth) {
            wErr.innerText = `Width must not exceed ${maxWidth}"`;
            wErr.classList.remove('d-none');
            isValid = false;
        }

        if (!customH.value || userH <= 0) {
            hErr.innerText = "Required";
            hErr.classList.remove('d-none');
            isValid = false;
        } else if (maxHeight > 0 && userH > maxHeight) {
            hErr.innerText = `Height must not exceed ${maxHeight}"`;
            hErr.classList.remove('d-none');
            isValid = false;
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
    }

    // --- ADD TO CART LOGIC ---
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

        fetch('../process/add_to_cart_printing_process.php', { method: 'POST', body: formData })
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
        if (!validateForm()) return;

        const formData = new FormData();
        formData.append('image', fileInput.files[0]);
        formData.append('type', paperSelect.value);
        
        // This grabs the actual text name of the paper safely!
        const paperNameText = paperSelect.selectedIndex > 0 ? paperSelect.options[paperSelect.selectedIndex].text : 'Standard';
        formData.append('paper_name', paperNameText);

        formData.append('size', sizeSelect.value);
        formData.append('qty', qtyInput.value);
        formData.append('width', customW.value);
        formData.append('height', customH.value);
        const priceText = document.getElementById('display-total').innerText.replace(/[^\d.]/g, '');
        formData.append('total_price', parseFloat(priceText) || 0);

        this.disabled = true;
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';

        fetch('../process/buy_now_printing_process.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                window.location.href = 'customer_checkout.php';
            } else {
                showToast(data.message || 'Error processing Buy Now', 'error');
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
</script>
</body>
</html>