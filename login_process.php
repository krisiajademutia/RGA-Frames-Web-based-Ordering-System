<!--(The Back-end Logic) Verifies password and redirects Admin to Admin Panel, Customer to Customer Home.-->
<?php
// login_process.php
session_start();
include 'db_connect.php';

if (isset($_POST['login_btn'])) {
    $phone = $_POST['phone_number'];
    $pass  = $_POST['password'];

    // 1. Find user by phone number
    $sql = "SELECT * FROM users WHERE phone_number = '$phone'";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        // 2. Verify Password
        if (password_verify($pass, $row['password'])) {
            
            // 3. Set Session Variables
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['role'] = $row['role'];

            // 4. Redirect based on Role
            if ($row['role'] == 'admin') {
                header("Location: admin_dashboard.php"); 
            } else {
                header("Location: customer_dashboard.php"); 
            }
            exit();

        } else {
            echo "<script>alert('Incorrect Password!'); window.location='login.php';</script>";
        }
    } else {
        echo "<script>alert('Phone number not found!'); window.location='login.php';</script>";
    }
}
?>