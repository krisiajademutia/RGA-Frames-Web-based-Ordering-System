<?php
include_once __DIR__ . '/../config/db_connect.php';

if (isset($_POST['paper_name'])) {
    $paper_name = mysqli_real_escape_string($conn, $_POST['paper_name']);
    
    // Updated query to fetch dimensions along with the name
    $query = "SELECT dimension, width_inch, height_inch 
              FROM tbl_paper_type 
              WHERE paper_name = '$paper_name' 
              AND is_active = 1 
              ORDER BY dimension ASC";
              
    $result = mysqli_query($conn, $query);

    echo '<option selected disabled>Select size</option>';
    
    while ($row = mysqli_fetch_assoc($result)) {
        $dim = htmlspecialchars($row['dimension']);
        $w = htmlspecialchars($row['width_inch']);
        $h = htmlspecialchars($row['height_inch']);
        
        // We store the inches in data attributes so JS can grab them instantly
        echo "<option value='$dim' data-width='$w' data-height='$h'>$dim</option>";
    }
    
    // The "Other" option stays as is, since the user will type these manually
    echo '<option value="Other">Other (Custom Size)</option>';
}
?>