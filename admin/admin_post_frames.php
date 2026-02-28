<?php
ob_start();
session_start();
include '../config/db_connect.php';

$types   = $conn->query("SELECT frame_type_id, type_name, type_price FROM tbl_frame_types WHERE is_active = 1");
$designs = $conn->query("SELECT frame_design_id, design_name, price FROM tbl_frame_designs WHERE is_active = 1");
$colors  = $conn->query("SELECT frame_color_id, color_name FROM tbl_frame_colors WHERE is_active = 1");
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
    </head>

    <body>

        <?php include __DIR__ . '/../includes/admin_header.php'; ?>

        <div class="post-admin-container">

        <?php if(isset($_SESSION['post_success'])): ?>
            <div class="alert alert-success">
            <?= $_SESSION['post_success']; unset($_SESSION['post_success']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['post_error'])): ?>
            <div class="alert alert-danger">
            <?= $_SESSION['post_error']; unset($_SESSION['post_error']); ?>
            </div>
        <?php endif; ?>

        <div class="post-header-wrapper">
        <div>
            <h1 class="fw-bold m-0" style="font-size: 24px;">Post Frames</h1>
            <p class="m-0 mt-1" style="font-size: 14px; color: #555;">
            Post and manage ready-made frame products
            </p>
        </div>

<!-- DROPDOWN (UNCHANGED) -->
    <div class="post-dropdown">
        <button class="post-dropbtn" type="button">
        <span><i class="fa-solid fa-circle-plus me-2"></i> Post New Frame</span>
        <i class="fa-solid fa-chevron-down ms-3" style="font-size: 10px;"></i>
        </button>

        <div class="post-dropdown-menu">
            <a href="#" style="background:#a4d4ca;">
             <i class="fa-solid fa-plus me-2"></i> Post New Frame
            </a>
            <a href="#" style="background:#559688; color:white;">
             <i class="fa-solid fa-box me-2"></i> Posted Frames
            </a>
        </div>
    </div>

</div>

<div class="post-card shadow-sm">
<div class="post-card-header">
    POST READY-MADE FRAMES</div>

<form action="/rga_frames/process/postframe_process.php"
method="POST"
enctype="multipart/form-data">

<div class="post-form-grid">

<!-- PRODUCT NAME -->
<div>
<label class="post-label">PRODUCT NAME <span class="text-danger">*</span></label>
<input type="text" name="product_name" class="post-input" required>
</div>

<!-- FRAME TYPE -->
<div>
<label class="post-label">FRAME TYPE <span class="text-danger">*</span></label>
<select name="frame_type_id" class="post-input post-calc-trigger" required>
<option value="" data-price="0">Select Type</option>
<?php while($r = $types->fetch_assoc()): ?>
<option value="<?= $r['frame_type_id'] ?>" data-price="<?= $r['type_price'] ?>">
<?= $r['type_name'] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- FRAME DESIGN -->
<div>
<label class="post-label">FRAME DESIGN <span class="text-danger">*</span></label>
<select name="frame_design_id" class="post-input post-calc-trigger" required>
<option value="" data-price="0">Select Design</option>
<?php while($r = $designs->fetch_assoc()): ?>
<option value="<?= $r['frame_design_id'] ?>" data-price="<?= $r['price'] ?>">
<?= $r['design_name'] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- FRAME COLOR -->
<div>
<label class="post-label">FRAME COLOR <span class="text-danger">*</span></label>
<select name="frame_color_id" class="post-input" required>
<option value="">Select Color</option>
<?php while($r = $colors->fetch_assoc()): ?>
<option value="<?= $r['frame_color_id'] ?>">
<?= $r['color_name'] ?>
</option>
<?php endwhile; ?>
</select>
</div>

<!-- WIDTH X HEIGHT (UNCHANGED DESIGN) -->
<div>
<label class="post-label">FRAME SIZE <span class="text-danger">*</span></label>
<div class="d-flex align-items-center gap-2">
<input type="number" step="0.01" name="width"
class="post-input post-size-box"
placeholder="Width" required>
<span class="fw-bold">X</span>
<input type="number" step="0.01" name="height"
class="post-input post-size-box"
placeholder="Height" required>
</div>
</div>

<!-- PRICE -->
<div>
<label class="post-label">SELLING PRICE (₱) <span class="text-danger">*</span></label>
<div class="position-relative">
<span style="position:absolute; left:15px; top:11px; font-weight:bold;">₱</span>
<input type="number" step="0.01"
name="product_price"
id="post_total_display"
class="post-input"
style="padding-left:35px;" required>
</div>
</div>

<!-- STOCK -->
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

<!-- IMAGE -->
<div class="post-upload-container">
<label class="post-label">PRODUCT PHOTO <span class="text-danger">*</span></label>

<div class="post-upload-zone"
onclick="document.getElementById('post_img_input').click();">

<input type="file"
name="image"
id="post_img_input"
style="display:none;"
required
onchange="handlePostFileChange(this)">

<i class="fa-solid fa-images"></i>
<p class="m-0" id="post_img_text"
style="font-size: 13px; font-weight: 600;">
Click to upload product photo
</p>
</div>
</div>

</div>

<div class="d-flex justify-content-center gap-3 mb-5">
<button type="reset" class="post-btn-clear">Clear</button>
<button type="submit" name="add_product" class="post-btn-submit">
Post Ready-made Frames
</button>
</div>

</form>
</div>
</div>

<script src="/rga_frames/assets/js/post_script.js"></script>
</body>
</html>