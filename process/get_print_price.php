<?php
include_once __DIR__ . '/../config/db_connect.php';

// Retrieve and sanitize inputs
// 'type' is now the paper_type_id (integer)
$paper_type_id = isset($_POST['type']) ? (int)$_POST['type'] : 0;
$size = mysqli_real_escape_string($conn, $_POST['size']);
$width = isset($_POST['width']) ? (float)$_POST['width'] : 0;
$height = isset($_POST['height']) ? (float)$_POST['height'] : 0;

if ($paper_type_id === 0) {
    echo "0.00";
    exit;
}

// Path A: Standard/Pre-defined sizes
if ($size !== 'Other') {
    // Look up the price in the fixed prices table based on paper ID and dimension string
    $query = "SELECT fixed_price FROM tbl_fixed_print_prices 
              WHERE paper_type_id = ? AND dimension = ? LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "is", $paper_type_id, $size);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    echo $row ? number_format($row['fixed_price'], 2, '.', '') : "0.00";
} 
// Path B: Custom sizes ('Other')
else {
    // Get the multiplier for the specific paper type
    $query = "SELECT multiplier FROM tbl_paper_type WHERE paper_type_id = ? LIMIT 1";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $paper_type_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);

    if ($row && !is_null($row['multiplier'])) {
        $multiplier = (float)$row['multiplier'];
        $area = $width * $height;
        $total_price = $area * $multiplier;
        echo number_format($total_price, 2, '.', '');
    } else {
        // Fallback: if no multiplier is set, return 0.00 to avoid broken calculations
        echo "0.00";
    }
}
?>