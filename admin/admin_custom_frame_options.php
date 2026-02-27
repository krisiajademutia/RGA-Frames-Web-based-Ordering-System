<?php
ob_start();
session_start();

// 1. DATABASE CONNECTION
$conn_file = __DIR__ . '/../config/db_connect.php';
if (!file_exists($conn_file)) {
    die("Fatal: db_connect.php not found.");
}
include $conn_file;

if (!$conn || $conn->connect_error) {
    die("Database Connection Failed.");
}

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'frame_types';

// 3. FORM SUBMISSION HANDLER
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_option'])) {
    
    $success = false;
    $status = isset($_POST['is_active']) ? 1 : 0;

    // A. FRAME TYPES
    if ($active_tab == 'frame_types') {
        $name = $_POST['type_name'];
        $price = (double)$_POST['type_price'];
        $img = $_FILES['type_image']['name'];
        move_uploaded_file($_FILES['type_image']['tmp_name'], "../uploads/" . $img);

        $stmt = $conn->prepare("INSERT INTO tbl_frame_types (type_name, type_price, image_name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $name, $price, $img, $status);
        $success = $stmt->execute();
    }

    // B. FRAME DESIGNS
    elseif ($active_tab == 'frame_designs') {
        $name = $_POST['design_name'];
        $price = (double)$_POST['design_price'];
        $img = $_FILES['design_image']['name'];
        move_uploaded_file($_FILES['design_image']['tmp_name'], "../uploads/" . $img);

        $stmt = $conn->prepare("INSERT INTO tbl_frame_designs (design_name, price, image_name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $name, $price, $img, $status);
        $success = $stmt->execute();
    }

    // C. FRAME COLORS
    elseif ($active_tab == 'frame_colors') {
        $name = $_POST['color_name'];
        // tbl_frame_colors typically only has name and status in basic setups
        $stmt = $conn->prepare("INSERT INTO tbl_frame_colors (color_name, is_active) VALUES (?, ?)");
        $stmt->bind_param("si", $name, $status);
        $success = $stmt->execute();
    }

    // D. FRAME SIZES
    elseif ($active_tab == 'frame_sizes') {
        $dim = $_POST['dimension'];
        $w = (double)$_POST['width_inch'];
        $h = (double)$_POST['height_inch'];
        $t = $w * $h; 
        $price = (double)$_POST['price'];

        $stmt = $conn->prepare("INSERT INTO tbl_frame_sizes (dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sddddi", $dim, $w, $h, $t, $price, $status);
        $success = $stmt->execute();
    }

    // E. MATBOARD COLORS
    elseif ($active_tab == 'matboard_colors') {
        $name = $_POST['mat_name'];
        $price = (double)($_POST['mat_price'] ?? 0.00);
        $img = $_FILES['mat_image']['name'];
        move_uploaded_file($_FILES['mat_image']['tmp_name'], "../uploads/" . $img);

        $stmt = $conn->prepare("INSERT INTO tbl_matboard_colors (matboard_color_name, base_price, image_name, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sdsi", $name, $price, $img, $status);
        $success = $stmt->execute();
    }

    // F. MOUNT TYPES
    elseif ($active_tab == 'mount_types') {
        $name = $_POST['mount_name'];
        $fee = (double)$_POST['mount_fee'];

        $stmt = $conn->prepare("INSERT INTO tbl_mount_type (mount_name, additional_fee, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("sdi", $name, $fee, $status);
        $success = $stmt->execute();
    }

    // G. PAPER TYPES
    elseif ($active_tab == 'paper_types') {
        $name = $_POST['paper_name'];
        $logic = $_POST['pricing_logic']; // 'FIXED' or 'CALCULATED'
        $dim = $_POST['p_dimension'];
        $w = (double)$_POST['p_width'];
        $h = (double)$_POST['p_height'];
        $t = $w * $h;
        $price = (double)$_POST['paper_price'];

        $stmt = $conn->prepare("INSERT INTO tbl_paper_type (paper_name, pricing_logic, dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssdddi", $name, $logic, $dim, $w, $h, $t, $price, $status);
        $success = $stmt->execute();
    }

    if ($success) {
        header("Location: admin_custom_frame_options.php?tab=$active_tab&success=1");
        exit();
    }
}
?>
<?php
// Ensure $active_tab is defined for the UI logic
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'frame_types';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Frame Management System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-cream: #F9F7F2;
            --sidebar-white: #FFFFFF;
            --accent-brown: #7C5E3D;
            --gold-button: #B3975D;
            --border-tan: #E5DEC9;
            --text-main: #6D5D4B;
            --text-sub: #A39485;
            --input-bg: #FAF9F6;
        }

        body {
            background-color: var(--bg-cream);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--text-main);
            margin: 0;
            padding: 40px;
        }

        .layout {
            display: flex;
            max-width: 1400px;
            margin: 0 auto;
            gap: 30px;
        }

        /* SIDEBAR NAVIGATION */
        .sidebar {
            width: 280px;
            background: var(--sidebar-white);
            border-radius: 15px;
            border: 1px solid var(--border-tan);
            padding: 20px 0;
            height: fit-content;
            box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        }

        .nav-section-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-sub);
            padding: 10px 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            text-decoration: none;
            color: var(--text-main);
            font-size: 14px;
            gap: 12px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-item i { width: 20px; color: var(--text-sub); font-size: 16px; }
        .nav-item:hover { background: #FAF9F6; }
        .nav-item.active {
            background: #FAF7F0;
            color: var(--accent-brown);
            font-weight: 600;
            border-left: 4px solid var(--gold-button);
        }
        .nav-item.active i { color: var(--gold-button); }

        /* MAIN CONTENT */
        .main-content { flex: 1; }
        .page-header h1 {
            font-family: 'Georgia', serif;
            font-size: 32px;
            color: var(--accent-brown);
            margin: 0 0 5px 0;
        }
        .page-header p { color: var(--text-sub); margin-bottom: 30px; font-size: 15px; }

        /* CARD STYLING */
        .card {
            background: white;
            border-radius: 15px;
            border: 1px solid var(--border-tan);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header {
            padding: 18px 25px;
            border-bottom: 1px solid var(--border-tan);
            font-size: 13px;
            font-weight: 700;
            color: var(--accent-brown);
            display: flex;
            align-items: center;
            gap: 10px;
            text-transform: uppercase;
        }

        .card-body { padding: 30px; }

        .db-info {
            background: #FAF9F6;
            border-left: 4px solid var(--gold-button);
            padding: 15px 20px;
            font-size: 13px;
            color: var(--text-sub);
            margin-bottom: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
        }

        .form-group { margin-bottom: 20px; }
        .full-width { grid-column: span 2; }

        label {
            display: block;
            font-size: 11px;
            font-weight: 700;
            color: var(--accent-brown);
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .input-wrapper { position: relative; }
        .currency-prefix {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-sub);
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-tan);
            border-radius: 8px;
            background: var(--input-bg);
            font-size: 14px;
            color: var(--text-main);
            box-sizing: border-box;
        }

        input.with-prefix { padding-left: 30px; }
        .sub-label { display: block; font-size: 11px; color: var(--text-sub); margin-top: 5px; font-style: italic; }

        .presets-container { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
        .preset-btn {
            padding: 6px 12px;
            border: 1px solid var(--border-tan);
            border-radius: 6px;
            background: white;
            font-size: 12px;
            cursor: pointer;
            transition: 0.2s;
        }
        .preset-btn:hover { border-color: var(--gold-button); background: var(--bg-cream); }

        .upload-box {
            border: 1.5px dashed var(--border-tan);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            color: var(--text-sub);
            cursor: pointer;
            background: #FFFEFC;
        }
        .upload-box i { font-size: 24px; margin-bottom: 8px; display: block; color: var(--gold-button); }

        .form-footer {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border-tan);
        }

        .btn {
            padding: 12px 28px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            border: none;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-clear { background: white; color: var(--text-main); border: 1px solid var(--border-tan); }
        .btn-submit { background: var(--gold-button); color: white; }
    </style>
</head>
<body>

<div class="layout">
    <aside class="sidebar">
        <div class="nav-section-label">Catalogue Options</div>
        <a href="?tab=frame_types" class="nav-item <?= $active_tab == 'frame_types' ? 'active' : '' ?>"><i class="fa-solid fa-table-cells-large"></i> Frame Types</a>
        <a href="?tab=frame_designs" class="nav-item <?= $active_tab == 'frame_designs' ? 'active' : '' ?>"><i class="fa-solid fa-pen-nib"></i> Frame Designs</a>
        <a href="?tab=frame_colors" class="nav-item <?= $active_tab == 'frame_colors' ? 'active' : '' ?>"><i class="fa-solid fa-palette"></i> Frame Colors</a>
        <a href="?tab=frame_sizes" class="nav-item <?= $active_tab == 'frame_sizes' ? 'active' : '' ?>"><i class="fa-solid fa-ruler-combined"></i> Frame Sizes</a>
        
        <div class="nav-section-label" style="margin-top:20px;">Customization</div>
        <a href="?tab=matboard_colors" class="nav-item <?= $active_tab == 'matboard_colors' ? 'active' : '' ?>"><i class="fa-solid fa-layer-group"></i> Matboard Colors</a>
        <a href="?tab=mount_types" class="nav-item <?= $active_tab == 'mount_types' ? 'active' : '' ?>"><i class="fa-solid fa-border-none"></i> Mount Types</a>
        <a href="?tab=paper_types" class="nav-item <?= $active_tab == 'paper_types' ? 'active' : '' ?>"><i class="fa-solid fa-file-lines"></i> Paper Types</a>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Frame Options</h1>
            <p>Manage frame components and catalogue pricing structures</p>
        </div>

        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-circle-plus"></i> Add New <?= str_replace('_', ' ', $active_tab) ?>
            </div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                
                <?php if($active_tab == 'frame_types'): ?>
                    <div class="db-info">TBL_FRAME_TYPES — type_name, type_price, image_name, is_active</div>
                    <div class="form-grid">
                        <div class="form-group"><label>Type Name *</label><input type="text" name="type_name" required placeholder="Standard"><small class="sub-label">type_name</small></div>
                        <div class="form-group"><label>Base Price (₱) *</label><div class="input-wrapper"><span class="currency-prefix">₱</span><input type="number" step="0.01" name="type_price" class="with-prefix" required></div><small class="sub-label">type_price</small></div>
                        <div class="form-group full-width">
                            <label>Frame Type Preview</label>
                            <input type="file" name="type_image" id="type_img" style="display:none;" required>
                            <div class="upload-box" onclick="document.getElementById('type_img').click();">
                                <i class="fa-regular fa-image"></i><b>Upload Type Image</b><br><small>image_name</small>
                            </div>
                        </div>
                    </div>

                <?php elseif($active_tab == 'frame_designs'): ?>
                    <div class="db-info">TBL_FRAME_DESIGNS — design_name, price, image_name, is_active</div>
                    <div class="form-grid">
                        <div class="form-group"><label>Design Name *</label><input type="text" name="design_name" required placeholder="Baroque Gold"><small class="sub-label">design_name</small></div>
                        <div class="form-group"><label>Added Price (₱) *</label><div class="input-wrapper"><span class="currency-prefix">₱</span><input type="number" step="0.01" name="design_price" class="with-prefix" required></div><small class="sub-label">price</small></div>
                        <div class="form-group full-width">
                            <input type="file" name="design_image" id="design_img" style="display:none;" required>
                            <div class="upload-box" onclick="document.getElementById('design_img').click();">
                                <i class="fa-regular fa-image"></i><b>Upload Design Texture</b><br><small>image_name</small>
                            </div>
                        </div>
                    </div>

                <?php elseif($active_tab == 'frame_colors'): ?>
                    <div class="db-info">TBL_FRAME_COLORS — color_name, is_active</div>
                    <div class="form-grid">
                        <div class="form-group full-width"><label>Color Name *</label><input type="text" name="color_name" required placeholder="Walnut"><small class="sub-label">color_name</small></div>
                    </div>

                <?php elseif($active_tab == 'frame_sizes'): ?>
                    <div class="db-info">TBL_FRAME_SIZES — dimension, width_inch, height_inch, total_inch, price, is_active</div>
                    <label>Quick Presets</label>
                    <div class="presets-container">
                        <?php foreach(['4×6','5×7','8×10','11×14','16×20'] as $s) echo "<button type='button' class='preset-btn' onclick=\"applySizePreset('$s')\">$s</button>"; ?>
                    </div>
                    <div class="form-grid">
                        <div class="form-group"><label>Width (in) *</label><input type="number" step="0.1" name="width_inch" id="fs_width" oninput="updateFsDimension()" required><small class="sub-label">width_inch</small></div>
                        <div class="form-group"><label>Height (in) *</label><input type="number" step="0.1" name="height_inch" id="fs_height" oninput="updateFsDimension()" required><small class="sub-label">height_inch</small></div>
                        <div class="form-group"><label>Display Label</label><input type="text" name="dimension" id="fs_dimension" readonly style="background:#f0f0f0;"><small class="sub-label">dimension (auto)</small></div>
                        <div class="form-group"><label>Price (₱) *</label><div class="input-wrapper"><span class="currency-prefix">₱</span><input type="number" step="0.01" name="price" class="with-prefix" required></div><small class="sub-label">price</small></div>
                    </div>
                    <script>
                        function updateFsDimension() {
                            const w = document.getElementById('fs_width').value;
                            const h = document.getElementById('fs_height').value;
                            document.getElementById('fs_dimension').value = (w && h) ? w + '×' + h : '';
                        }
                        function applySizePreset(size) {
                            const parts = size.split('×');
                            document.getElementById('fs_width').value = parts[0];
                            document.getElementById('fs_height').value = parts[1];
                            updateFsDimension();
                        }
                    </script>

                <?php elseif($active_tab == 'matboard_colors'): ?>
                    <div class="db-info">TBL_MATBOARD_COLORS — matboard_color_name, base_price, image_name, is_active</div>
                    <div class="form-grid">
                        <div class="form-group"><label>Matboard Name *</label><input type="text" name="mat_name" required><small class="sub-label">matboard_color_name</small></div>
                        <div class="form-group"><label>Base Price (₱)</label><div class="input-wrapper"><span class="currency-prefix">₱</span><input type="number" step="0.01" name="mat_price" class="with-prefix"></div><small class="sub-label">base_price</small></div>
                        <div class="form-group full-width">
                            <input type="file" name="mat_image" id="mat_img" style="display:none;" required>
                            <div class="upload-box" onclick="document.getElementById('mat_img').click();">
                                <i class="fa-solid fa-fill-drip"></i><b>Upload Swatch</b><br><small>image_name</small>
                            </div>
                        </div>
                    </div>

                <?php elseif($active_tab == 'mount_types'): ?>
                    <div class="db-info">TBL_MOUNT_TYPE — mount_name, additional_fee, is_active</div>
                    <div class="form-grid">
                        <div class="form-group"><label>Mount Style Name *</label><input type="text" name="mount_name" required><small class="sub-label">mount_name</small></div>
                        <div class="form-group"><label>Additional Fee (₱) *</label><div class="input-wrapper"><span class="currency-prefix">₱</span><input type="number" step="0.01" name="mount_fee" class="with-prefix" required></div><small class="sub-label">additional_fee</small></div>
                    </div>

                <?php elseif($active_tab == 'paper_types'): ?>
                    <div class="db-info">TBL_PAPER_TYPE — paper_name, pricing_logic, dimension, width_inch, height_inch, total_inch, price, is_active</div>
                    <div class="form-grid">
                        <div class="form-group"><label>Paper Name *</label><input type="text" name="paper_name" required><small class="sub-label">paper_name</small></div>
                        <div class="form-group"><label>Pricing Logic</label><select name="pricing_logic"><option value="FIXED">Fixed Price</option><option value="CALCULATED">Square Inch</option></select></div>
                        <div class="form-group"><label>Width (in)</label><input type="number" step="0.1" name="width_inch" id="p_width" oninput="updatePaperDim()"><small class="sub-label">width_inch</small></div>
                        <div class="form-group"><label>Height (in)</label><input type="number" step="0.1" name="height_inch" id="p_height" oninput="updatePaperDim()"><small class="sub-label">height_inch</small></div>
                        <input type="hidden" name="dimension" id="p_dimension">
                        <div class="form-group full-width"><label>Final Price (₱) *</label><div class="input-wrapper"><span class="currency-prefix">₱</span><input type="number" step="0.01" name="price" class="with-prefix" required></div><small class="sub-label">price</small></div>
                    </div>
                    <script>
                        function updatePaperDim() {
                            const w = document.getElementById('p_width').value;
                            const h = document.getElementById('p_height').value;
                            document.getElementById('p_dimension').value = (w && h) ? w + '×' + h : '';
                        }
                    </script>
                <?php endif; ?>

                <div class="form-group full-width" style="margin-top:20px;">
                    <label>Status</label>
                    <select name="is_active">
                        <option value="1">Active (Visible to users)</option>
                        <option value="0">Inactive (Hidden)</option>
                    </select>
                </div>

                <div class="form-footer">
                    <button type="reset" class="btn btn-clear">Clear Form</button>
                    <button type="submit" name="add_option" class="btn btn-submit">
                        <i class="fa-solid fa-plus"></i> Save to Database
                    </button>
                </div>
                </form>
            </div>
        </div>
    </main>
</div>

</body>
</html>