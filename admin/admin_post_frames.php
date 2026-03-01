<?php
ob_start();
session_start();
include '../config/db_connect.php';

$types   = $conn->query("SELECT frame_type_id, type_name, type_price FROM tbl_frame_types WHERE is_active = 1");
$designs = $conn->query("SELECT frame_design_id, design_name, price FROM tbl_frame_designs WHERE is_active = 1");
$colors  = $conn->query("SELECT frame_color_id, color_name FROM tbl_frame_colors WHERE is_active = 1");

$view = isset($_GET['view']) ? $_GET['view'] : 'post';

$edit_data = null;
if ($view == 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    $edit_res = $conn->query("SELECT p.*, s.quantity FROM tbl_ready_made_product p 
                               LEFT JOIN tbl_ready_made_product_stocks s ON p.r_product_id = s.r_product_id 
                               WHERE p.r_product_id = $edit_id");
    $edit_data = $edit_res->fetch_assoc();
}

$posted_query = "SELECT p.*, t.type_name, d.design_name, c.color_name, IFNULL(s.quantity, 0) as stock 
                 FROM tbl_ready_made_product p
                 LEFT JOIN tbl_frame_types t ON p.frame_type_id = t.frame_type_id
                 LEFT JOIN tbl_frame_designs d ON p.frame_design_id = d.frame_design_id
                 LEFT JOIN tbl_frame_colors c ON p.frame_color_id = c.frame_color_id
                 LEFT JOIN tbl_ready_made_product_stocks s ON p.r_product_id = s.r_product_id
                 ORDER BY p.r_product_id DESC";
$posted_frames = $conn->query($posted_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Frames Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/rga_frames/assets/css/style.css">
    <link rel="stylesheet" href="/rga_frames/assets/css/admin_post.css">
</head>
<body>

<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="post-admin-container">

    <?php if(isset($_SESSION['post_success'])): ?>
        <div class="alert alert-success"><?= $_SESSION['post_success']; unset($_SESSION['post_success']); ?></div>
    <?php endif; ?>

    <div class="post-header-wrapper">
        <div>
            <h1 class="fw-bold m-0" style="font-size: 26px;">Post Frames</h1>
            <p class="m-0 mt-1" style="font-size: 16px; color: #555;">Post and manage ready-made frame products</p>
        </div>
        <div class="post-dropdown">
            <button class="post-dropbtn" type="button">
                <span><i class="fa-solid fa-circle-plus me-2"></i> <?= ($view == 'posted') ? 'Posted Frames' : (($view == 'edit') ? 'Editing Frame' : 'Post New Frame'); ?></span>
                <i class="fa-solid fa-chevron-down ms-3" style="font-size: 10px;"></i>
            </button>
            <div class="post-dropdown-menu">
                <a href="admin_post_frames.php?view=post" style="background:#a4d4ca;"><i class="fa-solid fa-plus me-2"></i> Post New Frame</a>
                <a href="admin_post_frames.php?view=posted" style="background:#559688; color:white;"><i class="fa-solid fa-box me-2"></i> Posted Frames</a>
            </div>
        </div>
    </div>

    <?php if($view == 'posted'): ?>
    <div class="posted-main-container shadow-sm">
        <div class="posted-header-bar">
            <span>POSTED READY-MADE FRAMES</span>
            <span class="posted-badge"><?= $posted_frames->num_rows ?> product(s)</span>
        </div>
        <div class="posted-grid">
            <?php if($posted_frames->num_rows > 0): ?>
                <?php while($row = $posted_frames->fetch_assoc()): ?>
                    <div class="posted-card-item">
                        <div class="posted-image-box"><img src="/rga_frames/uploads/<?= $row['image_name'] ?>" alt="Product"></div>
                        <div class="posted-info">
                            <h4 class="posted-item-title"><?= $row['product_name'] ?></h4>
                            <p class="posted-item-meta m-0"><?= $row['width'] ?>x<?= $row['height'] ?>"</p>
                            <p class="posted-item-subtext m-0"><?= $row['design_name'] ?> | <?= $row['type_name'] ?></p>
                            <p class="posted-item-subtext m-0"><?= $row['color_name'] ?></p>
                            <span class="posted-stock-pill"><?= $row['stock'] ?> in Stock</span>
                        </div>
                        <div class="posted-card-footer">
                            <span class="posted-price-text">₱ <?= number_format($row['product_price'], 2) ?></span>
                            <div class="posted-action-group">
                                <a href="admin_post_frames.php?view=edit&id=<?= $row['r_product_id'] ?>" class="posted-edit-btn d-inline-flex align-items-center justify-content-center text-decoration-none"><i class="fa-solid fa-pen-to-square"></i></a>
                               <button type="button" class="posted-delete-btn"  onclick="confirmDelete(<?= $row['r_product_id'] ?>, '<?= addslashes($row['product_name']) ?>', event)">
    <i class="fa-solid fa-trash"></i>
</button>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-5 text-center w-100"><p class="text-muted">No products posted yet.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <?php elseif($view == 'edit' && $edit_data): ?>
    <div class="post-card shadow-sm">
        <div class="post-card-header">EDIT READY-MADE FRAME</div>
        <form action="/rga_frames/process/postframe_process.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="r_product_id" value="<?= $edit_data['r_product_id'] ?>">
            <div class="post-form-grid">
                <div>
                    <label class="post-label">PRODUCT NAME <span class="text-danger">*</span></label>
                    <input type="text" name="product_name" class="post-input" value="<?= $edit_data['product_name'] ?>" required>
                </div>
                <div>
                    <label class="post-label">FRAME TYPE <span class="text-danger">*</span></label>
                    <select name="frame_type_id" class="post-input post-calc-trigger" required>
                        <?php $types->data_seek(0); while($r = $types->fetch_assoc()): ?>
                            <option value="<?= $r['frame_type_id'] ?>" data-price="<?= $r['type_price'] ?>" <?= ($r['frame_type_id'] == $edit_data['frame_type_id']) ? 'selected' : '' ?>><?= $r['type_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="post-label">FRAME DESIGN <span class="text-danger">*</span></label>
                    <select name="frame_design_id" class="post-input post-calc-trigger" required>
                        <?php $designs->data_seek(0); while($r = $designs->fetch_assoc()): ?>
                            <option value="<?= $r['frame_design_id'] ?>" data-price="<?= $r['price'] ?>" <?= ($r['frame_design_id'] == $edit_data['frame_design_id']) ? 'selected' : '' ?>><?= $r['design_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="post-label">FRAME COLOR <span class="text-danger">*</span></label>
                    <select name="frame_color_id" class="post-input" required>
                        <?php $colors->data_seek(0); while($r = $colors->fetch_assoc()): ?>
                            <option value="<?= $r['frame_color_id'] ?>" <?= ($r['frame_color_id'] == $edit_data['frame_color_id']) ? 'selected' : '' ?>><?= $r['color_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="post-label">FRAME SIZE <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" step="0.01" name="width" class="post-input post-size-box" value="<?= $edit_data['width'] ?>" required>
                        <span class="fw-bold">X</span>
                        <input type="number" step="0.01" name="height" class="post-input post-size-box" value="<?= $edit_data['height'] ?>" required>
                    </div>
                </div>
                <div>
                    <label class="post-label">SELLING PRICE (₱) <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <span style="position:absolute; left:15px; top:11px; font-weight:bold;">₱</span>
                        <input type="number" step="0.01" name="product_price" id="post_total_display" class="post-input" style="padding-left:35px;" value="<?= $edit_data['product_price'] ?>" required>
                    </div>
                </div>
                <div>
                    <div class="mb-4">
                        <label class="post-label">STOCK QUANTITY <span class="text-danger">*</span></label>
                        <input type="number" name="stock_quantity" class="post-input" value="<?= $edit_data['quantity'] ?>" required>
                    </div>
                    <label class="post-label">PRODUCT STATUS</label>
                    <select name="status" class="post-input">
                        <option value="1" <?= (isset($edit_data['status']) && $edit_data['status'] == 1) ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (isset($edit_data['status']) && $edit_data['status'] == 0) ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="post-upload-container">
                    <label class="post-label">UPDATE PHOTO</label>
                    <div class="post-upload-zone" onclick="document.getElementById('post_img_input').click();">
                        <input type="file" name="image" id="post_img_input" style="display:none;" onchange="handlePostFileChange(this)">
                        <i class="fa-solid fa-images"></i>
                        <p class="m-0" id="post_img_text">Click to replace photo</p>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center gap-3 mb-3">
                <a href="admin_post_frames.php?view=posted" class="post-btn-clear">Cancel</a>
                <button type="submit" name="update_product" class="post-btn-submit">Update Ready-made Frame</button>
            </div>
        </form>
    </div>

    <?php else: ?>
    <div class="post-card shadow-sm">
        <div class="post-card-header">POST READY-MADE FRAMES</div>
        <form action="/rga_frames/process/postframe_process.php" method="POST" enctype="multipart/form-data">
            <div class="post-form-grid">
                <div>
                    <label class="post-label">PRODUCT NAME <span class="text-danger">*</span></label>
                    <input type="text" name="product_name" class="post-input" required>
                </div>
                <div>
                    <label class="post-label">FRAME TYPE <span class="text-danger">*</span></label>
                    <select name="frame_type_id" class="post-input post-calc-trigger" required>
                        <option value="" data-price="0">Select Type</option>
                        <?php $types->data_seek(0); while($r = $types->fetch_assoc()): ?>
                            <option value="<?= $r['frame_type_id'] ?>" data-price="<?= $r['type_price'] ?>"><?= $r['type_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="post-label">FRAME DESIGN <span class="text-danger">*</span></label>
                    <select name="frame_design_id" class="post-input post-calc-trigger" required>
                        <option value="" data-price="0">Select Design</option>
                        <?php $designs->data_seek(0); while($r = $designs->fetch_assoc()): ?>
                            <option value="<?= $r['frame_design_id'] ?>" data-price="<?= $r['price'] ?>"><?= $r['design_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="post-label">FRAME COLOR <span class="text-danger">*</span></label>
                    <select name="frame_color_id" class="post-input" required>
                        <option value="">Select Color</option>
                        <?php $colors->data_seek(0); while($r = $colors->fetch_assoc()): ?>
                            <option value="<?= $r['frame_color_id'] ?>"><?= $r['color_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="post-label">FRAME SIZE <span class="text-danger">*</span></label>
                    <div class="d-flex align-items-center gap-2">
                        <input type="number" step="0.01" name="width" class="post-input post-size-box" placeholder="Width" required>
                        <span class="fw-bold">X</span>
                        <input type="number" step="0.01" name="height" class="post-input post-size-box" placeholder="Height" required>
                    </div>
                </div>
                <div>
                    <label class="post-label">SELLING PRICE (₱) <span class="text-danger">*</span></label>
                    <div class="position-relative">
                        <span style="position:absolute; left:15px; top:11px; font-weight:bold;">₱</span>
                        <input type="number" step="0.01" name="product_price" id="post_total_display" class="post-input" style="padding-left:35px;" required>
                    </div>
                </div>
                <div>
                    <div class="mb-4">
                        <label class="post-label">STOCK QUANTITY <span class="text-danger">*</span></label>
                        <input type="number" name="stock_quantity" class="post-input" required>
                    </div>
                    <label class="post-label">PRODUCT STATUS</label>
                    <select name="status" class="post-input">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
                <div class="post-upload-container">
                    <label class="post-label">PRODUCT PHOTO <span class="text-danger">*</span></label>
                    <div class="post-upload-zone" onclick="document.getElementById('post_img_input').click();">
                        <input type="file" name="image" id="post_img_input" style="display:none;" required onchange="handlePostFileChange(this)">
                        <i class="fa-solid fa-images"></i>
                        <p class="m-0" id="post_img_text">Click to upload product photo</p>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center gap-3 mb-3">
                <button type="reset" class="post-btn-clear">Clear</button>
                <button type="submit" name="add_product" class="post-btn-submit">Post Ready-made Frames</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-post-modal shadow">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon-box">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <p class="modal-text-muted">Are you sure you want to delete this product?</p>
                <h5 id="deleteProductName" class="modal-product-name m-0"></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn-cancel" data-bs-dismiss="modal">Cancel</button>
                <a id="confirmDeleteLink" href="#" class="modal-btn-delete">Delete Product</a>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="/rga_frames/assets/js/post_script.js"></script>
</body>
</html>