<?php
// Include the logic that initializes the database, the OptionService, and registers all repositories
require_once __DIR__ . '/../process/fetch_options.php';

$status    = $_GET['success'] ?? null;
$error_msg = $_GET['error']   ?? null;
$edit_data = null;
$is_editing = false;

// Check if an "edit" action is requested via URL parameters
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $edit_id = (int)$_GET['id'];
    // $service is defined in fetch_options.php
    $edit_data = $service->getOptionById($active_tab, $edit_id); 
    if ($edit_data) {
        $is_editing = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frame Options Management | Admin</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/admin_header.php'; ?>

<div class="opt-admin-container">
    <div class="opt-header-wrapper">
        <div>
            <h1 style="color: var(--forest-dark); font-weight: 800; font-size: 28px; margin:0;">Frame Options</h1>
            <p style="color: #6B7280; margin-top: 5px; font-size: 15px;">Manage your custom framing components.</p>
        </div>
        <div class="opt-dropdown">
            <button class="opt-dropbtn">
                <span><?= htmlspecialchars($tab_label ?? 'Select Tab') ?></span>
                <i data-lucide="chevron-down" size="18"></i>
            </button>
            <div class="opt-dropdown-menu">
                <a href="?tab=frame_types">Frame Types</a>
                <a href="?tab=frame_designs">Frame Designs</a>
                <a href="?tab=frame_colors">Frame Colors</a>
                <a href="?tab=frame_sizes">Frame Sizes</a>
                <a href="?tab=matboard_colors">Matboard Colors</a>
                <a href="?tab=mount_types">Mount Types</a>
                <a href="?tab=paper_types">Paper Types</a>
            </div>
        </div>
    </div>

    <?php if($status === '1'): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            Operation successful.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php elseif($status === '0' || $error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Operation failed<?= isset($_GET['dberr']) ? ': ' . htmlspecialchars($_GET['dberr']) : '' ?>.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="opt-card" id="form-section"> 
        <div class="opt-card-header">
            <?= $is_editing ? 'Edit' : 'Add New' ?> <?= htmlspecialchars($tab_label ?? '') ?>
        </div>
    
        <form action="../process/posting_options.php?tab=<?= urlencode($active_tab) ?>" method="POST" enctype="multipart/form-data">
            
            <?php if($is_editing): ?>
                <?php 
                    $pkMap = [
                        'frame_types' => 'frame_type_id', 'frame_designs' => 'frame_design_id',
                        'frame_colors' => 'frame_color_id', 'frame_sizes' => 'frame_size_id',
                        'matboard_colors' => 'matboard_color_id', 'mount_types' => 'mount_type_id',
                        'paper_types' => 'paper_type_id'
                    ];
                    $pkCol = $pkMap[$active_tab] ?? 'id';
                ?>
                <input type="hidden" name="option_id" value="<?= $edit_data[$pkCol] ?? $edit_id ?>">
                <input type="hidden" name="action" value="edit">
            <?php endif; ?>

            <div class="opt-form-grid">

                <?php if($active_tab == 'frame_types'): ?>
                    <div>
                        <label class="opt-label">Type Name <span>*</span></label>
                        <input type="text" name="type_name" class="opt-input" required value="<?= htmlspecialchars($edit_data['type_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="type_price" class="opt-input" required value="<?= $edit_data['type_price'] ?? '' ?>">
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div class="opt-upload-container">
                        <label class="opt-label">PRODUCT PHOTO <span class="text-danger">*</span></label>
                        <div class="opt-upload-zone position-relative" onclick="document.getElementById('add_type_img').click();">
                            <input type="hidden" name="existing_image" id="existing_type_val" value="<?= htmlspecialchars($edit_data['image_name'] ?? '') ?>">
                            <input type="file" name="type_image" id="add_type_img" style="display:none;" onchange="handleSingleFilePreview(this, 'type_preview', 'type_text', 'existing_type_val')">
                            <div id="type_preview" class="preview-overlay"></div>
                            <div class="upload-content text-center">
                                <i class="fa-solid fa-images"></i>
                                <p class="m-0" id="type_text">Click to upload photo</p>
                            </div>
                        </div>
                        <?php if($is_editing && !empty($edit_data['image_name'])): ?>
                            <script>window.addEventListener('load', () => { showExistingImage('../uploads/<?= $edit_data['image_name'] ?>', 'type_preview', 'type_text', 'add_type_img', 'existing_type_val'); });</script>
                        <?php endif; ?>
                    </div>

                <?php elseif($active_tab == 'frame_designs'): ?>
                    <div>
                        <label class="opt-label">Design Name <span>*</span></label>
                        <input type="text" name="design_name" class="opt-input" required value="<?= htmlspecialchars($edit_data['design_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="price" class="opt-input" required value="<?= $edit_data['price'] ?? '' ?>">
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                   <div class="opt-upload-container">
                        <label class="opt-label">PRODUCT PHOTOS <span class="text-danger">*</span></label>
                        <div class="opt-upload-zone position-relative" onclick="document.getElementById('add_design_imgs').click();">
                            <input type="file" name="design_images[]" id="add_design_imgs" style="display:none;" multiple onchange="handleMultipleFilePreview(this, 'image_preview_container', 'opt_img_text')">
                            <div id="image_preview_container" class="preview-overlay"></div>
                            <div class="upload-content text-center">
                                <i class="fa-solid fa-images"></i>
                                <p class="m-0" id="opt_img_text">Click to upload multiple photos</p>
                            </div>
                        </div>
                    </div>
                    <?php if($is_editing && !empty($edit_data['images'])): ?>
                        <script>
                            window.addEventListener('load', () => {
                                loadExistingPhotos(
                                    <?= json_encode($edit_data['images']) ?>, 
                                    'image_preview_container', 
                                    'opt_img_text', 
                                    'add_design_imgs'
                                );
                            });
                        </script>
                    <?php endif; ?>

                <?php elseif($active_tab == 'frame_colors'): ?>
                    <div>
                        <label class="opt-label">Color Name <span>*</span></label>
                        <input type="text" name="color_name" class="opt-input" required value="<?= htmlspecialchars($edit_data['color_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div></div>
                    <div class="opt-upload-container">
                        <label class="opt-label">COLOR SWATCH <span class="text-danger">*</span></label>
                        <div class="opt-upload-zone position-relative" onclick="document.getElementById('color_img').click();">
                            <input type="hidden" name="existing_image" id="existing_color_val" value="<?= htmlspecialchars($edit_data['color_image'] ?? '') ?>">
                            <input type="file" name="color_image" id="color_img" style="display:none;" onchange="handleSingleFilePreview(this, 'color_preview', 'color_text', 'existing_color_val')">
                            <div id="color_preview" class="preview-overlay"></div>
                            <div class="upload-content text-center">
                                <i class="fa-solid fa-palette"></i>
                                <p class="m-0" id="color_text">Click to upload swatch</p>
                            </div>
                        </div>
                        <?php if($is_editing && !empty($edit_data['color_image'])): ?>
                            <script>window.addEventListener('load', () => { showExistingImage('../uploads/<?= $edit_data['color_image'] ?>', 'color_preview', 'color_text', 'color_img', 'existing_color_val'); });</script>
                        <?php endif; ?>
                    </div>

                <?php elseif($active_tab == 'matboard_colors'): ?>
                    <div>
                        <label class="opt-label">Color Name *</label>
                        <input type="text" name="matboard_color_name" class="opt-input" required value="<?= htmlspecialchars($edit_data['matboard_color_name'] ?? '') ?>">
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                    <div>
                        <label class="opt-label">Additional Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="base_price" class="opt-input" required value="<?= $edit_data['base_price'] ?? '' ?>">
                    </div>
                    <div class="opt-upload-container">
                        <label class="opt-label">MATBOARD SWATCH <span class="text-danger">*</span></label>
                        <div class="opt-upload-zone position-relative" onclick="document.getElementById('mat_img').click();">
                            <input type="hidden" name="existing_image" id="existing_mat_val" value="<?= htmlspecialchars($edit_data['image_name'] ?? '') ?>">
                            <input type="file" name="image_name" id="mat_img" style="display:none;" onchange="handleSingleFilePreview(this, 'mat_preview', 'mat_text', 'existing_mat_val')">
                            <div id="mat_preview" class="preview-overlay"></div>
                            <div class="upload-content text-center">
                                <i class="fa-solid fa-palette"></i>
                                <p class="m-0" id="mat_text">Click to upload swatch</p>
                            </div>
                        </div>
                        <?php if($is_editing && !empty($edit_data['image_name'])): ?>
                            <script>window.addEventListener('load', () => { showExistingImage('../uploads/<?= $edit_data['image_name'] ?>', 'mat_preview', 'mat_text', 'mat_img', 'existing_mat_val'); });</script>
                        <?php endif; ?>
                    </div>

                <?php elseif($active_tab == 'frame_sizes'): ?>
                    <div><label class="opt-label">Width *</label><input type="number" step="0.01" name="width" class="opt-input" required value="<?= $edit_data['width_inch'] ?? '' ?>"></div>
                    <div><label class="opt-label">Height *</label><input type="number" step="0.01" name="height" class="opt-input" required value="<?= $edit_data['height_inch'] ?? '' ?>"></div>
                    <div><label class="opt-label">Total Inches *</label><input type="number" step="0.01" name="total_inches" class="opt-input" required value="<?= $edit_data['total_inch'] ?? '' ?>"></div>
                    <div><label class="opt-label">Base Price *</label><input type="number" step="0.01" name="base_price" class="opt-input" required value="<?= $edit_data['price'] ?? '' ?>"></div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                <?php elseif($active_tab == 'mount_types'): ?>
                    <div><label class="opt-label">Mount Name *</label><input type="text" name="generic_name" class="opt-input" required value="<?= htmlspecialchars($edit_data['mount_name'] ?? '') ?>"></div>
                    <div><label class="opt-label">Fee (₱) *</label><input type="number" step="0.01" name="generic_price" class="opt-input" required value="<?= $edit_data['additional_fee'] ?? '' ?>"></div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>

                <?php elseif($active_tab == 'paper_types'): ?>
                    <div><label class="opt-label">Paper Name *</label><input type="text" name="paper_name" class="opt-input" required value="<?= htmlspecialchars($edit_data['paper_name'] ?? '') ?>"></div>
                    <div><label class="opt-label">Price (₱) *</label><input type="number" step="0.01" name="price" class="opt-input" required value="<?= $edit_data['price'] ?? '' ?>"></div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 1) ? 'selected' : '' ?>>Active</option>
                            <option value="0" <?= (isset($edit_data['is_active']) && $edit_data['is_active'] == 0) ? 'selected' : '' ?>>Inactive</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div style="grid-column: span 2; display:flex; justify-content:center; gap:15px; margin-top:20px;">
                    <?php if($is_editing): ?>
                        <a href="?tab=<?= $active_tab ?>" class="opt-btn-clear text-decoration-none" style="display:flex; align-items:center;">Cancel</a>
                        <button type="submit" name="update_option" class="opt-btn-submit">Update <?= ucwords($suffix ?? 'Option') ?></button>
                    <?php else: ?>
                        <button type="reset" class="opt-btn-clear">Clear</button>
                        <button type="submit" name="add_option" class="opt-btn-submit">Add <?= ucwords($suffix ?? 'Option') ?></button>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <div class="opted-main-container">
        <div class="opted-header-bar">
            <span class="opted-title">Posted <?= htmlspecialchars($tab_label ?? '') ?></span>
            <span class="opted-count-badge"><?= $res ? $res->num_rows : 0 ?> Items</span>
        </div>
        <div class="opted-list-wrapper">
            <?php if ($res && $res->num_rows > 0): ?>
                <?php while($row = $res->fetch_assoc()):
                    $title = $row['type_name'] ?? $row['design_name'] ?? $row['color_name'] ?? 
                             $row['matboard_color_name'] ?? $row['mount_name'] ?? $row['paper_name'] ?? 
                             ($active_tab == 'frame_sizes' ? ($row['dimension'] ?? 'Size') : 'Unnamed');
                    
                    $pkMapLoop = [
                        'frame_types' => 'frame_type_id', 'frame_designs' => 'frame_design_id',
                        'frame_colors' => 'frame_color_id', 'frame_sizes' => 'frame_size_id',
                        'matboard_colors' => 'matboard_color_id', 'mount_types' => 'mount_type_id',
                        'paper_types' => 'paper_type_id'
                    ];
                    $pkColLoop = $pkMapLoop[$active_tab] ?? 'id';
                    $recordId = $row[$pkColLoop] ?? 0;
                    $isActive = (int)($row['is_active'] ?? 1);
                ?>
                <div class="opted-row-item" id="row-<?= $recordId ?>">
                    <div class="opted-row-inner">
                        <div class="opted-item-main">
                            <h4 class="opted-item-name"><?= htmlspecialchars($title) ?></h4>
                            <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                        </div>
                        <div class="opted-actions">
                            <a href="?tab=<?= $active_tab ?>&action=edit&id=<?= $recordId ?>#form-section" class="action-icon-btn opt-btn-edit">
                                <i data-lucide="pencil" size="16"></i>
                            </a>
                            <button type="button" class="action-icon-btn opt-btn-delete" onclick="confirmDelete(<?= $recordId ?>, '<?= addslashes($title) ?>', '<?= $active_tab ?>')">
                                <i data-lucide="trash-2" size="16"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-5 text-center text-muted">No records found.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body p-4">
                <h5 id="deleteOptionName" class="fw-bold"></h5>
                <p>Are you sure you want to delete this?</p>
                <form id="deleteOptionForm" method="POST" action="../process/posting_options.php">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="tab" id="deleteOptionTab">
                    <input type="hidden" name="option_id" id="deleteOptionId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/admin_options.js"></script>
<script>
    lucide.createIcons();
</script>
</body>
</html>