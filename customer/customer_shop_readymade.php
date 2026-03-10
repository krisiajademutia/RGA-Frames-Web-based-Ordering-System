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

<link rel="stylesheet" href="../assets/css/customer_shop.css">

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
                        <option value="" selected>Size</option>
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
            <span>ALL READY-MADE FRAMES</span>
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
                                <?= ($row['stock'] > 0) ? $row['stock'] . ' In Stock' : 'Out of Stock' ?>
                            </span>
                        </div>
                        <div class="posted-card-footer border-top pt-3">
                            <span class="posted-price-text">₱ <?= number_format($row['product_price'], 2) ?></span>
                            <button class="btn btn-sm rounded-pill px-3 shadow-sm" 
                                    style="background: var(--forest-dark); color: white; font-weight: 700;"
                                    data-bs-toggle="modal" 
                                    data-bs-target="#productDetailsModal"
                                    data-product='<?= json_encode($row) ?>'
                                    <?= ($row['stock'] <= 0) ? 'disabled' : '' ?>>
                                <i class="fa-solid fa-eye me-1"></i> Details
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

    <div class="modal fade" id="productDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content custom-modal-content shadow-lg">
                <div class="modal-body p-5">
                    <div class="row g-5">
                        <div class="col-md-5">
                            <div class="p-2 bg-light rounded-4 mb-3">
                                <img id="modalProductImg" src="" class="img-fluid rounded-4 w-100 object-fit-cover" style="aspect-ratio: 16/11;">
                            </div>
                            <h2 id="modalProductName" class="modal-product-title"></h2>
                            
                            <div class="spec-row"><span class="spec-label">Size</span><span class="spec-value" id="specSize"></span></div>
                            <div class="spec-row"><span class="spec-label">Design</span><span class="spec-value" id="specDesign"></span></div>
                            <div class="spec-row"><span class="spec-label">Color</span><span class="spec-value" id="specColor"></span></div>
                            <div class="spec-row border-0"><span class="spec-label">Stock</span><span class="spec-value text-success" id="specStock"></span></div>
                            
                            <div class="mt-4">
                                <span class="text-muted small">starts at</span>
                                <h3 id="modalProductPrice" class="fw-bold" style="color: var(--forest-dark);"></h3>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                            
                            <section>
                                <label class="section-label">Service Type</label>
                                <div class="tile-group">
                                    <div class="option-tile active"><strong>Frame only</strong><span>Frame without print</span></div>
                                    <div class="option-tile"><strong>Frame & Print</strong><span>Frame + Printed image</span></div>
                                </div>
                            </section>

                            <section>
                                <label class="section-label">Primary Mat-board <small class="text-muted fw-normal">(optional)</small></label>
                                <div class="swatch-group">
                                    <div class="swatch-item active" style="background:#f3f4f6; font-size:9px; display:flex; align-items:center; justify-content:center;">None</div>
                                    <div class="swatch-item" style="background:#000033"></div>
                                    <div class="swatch-item" style="background:#004236"></div>
                                    <div class="swatch-item" style="background:#ffffff;"></div>
                                    <div class="swatch-item" style="background:#ffe4c4"></div>
                                    <div class="swatch-item" style="background:#000000"></div>
                                </div>
                            </section>

                            <section>
                                <label class="section-label">Mount Type</label>
                                <div class="tile-group">
                                    <div class="option-tile active"><strong>Wall Hanging</strong><span>Hang on wall</span></div>
                                    <div class="option-tile"><strong>Stand</strong><span>Tabletop display</span></div>
                                </div>
                            </section>

                            <form id="addToCartForm" class="mt-5">
                                <input type="hidden" name="product_id" id="modalProductId">
                                <div class="d-flex align-items-center justify-content-between gap-3">
                                    <div class="d-flex align-items-center gap-3">
                                        <label class="fw-bold small text-muted">Quantity</label>
                                        <div class="qty-container">
                                            <button type="button" class="qty-btn" onclick="adjustQty(-1)">-</button>
                                            <input type="number" name="quantity" id="modalQtyInput" class="qty-input-field" value="1" min="1" readonly>
                                            <button type="button" class="qty-btn" onclick="adjustQty(1)">+</button>
                                        </div>
                                    </div>
                                    <h3 id="footerPriceDisplay" class="fw-bold m-0" style="color: var(--forest-dark);"></h3>
                                </div>

                                <div class="d-flex gap-2 mt-4">
                                    <button type="button" class="btn-add-cart" onclick="submitAddToCart()">Add to Cart</button>
                                    <button type="button" class="btn-buy-now">Buy Now</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const productModal = document.getElementById('productDetailsModal');
    
    // Clear backdrop manually when the modal is hidden
    productModal.addEventListener('hidden.bs.modal', function () {
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => backdrop.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = 'auto';
    });

    productModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const product = JSON.parse(button.getAttribute('data-product'));

        document.getElementById('modalProductId').value = product.r_product_id;
        document.getElementById('modalProductName').innerText = product.product_name;
        document.getElementById('modalProductImg').src = "/rga_frames/uploads/" + product.image_name;
        
        const priceFormatted = "₱ " + parseFloat(product.product_price).toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('modalProductPrice').innerText = priceFormatted;
        document.getElementById('footerPriceDisplay').innerText = priceFormatted;

        document.getElementById('specSize').innerText = `${product.width}x${product.height}"`;
        document.getElementById('specDesign').innerText = product.design_name;
        document.getElementById('specColor').innerText = product.color_name;
        document.getElementById('specStock').innerText = `${product.stock} available`;
        
        document.getElementById('modalQtyInput').max = product.stock;
        document.getElementById('modalQtyInput').value = 1;
    });

    function adjustQty(val) {
        const input = document.getElementById('modalQtyInput');
        const max = parseInt(input.getAttribute('max'));
        let newVal = parseInt(input.value) + val;
        if (newVal >= 1 && newVal <= max) {
            input.value = newVal;
        }
    }

    function submitAddToCart() {
        const formData = new FormData(document.getElementById('addToCartForm'));
        alert(`Adding ${formData.get('quantity')} units of Product ID ${formData.get('product_id')} to cart!`);
        
        // Proper way to hide using Bootstrap Instance
        const modalInstance = bootstrap.Modal.getInstance(productModal) || new bootstrap.Modal(productModal);
        modalInstance.hide();
    }
</script>
<?php require_once __DIR__ . '/../includes/idx_footer.php'; ?>