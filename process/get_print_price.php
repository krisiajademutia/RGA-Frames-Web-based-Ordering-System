<?php
include_once __DIR__ . '/../config/db_connect.php';

$type = mysqli_real_escape_string($conn, $_POST['type']);
$size = mysqli_real_escape_string($conn, $_POST['size']);
$width = isset($_POST['width']) ? (float)$_POST['width'] : 0;
$height = isset($_POST['height']) ? (float)$_POST['height'] : 0;

// 1. Path A: Standard/Pre-defined sizes (Works for both)
if ($size !== 'Other') {
    $query = "SELECT price FROM tbl_paper_type WHERE paper_name = '$type' AND dimension = '$size' LIMIT 1";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    echo $row ? $row['price'] : 0.00;
} 
// 2. Path B: Custom sizes ('Other')
else {
    if ($type === 'Canvas') {
        // Logic: Find the smallest standard size that fits the custom area
        $custom_area = $width * $height;
        $query = "SELECT price FROM tbl_paper_type 
                  WHERE paper_name = '$type' 
                  AND total_inch >= $custom_area 
                  ORDER BY total_inch ASC 
                  LIMIT 1";
        $result = mysqli_query($conn, $query);
        $row = mysqli_fetch_assoc($result);

        // Fallback: If custom size is larger than our biggest Canvas size, use the biggest price
        if (!$row) {
            $query = "SELECT price FROM tbl_paper_type 
                      WHERE paper_name = '$type' 
                      ORDER BY total_inch DESC LIMIT 1";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
        }
        echo $row ? $row['price'] : 0.00;
    } else {
        // Logic: Glossy Photo Paper (Calculated: Area * 2.00)
        $area = $width * $height;
        echo number_format($area * 2.00, 2, '.', '');
    }
}
?>