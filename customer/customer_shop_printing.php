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

            <div class="ps-total-container">
                <span class="ps-total-label">TOTAL</span>
                <div class="ps-total-amount" id="display-total">₱0.00</div>
                <div class="d-flex gap-2">
                    <button class="ps-btn-cart"><i class="fas fa-cart-plus me-2"></i> Add to Cart</button>
                    <button class="ps-btn-buy-now" style="background-color: #0f3d33; color: white; border: none; padding: 10px 20px; border-radius: 5px; font-weight: bold;"><i class="fas fa-bolt me-2"></i> Buy Now</button>
                </div>
            </div>
        </div>
    </div>

<script src="../assets/js/customer_shop_printing.js"></script>
</body>
</html>