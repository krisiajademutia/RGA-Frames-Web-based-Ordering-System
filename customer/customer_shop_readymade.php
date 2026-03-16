<?php
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../config/db_connect.php';
require_once __DIR__ . '/../classes/Frames/Repository/FrameRepositoryInterface.php';
require_once __DIR__ . '/../classes/Frames/Repository/ReadyMadeFrameRepository.php';
require_once __DIR__ . '/../classes/Frames/FrameService.php';
require_once __DIR__ . '/../classes/Option/Repository/FixedPriceRepository.php';

$repository = new \Classes\Frames\Repository\ReadyMadeFrameRepository($conn);
$frameService = new \Classes\Frames\FrameService($repository);
$posted_frames = $frameService->getAllFrames();

$search_term = isset($_GET['search']) ? $_GET['search'] : '';
if (!empty($search_term)) {
    $posted_frames = array_filter($frameService->getAllFrames(), function($frame) use ($search_term) {
        return stripos($frame['product_name'], $search_term) !== false || 
                stripos($frame['design_name'], $search_term) !== false;
    });
} else {
    $posted_frames = $frameService->getAllFrames();
}

$fixedPriceRepo = new FixedPriceRepository($conn);
$allFixedPrices = $fixedPriceRepo->getAll();

$printServicePrice = 0;
$matboardServicePrice = 0;

while ($fpRow = $allFixedPrices->fetch_assoc()) {
    if ($fpRow['dimension'] === 'Print Service') {
        $printServicePrice = (float)$fpRow['fixed_price'];
    } elseif ($fpRow['dimension'] === 'Matboard Service') {
        $matboardServicePrice = (float)$fpRow['fixed_price'];
    }
}
    
//Secondary Matboard
$matOptions = [];
$matColorsQuery = $conn->query("SELECT * FROM tbl_matboard_color");
if ($matColorsQuery && $matColorsQuery->num_rows > 0) {
    while($m = $matColorsQuery->fetch_assoc()) { $matOptions[] = $m; }
}

// FETCH PAPER TYPES FOR PRINT OPTION
$paperOptions = [];
$paperQuery = $conn->query("SELECT * FROM tbl_paper_type");
if ($paperQuery && $paperQuery->num_rows > 0) {
    while($p = $paperQuery->fetch_assoc()) { $paperOptions[] = $p; }
}
?>

<link rel="stylesheet" href="../assets/css/customer_shop.css">

<style>
    body.modal-open { overflow: auto !important; padding-right: 0 !important; }
    .modal-backdrop.show:nth-of-type(n+2) { display: none !important; }
    .option-tile.active { border: 2px solid var(--forest-dark) !important; background: #f0fdf4 !important; }
    .swatch-item.active { outline: 2px solid var(--forest-dark); outline-offset: 2px; transform: scale(1.1); }

    #secondaryMatSection, #uploadSection, #paperTypeSection { display: none; }
    #imagePreviewContainer { 
        display: none; 
        width: 100px; 
        height: 100px; 
        overflow: hidden; 
        border-radius: 8px; 
        margin-bottom: 10px; 
        border: 1px solid #ddd; 
        background: #eee;
    }
    #imagePreview { width: 100%; height: 100%; object-fit: cover; }
    .btn-buy-now { background-color: #fff; color: var(--forest-dark); border: 2px solid var(--forest-dark); font-weight: 700; }
    .btn-buy-now:hover { background-color: var(--forest-dark); color: #fff; }
</style>

<div class="post-admin-container animate-fade-in-up" style="margin-top: 120px; padding-bottom: 60px;">
    <div class="csc-title-wrap mb-5 d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h1 class="csc-title">Ready-Made Frames</h1>
            <p class="csc-subtitle">Browse our collection of crafted frames</p>
        </div>
        <div class="shop-search-filter-group">
            <form action="" method="GET" class="search-filter-pill">
                <input type="text" name="search" class="search-input-field" placeholder="Search frames..." value="<?= htmlspecialchars($search_term) ?>">
                <button type="submit" class="search-action-btn">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </button>
            </form>
        </div>
    </div>

    <div class="posted-grid p-4">
        <?php if(!empty($posted_frames)): ?>
            <?php foreach($posted_frames as $row): ?>
                <div class="posted-card-item border">
                    <div class="posted-image-box">
                        <img src="/rga_frames/uploads/<?= $row['image_name'] ?>" alt="Frame">
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
                                style="background: var(--forest-dark); color: white;"
                                data-bs-toggle="modal" 
                                data-bs-target="#productDetailsModal"
                                data-product='<?= json_encode($row) ?>'>
                            <i class="fa-solid fa-eye me-1"></i> Details
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="p-5 text-center w-100">
                <i class="fa-solid fa-box-open fa-2x text-muted mb-3"></i>
                <p class="text-muted">No frames found for "<?= htmlspecialchars($search_term) ?>".</p>
            </div>
        <?php endif; ?>
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
                        </div>

                        <div class="col-md-7">
                            <button type="button" class="btn-close float-end" data-bs-dismiss="modal" aria-label="Close"></button>
                            <form id="addToCartForm">
                                <input type="hidden" name="product_id" id="modalProductId">
                                <input type="hidden" name="service_type" id="selectedService" value="Frame only">
                                <input type="hidden" name="paper_type" id="selectedPaper" value="None">
                                <input type="hidden" name="primary_mat" id="selectedMat" value="None">
                                <input type="hidden" name="secondary_mat" id="selectedSecondaryMat" value="None">
                                <input type="hidden" name="mount_type" id="selectedMount" value="Wall Hanging">

                                <section class="mb-4">
                                    <label class="section-label">Service Type</label>
                                    <div class="tile-group">
                                        <div class="option-tile active" onclick="selectService(this, 0, false)"><strong>Frame only</strong><span>No print</span></div>
                                        <div class="option-tile" onclick="selectService(this, <?= $printServicePrice ?>, true)"><strong>Frame & Print</strong><span>+₱<?= number_format($printServicePrice, 2) ?></span></div>
                                    </div>
                                    <div id="uploadSection" class="mt-3 p-3 border rounded bg-light">
                                        <div id="imagePreviewContainer"><img id="imagePreview" src=""></div>
                                        <label class="small fw-bold text-muted mb-2">Upload Photo</label>
                                        <input type="file" id="imageInput" name="print_image" class="form-control form-control-sm" onchange="previewUserImage(this)">
                                    </div>
                                </section>

                                <section id="paperTypeSection" class="mb-4">
                                    <label class="section-label">Paper Type</label>
                                    <div class="tile-group">
                                        <?php foreach($paperOptions as $index => $paper): ?>
                                            <div class="option-tile <?= $index === 0 ? 'active' : '' ?>" onclick="selectPaper(this, '<?= $paper['paper_name'] ?>')">
                                                <strong><?= $paper['paper_name'] ?></strong>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>

                                <section class="mb-4">
                                    <label class="section-label">Primary Mat-board <small class="text-muted fw-normal">(optional)</small></label>
                                    <div class="swatch-group">
                                        <div class="swatch-item active" style="background:#f3f4f6; font-size:9px; display:flex; align-items:center; justify-content:center;" onclick="selectMat(this, 0, 'None', true)">None</div>
                                        <?php foreach($matOptions as $mat): ?>
                                            <div class="swatch-item" style="background:<?= $mat['color_code'] ?>" onclick="selectMat(this, <?= $matboardServicePrice ?>, '<?= $mat['color_name'] ?>', false)"></div>
                                        <?php endforeach; ?>
                                    </div>
                                </section>

                                    <section id="secondaryMatSection" class="mb-4">
                                        <label class="section-label">Secondary Mat-board</label>
                                        <div class="swatch-group">
                                            <div class="swatch-item active" style="background:#f3f4f6; font-size:9px; display:flex; align-items:center; justify-content:center;" onclick="selectSecondaryMat(this, 0, 'None')">None</div>
                                            <?php foreach($matOptions as $mat): ?>
                                                <div class="swatch-item" style="background:<?= $mat['color_code'] ?>" onclick="selectSecondaryMat(this, <?= $matboardServicePrice ?>, '<?= $mat['color_name'] ?>')"></div>
                                            <?php endforeach; ?>
                                        </div>
                                    </section>

                                    <section class="mb-4">
                                        <label class="section-label">Mount Type</label>
                                        <div class="tile-group">
                                            <div class="option-tile active" onclick="selectMount(this, 'Wall Hanging')"><strong>Wall Hanging</strong><span>Hang on wall</span></div>
                                            <div class="option-tile" onclick="selectMount(this, 'Stand')"><strong>Stand</strong><span>Tabletop display</span></div>
                                        </div>
                                    </section>

                                    <div class="mt-5 d-flex align-items-center justify-content-between border-top pt-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <label class="fw-bold small text-muted">Quantity</label>
                                            <div class="qty-container">
                                                <button type="button" class="qty-btn" onclick="adjustQty(-1)">-</button>
                                                <input type="number" name="quantity" id="modalQtyInput" class="qty-input-field" value="1" min="1" readonly>
                                                <button type="button" class="qty-btn" onclick="adjustQty(1)">+</button>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <span class="text-muted small d-block">Total Price</span>
                                            <h3 id="modalProductPrice" class="fw-bold m-0" style="color: var(--forest-dark);">₱ 0.00</h3>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 mt-4">
                                        <button type="button" class="btn-add-cart w-100 rounded-pill" onclick="submitAddToCart()">Add to Cart</button>
                                        <button type="button" class="btn btn-buy-now w-100 rounded-pill" onclick="handleBuyNow()">Buy Now</button>
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
        let basePrice = 0, servicePrice = 0, pMatPrice = 0, sMatPrice = 0;
        const productModal = document.getElementById('productDetailsModal');

        productModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const product = JSON.parse(button.getAttribute('data-product'));

            basePrice = parseFloat(product.product_price);
            servicePrice = 0; pMatPrice = 0; sMatPrice = 0;

            document.getElementById('modalProductId').value = product.r_product_id;
            document.getElementById('modalProductName').innerText = product.product_name;
            document.getElementById('modalProductImg').src = "/rga_frames/uploads/" + product.image_name;
            document.getElementById('specSize').innerText = `${product.width}x${product.height}"`;
            document.getElementById('specDesign').innerText = product.design_name;
            document.getElementById('specColor').innerText = product.color_name;
            document.getElementById('specStock').innerText = `${product.stock} available`;
            
            document.getElementById('modalQtyInput').max = product.stock;
            document.getElementById('modalQtyInput').value = 1;

            resetSelections();
            updateTotalPrice();
        });

        function selectService(el, price, showOptions) {
            el.closest('.tile-group').querySelectorAll('.option-tile').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            servicePrice = parseFloat(price);
            document.getElementById('selectedService').value = el.querySelector('strong').innerText;
            
            document.getElementById('uploadSection').style.display = showOptions ? 'block' : 'none';
            document.getElementById('paperTypeSection').style.display = showOptions ? 'block' : 'none';
            
            updateTotalPrice();
        }

        function selectPaper(el, name) {
            el.closest('.tile-group').querySelectorAll('.option-tile').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('selectedPaper').value = name;
        }

        function selectMat(el, price, name, isNone) {
            el.closest('.swatch-group').querySelectorAll('.swatch-item').forEach(s => s.classList.remove('active'));
            el.classList.add('active');
            pMatPrice = parseFloat(price);
            document.getElementById('selectedMat').value = name;
        
            const secSec = document.getElementById('secondaryMatSection');
            secSec.style.display = isNone ? 'none' : 'block';
            if(isNone) { 
                sMatPrice = 0; 
                document.getElementById('selectedSecondaryMat').value = "None";
                secSec.querySelectorAll('.swatch-item').forEach(s => s.classList.remove('active'));
                secSec.querySelector('.swatch-item:first-child').classList.add('active');
            }
            updateTotalPrice();
        }

        function selectSecondaryMat(el, price, name) {
            el.closest('.swatch-group').querySelectorAll('.swatch-item').forEach(s => s.classList.remove('active'));
            el.classList.add('active');
            sMatPrice = parseFloat(price);
            document.getElementById('selectedSecondaryMat').value = name;
            updateTotalPrice();
        }

        function selectMount(el, name) {
            el.closest('.tile-group').querySelectorAll('.option-tile').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('selectedMount').value = name;
        }

        function previewUserImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('imagePreview').src = e.target.result;
                    document.getElementById('imagePreviewContainer').style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        function updateTotalPrice() {
            const qtyInput = document.getElementById('modalQtyInput');
            const qty = parseInt(qtyInput.value);
            const total = (basePrice + servicePrice + pMatPrice + sMatPrice) * qty;
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
            document.getElementById('uploadSection').style.display = 'none';
            document.getElementById('paperTypeSection').style.display = 'none';
            document.getElementById('secondaryMatSection').style.display = 'none';
            document.getElementById('imagePreviewContainer').style.display = 'none';
        }

        productModal.addEventListener('hidden.bs.modal', function () {
            document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
            document.body.classList.remove('modal-open');
            document.body.style.overflow = 'auto';
        });

        function submitAddToCart() {
            const formData = new FormData(document.getElementById('addToCartForm'));
            alert(`Added to Cart!\nTotal: ${document.getElementById('modalProductPrice').innerText}`);
        }
    </script>

    <?php require_once __DIR__ . '/../includes/idx_footer.php'; ?>