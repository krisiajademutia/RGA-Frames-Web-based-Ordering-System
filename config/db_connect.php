<?php
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$dbname = getenv('DB_NAME');
$port = getenv('DB_PORT') ?: 3306;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    // Aiven databases require a secure SSL connection over the public internet
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
    $conn->real_connect($host, $user, $password, $dbname, $port);
    
    // Keeps your character set exactly the same as your local XAMPP setup
    $conn->set_charset("utf8");
} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
