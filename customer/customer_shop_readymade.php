<?php
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Frames/Repository/FrameRepositoryInterface.php';
require_once __DIR__ . '/../classes/Frames/Repository/ReadyMadeFrameRepository.php';
require_once __DIR__ . '/../classes/Frames/FrameService.php';

$repository = new \Classes\Frames\Repository\ReadyMadeFrameRepository($conn);
$frameService = new \Classes\Frames\FrameService($repository);
$posted_frames = $frameService->getAllFrames();
?>

<div class="post-admin-container animate-fade-in-up" style="margin-top: 120px; padding-bottom: 60px;">
    
    <div class="post-header-wrapper mb-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="fw-bold m-0" style="font-size: 26px;">Ready-Made Frames</h1>
            <p class="m-0 mt-1" style="font-size: 16px; color: #555;">Browse our collection of crafted frames</p>
        </div>

        <div class="shop-search-filter-group">
            <form action="" method="GET" class="search-filter-pill">
                <input type="text" name="search" class="search-input-field" placeholder="Search frames...">
                
                <div class="filter-dropdown-wrapper">
                    <select name="size" class="compact-filter-select">
                        <option value="" selected>Size  ∨</option>
                        <option value="4x6">4x6</option>
                        <option value="5x7">5x7</option>
                        <option value="8x10">8x10</option>
                    </select>
                </div>

                <button type="submit" class="search-action-btn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="posted-main-container shadow-sm">
        <div class="posted-header-bar">
            <span>AVAILABLE READY-MADE FRAMES</span>
            <span class="posted-badge"><?= count($posted_frames) ?> items found</span>
        </div>

        <div class="posted-grid p-4">
            <?php if(!empty($posted_frames)): ?>
                <?php foreach($posted_frames as $row): ?>
                    <div class="posted-card-item border">
                        <div class="posted-image-box">
                            <img src="/rga_frames/uploads/<?= $row['image_name'] ?>" alt="Frame">
                            <?php if($row['stock'] > 0 && $row['stock'] <= 5): ?>
                                <span class="badge bg-warning text-dark position-absolute top-0 start-0 m-2">Limited</span>
                            <?php endif; ?>
                        </div>
                        <div class="posted-info">
                            <h4 class="posted-item-title"><?= htmlspecialchars($row['product_name']) ?></h4>
                            <p class="posted-item-meta m-0"><?= $row['width'] ?>x<?= $row['height'] ?>"</p>
                            <p class="posted-item-subtext m-0 text-muted small"><?= $row['design_name'] ?> | <?= $row['type_name'] ?></p>
                            
                            <span class="posted-stock-pill <?= ($row['stock'] > 0) ? 'bg-success' : 'bg-danger' ?> mt-2">
                                <?= ($row['stock'] > 0) ? $row['stock'] . ' in stock' : 'Out of Stock' ?>
                            </span>
                        </div>
                        <div class="posted-card-footer border-top pt-3">
                            <span class="posted-price-text">₱ <?= number_format($row['product_price'], 2) ?></span>
                            <button class="btn btn-sm rounded-pill px-3 shadow-sm" 
                                    style="background: var(--forest-dark); color: white; font-weight: 700;"
                                    <?= ($row['stock'] <= 0) ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-cart-plus me-1"></i> Add to Cart
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-5 text-center w-100">
                    <i class="fa-solid fa-box-open fa-2x text-muted mb-3"></i>
                    <p class="text-muted">No frames match your criteria.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/idx_footer.php'; ?>