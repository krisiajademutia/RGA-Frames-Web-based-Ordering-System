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
                        </div>
                        <div class="col-md-6">
                            <label class="ps-label">SIZE</label>
                            <select class="form-select ps-select" id="size-select" disabled>
                                <option selected disabled>Select a paper name first</option>
                            </select>
                            <div id="custom-size-fields" class="row mt-2 d-none">
                                <div class="col-6"><input type="number" class="form-control" id="custom-width" placeholder="W (in)" step="0.1"></div>
                                <div class="col-6"><input type="number" class="form-control" id="custom-height" placeholder="H (in)" step="0.1"></div>
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
                <button class="ps-btn-cart"><i class="fas fa-cart-plus me-2"></i> Add to Cart</button>
            </div>
        </div>
    </div>

    <script>
    const paperSelect = document.getElementById('paper-type-select');
    const sizeSelect = document.getElementById('size-select');
    const customFields = document.getElementById('custom-size-fields');
    const customW = document.getElementById('custom-width');
    const customH = document.getElementById('custom-height');
    const qtyInput = document.getElementById('qty-input');
    const fileInput = document.getElementById('ps-file-input');
    const imagePreview = document.getElementById('image-preview');
    const uploadContent = document.getElementById('upload-content');
    const previewWrapper = document.getElementById('preview-wrapper');

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

        fetch('../process/get_print_price.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(price => {
            const total = parseFloat(price) * qty;
            document.getElementById('display-total').innerText = '₱' + (isNaN(total) ? "0.00" : total.toFixed(2));
        })
        .catch(error => console.error('Error fetching price:', error));
    }

    // 2. Event Listeners
    [paperSelect, sizeSelect, customW, customH].forEach(el => el.addEventListener('change', updatePrice));
    
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

    // 3. Paper Type Selection (Resets size)
    paperSelect.addEventListener('change', function() {
        const xhr = new XMLHttpRequest();
        xhr.open('POST', '../process/fetch_sizes.php', true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            sizeSelect.innerHTML = this.responseText;
            sizeSelect.disabled = false;
        };
        xhr.send('paper_name=' + encodeURIComponent(this.value));
    });

    // 4. Custom Size Toggle
    sizeSelect.addEventListener('change', function() {
        customFields.classList.toggle('d-none', this.value !== 'Other');
        updatePrice();
    });

    // 5. Image Preview
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
</script>
</html>