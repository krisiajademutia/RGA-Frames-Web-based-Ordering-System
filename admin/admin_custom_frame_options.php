<?php
require_once __DIR__ . '/../process/fetch_options.php';

$status    = $_GET['success'] ?? null;
$error_msg = $_GET['error']   ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frame Options Management | Admin</title>
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .opted-edit-form { display: none; background: #f8faf8; border-top: 1px solid #e0e7e0; padding: 16px 20px; }
        .opted-edit-form.open { display: block; }
        .opted-edit-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px; align-items: end; }
        .opted-edit-grid .opt-label { font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 4px; display: block; }
        .opted-edit-grid .opt-input { width: 100%; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 13px; }
        .opted-edit-actions { display: flex; gap: 8px; margin-top: 12px; justify-content: flex-end; }
        .opted-row-item { flex-direction: column; padding: 0; overflow: hidden; }
        .opted-row-inner { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; width: 100%; }
        .multi-upload-zone { border: 2px dashed #b5c4b5; border-radius: 10px; padding: 14px; background: #f8faf8; cursor: pointer; }
        .multi-upload-zone input[type=file] { display: none; }
        .multi-upload-trigger { display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 13px; cursor: pointer; }
        .preview-scroll { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .preview-thumb { position: relative; width: 90px; height: 90px; border-radius: 8px; overflow: hidden; border: 2px solid #d1d5db; background: #fff; }
        .preview-thumb img { width: 100%; height: 100%; object-fit: cover; }
        .preview-thumb .primary-badge { position: absolute; bottom: 0; left: 0; right: 0; background: #2d6a4f; color: #fff; font-size: 10px; text-align: center; padding: 2px 0; font-weight: 700; }
        
        /* New Styles for Image Removal */
        .current-images-grid { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 15px; padding: 10px; background: #fff; border: 1px solid #e5e7eb; border-radius: 8px; }
        .current-img-wrapper { position: relative; width: 80px; height: 80px; border-radius: 6px; overflow: hidden; border: 1px solid #d1d5db; }
        .current-img-wrapper img { width: 100%; height: 100%; object-fit: cover; }
        .delete-checkbox-wrapper { position: absolute; top: 0; right: 0; left: 0; bottom: 0; background: rgba(220, 38, 38, 0); transition: all 0.2s; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .delete-checkbox-wrapper input { width: 18px; height: 18px; cursor: pointer; opacity: 0.8; }
        .delete-checkbox-wrapper:hover { background: rgba(220, 38, 38, 0.4); }
        .delete-checkbox-wrapper input:checked + .delete-overlay { opacity: 1; }
        .delete-overlay { position: absolute; top: 2px; left: 2px; color: white; background: #dc2626; border-radius: 4px; padding: 2px; font-size: 8px; font-weight: bold; pointer-events: none; opacity: 0; }
        .remove-label { font-size: 11px; color: #dc2626; font-weight: 600; margin-bottom: 5px; display: block; }
    </style>
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

    <div class="opt-card">
        <div class="opt-card-header">Add New <?= htmlspecialchars($tab_label ?? '') ?></div>
        <form action="../process/posting_options.php?tab=<?= urlencode($active_tab) ?>" method="POST" enctype="multipart/form-data">
            <div class="opt-form-grid">

                <?php if($active_tab == 'frame_types'): ?>
                    <div>
                        <label class="opt-label">Type Name <span>*</span></label>
                        <input type="text" name="type_name" class="opt-input" required placeholder="e.g. Wooden">
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="type_price" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1">Active - visible to customers</option>
                            <option value="0">Inactive - hidden from customers</option>
                        </select>
                    </div>
                    <div style="grid-row: span 2;">
                        <label class="opt-label">PHOTO</label>
                        <div class="opt-upload-zone">
                            <input type="file" name="type_image">
                            <i data-lucide="image"></i>
                            <span>Click to upload image</span>
                        </div>
                    </div>

                <?php elseif($active_tab == 'frame_designs'): ?>
                    <div>
                        <label class="opt-label">Design Name <span>*</span></label>
                        <input type="text" name="design_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="price" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1">Active - visible to customers</option>
                            <option value="0">Inactive - hidden from customers</option>
                        </select>
                    </div>
                    <div style="grid-column: span 2;">
                        <label class="opt-label">DESIGN PHOTOS <small style="font-weight:400;color:#6b7280;">(First image = primary)</small></label>
                        <div class="multi-upload-zone" id="addDesignUploadZone">
                            <input type="file" name="design_images[]" id="addDesignInput" multiple accept="image/*">
                            <label class="multi-upload-trigger" for="addDesignInput">
                                <i data-lucide="image" size="18"></i>
                                <span>Click to upload design images</span>
                            </label>
                            <div class="preview-scroll" id="addDesignPreviews"></div>
                        </div>
                    </div>

                <?php elseif($active_tab == 'frame_colors'): ?>
                    <div>
                        <label class="opt-label">Color Name <span>*</span></label>
                        <input type="text" name="color_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1">Active - visible to customers</option>
                            <option value="0">Inactive - hidden from customers</option>
                        </select>
                    </div>
                    <div>
                        <label class="opt-label">PHOTO</label>
                        <div class="opt-upload-zone">
                            <input type="file" name="color_image">
                            <i data-lucide="palette"></i>
                            <span>Click to upload color image</span>
                        </div>
                    </div>

                <?php elseif($active_tab == 'frame_sizes'): ?>
                    <div>
                        <label class="opt-label">Width (Inches) <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="width" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Height (Inches) <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="height" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Total Inches <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="total_inches" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Base Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="base_price" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1">Active - visible to customers</option>
                            <option value="0">Inactive - hidden from customers</option>
                        </select>
                    </div>

                <?php elseif($active_tab == 'matboard_colors'): ?>
                    <div>
                        <label class="opt-label">Color Name <span>*</span></label>
                        <input type="text" name="matboard_color_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1">Active - visible to customers</option>
                            <option value="0">Inactive - hidden from customers</option>
                        </select>
                    </div>
                    <div>
                        <label class="opt-label">PHOTO</label>
                        <div class="opt-upload-zone">
                            <input type="file" name="matboard_image">
                            <i data-lucide="image"></i>
                            <span>Click to upload image</span>
                        </div>
                    </div>

                <?php elseif($active_tab == 'mount_types'): ?>
                    <div>
                        <label class="opt-label">Mount Type Name <span>*</span></label>
                        <input type="text" name="generic_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Additional Fee (₱) <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="generic_price" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1">Active - visible to customers</option>
                            <option value="0">Inactive - hidden from customers</option>
                        </select>
                    </div>

                <?php elseif($active_tab == 'paper_types'): ?>
                    <div>
                        <label class="opt-label">Paper Name <span>*</span></label>
                        <input type="text" name="generic_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" min="0" name="generic_price" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Status</label>
                        <select name="is_active" class="opt-input">
                            <option value="1">Active - visible to customers</option>
                            <option value="0">Inactive - hidden from customers</option>
                        </select>
                    </div>
                <?php endif; ?>

                <div style="grid-column: span 2; display:flex; justify-content:center; gap:15px; margin-top:20px;">
                    <button type="reset" class="opt-btn-clear">Clear</button>
                    <button type="submit" name="add_option" class="opt-btn-submit">Add <?= ucwords($suffix ?? 'Option') ?></button>
                </div>
            </div>
        </form>
    </div>

    <div class="opted-main-container">
        <div class="opted-header-bar">
            <span class="opted-title">Posted <?= htmlspecialchars($tab_label ?? '') ?></span>
            <span class="opted-count-badge"><?= $count ?> <?= ucwords($suffix ?? '') ?><?= $count != 1 ? 's' : '' ?></span>
        </div>
        <div class="opted-list-wrapper">
            <?php if ($res && $res->num_rows > 0): ?>
                <?php while($row = $res->fetch_assoc()):

                    // ── Resolve display title ──
                    $title = $row['type_name'] ?? $row['design_name'] ?? $row['color_name'] ??
                             $row['matboard_color_name'] ?? $row['mount_name'] ?? $row['paper_name'] ?? null;
                    if ($title === null && isset($row['dimension'])) {
                        $title = $row['dimension'];
                    }

                    // ── Resolve display price (null = no price for this tab) ──
                    $price = null;
                    if (isset($row['type_price']))     $price = $row['type_price'];
                    elseif (isset($row['price']))      $price = $row['price'];
                    elseif (isset($row['additional_fee'])) $price = $row['additional_fee'];
                    elseif (isset($row['base_price'])) $price = $row['base_price'];

                    // ── Correct PKs matching actual DB schema ──
                    $pkMap = [
                        'frame_types'     => 'frame_type_id',
                        'frame_designs'   => 'frame_design_id',
                        'frame_colors'    => 'frame_color_id',
                        'frame_sizes'     => 'frame_size_id',
                        'matboard_colors' => 'matboard_color_id',
                        'mount_types'     => 'mount_type_id',
                        'paper_types'     => 'paper_type_id',
                    ];
                    $pkCol    = $pkMap[$active_tab] ?? null;
                    $recordId = $pkCol ? ($row[$pkCol] ?? 0) : 0;
                    $isActive = (int)($row['is_active'] ?? 1);
                ?>
                <div class="opted-row-item" id="row-<?= $recordId ?>">

                    <div class="opted-row-inner">
                        <div class="opted-item-main">
                            <h4 class="opted-item-name"><?= htmlspecialchars($title ?? 'Unnamed') ?></h4>
                            <p class="opted-item-sub">
                                <?php if ($price !== null): ?>
                                    ₱<?= number_format((float)$price, 2) ?> &nbsp;|&nbsp;
                                <?php endif; ?>
                                <span class="badge <?= $isActive ? 'bg-success' : 'bg-secondary' ?>" style="font-size:11px;">
                                    <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                            </p>
                        </div>
                        <div class="opted-actions">
                            <button type="button" class="action-icon-btn opt-btn-edit" title="Edit"
                                onclick="toggleEditForm(<?= $recordId ?>)">
                                <i data-lucide="pencil" size="16"></i>
                            </button>
                            <button type="button" class="action-icon-btn opt-btn-delete" title="Delete"
                                onclick="confirmDelete(<?= $recordId ?>, '<?= addslashes(htmlspecialchars($title ?? '')) ?>', '<?= htmlspecialchars($active_tab) ?>')">
                                <i data-lucide="trash-2" size="16"></i>
                            </button>
                        </div>
                    </div>

                    <div class="opted-edit-form" id="edit-form-<?= $recordId ?>">
                        <form action="../process/posting_options.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action"    value="edit">
                            <input type="hidden" name="tab"       value="<?= htmlspecialchars($active_tab) ?>">
                            <input type="hidden" name="option_id" value="<?= $recordId ?>">
                            <div class="opted-edit-grid">

                                <?php if($active_tab == 'frame_types'): ?>
                                    <div>
                                        <label class="opt-label">Type Name <span>*</span></label>
                                        <input type="text" name="edit_name" class="opt-input" required
                                            value="<?= htmlspecialchars($row['type_name'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="opt-label">Price (₱)</label>
                                        <input type="number" step="0.01" min="0" name="edit_price" class="opt-input"
                                            value="<?= $row['type_price'] ?? 0 ?>">
                                    </div>

                                <?php elseif($active_tab == 'frame_designs'): ?>
                                    <div>
                                        <label class="opt-label">Design Name <span>*</span></label>
                                        <input type="text" name="edit_name" class="opt-input" required
                                            value="<?= htmlspecialchars($row['design_name'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="opt-label">Price (₱)</label>
                                        <input type="number" step="0.01" min="0" name="edit_price" class="opt-input"
                                            value="<?= $row['price'] ?? 0 ?>">
                                    </div>

                                    <div style="grid-column: span 2;">
                                        <span class="remove-label">Current Images (Check to remove)</span>
                                        <div class="current-images-grid">
                                            <?php 
                                            // Fetch current images for this design
                                            $imgStmt = $conn->prepare("SELECT * FROM tbl_frame_design_images WHERE frame_design_id = ?");
                                            $imgStmt->bind_param("i", $recordId);
                                            $imgStmt->execute();
                                            $images = $imgStmt->get_result();
                                            if($images->num_rows > 0):
                                                while($img = $images->fetch_assoc()): ?>
                                                    <div class="current-img-wrapper">
                                                        <img src="../uploads/<?= htmlspecialchars($img['image_name']) ?>" alt="Design">
                                                        <label class="delete-checkbox-wrapper">
                                                            <input type="checkbox" name="remove_images[]" value="<?= $img['image_design_id'] ?>">
                                                            <div class="delete-overlay">DELETE</div>
                                                        </label>
                                                    </div>
                                                <?php endwhile;
                                            else: ?>
                                                <small class="text-muted">No images uploaded.</small>
                                            <?php endif; ?>
                                        </div>

                                        <label class="opt-label">Add More Photos <small style="font-weight:400;color:#6b7280;">(optional)</small></label>
                                        <div class="multi-upload-zone">
                                            <input type="file" name="design_images[]" id="editDesignInput-<?= $recordId ?>" multiple accept="image/*">
                                            <label class="multi-upload-trigger" for="editDesignInput-<?= $recordId ?>">
                                                <i data-lucide="image" size="18"></i>
                                                <span>Click to upload additional images</span>
                                            </label>
                                            <div class="preview-scroll" id="editDesignPreviews-<?= $recordId ?>"></div>
                                        </div>
                                    </div>

                                <?php elseif($active_tab == 'frame_colors'): ?>
                                    <div>
                                        <label class="opt-label">Color Name <span>*</span></label>
                                        <input type="text" name="edit_name" class="opt-input" required
                                            value="<?= htmlspecialchars($row['color_name'] ?? '') ?>">
                                    </div>

                                <?php elseif($active_tab == 'frame_sizes'): ?>
                                    <div>
                                        <label class="opt-label">Base Price (₱)</label>
                                        <input type="number" step="0.01" min="0" name="edit_price" class="opt-input"
                                            value="<?= $row['price'] ?? 0 ?>">
                                    </div>

                                <?php elseif($active_tab == 'matboard_colors'): ?>
                                    <div>
                                        <label class="opt-label">Color Name <span>*</span></label>
                                        <input type="text" name="edit_name" class="opt-input" required
                                            value="<?= htmlspecialchars($row['matboard_color_name'] ?? '') ?>">
                                    </div>

                                <?php elseif($active_tab == 'mount_types'): ?>
                                    <div>
                                        <label class="opt-label">Mount Type Name <span>*</span></label>
                                        <input type="text" name="edit_name" class="opt-input" required
                                            value="<?= htmlspecialchars($row['mount_name'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="opt-label">Additional Fee (₱)</label>
                                        <input type="number" step="0.01" min="0" name="edit_price" class="opt-input"
                                            value="<?= $row['additional_fee'] ?? 0 ?>">
                                    </div>

                                <?php elseif($active_tab == 'paper_types'): ?>
                                    <div>
                                        <label class="opt-label">Paper Name <span>*</span></label>
                                        <input type="text" name="edit_name" class="opt-input" required
                                            value="<?= htmlspecialchars($row['paper_name'] ?? '') ?>">
                                    </div>
                                    <div>
                                        <label class="opt-label">Price (₱)</label>
                                        <input type="number" step="0.01" min="0" name="edit_price" class="opt-input"
                                            value="<?= $row['price'] ?? 0 ?>">
                                    </div>
                                <?php endif; ?>

                                <div>
                                    <label class="opt-label">Status</label>
                                    <select name="edit_is_active" class="opt-input">
                                        <option value="1" <?= $isActive === 1 ? 'selected' : '' ?>>Active - visible to customers</option>
                                        <option value="0" <?= $isActive === 0 ? 'selected' : '' ?>>Inactive - hidden from customers</option>
                                    </select>
                                </div>

                            </div>

                            <div class="opted-edit-actions">
                                <button type="button" class="opt-btn-clear" onclick="toggleEditForm(<?= $recordId ?>)">Cancel</button>
                                <button type="submit" class="opt-btn-submit" style="padding:8px 20px; font-size:13px;">Save Changes</button>
                            </div>
                        </form>
                    </div>

                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i data-lucide="inbox" size="48" style="opacity:0.3; margin-bottom:15px;"></i>
                    <p>No records found for this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content custom-post-modal shadow">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="modal-icon-box mx-auto mb-3" style="width:60px;height:60px;border-radius:50%;background:#fff0f0;color:#d9534f;display:flex;align-items:center;justify-content:center;font-size:24px;">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <p class="text-muted mb-1">Are you sure you want to delete this option?</p>
                <h5 id="deleteOptionName" class="fw-bold mb-4"></h5>
            </div>
            <div class="modal-footer border-0 justify-content-center pb-4">
                <button type="button" class="btn border-secondary px-4" data-bs-dismiss="modal" style="border-radius:10px;">Cancel</button>
                <form id="deleteOptionForm" method="POST" action="../process/posting_options.php" style="display:inline;">
                    <input type="hidden" name="action"    value="delete">
                    <input type="hidden" name="tab"       id="deleteOptionTab">
                    <input type="hidden" name="option_id" id="deleteOptionId">
                    <button type="submit" class="btn btn-danger px-4" style="border-radius:10px;">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    lucide.createIcons();

    function toggleEditForm(id) {
        const form = document.getElementById('edit-form-' + id);
        form.classList.toggle('open');
        lucide.createIcons();
    }

    function confirmDelete(id, name, tab) {
        document.getElementById('deleteOptionName').textContent = name;
        document.getElementById('deleteOptionId').value         = id;
        document.getElementById('deleteOptionTab').value        = tab;
        new bootstrap.Modal(document.getElementById('deleteConfirmModal')).show();
    }

    function initMultiUpload(inputId, previewContainerId) {
        const input   = document.getElementById(inputId);
        const preview = document.getElementById(previewContainerId);
        if (!input || !preview) return;
        input.addEventListener('change', function () {
            preview.innerHTML = '';
            Array.from(this.files).forEach((file, idx) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const thumb = document.createElement('div');
                    thumb.className = 'preview-thumb';
                    thumb.innerHTML = `<img src="${e.target.result}" alt="preview">${idx === 0 ? '<div class="primary-badge">Primary</div>' : ''}`;
                    preview.appendChild(thumb);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    initMultiUpload('addDesignInput', 'addDesignPreviews');
    document.querySelectorAll('[id^="editDesignInput-"]').forEach(input => {
        const id = input.id.replace('editDesignInput-', '');
        initMultiUpload('editDesignInput-' + id, 'editDesignPreviews-' + id);
    });
</script>
</body>
</html>