<?php
// Pulls the safe Aiven connection settings
require_once 'config/db_connect.php'; 

$sqlFile = 'rga_frames_db.sql';

if (!file_exists($sqlFile)) {
    die("Error: $sqlFile not found in the root directory. Please make sure it is uploaded to GitHub.");
}

try {
    $query = file_get_contents($sqlFile);
    
    // Execute the SQL file statements directly into Aiven
    if ($conn->multi_query($query)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        echo "<h1>🎉 Success! Your rga_frames_db.sql database tables have been fully imported into Aiven!</h1>";
        echo "<p>Please delete this <b>import_db.php</b> file from GitHub now for security.</p>";
    } else {
        echo "Query execution failed: " . $conn->error;
    }
} catch (Exception $e) {
    echo "Database import error: " . $e->getMessage();
}
?>
