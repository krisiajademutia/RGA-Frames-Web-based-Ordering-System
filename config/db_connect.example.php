<?php
$servername = "localhost";
$username   = "root";          // Default XAMPP username
$password   = "";              // Default XAMPP password (usually empty)
$dbname     = "database_name"; // MUST match the name in your phpMyAdmin

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8");
?>