<?php
// customer/customer_shop_custom.php
require_once __DIR__ . '/../includes/customer_header.php';
require_once __DIR__ . '/../classes/CustomFrame/CustomFrameService.php';

$service     = new CustomFrameService($conn);
$builderData = $service->getFrameBuilderData();

$frameTypes     = $builderData['frame_types'];
$frameDesigns   = $builderData['frame_designs'];
$frameColors    = $builderData['frame_colors'];
$frameSizes     = $builderData['frame_sizes'];
$matboardColors = $builderData['matboard_colors'];
$mountTypes     = $builderData['mount_types'];
$paperTypes     = $builderData['paper_types'];

// Preset sizes from Figma (filter from DB, fallback to fixed list)
$presetLabels = ['4×6"','5×7"','8×10"','8×12"','11×14"','12×16"','16×20"','18×24"','20×24"'];
?>

<div class="csc-page">
    <div class="container-fluid csc-container">

        <!-- Page Title -->
        <div class="csc-title-wrap mb-4">
            <h1 class="csc-title">Custom Frames</h1>
            <p class="csc-subtitle">Design your own frame to your liking.</p>
        </div>

        <div class="row g-4">
            <!-- LEFT: Builder -->
            <div class="col-lg-8">

                <!-- 1. SERVICE TYPE -->
                <div class="csc-section mb-4">
                    <div class="csc-section-header">SERVICE TYPE</div>
                    <div class="csc-section-body">
                        <div class="csc-service-grid">
                            <label class="csc-service-option selected" data-value="FRAME_ONLY">
                                <input type="radio" name="service_type" value="FRAME_ONLY" checked hidden>
                                <div class="csc-service-icon">
                                    <i class="fas fa-border-all"></i>
                                </div>
                                <div>
                                    <div class="csc-service-label">Frame only</div>
                                    <div class="csc-service-sub">Frame without print</div>
                                </div>
                            </label>
                            <label class="csc-service-option" data-value="FRAME_PRINT">
                                <input type="radio" name="service_type" value="FRAME_PRINT" hidden>
                                <div class="csc-service-icon">
                                    <i class="fas fa-print"></i>
                                </div>
                                <div>
                                    <div class="csc-service-label">Frame & Print</div>
                                    <div class="csc-service-sub">Frame + Printed image</div>
                                </div>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- 2. PAPER TYPE & IMAGE UPLOAD (shown only when Frame & Print) -->
                <div class="csc-section mb-4" id="csc-print-section" style="display:none;">
                    <div class="csc-section-header">PAPER TYPE & IMAGE UPLOAD</div>
                    <div class="csc-section-body">
                        <div class="row g-4 align-items-start">
                            <!-- Image Upload -->
                            <div class="col-md-5">
                                <label class="csc-field-label">IMAGE UPLOAD</label>
                                <label class="csc-upload-area" id="csc-upload-label" for="csc-image-input">
                                    <div class="csc-upload-placeholder" id="csc-upload-placeholder">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <img id="csc-image-preview" class="csc-upload-preview" style="display:none;" alt="Preview">
                                    <div class="csc-upload-btn">
                                        <i class="fas fa-arrow-up-from-bracket"></i>
                                    </div>
                                    <input type="file" id="csc-image-input" accept="image/*" hidden>
                                </label>
                            </div>
                            <!-- Paper Type -->
                            <div class="col-md-7">
                                <label class="csc-field-label">PAPER TYPE</label>
                                <select class="csc-select" id="csc-paper-type">
                                    <option value="">Select paper type</option>
                                    <?php foreach ($paperTypes as $pt): ?>
                                        <option value="<?= $pt['paper_type_id'] ?>"
                                                data-price="<?= $pt['price'] ?>"
                                                data-logic="<?= $pt['pricing_logic'] ?>">
                                            <?= htmlspecialchars($pt['paper_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. FRAME TYPE & DESIGN -->
                <div class="csc-section mb-4">
                    <div class="csc-section-header">FRAME TYPE & DESIGN</div>
                    <div class="csc-section-body">
                        <!-- Frame Type -->
                        <div class="csc-type-grid mb-4">
                            <?php foreach ($frameTypes as $ft): ?>
                                <label class="csc-type-option" data-value="<?= $ft['frame_type_id'] ?>" data-price="<?= $ft['type_price'] ?>">
                                    <input type="radio" name="frame_type" value="<?= $ft['frame_type_id'] ?>" hidden>
                                    <span class="csc-type-name"><?= htmlspecialchars($ft['type_name']) ?></span>
                                    <span class="csc-type-price">₱<?= number_format($ft['type_price'], 2) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Frame Designs Grid -->
                        <div class="csc-design-grid">
                            <?php foreach ($frameDesigns as $fd): ?>
                                <label class="csc-design-card" data-value="<?= $fd['frame_design_id'] ?>" data-price="<?= $fd['price'] ?>">
                                    <input type="radio" name="frame_design" value="<?= $fd['frame_design_id'] ?>" hidden>
                                    <?php if ($fd['image_name']): ?>
                                        <img src="../assets/img/<?= htmlspecialchars($fd['image_name']) ?>"
                                             alt="<?= htmlspecialchars($fd['design_name']) ?>"
                                             class="csc-design-img">
                                    <?php else: ?>
                                        <div class="csc-design-placeholder">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div class="csc-design-name"><?= htmlspecialchars($fd['design_name']) ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 4. FRAME COLOR -->
                <div class="csc-section mb-4">
                    <div class="csc-section-header">FRAME COLOR</div>
                    <div class="csc-section-body">
                        <div class="csc-color-grid">
                            <?php foreach ($frameColors as $fc): ?>
                                <label class="csc-color-card" data-value="<?= $fc['frame_color_id'] ?>">
                                    <input type="radio" name="frame_color" value="<?= $fc['frame_color_id'] ?>" hidden>
                                    <?php if ($fc['color_image']): ?>
                                        <img src="../assets/img/<?= htmlspecialchars($fc['color_image']) ?>"
                                             alt="<?= htmlspecialchars($fc['color_name']) ?>"
                                             class="csc-color-swatch">
                                    <?php else:
                                        $colorMap = ['gold'=>'#c9a84c','silver'=>'#e8e8e8','white'=>'#ffffff','walnut'=>'#7b3f00','navy'=>'#0a0e2b','red'=>'#cc0000','black'=>'#111111'];
                                        $bgColor  = $colorMap[strtolower($fc['color_name'])] ?? '#cccccc';
                                    ?>
                                        <div class="csc-color-swatch csc-color-fallback"
                                             style="background: <?= $bgColor ?>;">
                                        </div>
                                    <?php endif; ?>
                                    <div class="csc-color-name"><?= htmlspecialchars($fc['color_name']) ?></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 5. FRAME SIZE -->
                <div class="csc-section mb-4">
                    <div class="csc-section-header">FRAME SIZE</div>
                    <div class="csc-section-body">
                        <div class="csc-size-pills" id="csc-size-pills">
                            <?php foreach ($frameSizes as $fs): ?>
                                <label class="csc-size-pill"
                                       data-value="<?= $fs['frame_size_id'] ?>"
                                       data-width="<?= $fs['width_inch'] ?>"
                                       data-height="<?= $fs['height_inch'] ?>"
                                       data-price="<?= $fs['price'] ?>">
                                    <input type="radio" name="frame_size" value="<?= $fs['frame_size_id'] ?>" hidden>
                                    <?= htmlspecialchars($fs['dimension']) ?>
                                </label>
                            <?php endforeach; ?>
                            <label class="csc-size-pill active" data-value="OTHER" data-width="" data-height="" data-price="0">
                                <input type="radio" name="frame_size" value="OTHER" checked hidden>
                                Other
                            </label>
                        </div>

                        <!-- Custom width/height inputs (always visible, auto-fills when preset chosen) -->
                        <div class="csc-custom-size-wrap mt-3">
                            <div class="csc-custom-size-field">
                                <label class="csc-field-label">WIDTH (IN)</label>
                                <input type="number" class="csc-size-input" id="csc-width" placeholder="e.g. 22" min="1" step="0.5">
                            </div>
                            <span class="csc-size-x">×</span>
                            <div class="csc-custom-size-field">
                                <label class="csc-field-label">HEIGHT (IN)</label>
                                <input type="number" class="csc-size-input" id="csc-height" placeholder="e.g. 28" min="1" step="0.5">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 6. MAT-BOARD COLOR -->
                <div class="csc-section mb-4">
                    <div class="csc-section-header">MAT-BOARD COLOR</div>
                    <div class="csc-section-body">
                        <!-- Primary -->
                        <p class="csc-matboard-label">Primary Mat-board <span class="csc-optional">(optional)</span></p>
                        <div class="csc-matboard-grid" id="csc-primary-matboard">
                            <label class="csc-matboard-card selected" data-value="0">
                                <input type="radio" name="primary_matboard" value="0" checked hidden>
                                <div class="csc-matboard-swatch csc-matboard-none"></div>
                                <span>None</span>
                            </label>
                            <?php foreach ($matboardColors as $mc): ?>
                                <label class="csc-matboard-card" data-value="<?= $mc['matboard_color_id'] ?>" data-price="<?= $mc['base_price'] ?>">
                                    <input type="radio" name="primary_matboard" value="<?= $mc['matboard_color_id'] ?>" hidden>
                                    <?php if ($mc['image_name']): ?>
                                        <img src="../assets/img/<?= htmlspecialchars($mc['image_name']) ?>"
                                             class="csc-matboard-swatch" alt="">
                                    <?php else:
                                        $mbMap = ['white'=>'#ffffff','cream'=>'#f5e6c8','black'=>'#111111','navy'=>'#0a0e2b','forest green'=>'#1a4731'];
                                        $mbBg  = $mbMap[strtolower($mc['matboard_color_name'])] ?? '#cccccc';
                                    ?>
                                        <div class="csc-matboard-swatch" style="background:<?= $mbBg ?>;"></div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($mc['matboard_color_name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>

                        <!-- Secondary -->
                        <p class="csc-matboard-label mt-3">Secondary Mat-board <span class="csc-optional">(optional)</span></p>
                        <div class="csc-matboard-grid" id="csc-secondary-matboard">
                            <label class="csc-matboard-card selected" data-value="0">
                                <input type="radio" name="secondary_matboard" value="0" checked hidden>
                                <div class="csc-matboard-swatch csc-matboard-none"></div>
                                <span>None</span>
                            </label>
                            <?php foreach ($matboardColors as $mc): ?>
                                <label class="csc-matboard-card" data-value="<?= $mc['matboard_color_id'] ?>" data-price="<?= $mc['base_price'] ?>">
                                    <input type="radio" name="secondary_matboard" value="<?= $mc['matboard_color_id'] ?>" hidden>
                                    <?php if ($mc['image_name']): ?>
                                        <img src="../assets/img/<?= htmlspecialchars($mc['image_name']) ?>"
                                             class="csc-matboard-swatch" alt="">
                                    <?php else:
                                        $mbMap = ['white'=>'#ffffff','cream'=>'#f5e6c8','black'=>'#111111','navy'=>'#0a0e2b','forest green'=>'#1a4731'];
                                        $mbBg  = $mbMap[strtolower($mc['matboard_color_name'])] ?? '#cccccc';
                                    ?>
                                        <div class="csc-matboard-swatch" style="background:<?= $mbBg ?>;"></div>
                                    <?php endif; ?>
                                    <span><?= htmlspecialchars($mc['matboard_color_name']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 7. MOUNT TYPE -->
                <div class="csc-section mb-4">
                    <div class="csc-section-header">MOUNT TYPE</div>
                    <div class="csc-section-body">
                        <div class="csc-service-grid">
                            <?php foreach ($mountTypes as $mt): ?>
                                <label class="csc-service-option" data-value="<?= $mt['mount_type_id'] ?>" data-price="<?= $mt['additional_fee'] ?? 0 ?>">
                                    <input type="radio" name="mount_type" value="<?= $mt['mount_type_id'] ?>" hidden>
                                    <div>
                                        <div class="csc-service-label"><?= htmlspecialchars($mt['mount_name']) ?></div>
                                        <div class="csc-service-sub">
                                            <?= strtolower($mt['mount_name']) === 'hanging' || strtolower($mt['mount_name']) === 'wall hanging' ? 'Hang on wall' : 'Tabletop display' ?>
                                        </div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

            </div><!-- end col-lg-8 -->

            <!-- RIGHT: Order Summary -->
            <div class="col-lg-4">
                <div class="csc-summary-wrap">
                    <div class="csc-summary-header">ORDER SUMMARY</div>
                    <div class="csc-summary-body">
                        <div class="csc-summary-row">
                            <span>Service</span>
                            <span id="sum-service" class="csc-summary-val">Frame only</span>
                        </div>
                        <div class="csc-summary-row">
                            <span>Frame Type</span>
                            <span id="sum-frame-type" class="csc-summary-muted">not selected</span>
                        </div>
                        <div class="csc-summary-row">
                            <span>Design</span>
                            <span id="sum-design" class="csc-summary-muted">not selected</span>
                        </div>
                        <div class="csc-summary-row">
                            <span>Color</span>
                            <span id="sum-color" class="csc-summary-muted">not selected</span>
                        </div>
                        <div class="csc-summary-row">
                            <span>Size</span>
                            <span id="sum-size" class="csc-summary-val">Other</span>
                        </div>
                        <div class="csc-summary-row">
                            <span>Mat-board</span>
                            <span id="sum-matboard" class="csc-summary-muted">not selected</span>
                        </div>
                        <div class="csc-summary-row">
                            <span>Mount</span>
                            <span id="sum-mount" class="csc-summary-muted">not selected</span>
                        </div>
                        <div class="csc-summary-row" id="sum-image-row" style="display:none;">
                            <span>Image</span>
                            <span id="sum-image" class="csc-summary-muted">not uploaded</span>
                        </div>
                        <div class="csc-summary-row" id="sum-paper-row" style="display:none;">
                            <span>Paper</span>
                            <span id="sum-paper" class="csc-summary-muted">not selected</span>
                        </div>

                        <!-- Quantity -->
                        <div class="csc-summary-row csc-summary-qty-row">
                            <span>Quantity</span>
                            <div class="csc-qty-control">
                                <button class="csc-qty-btn" id="csc-qty-minus">−</button>
                                <input type="number" class="csc-qty-input" id="csc-qty" value="1" min="1" max="99">
                                <button class="csc-qty-btn" id="csc-qty-plus">+</button>
                            </div>
                        </div>

                        <hr class="csc-summary-divider">

                        <!-- Total -->
                        <div class="csc-summary-total-row">
                            <span class="csc-summary-total-label">Total</span>
                            <span class="csc-summary-total-amount" id="csc-total">₱0.00</span>
                        </div>

                        <!-- Buttons -->
                        <div class="csc-summary-actions">
                            <button class="csc-btn-cart" id="csc-add-to-cart">
                                <i class="fas fa-cart-shopping"></i> Add to Cart
                            </button>
                            <button class="csc-btn-buy" id="csc-buy-now">Buy Now</button>
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- end row -->
    </div><!-- end container -->
</div>

<!-- Pass PHP data to JS -->
<script>
const CSC_DATA = {
    frameSizes: <?= json_encode(array_map(fn($s) => [
        'id'     => $s['frame_size_id'],
        'label'  => $s['dimension'],
        'width'  => $s['width_inch'],
        'height' => $s['height_inch'],
        'price'  => $s['price'],
    ], $frameSizes)) ?>,
    paperTypes: <?= json_encode(array_map(fn($p) => [
        'id'    => $p['paper_type_id'],
        'name'  => $p['paper_name'],
        'price' => $p['price'],
        'logic' => $p['pricing_logic'],
    ], $paperTypes)) ?>,
};
</script>
<script src="../assets/js/customer_shop_custom.js"></script>

<?php include __DIR__ . '/../includes/idx_footer.php'; ?>