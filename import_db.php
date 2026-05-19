<?php
// Pulls the configuration variables
if (file_exists('config/db_connect.php')) {
    // We will build a direct connection here using SSL to bypass restrictions
    $host = getenv('DB_HOST') ?: 'mysql-f30bdf6-rga-frames.aivencloud.com';
    $user = getenv('DB_USER') ?: 'avnadmin';
    $pass = getenv('DB_PASSWORD'); 
    $db   = 'defaultdb';
    $port = getenv('DB_PORT') ?: 10491;
}

$conn = mysqli_init();
if (!$conn) {
    die("mysqli_init failed");
}

// This flag tells PHP to connect using SSL, which Aiven requires!
$conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

if (!@$conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL)) {
    die("<h1>❌ Database connection failed:</h1> " . $conn->connect_error);
}

$sqlFile = 'rga_frames_db.sql';
if (!file_exists($sqlFile)) {
    die("<h1>❌ Error: $sqlFile not found!</h1><p>Please upload your .sql file to your main GitHub repository directory.</p>");
}

try {
    $lines = file($sqlFile);
    $cleanQuery = "";
    
    foreach ($lines as $line) {
        // Skip local database creation strings that crash on Aiven
        if (stripos(trim($line), 'CREATE DATABASE') === 0 || stripos(trim($line), 'USE ') === 0) {
            continue; 
        }
        $cleanQuery .= $line;
    }

    if ($conn->multi_query($cleanQuery)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());
        echo "<h1>🎉 Success! Your rga_frames_db.sql database tables have been fully imported into Aiven defaultdb!</h1>";
        echo "<p>You can now go to your home link and log in. Please delete <b>import_db.php</b> from GitHub for security.</p>";
    } else {
        echo "<h1>❌ Query execution failed:</h1> " . $conn->error;
    }
} catch (Exception $e) {
    echo "<h1>❌ Database import error:</h1> " . $e->getMessage();
}
?>
