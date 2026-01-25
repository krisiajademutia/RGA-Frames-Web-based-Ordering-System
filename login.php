<!-- FRONT END FORM FOR USER LOGIN -->
 <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RGA Frames</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .auth-container {
            width: 350px;
            margin: 80px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            border-top: 5px solid #b8860b;
        }
        input {
            width: 100%;
            padding: 10px;
            margin: 8px 0 20px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            background-color: #c49a6c; /* Light Brown */
            color: white;
            padding: 12px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .btn-submit:hover { background-color: #a37c52; }
    </style>
</head>
<body>

    <nav class="navbar">
        <div class="logo">RGA Frames</div>
        <a href="index.php" style="text-decoration: none; color: #8b4513;">Back to Home</a>
    </nav>

    <div class="auth-container">
        <h2 style="text-align: center; color: #8b4513;">Welcome Back</h2>
        <form action="login_process.php" method="POST">
            
            <label>Phone Number</label>
            <input type="text" name="phone_number" required placeholder="09123456789">

            <label>Password</label>
            <input type="password" name="password" required>

            <button type="submit" name="login_btn" class="btn-submit">Login</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            No account yet? <a href="register.php" style="color: #00b85c;">Register here</a>
        </p>
    </div>

</body>
</html>