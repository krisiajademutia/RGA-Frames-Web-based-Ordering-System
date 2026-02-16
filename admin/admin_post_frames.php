<?php
// admin_custom_frame_options.php

// === MUST BE FIRST LINE - NO SPACE, NO BLANK LINE BEFORE THIS ===

ob_start();           // Start output buffering immediately
session_start();

// Load database connection FIRST - before header or anything else
echo "<!-- Debug: attempting to load db_connect.php -->\n";

$conn_file = __DIR__ . '/../config/db_connect.php';
if (!file_exists($conn_file)) {
    die("Fatal: db_connect.php not found at: $conn_file");
}

include $conn_file;

echo "<!-- Debug: db_connect.php included -->\n";

// Check connection immediately
if (!$conn) {
    die("Fatal: \$conn is null after include");
}
if ($conn->connect_error) {
    die("Fatal: Connection error: " . $conn->connect_error);
}

echo "<!-- Debug: \$conn is alive (" . $conn->host_info . ") -->\n";

// Now safe to include header
include __DIR__ . '/../includes/admin_header.php';

echo "<!-- Debug: admin_header.php included -->\n";

// Check again after header
if (!$conn) {
    die("Fatal: \$conn became null after admin_header.php");
}

// CRITICAL SAFETY CHECK - stop immediately if connection is bad
if (!$conn || $conn->connect_error) {
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1 style='color:red'>Database Connection Failed</h1>";
    echo "<pre>";
    echo "File: " . __DIR__ . '/../db_connect.php' . "\n";
    echo "Error: " . ($conn->connect_error ?? 'No $conn object created') . "\n";
    echo "Check: credentials, database name, host, or file path";
    echo "</pre>";
    exit;
}

// Debug: confirm connection is alive
// (remove these lines after testing if you want)
echo "<!-- Debug: db_connect.php included successfully -->\n";
echo "<!-- Debug: \$conn is valid (" . $conn->host_info . ") -->\n";

// =

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
    $size_id = $_POST['frame_size_id'];
    $price = $_POST['product_price'];

    // Handle Image Upload
    $image_name = $_FILES['image']['name'];
    $target = "../uploads/" . basename($image_name);
    move_uploaded_file($_FILES['image']['tmp_name'], $target);

    $sql = "INSERT INTO tbl_ready_made_product (product_name, frame_type_id, frame_design_id, frame_color_id, frame_size_id, product_price, image) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siiiids", $product_name, $type_id, $design_id, $color_id, $size_id, $price, $image_name);
    
    if ($stmt->execute()) {
        echo "<script>alert('Product added successfully!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Post Custom Frame Options</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --color-green: #A7C957;
            --color-gold: #B89655;
            --color-brown: #795338;
            --bg-light: #f8f9fa;
            --text-dark: #333;
            --text-grey: #666;
        }

        body {
            font-family: 'Inter', 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-dark);
            padding-top: 100px;
        }
        .container { max-width: 800px; margin: 0 auto; padding-bottom: 50px; }
        .page-title { font-size: 24px; font-weight: bold; margin-bottom: 5px; color: var(--color-brown); }
        .page-subtitle { font-size: 14px; color: var(--text-grey); margin-bottom: 25px; }
        
        .orders-card { 
            background: white; 
            border-radius: 12px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.05); 
            padding: 30px;
            border-top: 3px solid var(--color-gold); 
        }

        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: var(--color-brown); }
        input, select { 
            width: 100%; 
            padding: 12px; 
            border: 1px solid #ddd; 
            border-radius: 6px; 
            box-sizing: border-box;
        }
        .btn-submit {
            background-color: var(--color-green);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            width: 100%;
            transition: opacity 0.3s;
        }
        .btn-submit:hover { opacity: 0.9; }
        .price-summary {
            background: #f1f1f1;
            padding: 15px;
            border-radius: 6px;
            margin-top: 10px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>

    <?php include __DIR__ . '/../includes/admin_header.php'; ?>

    <div class="container">
        <h1 class="page-title">Add New Products</h1>
        <p class="page-subtitle">Manage Ready-Made Frames</p>

        <div class="orders-card">
            <form action="" method="POST" enctype="multipart/form-data" id="productForm">
                <div class="form-group">
                    <label>Product Name</label>
                    <input type="text" name="product_name" required placeholder="Enter product name">
                </div>

                <div class="form-group">
                    <label>Frame Type</label>
                    <select name="frame_type_id" id="frame_type" class="calc-trigger" required>
                        <option value="0" data-price="0">Select Type</option>
                        <?php while($row = $types->fetch_assoc()): ?>
                            <option value="<?= $row['frame_type_id'] ?>" data-price="<?= $row['type_price'] ?>"><?= $row['type_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Frame Design</label>
                    <select name="frame_design_id" id="frame_design" class="calc-trigger" required>
                        <option value="0" data-price="0">Select Design</option>
                        <?php while($row = $designs->fetch_assoc()): ?>
                            <option value="<?= $row['frame_design_id'] ?>" data-price="<?= $row['price'] ?>"><?= $row['design_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Frame Color</label>
                    <select name="frame_color_id" required>
                        <option value="">Select Color</option>
                        <?php while($row = $colors->fetch_assoc()): ?>
                            <option value="<?= $row['frame_color_id'] ?>"><?= $row['color_name'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Frame Size</label>
                    <select name="frame_size_id" id="frame_size" class="calc-trigger" required>
                        <option value="0" data-price="0">Select Size</option>
                        <?php while($row = $sizes->fetch_assoc()): ?>
                            <option value="<?= $row['frame_size_id'] ?>" data-price="<?= $row['price'] ?>"><?= $row['dimension'] ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="image" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label>Product Price (Calculated)</label>
                    <input type="number" step="0.01" name="product_price" id="total_price" required>
                    <div class="price-summary">
                        Total based on components: ₱<span id="calc_label">0.00</span>
                    </div>
                </div>

                <button type="submit" name="add_product" class="btn-submit">Save Ready-Made Product</button>
            </form>
        </div>
    </div>

    <script>
        const triggers = document.querySelectorAll('.calc-trigger');
        const priceInput = document.getElementById('total_price');
        const priceLabel = document.getElementById('calc_label');

        function calculatePrice() {
            let total = 0;
            triggers.forEach(select => {
                const selectedOption = select.options[select.selectedIndex];
                const price = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                total += price;
            });
            
            priceInput.value = total.toFixed(2);
            priceLabel.innerText = total.toFixed(2);
        }

        triggers.forEach(select => {
            select.addEventListener('change', calculatePrice);
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>