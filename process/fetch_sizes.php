<?php
include_once __DIR__ . '/../config/db_connect.php';

if (isset($_POST['paper_name'])) {
    $paper_name = mysqli_real_escape_string($conn, $_POST['paper_name']);
    $query = "SELECT DISTINCT dimension FROM tbl_paper_type WHERE paper_name = '$paper_name' AND is_active = 1 ORDER BY dimension ASC";
    $result = mysqli_query($conn, $query);

    echo '<option selected disabled>Select size</option>';
    while ($row = mysqli_fetch_assoc($result)) {
        echo '<option value="'.htmlspecialchars($row['dimension']).'">'.htmlspecialchars($row['dimension']).'</option>';
    }
    echo '<option value="Other">Other (Custom Size)</option>';
}
?>