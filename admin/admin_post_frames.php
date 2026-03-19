<?php
ob_start();
session_start();
require_once '../config/db_connect.php';
require_once '../classes/Frames/Repository/FrameRepositoryInterface.php';
require_once '../classes/Frames/Repository/ReadyMadeFrameRepository.php';
require_once '../classes/Frames/FrameService.php';

$repository = new \Classes\Frames\Repository\ReadyMadeFrameRepository($conn);
$frameService = new \Classes\Frames\FrameService($repository);

$types   = $conn->query("SELECT frame_type_id, type_name, type_price FROM tbl_frame_types WHERE is_active = 1");
$designs = $conn->query("SELECT frame_design_id, design_name, price FROM tbl_frame_designs WHERE is_active = 1");
$colors  = $conn->query("SELECT frame_color_id, color_name FROM tbl_frame_colors WHERE is_active = 1");

$view = $_GET['view'] ?? 'post';
$edit_data = null;
$edit_images = [];

if ($view == 'edit' && isset($_GET['id'])) {
    $product_id = (int)$_GET['id'];
    $edit_data = $frameService->getFrameById($product_id);
    
    $img_stmt = $conn->prepare("SELECT image_name, is_primary FROM tbl_ready_made_product_images WHERE r_product_id = ? ORDER BY is_primary DESC");
    $img_stmt->bind_param("i", $product_id);
    $img_stmt->execute();
    $edit_images = $img_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$posted_frames = $frameService->getAllFrames();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Frames Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="/rga_frames/assets/css/style.css">
</head>
<body>

<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="post-admin-container">
    <?php if(isset($_SESSION['post_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fa-solid fa-circle-exclamation me-2"></i>
        <?= $_SESSION['post_error']; unset($_SESSION['post_error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>
    
    <div class="post-header-wrapper">
        <div>
            <h1 style="color: var(--forest-dark); font-weight: 800; font-size: 28px; margin:0;">Ready-made Frames</h1>
            <p style="color: #6B7280; margin-top: 5px; font-size: 15px;">Post and manage ready-made frame products visible to customers.</p>
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
            <span class="posted-badge"><?= count($posted_frames) ?> product(s)</span>
        </div>
        <div class="posted-grid">
            <?php if(!empty($posted_frames)): ?>
                <?php foreach($posted_frames as $row): 
                    $p_id = $row['r_product_id'];
                    $img_res = $conn->query("SELECT image_name FROM tbl_ready_made_product_images WHERE r_product_id = $p_id ORDER BY is_primary DESC");
                    $product_images = $img_res->fetch_all(MYSQLI_ASSOC);
                ?>
                    <div class="posted-card-item">
                        <div class="posted-image-box position-relative">
                            <div id="carousel-<?= $p_id ?>" class="carousel slide h-100" data-bs-ride="false">
                                <div class="carousel-inner h-100">
                                    <?php if(!empty($product_images)): ?>
                                        <?php foreach($product_images as $index => $img): ?>
                                            <div class="carousel-item h-100 <?= $index === 0 ? 'active' : '' ?>">
                                                <img src="/rga_frames/uploads/<?= $img['image_name'] ?>" class="d-block w-100 h-100 object-fit-cover" alt="Product">
                                            </div>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <div class="carousel-item active h-100">
                                            <div class="d-flex align-items-center justify-content-center h-100 bg-light text-muted">No Image</div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <?php if(count($product_images) > 1): ?>
                                    <div class="card-dots-menu dropdown">
                                        <button class="btn btn-dark btn-sm rounded-circle opacity-75" data-bs-toggle="dropdown" style="position: absolute; top: 10px; right: 10px; z-index: 5;">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                            <li><button class="dropdown-item" onclick="bootstrap.Carousel.getOrCreateInstance('#carousel-<?= $p_id ?>').prev()"><i class="fa-solid fa-chevron-left me-2"></i> Previous Image</button></li>
                                            <li><button class="dropdown-item" onclick="bootstrap.Carousel.getOrCreateInstance('#carousel-<?= $p_id ?>').next()"><i class="fa-solid fa-chevron-right me-2"></i> Next Image</button></li>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="posted-info">
                            <h4 class="posted-item-title"><?= htmlspecialchars($row['product_name']) ?></h4>
                            <p class="posted-item-meta m-0"><?= $row['width'] ?>x<?= $row['height'] ?>"</p>
                            <p class="posted-item-subtext m-0"><?= $row['design_name'] ?> | <?= $row['type_name'] ?></p>
                            <p class="posted-item-subtext m-0"><?= $row['color_name'] ?></p>
                            <span class="posted-stock-pill <?= ($row['stock'] > 0) ? 'bg-success' : 'bg-danger' ?>">
                                <?= $row['stock'] ?> in Stock
                            </span>
                        </div>
                        <div class="posted-card-footer">
                            <span class="posted-price-text">₱ <?= number_format($row['product_price'], 2) ?></span>
                            <div class="posted-action-group">
                                <a href="admin_post_frames.php?view=edit&id=<?= $row['r_product_id'] ?>" class="posted-edit-btn d-inline-flex align-items-center justify-content-center text-decoration-none"><i class="fa-solid fa-pen-to-square"></i></a>
                                <button type="button" class="posted-delete-btn" onclick="confirmDelete(<?= $row['r_product_id'] ?>, '<?= addslashes($row['product_name']) ?>')">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
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
        <input type="hidden" name="removed_images" id="removed_images" value="">
        
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
                <label class="post-label">STOCK QUANTITY <span class="text-danger">*</span></label>
                <input type="number" name="stock_quantity" class="post-input" value="<?= $edit_data['quantity'] ?>" required>
            </div>
                <div class="post-upload-container">
                <label class="post-label">PRODUCT PHOTOS <span class="text-danger">*</span></label>
                <div class="post-upload-zone position-relative" onclick="document.getElementById('edit_design_imgs').click();">
                    <input type="file" name="images[]" id="edit_design_imgs" style="display:none;" multiple onchange="handleMultipleFilePreview(this, 'image_preview_container', 'post_img_text')">
                    <div id="image_preview_container" class="preview-overlay"></div>
                    <div class="upload-content text-center">
                        <i class="fa-solid fa-images"></i>
                        <p class="m-0" id="post_img_text">Click to upload photos</p>
                    </div>
                </div>
                <?php if(!empty($edit_images)): ?>
                    <script>
                        window.addEventListener('load', () => { 
                            loadExistingPhotos(<?= json_encode($edit_images) ?>, 'image_preview_container', 'post_img_text', 'edit_design_imgs'); 
                        });
                    </script>
                <?php endif; ?>
            </div>
        </div>

        <div class="d-flex justify-content-center gap-3 mb-3">
            <a href="admin_post_frames.php?view=posted" class="post-btn-clear text-decoration-none d-flex align-items-center justify-content-center">Cancel</a>
            <button type="submit" name="update_product" class="post-btn-submit">Update Frame</button>
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
                    <label class="post-label">STOCK QUANTITY <span class="text-danger">*</span></label>
                    <input type="number" name="stock_quantity" class="post-input" required>
                </div>
                <div class="post-upload-container">
                    <label class="post-label">PRODUCT PHOTOS <span class="text-danger">*</span></label>
                    <div class="post-upload-zone position-relative" onclick="document.getElementById('post_img_input').click();">
                        <input type="file" name="images[]" id="post_img_input" style="display:none;" required multiple onchange="handleMultipleFilePreview(this, 'image_preview_container', 'post_img_text')">
                        <div id="image_preview_container" class="preview-overlay"></div>
                        <div class="upload-content text-center">
                            <i class="fa-solid fa-images"></i>
                            <p class="m-0" id="post_img_text">Click to upload photos</p>
                        </div>
                    </div>
                </div>
            </div>
           <div class="d-flex justify-content-center gap-3 mb-3" style="margin-top: 30px;">
                <button type="reset" class="post-btn-clear" onclick="document.getElementById('image_preview_container').innerHTML = ''; selectedFiles = [];">Clear</button>
                <button type="submit" name="add_product" class="post-btn-submit">Post Frame</button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow" style="border-radius: 15px;">
            <div class="modal-body text-center">
                <div class="mx-auto mb-3" style="width:60px; height:60px; border-radius:50%; background:#e6f0ee; color:#004030; display:flex; align-items:center; justify-content:center; font-size:24px;">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <p class="text-muted mb-1">Are you sure you want to delete this product?</p>
                <h5 id="deleteProductName" class="fw-bold mb-4"></h5>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn border-secondary px-4" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <a id="confirmDeleteLink" href="#" class="btn px-4" style="border-radius:10px; background:#004030; border-color:#004030; color: #fff;">Delete Product</a>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_SESSION['post_success_modal'])): ?>
    <div class="modal fade" id="successOperationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content post-alert-content shadow">
                <div class="modal-header border-0 pt-3 pe-3">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pb-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-circle-check" style="font-size: 60px; color: #004030;"></i>
                    </div>
                    <h5 class="post-alert-title px-4" style="color: #4B5563; font-weight: 400;">
                        Ready-made frame <strong style="color: #111827;"><?= htmlspecialchars($_SESSION['post_success_modal']['name']) ?></strong> 
                        <?= htmlspecialchars($_SESSION['post_success_modal']['action']) ?> successfully!
                    </h5>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('successOperationModal')).show();
        });
    </script>
    <?php unset($_SESSION['post_success_modal']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['post_error_modal'])): ?>
    <div class="modal fade" id="postErrorModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content post-alert-content shadow" style="border-radius: 20px;">
                <div class="modal-header border-0 pt-3 pe-3">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center pb-5">
                    <div class="mb-4">
                        <i class="fa-solid fa-circle-xmark" style="font-size: 60px; color: #dc3545;"></i>
                    </div>
                    <h4 class="fw-bold mb-2" style="color: #111827;">
                        <?= htmlspecialchars($_SESSION['post_error_modal']['title']) ?>
                    </h4>
                    <p class="post-alert-title px-4" style="color: #4B5563; font-weight: 400;">
                        <?= htmlspecialchars($_SESSION['post_error_modal']['message']) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new bootstrap.Modal(document.getElementById('postErrorModal')).show();
        });
    </script>
    <?php unset($_SESSION['post_error_modal']); ?>
<?php endif; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/rga_frames/assets/js/post_script.js"></script>
</body>
</html>