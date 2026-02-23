<?php
ob_start(); 
session_start();

$conn_file = __DIR__ . '/../config/db_connect.php';
if (!file_exists($conn_file)) {
    die("Fatal: db_connect.php not found at: $conn_file");
}

include $conn_file;

if (!$conn || $conn->connect_error) {
    die("Fatal: Connection error: " . ($conn->connect_error ?? 'No $conn object'));
}

// --- FETCH DATA FOR DROPDOWNS ---
$types = $conn->query("SELECT frame_type_id, type_name, type_price FROM tbl_frame_types WHERE is_active = 1");
$designs = $conn->query("SELECT frame_design_id, design_name, price FROM tbl_frame_designs WHERE is_active = 1");
$colors = $conn->query("SELECT frame_color_id, color_name FROM tbl_frame_colors WHERE is_active = 1");
$sizes = $conn->query("SELECT frame_size_id, dimension, price FROM tbl_frame_sizes WHERE is_active = 1");

// --- HANDLE FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $type_id = $_POST['frame_type_id'];
    $design_id = $_POST['frame_design_id'];
    $color_id = $_POST['frame_color_id'];
    $size_id = $_POST['frame_size_id'] ?? 0; // Handled by JS selection
    $price = $_POST['product_price'];
    $stock = $_POST['stock_quantity'] ?? 0;

    // Handle Image Upload
    $image_name = $_FILES['image']['name'];
    $target = "../uploads/" . basename($image_name);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $sql = "INSERT INTO tbl_ready_made_product (product_name, frame_type_id, frame_design_id, frame_color_id, frame_size_id, product_price, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiids", $product_name, $type_id, $design_id, $color_id, $size_id, $price, $image_name);
    
    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!'); window.location.href=window.location.href;</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Frames</title>
    <style>
        :root {
            --bg-color: #f7f3ed;
            --primary-brown: #7c5e3d;
            --accent-gold: #b3975d;
            --text-main: #6d5d4b;
            --text-light: #a39485;
            --border-color: #e5dec9;
            --white: #ffffff;
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-main);
            margin: 0;
            padding: 40px;
        }

        .container { max-width: 1200px; margin: 0 auto; }

        .main-header h1 {
            font-family: 'Georgia', serif;
            font-size: 32px;
            color: var(--primary-brown);
            margin-bottom: 5px;
        }

        .main-header p { color: var(--text-light); margin-top: 0; margin-bottom: 30px; }

        .layout { display: flex; gap: 20px; align-items: flex-start; }

        /* Sidebar */
        .sidebar { width: 200px; flex-shrink: 0; }
        .sidebar-group {
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }
        .sidebar-label {
            display: block;
            padding: 15px 20px;
            font-size: 11px;
            font-weight: bold;
            letter-spacing: 1px;
            color: var(--text-light);
            border-bottom: 1px solid var(--border-color);
        }
        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 14px;
        }
        .nav-item.active { background-color: #faf7f0; color: var(--accent-gold); font-weight: bold; }
        .icon-plus { margin-right: 10px; background: var(--accent-gold); color: white; width: 18px; height: 18px; display: inline-flex; align-items: center; justify-content: center; border-radius: 50%; font-size: 12px; }

        /* Main Card */
        .content-card {
            flex-grow: 1;
            background: var(--white);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }

        .card-header {
            padding: 15px 25px;
            font-weight: bold;
            font-size: 13px;
            color: var(--primary-brown);
            border-bottom: 1px solid var(--border-color);
            background: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-container { padding: 30px; }

        .info-box {
            background-color: #f9f8f5;
            border-left: 4px solid var(--accent-gold);
            padding: 15px;
            font-size: 13px;
            color: var(--text-light);
            margin-bottom: 30px;
            line-height: 1.5;
        }

        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 40px; margin-bottom: 25px; }

        .field-group { display: flex; flex-direction: column; }
        .field-group label { font-size: 11px; font-weight: bold; margin-bottom: 8px; color: var(--primary-brown); letter-spacing: 0.5px; }
        .required { color: #c0392b; }

        input[type="text"], input[type="number"], select {
            background-color: #f9f8f5;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 12px;
            color: var(--text-main);
            outline: none;
        }

        .db-hint { font-size: 10px; color: var(--text-light); margin-top: 5px; }

        /* Size Presets */
        .size-presets { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px; }
        .btn-size {
            background: #f9f8f5;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 8px 12px;
            font-size: 12px;
            cursor: pointer;
            color: var(--text-main);
        }
        .btn-size.active { background: var(--accent-gold); color: white; border-color: var(--accent-gold); }

        /* Price/Stock styling */
        .input-wrapper { position: relative; display: flex; align-items: center; }
        .input-wrapper span { position: absolute; left: 12px; color: var(--text-light); }
        .price-field { padding-left: 25px !important; }

        /* Upload Box */
        .upload-box {
            border: 2px dashed var(--accent-gold);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            background: #fdfbf8;
            cursor: pointer;
            transition: 0.3s;
        }
        .upload-box:hover { background: #f7f3ed; }
        .upload-box i { font-size: 24px; color: var(--accent-gold); display: block; margin-bottom: 10px; }
        .upload-box p { margin: 5px 0; font-size: 13px; }

        .form-actions { display: flex; justify-content: flex-end; gap: 15px; margin-top: 40px; }
        .btn-clear { background: white; border: 1px solid var(--border-color); padding: 12px 25px; border-radius: 8px; color: var(--text-light); cursor: pointer; }
        .btn-submit { background: var(--accent-gold); border: none; padding: 12px 30px; border-radius: 8px; color: white; font-weight: bold; cursor: pointer; }
    </style>
</head>
<body>
  <?php include __DIR__ . '/../includes/admin_header.php'; ?>
<div class="container">
    <header class="main-header">
        <h1>Post Frames</h1>
        <p>Post and manage ready-made frame products visible to customers</p>
    </header>

    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-group">
                <span class="sidebar-label">POST FRAMES</span>
                <nav>
                    <a href="#" class="nav-item active"><span class="icon-plus">+</span> Post New Frame</a>
                    <a href="#" class="nav-item">🗃️ Posted Frames</a>
                </nav>
            </div>
        </aside>

        <main class="content-card">
            <div class="card-header">📦 POST NEW READY-MADE FRAME</div>
            <div class="form-container">
                <div class="info-box">
                    TBL_READY_MADE_PRODUCT — Frame type, design and color are selected from options posted in <strong>Frame Options</strong>. Size uses the preset/custom input.
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="field-group">
                            <label>PRODUCT NAME <span class="required">*</span></label>
                            <input type="text" name="product_name" required placeholder="e.g. Classic Oak 8×10 Frame">
                            <span class="db-hint">product_name</span>
                        </div>

                        <div class="field-group">
                            <label>FRAME TYPE <span class="required">*</span></label>
                            <select name="frame_type_id" class="calc-trigger" required>
                                <option value="" data-price="0">— Select from Frame Options —</option>
                                <?php while($row = $types->fetch_assoc()): ?>
                                    <option value="<?= $row['frame_type_id'] ?>" data-price="<?= $row['type_price'] ?>"><?= $row['type_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <span class="db-hint">frame_type_id → TBL_FRAME_TYPES</span>
                        </div>

                        <div class="field-group">
                            <label>FRAME DESIGN <span class="required">*</span></label>
                            <select name="frame_design_id" class="calc-trigger" required>
                                <option value="" data-price="0">— Select from Frame Options —</option>
                                <?php while($row = $designs->fetch_assoc()): ?>
                                    <option value="<?= $row['frame_design_id'] ?>" data-price="<?= $row['price'] ?>"><?= $row['design_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <span class="db-hint">frame_design_id → TBL_FRAME_DESIGNS</span>
                        </div>

                        <div class="field-group">
                            <label>FRAME COLOR <span class="required">*</span></label>
                            <select name="frame_color_id" required>
                                <option value="">— Select from Frame Options —</option>
                                <?php while($row = $colors->fetch_assoc()): ?>
                                    <option value="<?= $row['frame_color_id'] ?>"><?= $row['color_name'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <span class="db-hint">frame_color_id → TBL_FRAME_COLORS</span>
                        </div>
                    </div>

                    <div class="field-group" style="margin-bottom: 25px;">
                        <label>FRAME SIZE <span class="required">*</span></label>
                        <span class="db-hint" style="margin-bottom: 10px;">Click a preset. Links to frame_size_ID → TBL_FRAME_SIZES.</span>
                        <div class="size-presets">
                            <input type="hidden" name="frame_size_id" id="selected_size_id" required>
                            <?php while($row = $sizes->fetch_assoc()): ?>
                                <button type="button" class="btn-size calc-trigger-btn" 
                                        data-id="<?= $row['frame_size_id'] ?>" 
                                        data-price="<?= $row['price'] ?>">
                                    <?= $row['dimension'] ?>
                                </button>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="field-group">
                            <label>SELLING PRICE (₱) <span class="required">*</span></label>
                            <div class="input-wrapper">
                                <span>₱</span>
                                <input type="number" step="0.01" name="product_price" id="total_price" class="price-field" value="0.00" required>
                            </div>
                            <span class="db-hint">TBL_READY_MADE_PRODUCT / selling price</span>
                        </div>

                        <div class="field-group">
                            <label>STOCK QUANTITY <span class="required">*</span></label>
                            <input type="number" name="stock_quantity" placeholder="e.g. 10" required>
                            <span class="db-hint">TBL_READY_MADE_PRODUCT_STOCKS.quantity</span>
                        </div>
                    </div>

                    <div class="field-group" style="margin-bottom: 25px;">
                        <label>PRODUCT PHOTO</label>
                        <div class="upload-box" onclick="document.getElementById('fileInput').click();">
                            <input type="file" name="image" id="fileInput" accept="image/*" style="display:none;" required>
                            <i>🖼️</i>
                            <p><strong>Click to upload product photo</strong></p>
                            <p class="db-hint">PNG or JPG · Max 3MB · image_name</p>
                        </div>
                    </div>

                    <div class="field-group" style="max-width: 300px; margin-bottom: 25px;">
                        <label>STATUS</label>
                        <select name="status">
                            <option value="1">Active — visible to customers</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="reset" class="btn-clear">Clear</button>
                        <button type="submit" name="add_product" class="btn-submit">Post Ready-Made Frame</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</div>

<script>
    const selects = document.querySelectorAll('.calc-trigger');
    const sizeBtns = document.querySelectorAll('.calc-trigger-btn');
    const priceInput = document.getElementById('total_price');
    const hiddenSizeInput = document.getElementById('selected_size_id');

    let selectedSizePrice = 0;

    function calculateTotal() {
        let total = 0;
        selects.forEach(s => {
            const price = parseFloat(s.options[s.selectedIndex]?.getAttribute('data-price')) || 0;
            total += price;
        });
        total += selectedSizePrice;
        priceInput.value = total.toFixed(2);
    }

    selects.forEach(s => s.addEventListener('change', calculateTotal));

    sizeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            sizeBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            hiddenSizeInput.value = btn.getAttribute('data-id');
            selectedSizePrice = parseFloat(btn.getAttribute('data-price')) || 0;
            calculateTotal();
        });
    });
</script>

</body>
</html>