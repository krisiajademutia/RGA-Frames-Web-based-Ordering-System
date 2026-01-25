<!---(The Back-end Logic) Connects to DB, checks for duplicates, encrypts password, and saves.--->
<?php
// register_process.php
session_start();
include 'db_connect.php';

if (isset($_POST['register_btn'])) {
    $fname = $_POST['first_name'];
    $lname = $_POST['last_name'];
    $phone = $_POST['phone_number'];
    $addr  = $_POST['address'];
    $pass  = $_POST['password'];

    // 1. Check if Phone Number already exists
    $check_sql = "SELECT * FROM users WHERE phone_number = '$phone'";
    $result = $conn->query($check_sql);

    if ($result->num_rows > 0) {
        echo "<script>alert('Phone number already registered!'); window.location='register.php';</script>";
    } else {
        // 2. Encrypt Password
        $hashed_password = password_hash($pass, PASSWORD_DEFAULT);

        // 3. Insert into Database
        $sql = "INSERT INTO users (first_name, last_name, phone_number, password, address, role) 
                VALUES ('$fname', '$lname', '$phone', '$hashed_password', '$addr', 'customer')";

        if ($conn->query($sql) === TRUE) {
            echo "<script>alert('Registration Successful! Please Login.'); window.location='login.php';</script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>