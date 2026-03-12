<?php
// Serves the original image file as a forced download — preserves full quality.
session_start();
include __DIR__ . '/../config/db_connect.php';

if (!isset($_SESSION['user_id']) || strtoupper($_SESSION['role'] ?? '') !== 'ADMIN') {
    http_response_code(403);
    die("Access Denied");
}

$rawPath  = $_GET['path'] ?? '';
$filename = $_GET['name'] ?? 'download';

// Sanitize: strip any directory traversal attempts
$rawPath = ltrim($rawPath, '/');
$rawPath = preg_replace('/\.\.\//', '', $rawPath); 
$rawPath = preg_replace('/\.\.\\\\/', '', $rawPath); 

// Build absolute path — images are stored relative to project root
$basePath = realpath(__DIR__ . '/../');
$fullPath = realpath($basePath . '/' . $rawPath);

// Security: must still be inside project root
if (!$fullPath || strpos($fullPath, $basePath) !== 0) {
    http_response_code(400);
    die("Invalid file path.");
}

if (!file_exists($fullPath)) {
    http_response_code(404);
    die("File not found.");
}

// Detect MIME type
$finfo    = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->file($fullPath);

// Only allow image types
$allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
if (!in_array($mimeType, $allowed_mimes)) {
    http_response_code(415);
    die("Unsupported file type.");
}

// Preserve original extension
$ext      = pathinfo($fullPath, PATHINFO_EXTENSION);
$safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $filename) . '.' . $ext;

// Force download headers — NO re-encoding, serves the original bytes
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $safeName . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: no-cache, no-store');
header('Pragma: no-cache');

// Stream the file directly — zero quality loss
readfile($fullPath);
exit();