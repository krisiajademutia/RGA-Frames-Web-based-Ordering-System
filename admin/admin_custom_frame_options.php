<?php
require_once __DIR__ . '/../process/fetch_options.php';

$status = $_GET['success'] ?? null;
$error_msg = $_GET['error'] ?? null;
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
            Successfully added new <?= strtolower($tab_label) ?>.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php elseif($status === '0' || $error_msg): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            Failed to add option.
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="opt-card">
        <div class="opt-card-header">Add New <?= htmlspecialchars($tab_label ?? '') ?></div>
        <form action="../process/posting_options.php?tab=<?= urlencode($active_tab) ?>" method="POST" enctype="multipart/form-data">
            <div class="opt-form-grid">
                
                <?php if($active_tab == 'frame_types'): ?>
                    <div>
                        <label class="opt-label">Type Name <span>*</span></label>
                        <input type="text" name="type_name" class="opt-input" required placeholder="e.g. Standard">
                    </div>
                    <div style="grid-row: span 2;">
                        <label class="opt-label"><?= strtoupper($tab_label) ?> PHOTO <span>*</span></label>
                        <div class="opt-upload-zone">
                            <input type="file" name="type_image" required>
                            <i data-lucide="image"></i>
                            <span>Click to upload <?= strtolower($tab_label) ?> image</span>
                        </div>
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="type_price" class="opt-input" required>
                    </div>

                <?php elseif($active_tab == 'frame_designs'): ?>
                    <div>
                        <label class="opt-label">Design Name <span>*</span></label>
                        <input type="text" name="design_name" class="opt-input" required>
                    </div>
                    <div style="grid-row: span 2;">
                        <label class="opt-label"><?= strtoupper($tab_label) ?> PHOTO <span>*</span></label>
                        <div class="opt-upload-zone">
                            <input type="file" name="design_image" required>
                            <i data-lucide="image"></i>
                            <span>Click to upload <?= strtolower($tab_label) ?> image</span>
                        </div>
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="price" class="opt-input" required>
                    </div>

                <?php elseif($active_tab == 'frame_colors'): ?>
                    <div>
                        <label class="opt-label">Color Name <span>*</span></label>
                        <input type="text" name="color_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label"><?= strtoupper($tab_label) ?> PHOTO <span>*</span></label>
                        <div class="opt-upload-zone">
                            <input type="file" name="color_image" required>
                            <i data-lucide="palette"></i>
                            <span>Click to upload <?= strtolower($tab_label) ?> image</span>
                        </div>
                    </div>

                <?php elseif($active_tab == 'frame_sizes'): ?>
                    <div>
                        <label class="opt-label">Width (Inches) <span>*</span></label>
                        <input type="number" step="0.01" name="width" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Height (Inches) <span>*</span></label>
                        <input type="number" step="0.01" name="height" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Total Inches <span>*</span></label>
                        <input type="number" step="0.01" name="total_inches" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Base Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="base_price" class="opt-input" required>
                    </div>

                <?php elseif($active_tab == 'matboard_colors'): ?>
                    <div>
                        <label class="opt-label">Color Name <span>*</span></label>
                        <input type="text" name="matboard_color_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label"><?= strtoupper($tab_label) ?> PHOTO <span>*</span></label>
                        <div class="opt-upload-zone">
                            <input type="file" name="matboard_image" required>
                            <i data-lucide="image"></i>
                            <span>Click to upload <?= strtolower($tab_label) ?> image</span>
                        </div>
                    </div>

                <?php elseif($active_tab == 'mount_types'): ?>
                    <div>
                        <label class="opt-label">Mount Type <span>*</span></label>
                        <input type="text" name="generic_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Additional Fee (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="generic_price" class="opt-input" required>
                    </div>

                <?php elseif($active_tab == 'paper_types'): ?>
                    <div>
                        <label class="opt-label">Paper Name <span>*</span></label>
                        <input type="text" name="generic_name" class="opt-input" required>
                    </div>
                    <div>
                        <label class="opt-label">Price (₱) <span>*</span></label>
                        <input type="number" step="0.01" name="generic_price" class="opt-input" required>
                    </div>
                <?php endif; ?>

                <div style="grid-column: span 2; display: flex; justify-content: center; gap: 15px; margin-top: 20px;">
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
                    $title = $row['type_name'] ?? $row['design_name'] ?? $row['color_name'] ?? 
                             $row['matboard_color_name'] ?? $row['mount_name'] ?? $row['paper_name'] ?? 
                             $row['generic_name'] ?? null;

                    if ($title === null && isset($row['width'], $row['height'])) {
                        $title = $row['width'] . '" x ' . $row['height'] . '"';
                    }

                    $price = $row['type_price'] ?? $row['price'] ?? $row['additional_fee'] ?? 
                             $row['base_price'] ?? $row['generic_price'] ?? 0;
                ?>
                <div class="opted-row-item">
                    <div class="opted-item-main">
                        <h4 class="opted-item-name"><?= htmlspecialchars($title ?? 'Unnamed') ?></h4>
                        <p class="opted-item-sub">Price: ₱<?= number_format($price, 2) ?></p>
                    </div>
                    <div class="opted-actions">
                        <button class="action-icon-btn opt-btn-edit" title="Edit"><i data-lucide="pencil" size="16"></i></button>
                        <button class="action-icon-btn opt-btn-delete" title="Delete"><i data-lucide="trash-2" size="16"></i></button>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="p-5 text-center text-muted">
                    <i data-lucide="inbox" size="48" style="opacity: 0.3; margin-bottom: 15px;"></i>
                    <p>No records found for this category.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script>
    lucide.createIcons();
</script>
</body>
</html>