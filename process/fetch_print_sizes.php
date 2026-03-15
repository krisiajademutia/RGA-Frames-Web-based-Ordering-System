<?php
include_once __DIR__ . '/../config/db_connect.php';

$paper_type_id = isset($_POST['paper_id']) ? (int)$_POST['paper_id'] : 0;

if ($paper_type_id > 0) {

    // Get max allowed dimensions
    $maxQuery = "SELECT max_width_inch, max_height_inch 
                 FROM tbl_paper_type 
                 WHERE paper_type_id = ?";
    $maxStmt = mysqli_prepare($conn, $maxQuery);
    mysqli_stmt_bind_param($maxStmt, "i", $paper_type_id);
    mysqli_stmt_execute($maxStmt);
    $maxResult = mysqli_stmt_get_result($maxStmt);
    $paper = mysqli_fetch_assoc($maxResult);

    $maxW = $paper['max_width_inch'];
    $maxH = $paper['max_height_inch'];

    $query = "SELECT dimension, width_inch, height_inch 
              FROM tbl_fixed_print_prices 
              WHERE paper_type_id = ? 
              ORDER BY dimension ASC";

    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $paper_type_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    echo '<option selected disabled>Select size</option>';

    while ($row = mysqli_fetch_assoc($result)) {

        $dim = htmlspecialchars($row['dimension']);
        $w = htmlspecialchars($row['width_inch']);
        $h = htmlspecialchars($row['height_inch']);

        echo "<option 
                value='$dim' 
                data-width='$w' 
                data-height='$h'
                data-maxwidth='$maxW'
                data-maxheight='$maxH'
              >
              $dim
              </option>";
    }

    echo "<option 
            value='Other'
            data-maxwidth='$maxW'
            data-maxheight='$maxH'
          >
          Other (Custom Size)
          </option>";
}
?>