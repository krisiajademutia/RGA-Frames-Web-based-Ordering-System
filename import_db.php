<?php
// import_db.php - One-time database import script
echo "<h2>🚀 Starting Database Import...</h2>";
flush();

// === Connection Settings ===
$host     = getenv('DB_HOST') ?: 'mysql-f30bdf6-rga-frames.a.aivencloud.com';
$user     = getenv('DB_USER') ?: 'avnadmin';
$password = getenv('DB_PASSWORD');
$dbname   = getenv('DB_NAME') ?: 'defaultdb';
$port     = getenv('DB_PORT') ?: 10491;

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_init();
    if (!$conn) throw new Exception("mysqli_init failed");

    $conn->ssl_set(NULL, NULL, __DIR__ . '/ca.pem', NULL, NULL);
    $conn->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);

    $success = $conn->real_connect(
        $host, $user, $password, $dbname, (int)$port, NULL, MYSQLI_CLIENT_SSL
    );

    if (!$success) throw new Exception("Connection failed: " . $conn->connect_error);

    $conn->set_charset("utf8mb4");
    echo "<p>✅ Connected to Aiven successfully.</p>";
    flush();

} catch (Exception $e) {
    die("<h1>❌ Database connection failed:</h1> " . $e->getMessage());
}

// === Import SQL File ===
$sqlFile = __DIR__ . '/rga_frames_db.sql';

if (!file_exists($sqlFile)) {
    die("<h1>❌ Error: rga_frames_db.sql not found!</h1>");
}

// === Disable foreign key checks and parse SQL into individual statements ===
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

$sql = file_get_contents($sqlFile);

// Remove comments and SET GLOBAL/SESSION lines Aiven doesn't allow
$sql = preg_replace('/^--.*$/m', '', $sql);
$sql = preg_replace('/^\/\*.*?\*\/;?\s*$/ms', '', $sql);
$sql = preg_replace('/^\s*SET\s+(GLOBAL|SESSION)\s+sql_require_primary_key\s*=.*?;\s*$/im', '', $sql);

// Split into individual statements
$statements = array_filter(
    array_map('trim', explode(';', $sql)),
    fn($s) => strlen($s) > 0
);

$successCount = 0;
$errorCount = 0;
$errors = [];

foreach ($statements as $statement) {
    try {
        $conn->query($statement);
        $successCount++;
    } catch (Exception $e) {
        $errorCount++;
        $errors[] = "<li><b>Error:</b> " . htmlspecialchars($e->getMessage()) . "<br><small>" . htmlspecialchars(substr($statement, 0, 100)) . "...</small></li>";
    }
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");

echo "<h2>📊 Import Summary:</h2>";
echo "<p>✅ Successful statements: <b>$successCount</b></p>";
echo "<p>❌ Failed statements: <b>$errorCount</b></p>";

if ($errorCount === 0) {
    echo "<h1>🎉 SUCCESS! Database imported successfully!</h1>";
    echo "<p>You can now <a href='/'>log into your system</a>.</p>";
    echo "<p><strong>⚠️ Security Note:</strong> Please delete or rename this import_db.php file after use.</p>";
} else {
    echo "<h3>⚠️ Imported with some errors:</h3><ul>" . implode('', $errors) . "</ul>";
    echo "<p>If the errors are just duplicate entries or minor issues, your system may still work. Try <a href='/'>logging in</a>.</p>";
}

$conn->close();
?>
