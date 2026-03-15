<?php
include_once __DIR__ . '/../config/db_connect.php';

// Change $_POST['paper_name'] to $_POST['paper_id']
$paper_type_id = isset($_POST['paper_id']) ? (int)$_POST['paper_id'] : 0;

if ($paper_type_id > 0) {
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
        echo "<option value='$dim' data-width='$w' data-height='$h'>$dim</option>";
    }
    echo '<option value="Other">Other (Custom Size)</option>';
    // In your loop while fetching sizes from DB:
      echo '<option value="'.$row['size_id'].'" 
      data-width="'.$row['max_width_inch'].'" 
      data-height="'.$row['max_height_inch'].'">'.$row['size_name'].'</option>';
}
?>