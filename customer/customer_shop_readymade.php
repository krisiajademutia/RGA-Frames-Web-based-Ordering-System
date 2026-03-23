<?php
// customer/customer_shop_readymade.php
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/ReadyMade/Repository/ReadyMadeRepository.php';
require_once __DIR__ . '/../classes/ReadyMade/ReadyMadeService.php';

$repo          = new \Classes\ReadyMade\Repository\ReadyMadeRepository($conn);
$service       = new \Classes\ReadyMade\ReadyMadeService($repo);
$search_term   = trim($_GET['search'] ?? '');
$posted_frames = $service->getFrames($search_term);

$matOptions   = $service->getMatboardColors();
$paperOptions = $service->getPaperTypes();
$mountTypes   = $service->getMountTypes();

$multiplierRow    = $conn->query("SELECT LOWER(paper_name) AS paper_name FROM tbl_paper_type");
$paperMultipliers = [];
if ($multiplierRow) {
    while ($r = $multiplierRow->fetch_assoc()) {
        $pName = $r['paper_name'];
        if (str_contains($pName, 'photo'))  $paperMultipliers[$pName] = 1.5;
        if (str_contains($pName, 'canvas')) $paperMultipliers[$pName] = 2.5;
    }
}
?>

<div class="post-admin-container animate-fade-in-up" style="margin-top: 120px; padding-bottom: 60px;">
    <div class="csc-title-wrap mb-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="csc-title">Ready-Made Frames</h1>
            <p class="csc-subtitle">Browse our collection of crafted frames</p>
        </div>
        <div class="shop-search-filter-group">
            <form action="" method="GET" class="search-filter-pill">
                <input type="text" name="search" class="search-input-field"
                       placeholder="Search frames..."
                       value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit" class="search-action-btn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="posted-grid p-4">
        <?php if (!empty($posted_frames)): foreach ($posted_frames as $row): ?>
        <div class="posted-card-item border">
            <div class="posted-image-box">
                <img src="/rga_frames/uploads/<?= htmlspecialchars($row['image_name'] ?? '') ?>" alt="Frame">
            </div>
            <div class="posted-info">
                <h4 class="posted-item-title"><?= htmlspecialchars($row['product_name']) ?></h4>
                <p class="posted-item-meta m-0"><?= $row['width'] ?>x<?= $row['height'] ?>"</p>
                <span class="posted-stock-pill <?= ($row['stock'] > 0) ? 'bg-success' : 'bg-danger' ?> mt-2">
                    <?= ($row['stock'] > 0) ? $row['stock'] . ' In Stock' : 'Out of Stock' ?>
                </span>
            </div>
            <div class="posted-card-footer border-top pt-3">
                <span class="posted-price-text">₱ <?= number_format($row['product_price'], 2) ?></span>
                <button class="btn btn-sm rounded-pill px-3 shadow-sm open-details-btn"
                        style="background: #0F473A; color: white;"
                        data-bs-toggle="modal"
                        data-bs-target="#productDetailsModal"
                        data-product='<?= htmlspecialchars(json_encode($row), ENT_QUOTES) ?>'>
                    <i class="fa-solid fa-eye me-1"></i> Details
                </button>
            </div>
        </div>
        <?php endforeach; else: ?>
        <div class="p-5 text-center w-100">
            <i class="fa-solid fa-box-open fa-2x text-muted mb-3"></i>
            <p class="text-muted">No frames found for "<?= htmlspecialchars($search_term) ?>".</p>
        </div>
        <?php endif; ?>
    </div>

    <!-- Product Details Modal -->
    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content custom-modal-content shadow-lg">
                <div class="modal-body p-5">
                    <div class="row g-5">

                        <!-- Left: Image + specs -->
                        <div class="col-md-5">
                            <div class="p-2 bg-light rounded-4 mb-3">
                                <img id="modalProductImg" src=""
                                     class="img-fluid rounded-4 w-100 object-fit-cover"
                                     style="aspect-ratio: 16/11;">
                            </div>
                            <h2 id="modalProductName" class="modal-product-title"></h2>
                            <div class="spec-row"><span class="spec-label">Size</span><span class="spec-value" id="specSize"></span></div>
                            <div class="spec-row"><span class="spec-label">Design</span><span class="spec-value" id="specDesign"></span></div>
                            <div class="spec-row"><span class="spec-label">Color</span><span class="spec-value" id="specColor"></span></div>
                            <div class="spec-row border-0"><span class="spec-label">Stock</span><span class="spec-value text-success" id="specStock"></span></div>
                        </div>

                        <!-- Right: Form -->
                        <div class="col-md-7">
                            <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                            <form id="addToCartForm" enctype="multipart/form-data">
                                <input type="hidden" name="r_product_id" id="modalProductId">
                                <input type="hidden" id="selectedService">
                                <input type="hidden" id="selectedPaperId">
                                <input type="hidden" id="selectedMatId">
                                <input type="hidden" id="selectedSecondaryMatId">
                                <input type="hidden" id="selectedMountId">
                                <input type="hidden" id="hiddenProductWidth">
                                <input type="hidden" id="hiddenProductHeight">
                                <input type="hidden" id="hiddenCurrentMultiplier" value="0">

                                <!-- Service Type -->
                                <section class="mb-4">
                                    <label class="cust-rdymd-section-label">Service Type</label>
                                    <div class="cust-rdymd-tile-group">
                                        <div class="cust-rdymd-option-tile active"
                                             onclick="selectService(this, 'FRAME_ONLY')">
                                            <strong>Frame only</strong><span>No print</span>
                                        </div>
                                        <div class="cust-rdymd-option-tile"
                                             onclick="selectService(this, 'FRAME&PRINT')">
                                            <strong>Frame & Print</strong><span>Requires image upload</span>
                                        </div>
                                    </div>
                                    <div id="cust-rdymd-upload-section" class="mt-3 p-3 border rounded bg-light">
                                        <div id="cust-rdymd-image-preview-container">
                                            <img id="cust-rdymd-image-preview" src="">
                                        </div>
                                        <label class="small fw-bold text-muted mb-2">Upload Photo</label>
                                        <input type="file" id="cust-rdymd-image-input" name="print_image"
                                               class="form-control form-control-sm"
                                               onchange="previewUserImage(this)">
                                    </div>
                                </section>

                                <!-- Paper Type -->
                                <section id="cust-rdymd-paper-type-section" class="mb-4">
                                    <label class="cust-rdymd-section-label">Paper Type</label>
                                    <div class="cust-rdymd-tile-group">
                                        <?php foreach ($paperOptions as $index => $paper): ?>
                                        <div class="cust-rdymd-option-tile <?= $index === 0 ? 'active' : '' ?>"
                                             data-paper-id="<?= $paper['paper_type_id'] ?>"
                                             data-multiplier="<?= $paperMultipliers[strtolower($paper['paper_name'])] ?? 0 ?>"
                                             onclick="selectPaper(this)">
                                            <strong><?= htmlspecialchars($paper['paper_name']) ?></strong>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>

                                <!-- Primary Matboard -->
                                <section class="mb-4">
                                    <label class="cust-rdymd-section-label">
                                        Primary Mat-board <small class="text-muted fw-normal">(optional)</small>
                                    </label>
                                    <div class="cust-rdymd-swatch-group">
                                        <div class="cust-rdymd-swatch-item active"
                                             style="background:#f3f4f6; font-size:9px; display:flex; align-items:center; justify-content:center;"
                                             onclick="selectMat(this, null, true)">None</div>
                                        <?php foreach ($matOptions as $mat): ?>
                                        <div class="cust-rdymd-swatch-item"
                                             style="<?= $mat['image_name'] ? "background-image:url('/rga_frames/uploads/" . htmlspecialchars($mat['image_name']) . "'); background-size:cover;" : "background:#ccc;" ?>"
                                             title="<?= htmlspecialchars($mat['matboard_color_name']) ?> (+₱<?= number_format($mat['base_price'], 2) ?>)"
                                             data-mat-id="<?= $mat['matboard_color_id'] ?>"
                                             data-price="<?= $mat['base_price'] ?>"
                                             onclick="selectMat(this)"></div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>

                                <!-- Secondary Matboard -->
                                <section id="cust-rdymd-secondary-mat-section" class="mb-4">
                                    <label class="cust-rdymd-section-label">Secondary Mat-board</label>
                                    <div class="cust-rdymd-swatch-group">
                                        <div class="cust-rdymd-swatch-item active"
                                             style="background:#f3f4f6; font-size:9px; display:flex; align-items:center; justify-content:center;"
                                             onclick="selectSecondaryMat(this, null)">None</div>
                                        <?php foreach ($matOptions as $mat): ?>
                                        <div class="cust-rdymd-swatch-item"
                                             style="<?= $mat['image_name'] ? "background-image:url('/rga_frames/uploads/" . htmlspecialchars($mat['image_name']) . "'); background-size:cover;" : "background:#ccc;" ?>"
                                             title="<?= htmlspecialchars($mat['matboard_color_name']) ?> (+₱<?= number_format($mat['base_price'], 2) ?>)"
                                             data-mat-id="<?= $mat['matboard_color_id'] ?>"
                                             data-price="<?= $mat['base_price'] ?>"
                                             onclick="selectSecondaryMat(this)"></div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>

                                <!-- Mount Type -->
                                <section class="mb-4">
                                    <label class="cust-rdymd-section-label">Mount Type</label>
                                    <div class="cust-rdymd-tile-group">
                                        <?php foreach ($mountTypes as $index => $mount): ?>
                                        <div class="cust-rdymd-option-tile <?= $index === 0 ? 'active' : '' ?>"
                                             data-mount-id="<?= $mount['mount_type_id'] ?>"
                                             data-price="<?= $mount['additional_fee'] ?>"
                                             onclick="selectMount(this)">
                                            <strong><?= htmlspecialchars($mount['mount_name']) ?></strong>
                                            <span>(+₱<?= number_format($mount['additional_fee'], 2) ?>)</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>

                                <!-- Quantity + Total -->
                                <div class="mt-5 d-flex align-items-center justify-content-between border-top pt-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <label class="fw-bold small text-muted">Quantity</label>
                                        <div class="qty-container">
                                            <button type="button" class="qty-btn" onclick="adjustQty(-1)">-</button>
                                            <input type="number" name="quantity" id="modalQtyInput"
                                                   class="qty-input-field" value="1" min="1" readonly>
                                            <button type="button" class="qty-btn" onclick="adjustQty(1)">+</button>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="text-muted small d-block">Total Price</span>
                                        <h3 id="modalProductPrice" class="fw-bold m-0" style="color:#0F473A;">₱ 0.00</h3>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex gap-2 mt-4 cust-rdymd-btn-actions-row">
                                    <button type="button"
                                            class="cust-rdymd-btn-add-cart w-100 rounded-pill"
                                            onclick="handleReadyMadeSubmit('add_to_cart')">Add to Cart</button>
                                    <button type="button"
                                            class="cust-rdymd-btn-buy-now w-100 rounded-pill"
                                            onclick="handleReadyMadeSubmit('buy_now')">Buy Now</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<div id="cust-rdymd-toast-wrap"></div>

<script src="../assets/js/customer_shop_readymade.js"></script>

<?php require_once __DIR__ . '/../includes/idx_footer.php'; ?>