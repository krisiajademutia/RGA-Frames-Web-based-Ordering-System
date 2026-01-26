<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Database Connection
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "rga_frames_db"; 

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

// Logic to determine the active tab based on the URL parameter
$active_tab = isset($_GET['status']) ? $_GET['status'] : 'frame_colors';

// --- FIXED DELETION LOGIC ---
if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);
    
    if ($active_tab == 'sizes') {
        $stmt = $conn->prepare("DELETE FROM custom_sizes WHERE size_id = ?"); 
    } else {
        $stmt = $conn->prepare("DELETE FROM custom_options WHERE option_id = ?");
    }
    
    if ($stmt) {
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "<p style='color: var(--color-green); font-weight: bold;'>Deleted successfully!</p>";
        }
        $stmt->close();
    } else {
        $message = "<p style='color: var(--color-danger);'>Prepare failed: " . $conn->error . "</p>";
    }
}

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // LOGIC FOR CUSTOM OPTIONS (Designs, Colors, Matboards)
    if (isset($_POST['add_customization'])) {
        $category = $_POST['category'];
        $name = $_POST['option_name'];
        $price = $_POST['price_addition'];
        $image_url = "";

        if (isset($_FILES['image_file']) && $_FILES['image_file']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) { mkdir($target_dir, 0777, true); }
            $file_name = time() . "_" . basename($_FILES["image_file"]["name"]);
            $target_file = $target_dir . $file_name;
            if (move_uploaded_file($_FILES["image_file"]["tmp_name"], $target_file)) {
                $image_url = $target_file;
            }
        }

        $stmt = $conn->prepare("INSERT INTO custom_options (category, name, image_url, price_addition, is_available) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("sssd", $category, $name, $image_url, $price);
        if ($stmt->execute()) {
            $message = "<p style='color: var(--color-green); font-weight: bold;'>Option added successfully!</p>";
        } else {
            $message = "<p style='color: var(--color-danger); font-weight: bold;'>Error: " . $conn->error . "</p>";
        }
        $stmt->close();
    }

    // LOGIC FOR CUSTOM SIZES
    if (isset($_POST['add_size'])) {
        $label = $_POST['size_label'];
        $width = $_POST['width_inches'];
        $height = $_POST['height_inches'];
        $base_price = $_POST['base_price'];

        $stmt = $conn->prepare("INSERT INTO custom_sizes (size_label, width_inches, height_inches, base_price, is_active) VALUES (?, ?, ?, ?, 1)");
        $stmt->bind_param("sddd", $label, $width, $height, $base_price);
        if ($stmt->execute()) {
            $message = "<p style='color: var(--color-green); font-weight: bold;'>Size added successfully!</p>";
        } else {
            $message = "<p style='color: var(--color-danger); font-weight: bold;'>Error: " . $conn->error . "</p>";
        }
        $stmt->close();
    }

}

// Fetch existing options for the current view
$existing_options = [];
if ($active_tab == 'frame_colors') {
    $res = $conn->query("SELECT * FROM custom_options WHERE category IN ('Frame Design', 'Frame Color', 'Matboard') ORDER BY category, name ASC");
    while($row = $res->fetch_assoc()) { $existing_options[] = $row; }
} elseif ($active_tab == 'sizes') {
    $res = $conn->query("SELECT * FROM custom_sizes ORDER BY base_price ASC");
    while($row = $res->fetch_assoc()) { $existing_options[] = $row; }
} 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Frame Catalog</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --color-green: #A7C957;
            --color-gold: #B89655;
            --color-brown: #795338;
            --bg-light: #f8f9fa;
            --text-dark: #333;
            --text-grey: #666;
            --color-danger: #dc3545; 
        }
        body { font-family: 'Inter', 'Segoe UI', sans-serif; margin: 0; padding: 0; background-color: var(--bg-light); color: var(--text-dark); padding-top: 100px; }
        .container { max-width: 1200px; margin: 0 auto; padding-bottom: 50px; }
        .page-title { font-size: 24px; font-weight: bold; margin-bottom: 5px; color: var(--color-brown); }
        .page-subtitle { font-size: 14px; color: var(--text-grey); margin-bottom: 25px; }
        .orders-card { background: white; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; border-top: 3px solid var(--color-gold); }
        .tabs-header { display: flex; border-bottom: 1px solid #eee; padding: 0 20px; overflow-x: auto; }
        .tab-link { padding: 20px 20px; text-decoration: none; color: var(--text-grey); font-weight: 600; font-size: 13px; position: relative; white-space: nowrap; display: flex; align-items: center; transition: color 0.2s; }
        .tab-link:hover { color: var(--color-gold); }
        .tab-link.active { color: var(--color-gold) !important; }
        .tab-link.active::after { content: ''; position: absolute; bottom: -1px; left: 0; width: 100%; height: 3px; background-color: var(--color-gold); }
        .content-area { padding: 40px; text-align: left; }
        .form-container { max-width: 800px; margin-bottom: 50px; }
        .form-title { font-size: 18px; font-weight: 700; color: #0d1b2a; margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 14px; font-weight: 600; color: #4a5568; margin-bottom: 8px; }
        .form-group input, .form-group select { width: 100%; padding: 12px 16px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 15px; background-color: #f8fafc; transition: all 0.2s; box-sizing: border-box; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--color-gold); background-color: #fff; box-shadow: 0 0 0 3px rgba(184, 150, 85, 0.1); }
        .btn-submit { background-color: var(--color-gold); color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; transition: opacity 0.2s; }
        .btn-submit:hover { opacity: 0.9; }
        .dimensions-row { display: flex; align-items: center; gap: 15px; margin-bottom: 20px; }
        .dimension-input-group { width: 150px; }
        .separator-x { font-weight: bold; color: var(--text-grey); margin-top: 25px; }
        .display-section { border-top: 1px solid #eee; padding-top: 40px; }
        .grid-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; margin-top: 20px; }
        .option-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 15px; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s; }
        .option-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .option-info { display: flex; align-items: center; gap: 15px; }
        .option-img-preview { width: 50px; height: 50px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; overflow: hidden; border: 1px solid #eee; }
        .option-img-preview img { width: 100%; height: 100%; object-fit: cover; }
        .option-details h4 { margin: 0; font-size: 15px; color: var(--text-dark); }
        .option-details p { margin: 2px 0 0; font-size: 12px; color: var(--text-grey); }
        .btn-delete { color: var(--color-danger); background: none; border: none; cursor: pointer; font-size: 16px; padding: 8px; transition: opacity 0.2s; }
        .btn-delete:hover { opacity: 0.7; }
    </style>
</head>
<body>

    <?php include 'admin_header.php'; ?>

    <div class="container">
        <h1 class="page-title">Manage Frame Catalog</h1>
        <p class="page-subtitle">Offer customers a variety of framing options for personalized customization.</p>

        <div class="orders-card">
            <div class="tabs-header">
                <a href="?status=frame_colors" class="tab-link <?php echo ($active_tab == 'frame_colors') ? 'active' : ''; ?>">Frame Customizations</a>
                <a href="?status=sizes" class="tab-link <?php echo ($active_tab == 'sizes') ? 'active' : ''; ?>">Sizes</a>
            </div>
            <div class="content-area">
                <div class="form-container">
                    <h2 class="form-title">Add New <?php echo ucwords(str_replace('_', ' ', rtrim($active_tab, 's'))); ?> Option</h2>
                    <?php echo $message; ?>

                    <?php if ($active_tab == 'frame_colors'): ?>
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label>Category *</label>
                                <select name="category" required>
                                    <option value="Frame Design">Frame Design</option>
                                    <option value="Frame Color">Frame Color</option>
                                    <option value="Matboard">Matboard</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Name *</label>
                                <input type="text" name="option_name" placeholder="e.g., Gold, Oak, White" required>
                            </div>
                            <div class="form-group">
                                <label>Image (Upload from device)</label>
                                <input type="file" name="image_file" accept="image/*">
                            </div>
                            <div class="form-group">
                                <label>Price Addition *</label>
                                <input type="number" name="price_addition" step="0.01" placeholder="0.00" required>
                            </div>
                            <button type="submit" name="add_customization" class="btn-submit">Add Option</button>
                        </form>

                    <?php elseif ($active_tab == 'sizes'): ?>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label>Size Label *</label>
                                <input type="text" name="size_label" placeholder="e.g., 8x10, A4, Large" required>
                            </div>
                            <div class="dimensions-row">
                                <div class="form-group dimension-input-group">
                                    <label>Width (Inches)</label>
                                    <input type="number" name="width_inches" step="0.01" placeholder="0.00">
                                </div>
                                <span class="separator-x">X</span>
                                <div class="form-group dimension-input-group">
                                    <label>Height (Inches)</label>
                                    <input type="number" name="height_inches" step="0.01" placeholder="0.00">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Base Price *</label>
                                <input type="number" name="base_price" step="0.01" placeholder="0.00" required>
                            </div>
                            <button type="submit" name="add_size" class="btn-submit">Add Size</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if (in_array($active_tab, ['frame_colors', 'sizes'])): ?>
                <div class="display-section">
                    <h2 class="form-title">Available <?php echo ucwords(str_replace('_', ' ', $active_tab)); ?> (<?php echo count($existing_options); ?>)</h2>
                    <div class="grid-container">
                        <?php foreach ($existing_options as $option): 
                            $current_id = ($active_tab == 'sizes') ? $option['size_id'] : $option['option_id'];
                        ?>
                            <div class="option-card">
                                <div class="option-info">
                                    <div class="option-img-preview">
                                        <?php if ($active_tab == 'sizes'): ?>
                                            <i class="fas fa-expand-arrows-alt" style="color: #cbd5e1;"></i>
                                        <?php elseif (!empty($option['image_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($option['image_url']); ?>" alt="Option">
                                        <?php else: ?>
                                            <i class="fas fa-scroll" style="color: #cbd5e1;"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="option-details">
                                        <?php if ($active_tab == 'sizes'): ?>
                                            <h4><?php echo htmlspecialchars($option['size_label']); ?></h4>
                                            <p><?php echo $option['width_inches']; ?> x <?php echo $option['height_inches']; ?> in</p>
                                        <?php else: ?>
                                            <h4><?php echo htmlspecialchars($option['name']); ?></h4>
                                            <p><?php echo htmlspecialchars($option['category']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <form action="" method="GET" style="margin: 0;">
                                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($active_tab); ?>">
                                    <input type="hidden" name="delete_id" value="<?php echo $current_id; ?>">
                                    <button type="submit" class="btn-delete" onclick="return confirm('Delete this item?')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>