<?php
// config/db_connect.php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // === LOCAL XAMPP ===
    if (strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false || 
        ($_SERVER['HTTP_HOST'] ?? '') === '127.0.0.1') {
        
        $host = 'localhost';
        $user = 'root';
        $password = '';
        $dbname = 'rga_frames_db';        // ← Change if your local DB name is different
        $port = 3306;

        $conn = new mysqli($host, $user, $password, $dbname, $port);
        
    } else {
        // === RENDER + AIVEN ===
        $host     = getenv('DB_HOST');
        $user     = getenv('DB_USER');
        $password = getenv('DB_PASSWORD');
        $dbname   = getenv('DB_NAME');
        $port     = getenv('DB_PORT') ?: 10491;

        $conn = mysqli_init();
        if (!$conn) {
            throw new Exception("mysqli_init failed");
        }

        $conn->ssl_set(NULL, NULL, __DIR__ . '/../ca.pem', NULL, NULL);
        $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

        $success = $conn->real_connect($host, $user, $password, $dbname, $port, NULL, MYSQLI_CLIENT_SSL);
        
        if (!$success) {
            throw new Exception($conn->connect_error);
        }
    }

    $conn->set_charset("utf8mb4");

} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
