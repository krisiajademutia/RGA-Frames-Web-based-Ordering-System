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

<style>
    body.modal-open { overflow: auto !important; padding-right: 0 !important; }
    .modal-backdrop.show:nth-of-type(n+2) { display: none !important; }
    /* Visual feedback for selection without changing layout */
    .option-tile.active { border: 2px solid var(--forest-dark) !important; background: #f0fdf4 !important; }
    .swatch-item.active { outline: 2px solid var(--forest-dark); outline-offset: 2px; transform: scale(1.1); }
</style>

<div class="post-admin-container animate-fade-in-up" style="margin-top: 120px; padding-bottom: 60px;">
    <div class="csc-title-wrap mb-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="csc-title">Ready-Made Frames</h1>
            <p class="csc-subtitle">Browse our collection of crafted frames</p>
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
                            <button class="btn btn-sm rounded-pill px-3 shadow-sm open-details-btn" 
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
                                <span class="text-muted small">Total Price</span>
                                <h3 id="modalProductPrice" class="fw-bold" style="color: var(--forest-dark);">₱ 0.00</h3>
                            </div>
                        </div>

                        <div class="col-md-7">
                            <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                            
                            <form id="addToCartForm">
                                <input type="hidden" name="product_id" id="modalProductId">
                                <input type="hidden" name="service_type" id="selectedService" value="Frame only">
                                <input type="hidden" name="matboard_color" id="selectedMat" value="None">
                                <input type="hidden" name="mount_type" id="selectedMount" value="Wall Hanging">

                                <section class="mb-4">
                                    <label class="section-label">Service Type</label>
                                    <div class="tile-group">
                                        <div class="option-tile active" onclick="selectService(this, 0)"><strong>Frame only</strong><span>Frame without print</span></div>
                                        <div class="option-tile" onclick="selectService(this, 150)"><strong>Frame & Print</strong><span>Frame + Printed image (+₱150)</span></div>
                                    </div>
                                </section>

                                <section class="mb-4">
                                    <label class="section-label">Primary Mat-board <small class="text-muted fw-normal">(optional)</small></label>
                                    <div class="swatch-group">
                                        <div class="swatch-item active" style="background:#f3f4f6; font-size:9px; display:flex; align-items:center; justify-content:center;" onclick="selectMat(this, 0, 'None')">None</div>
                                        <div class="swatch-item" style="background:#000033" onclick="selectMat(this, 50, 'Navy Blue')"></div>
                                        <div class="swatch-item" style="background:#004236" onclick="selectMat(this, 50, 'Forest Green')"></div>
                                        <div class="swatch-item" style="background:#ffffff; border: 1px solid #ddd;" onclick="selectMat(this, 50, 'White')"></div>
                                        <div class="swatch-item" style="background:#ffe4c4" onclick="selectMat(this, 50, 'Beige')"></div>
                                        <div class="swatch-item" style="background:#000000" onclick="selectMat(this, 50, 'Black')"></div>
                                    </div>
                                </section>

                                <section class="mb-4">
                                    <label class="section-label">Mount Type</label>
                                    <div class="tile-group">
                                        <div class="option-tile active" onclick="selectMount(this, 'Wall Hanging')"><strong>Wall Hanging</strong><span>Hang on wall</span></div>
                                        <div class="option-tile" onclick="selectMount(this, 'Stand')"><strong>Stand</strong><span>Tabletop display</span></div>
                                    </div>
                                </section>

                                <div class="mt-5">
                                    <div class="d-flex align-items-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <label class="fw-bold small text-muted">Quantity</label>
                                            <div class="qty-container">
                                                <button type="button" class="qty-btn" onclick="adjustQty(-1)">-</button>
                                                <input type="number" name="quantity" id="modalQtyInput" class="qty-input-field" value="1" min="1" readonly>
                                                <button type="button" class="qty-btn" onclick="adjustQty(1)">+</button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-4">
                                        <button type="button" class="btn-add-cart" onclick="submitAddToCart()">Add to Cart</button>
                                        <button type="button" class="btn-buy-now">Buy Now</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let basePrice = 0;
    let servicePrice = 0;
    let matPrice = 0;

    const productModal = document.getElementById('productDetailsModal');

    // 1. POPULATE MODAL ON OPEN
    productModal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const product = JSON.parse(button.getAttribute('data-product'));

        basePrice = parseFloat(product.product_price);
        servicePrice = 0;
        matPrice = 0;

        document.getElementById('modalProductId').value = product.r_product_id;
        document.getElementById('modalProductName').innerText = product.product_name;
        document.getElementById('modalProductImg').src = "/rga_frames/uploads/" + product.image_name;
        document.getElementById('specSize').innerText = `${product.width}x${product.height}"`;
        document.getElementById('specDesign').innerText = product.design_name;
        document.getElementById('specColor').innerText = product.color_name;
        document.getElementById('specStock').innerText = `${product.stock} available`;
        
        document.getElementById('modalQtyInput').max = product.stock;
        document.getElementById('modalQtyInput').value = 1;

        // Reset selections to default
        resetSelections();
        updateTotalPrice();
    });

    // 2. SELECTION FUNCTIONS
    function selectService(el, price) {
        document.querySelectorAll('#addToCartForm .option-tile').forEach(t => {
            if(t.parentNode.previousElementSibling.innerText === 'Service Type') t.classList.remove('active');
        });
        el.classList.add('active');
        servicePrice = price;
        document.getElementById('selectedService').value = el.querySelector('strong').innerText;
        updateTotalPrice();
    }

    function selectMat(el, price, name) {
        document.querySelectorAll('.swatch-item').forEach(s => s.classList.remove('active'));
        el.classList.add('active');
        matPrice = price;
        document.getElementById('selectedMat').value = name;
        updateTotalPrice();
    }

    function selectMount(el, name) {
        document.querySelectorAll('#addToCartForm .option-tile').forEach(t => {
            if(t.parentNode.previousElementSibling.innerText === 'Mount Type') t.classList.remove('active');
        });
        el.classList.add('active');
        document.getElementById('selectedMount').value = name;
    }

    // 3. PRICE CALCULATION
    function updateTotalPrice() {
        const qty = parseInt(document.getElementById('modalQtyInput').value);
        const total = (basePrice + servicePrice + matPrice) * qty;
        document.getElementById('modalProductPrice').innerText = "₱ " + total.toLocaleString(undefined, {minimumFractionDigits: 2});
    }

    function adjustQty(val) {
        const input = document.getElementById('modalQtyInput');
        const max = parseInt(input.getAttribute('max')) || 1;
        let newVal = parseInt(input.value) + val;
        if (newVal >= 1 && newVal <= max) {
            input.value = newVal;
            updateTotalPrice();
        }
    }

    function resetSelections() {
        document.querySelectorAll('.option-tile, .swatch-item').forEach(el => el.classList.remove('active'));
        document.querySelector('.option-tile:first-child').classList.add('active');
        document.querySelector('.swatch-item:first-child').classList.add('active');
        document.getElementById('selectedService').value = "Frame only";
        document.getElementById('selectedMat').value = "None";
        document.getElementById('selectedMount').value = "Wall Hanging";
    }

    // 4. BACKDROP FIX
    productModal.addEventListener('hidden.bs.modal', function () {
        document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
        document.body.classList.remove('modal-open');
        document.body.style.overflow = 'auto';
    });

    function submitAddToCart() {
        const formData = new FormData(document.getElementById('addToCartForm'));
        alert(`Function Triggered!\nProduct ID: ${formData.get('product_id')}\nQty: ${formData.get('quantity')}\nService: ${formData.get('service_type')}\nMat: ${formData.get('matboard_color')}`);
        // Here you would add your fetch() or AJAX call to your cart process file.
    }
</script>

<?php require_once __DIR__ . '/../includes/idx_footer.php'; ?>