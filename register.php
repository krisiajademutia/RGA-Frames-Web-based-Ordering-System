<!-- FRONT END FORM FOR REGISTERING A NEW USER -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - RGA Frames</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            width: 400px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-top: 5px solid #b8860b;
        }
        input, textarea {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box; 
        }
        .btn-submit {
            width: 100%;
            background-color: #00b85c;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-submit:hover { background-color: #009e4f; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">RGA Frames</div>
        <a href="index.php" style="text-decoration: none; color: #8b4513;">Back to Home</a>
    </nav>

    <div class="auth-container">
        <h2 style="text-align: center; color: #8b4513;">Create Account</h2>
        <form action="register_process.php" method="POST">
            
            <label>First Name</label>
            <input type="text" name="first_name" required>

            <label>Last Name</label>
            <input type="text" name="last_name" required>

            <label>Phone Number</label>
            <input type="text" name="phone_number" required placeholder="09123456789">

            <label>Delivery Address</label>
            <textarea name="address" required rows="3"></textarea>

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="register_btn" class="btn-submit">Register Now</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            Already have an account? <a href="login.php" style="color: #c49a6c;">Login here</a>
        </p>
    </div>

</body>
</html>