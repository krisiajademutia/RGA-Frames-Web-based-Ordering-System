<?php
ob_start();           
session_start();

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

// === AJAX TOGGLE HANDLER ===
if (isset($_POST['action']) && $_POST['action'] === 'toggle_active') {
    header('Content-Type: application/json');
    ob_clean();

    $id = (int)($_POST['id'] ?? 0);
    $new_active = (int)($_POST['new_active'] ?? 0);
    $tab = $_POST['tab'] ?? '';

    if ($id <= 0 || empty($tab)) {
        echo json_encode(['success' => false, 'error' => 'Missing id or tab']);
        exit;
    }

    $table_map = [
        'colors'    => ['table' => 'tbl_frame_colors',    'id_col' => 'frame_color_id'],
        'designs'   => ['table' => 'tbl_frame_designs',   'id_col' => 'frame_design_id'],
        'matboards' => ['table' => 'tbl_matboard_colors', 'id_col' => 'matboard_color_id'],
        'sizes'     => ['table' => 'tbl_frame_sizes',     'id_col' => 'frame_size_id'],
        'mounts'    => ['table' => 'tbl_mount_type',      'id_col' => 'mount_type_id'],
        'paper'     => ['table' => 'tbl_paper_type',      'id_col' => 'paper_type_id'],
        'types'     => ['table' => 'tbl_frame_type',      'id_col' => 'frame_type_id'],
    ];

    if (!isset($table_map[$tab])) {
        echo json_encode(['success' => false, 'error' => 'Invalid tab']);
        exit;
    }

    $table   = $table_map[$tab]['table'];
    $id_col  = $table_map[$tab]['id_col'];

    $stmt = $conn->prepare("UPDATE $table SET is_active = ? WHERE $id_col = ?");
    $stmt->bind_param("ii", $new_active, $id);
    $success = $stmt->execute();
    $stmt->close();

    echo json_encode([
        'success'    => $success,
        'new_active' => $new_active
    ]);
    exit;
}

// === ONLY NOW include the header ===


// === REST OF YOUR CODE STARTS HERE ===
$message = "";

// Upload directories
$upload_base_dir = __DIR__ . '/../uploads/';
$upload_base_url = '/rga_frames/uploads/';

foreach (['designs', 'matboards', 'types'] as $sub) {
    $dir = $upload_base_dir . $sub . '/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
}

// Active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'colors';

// Handle deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int)$_GET['id'];

    $table_map = [
        'colors'    => ['table' => 'tbl_frame_colors',    'id_col' => 'frame_color_id'],
        'designs'   => ['table' => 'tbl_frame_designs',   'id_col' => 'frame_design_id'],
        'matboards' => ['table' => 'tbl_matboard_colors', 'id_col' => 'matboard_color_id'],
        'sizes'     => ['table' => 'tbl_frame_sizes',     'id_col' => 'frame_size_id'],
        'mounts'    => ['table' => 'tbl_mount_type',      'id_col' => 'mount_type_id'],
        'paper'     => ['table' => 'tbl_paper_type',      'id_col' => 'paper_type_id'],
        'types'     => ['table' => 'tbl_frame_type',      'id_col' => 'frame_type_id'],
    ];

    if (isset($table_map[$active_tab])) {
        $table   = $table_map[$active_tab]['table'];
        $id_col  = $table_map[$active_tab]['id_col'];

        $stmt = $conn->prepare("DELETE FROM $table WHERE $id_col = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                        Deleted successfully!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>';
    }
}

// Handle form submission (add new option)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_option'])) {
    $category   = $_POST['category'] ?? '';
    $name       = trim($_POST['option_name'] ?? '');
    $price      = !empty($_POST['price']) ? floatval($_POST['price']) : null;
    $is_active  = isset($_POST['is_active']) ? 1 : 0;
    $image_name = '';

    // Image upload
    $subfolder = '';
    if ($category === 'designs') $subfolder = 'designs/';
    if ($category === 'matboards') $subfolder = 'matboards/';
    if ($category === 'types') $subfolder = 'types/';

    if ($subfolder && !empty($_FILES['image']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $allowed)) {
            $image_name = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', basename($_FILES['image']['name']));
            $target = $upload_base_dir . $subfolder . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                // success
            } else {
                $message = '<div class="alert alert-danger">Failed to upload image.</div>';
            }
        } else {
            $message = '<div class="alert alert-danger">Only JPG, PNG, GIF allowed.</div>';
        }
    }

    if (empty($name)) {
        $message = '<div class="alert alert-danger">Name is required!</div>';
    } else {
        $stmt = null;

        switch ($category) {
            case 'colors':
                $stmt = $conn->prepare("INSERT INTO tbl_frame_colors (color_name, is_active) VALUES (?, ?)");
                $stmt->bind_param("si", $name, $is_active);
                break;
            case 'designs':
                $stmt = $conn->prepare("INSERT INTO tbl_frame_designs (design_name, price, image_name, is_active) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sdsi", $name, $price, $image_name, $is_active);
                break;
            case 'matboards':
                $stmt = $conn->prepare("INSERT INTO tbl_matboard_colors (matboard_color_name, image_name, is_active) VALUES (?, ?, ?)");
                $stmt->bind_param("ssi", $name, $image_name, $is_active);
                break;
            case 'sizes':
                $dimension = trim($_POST['dimension'] ?? '');
                $width = !empty($_POST['width_inch']) ? floatval($_POST['width_inch']) : null;
                $height = !empty($_POST['height_inch']) ? floatval($_POST['height_inch']) : null;
                $total_inch = ($width && $height) ? $width * $height : null;
                $stmt = $conn->prepare("INSERT INTO tbl_frame_sizes (dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sdddii", $dimension, $width, $height, $total_inch, $price, $is_active);
                break;
            case 'mounts':
                $stmt = $conn->prepare("INSERT INTO tbl_mount_type (mount_name, additional_fee, is_active) VALUES (?, ?, ?)");
                $stmt->bind_param("sdi", $name, $price, $is_active);
                break;
            case 'paper':
                $size = trim($_POST['size'] ?? '');
                $dimension = trim($_POST['dimension'] ?? '');
                $width_inch = !empty($_POST['width_inch']) ? floatval($_POST['width_inch']) : null;
                $height_inch = !empty($_POST['height_inch']) ? floatval($_POST['height_inch']) : null;
                $total_inch = ($width_inch && $height_inch) ? $width_inch * $height_inch : null;
                $stmt = $conn->prepare("INSERT INTO tbl_paper_type (paper_name, size, dimension, width_inch, height_inch, total_inch, price, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("sssdddii", $name, $size, $dimension, $width_inch, $height_inch, $total_inch, $price, $is_active);
                break;
            case 'types':
                $stmt = $conn->prepare("INSERT INTO tbl_frame_types (type_name, price, image_name, is_active) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("sdsi", $name, $price, $image_name, $is_active);
                break;
        }

        if ($stmt && $stmt->execute()) {
            $message = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                            Added successfully!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        } else {
            $error_msg = $stmt ? $stmt->error : 'Unknown error';
            $message = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Error adding: ' . htmlspecialchars($error_msg) . '
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>';
        }

        if ($stmt) $stmt->close();
    }
}

// Fetch options
$options = [];
$query_map = [
    'colors'    => "SELECT * FROM tbl_frame_colors ORDER BY color_name",
    'designs'   => "SELECT * FROM tbl_frame_designs ORDER BY design_name",
    'matboards' => "SELECT * FROM tbl_matboard_colors ORDER BY matboard_color_name",
    'sizes'     => "SELECT * FROM tbl_frame_sizes ORDER BY dimension",
    'mounts'    => "SELECT * FROM tbl_mount_type ORDER BY mount_name",
    'paper'     => "SELECT * FROM tbl_paper_type ORDER BY paper_name",
    'types'     => "SELECT * FROM tbl_frame_types ORDER BY type_name",
];

if (isset($query_map[$active_tab])) {
    $result = $conn->query($query_map[$active_tab]);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $options[] = $row;
        }
    } else {
        $message = '<div class="alert alert-danger">Query failed: ' . $conn->error . '</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Custom Frame Options</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { padding-top: 80px; background: #f8f9fa; }
        .nav-tabs .nav-link { font-weight: 600; white-space: nowrap; }
        .nav-tabs .nav-link.active { color: #795338; border-color: #795338 #795338 #fff; }
        .card { border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .form-label { font-weight: 600; }
        .btn-primary { background-color: #795338; border-color: #795338; }
        .btn-primary:hover { background-color: #5c3f28; }
        .table th { background: #f1ede4; }
        .form-switch .form-check-input:checked { background-color: #795338; border-color: #795338; }
        .thumbnail { max-width: 60px; height: auto; border-radius: 4px; }
    </style>
</head>
<body>

<div class="container mt-4 mb-5">
    <div class="card shadow">
        <div class="card-header bg-white border-bottom">
            <h4 class="mb-0">Frame Customization Options</h4>
            <small class="text-muted">Manage colors, designs, matboards, sizes, mount types, paper types, and frame types</small>
        </div>

        <div class="card-body">
            <?php if ($message): echo $message; endif; ?>

            <ul class="nav nav-tabs mb-4 flex-nowrap overflow-auto">
                <li class="nav-item"><a class="nav-link <?= $active_tab == 'colors' ? 'active' : '' ?>" href="?tab=colors">Frame Colors</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_tab == 'designs' ? 'active' : '' ?>" href="?tab=designs">Frame Designs</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_tab == 'matboards' ? 'active' : '' ?>" href="?tab=matboards">Matboards</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_tab == 'sizes' ? 'active' : '' ?>" href="?tab=sizes">Sizes</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_tab == 'mounts' ? 'active' : '' ?>" href="?tab=mounts">Mount Types</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_tab == 'paper' ? 'active' : '' ?>" href="?tab=paper">Paper Types</a></li>
                <li class="nav-item"><a class="nav-link <?= $active_tab == 'types' ? 'active' : '' ?>" href="?tab=types">Frame Types</a></li>
            </ul>

            <!-- Add Form -->
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Add New <?php 
                        $titles = [
                            'colors' => 'Frame Color',
                            'designs' => 'Frame Design',
                            'matboards' => 'Matboard Color',
                            'sizes' => 'Frame Size',
                            'mounts' => 'Mount Type',
                            'paper' => 'Paper Type',
                            'types' => 'Frame Type',
                        ];
                        echo $titles[$active_tab] ?? 'Option';
                    ?></h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data" class="row g-3">
                        <input type="hidden" name="category" value="<?= htmlspecialchars($active_tab) ?>">
                        <input type="hidden" name="edit_id" id="edit_id" value="0">

                        <div class="col-md-6">
                            <label class="form-label">Name *</label>
                            <input type="text" name="option_name" id="option_name" class="form-control" required>
                        </div>

                        <?php if (in_array($active_tab, ['designs', 'matboards', 'types'])): ?>
                        <div class="col-md-6">
                            <label class="form-label">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small id="current_image" class="form-text text-muted"></small>
                        </div>
                        <?php endif; ?>

                        <?php if (in_array($active_tab, ['designs', 'sizes', 'mounts', 'paper', 'types'])): ?>
                        <div class="col-md-6">
                            <label class="form-label">Price (₱)</label>
                            <input type="number" step="0.01" name="price" id="price" class="form-control">
                        </div>
                        <?php endif; ?>

                        <?php if ($active_tab == 'sizes' || $active_tab == 'paper'): ?>
                        <div class="col-md-3">
                            <label class="form-label">Width (inch)</label>
                            <input type="number" step="0.1" name="width_inch" id="width_inch" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Height (inch)</label>
                            <input type="number" step="0.1" name="height_inch" id="height_inch" class="form-control">
                        </div>
                        <?php endif; ?>

                        <?php if ($active_tab == 'sizes'): ?>
                        <div class="col-md-3">
                            <label class="form-label">Dimension</label>
                            <input type="text" name="dimension" id="dimension" class="form-control" placeholder="e.g. 8x10">
                        </div>
                        <?php endif; ?>

                        <?php if ($active_tab == 'paper'): ?>
                        <div class="col-md-6">
                            <label class="form-label">Size (e.g. A4)</label>
                            <input type="text" name="size" id="size" class="form-control">
                        </div>
                        <?php endif; ?>

                        <div class="col-md-6">
                            <label class="form-label">Available</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" checked>
                                <label class="form-check-label" for="is_active">Yes / No</label>
                            </div>
                        </div>

                        <div class="col-12">
                            <button type="submit" name="add_option" class="btn btn-primary" id="submitBtn">
                                <i class="fas fa-plus me-1"></i> Add
                            </button>
                            <button type="button" class="btn btn-secondary ms-2" id="cancelEdit" style="display:none;">
                                Cancel Edit
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Existing Options</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Name</th>
                                    <?php if ($active_tab == 'sizes' || $active_tab == 'paper'): ?>
                                        <th>Width</th>
                                        <th>Height</th>
                                        <th>Total Inch</th>
                                    <?php endif; ?>
                                    <?php if ($active_tab == 'paper'): ?>
                                        <th>Size</th>
                                    <?php endif; ?>
                                    <?php if (in_array($active_tab, ['designs', 'sizes', 'mounts', 'paper', 'types'])): ?>
                                        <th>Price</th>
                                    <?php endif; ?>
                                    <?php if (in_array($active_tab, ['designs', 'matboards', 'types'])): ?>
                                        <th>Image</th>
                                    <?php endif; ?>
                                    <?php if ($active_tab == 'mounts'): ?>
                                        <th>Additional Fee</th>
                                    <?php endif; ?>
                                    <th>Available</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($options)): ?>
                                    <tr><td colspan="12" class="text-center py-5">No options found.</td></tr>
                                <?php else: ?>
                                    <?php $counter = 1; foreach ($options as $option): ?>
                                        <tr>
                                            <td><?= $counter++ ?></td>
                                            <td>
                                                <?= htmlspecialchars(
                                                    $option['color_name'] ??
                                                    $option['design_name'] ??
                                                    $option['matboard_color_name'] ??
                                                    $option['dimension'] ??
                                                    $option['mount_name'] ??
                                                    $option['paper_name'] ??
                                                    $option['type_name'] ?? 'N/A'
                                                ) ?>
                                            </td>

                                            <?php if ($active_tab == 'sizes' || $active_tab == 'paper'): ?>
                                                <td><?= $option['width_inch'] ?? '-' ?></td>
                                                <td><?= $option['height_inch'] ?? '-' ?></td>
                                                <td><?= $option['total_inch'] ?? '-' ?></td>
                                            <?php endif; ?>

                                            <?php if ($active_tab == 'paper'): ?>
                                                <td><?= $option['size'] ?? '-' ?></td>
                                            <?php endif; ?>

                                            <?php if (in_array($active_tab, ['designs', 'sizes', 'mounts', 'paper', 'types'])): ?>
                                                <td>₱<?= number_format($option['price'] ?? $option['additional_fee'] ?? 0, 2) ?></td>
                                            <?php endif; ?>

                                            <?php if (in_array($active_tab, ['designs', 'matboards', 'types'])): ?>
                                                <td>
                                                    <?php if ($option['image_name']): ?>
                                                        <img src="<?= $upload_urls[$active_tab] . htmlspecialchars($option['image_name']) ?>" 
                                                             alt="Preview" class="thumbnail">
                                                    <?php else: ?>
                                                        -
                                                    <?php endif; ?>
                                                </td>
                                            <?php endif; ?>

                                            <?php if ($active_tab == 'mounts'): ?>
                                                <td>₱<?= number_format($option['additional_fee'] ?? 0, 2) ?></td>
                                            <?php endif; ?>

                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           <?= $option['is_active'] ? 'checked' : '' ?>
                                                           onchange="toggleActive('<?= $active_tab ?>', <?= $option[array_key_first($option)] ?>, this)">
                                                </div>
                                            </td>

                                            <td>
                                                <button class="btn btn-sm btn-warning edit-btn" 
                                                        data-id="<?= $option[array_key_first($option)] ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                                <a href="?tab=<?= urlencode($active_tab) ?>&action=delete&id=<?= $option[array_key_first($option)] ?>" 
                                                   class="btn btn-sm btn-danger" onclick="return confirm('Delete permanently?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function toggleActive(tab, id, checkbox) {
            const newActive = checkbox.checked ? 1 : 0;

            try {
                const formData = new FormData();
                formData.append('action', 'toggle_active');
                formData.append('id', id);
                formData.append('new_active', newActive);
                formData.append('tab', tab);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const text = await response.text();

                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    console.log('Raw response (not JSON):', text.substring(0, 500));
                    throw new Error('Invalid JSON from server');
                }

                if (data.success) {
                    const label = checkbox.nextElementSibling;
                    label.textContent = newActive ? 'Available' : 'Unavailable';
                } else {
                    alert('Update failed: ' + (data.error || 'Unknown error'));
                    checkbox.checked = !checkbox.checked;
                }
            } catch (err) {
                console.error('Toggle error:', err);
                alert('Connection problem: ' + err.message);
                checkbox.checked = !checkbox.checked;
            }
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>