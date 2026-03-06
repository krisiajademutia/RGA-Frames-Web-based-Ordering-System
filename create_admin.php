<?php
// create_admin.php - TEMPORARY TOOL
require_once 'config/db_connect.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $raw_password = $_POST['password'];

    // This creates the secure hash your AuthService.php is looking for!
    $hashed_password = password_hash($raw_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO tbl_admin (first_name, last_name, username, email, password) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $first_name, $last_name, $username, $email, $hashed_password);

    if ($stmt->execute()) {
        $message = "<h3 style='color: green;'>Success! Admin created. You can now log in normally.</h3>";
    } else {
        $message = "<h3 style='color: red;'>Error: " . $stmt->error . "</h3>";
    }
    $stmt->close();
}
?>

<div style="padding: 50px; font-family: sans-serif;">
    <h2>Secret Admin Creator</h2>
    <?php echo $message; ?>
    <form method="POST">
        <input type="text" name="first_name" placeholder="First Name" required><br><br>
        <input type="text" name="last_name" placeholder="Last Name" required><br><br>
        <input type="text" name="username" placeholder="Username" required><br><br>
        <input type="email" name="email" placeholder="Email Address" required><br><br>
        <input type="text" name="password" placeholder="Password (e.g., password123)" required><br><br>
        <button type="submit">Create Admin</button>
    </form>
</div>