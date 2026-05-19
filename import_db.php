<?php
// import_db.php - One-time database import script
echo "<h2>🚀 Starting Database Import...</h2>";

// === Connection Settings ===
$host     = getenv('DB_HOST') ?: 'mysql-f30bdf6-rga-frames.a.aivencloud.com';
$user     = getenv('DB_USER') ?: 'avnadmin';
$password = getenv('DB_PASSWORD');
$dbname   = getenv('DB_NAME') ?: 'defaultdb';
$port     = getenv('DB_PORT') ?: 10491;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    if (!$conn) {
        throw new Exception("mysqli_init failed");
    }

    // === Proper SSL for Aiven ===
    $conn->ssl_set(NULL, NULL, __DIR__ . '/ca.pem', NULL, NULL);
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

    $success = $conn->real_connect(
        $host, $user, $password, $dbname, (int)$port, NULL, MYSQLI_CLIENT_SSL
    );

    if (!$success) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    $conn->set_charset("utf8mb4");
    echo "<p>✅ Connected to Aiven successfully.</p>";

} catch (Exception $e) {
    die("<h1>❌ Database connection failed:</h1> " . $e->getMessage());
}

// === Import SQL File ===
$sqlFile = __DIR__ . '/rga_frames_db.sql';

if (!file_exists($sqlFile)) {
    die("<h1>❌ Error: rga_frames_db.sql not found!</h1><p>Make sure it is in the root folder and committed to GitHub.</p>");
}

try {
    $lines = file($sqlFile);
    $cleanQuery = "";

    foreach ($lines as $line) {
        $line = trim($line);

        // Skip lines that cause problems on Aiven
        if (stripos($line, 'CREATE DATABASE') === 0 ||
            stripos($line, 'USE ') === 0 ||
            empty($line) ||
            strpos($line, '--') === 0) {
            continue;
        }

        $cleanQuery .= $line . "\n";
    }

    // === Fix for Aiven's strict primary key requirement ===
    $cleanQuery = "SET SESSION sql_require_primary_key = 0;\n" . $cleanQuery;

    if ($conn->multi_query($cleanQuery)) {
        do {
            if ($result = $conn->store_result()) {
                $result->free();
            }
        } while ($conn->next_result());

        echo "<h1>🎉 SUCCESS! Database tables have been imported successfully!</h1>";
        echo "<p>You can now try logging into your system.</p>";
        echo "<p><strong>Security Note:</strong> Delete or rename this import_db.php file after use.</p>";

    } else {
        throw new Exception($conn->error);
    }

} catch (Exception $e) {
    die("<h1>❌ Import Failed:</h1> " . $e->getMessage());
}

$conn->close();
?>
