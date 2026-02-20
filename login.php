<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$old_username = $_SESSION['old_username'] ?? '';
unset($_SESSION['errors'], $_SESSION['old_username']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - RGA Frames</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">  
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="log-body">

    <?php include 'includes/guest_header.php'; ?>

    <div class="log-container">
        <div class="log-split-layout">

            <!-- Form Side -->
            <div class="log-form-side">
                <div class="log-form-wrapper">

                    <div class="log-welcome-section">
                        <div class="log-icon-badge">
                            <i class="fas fa-box-open"></i>
                        </div>
                        <h1 class="log-title">Welcome Back!</h1>
                        <p class="log-subtitle">Log in with your username to manage your custom frame selections.</p>
                    </div>

                    <form action="process/login_process.php" method="POST" novalidate class="log-form">

                        <div class="log-form-group">
                            <label class="log-label">Username</label>
                            <div class="log-input-wrapper">
                                <span class="log-field-icon"><i class="fas fa-user"></i></span>
                                <input type="text" name="username"
                                       class="log-input <?php echo isset($errors['username']) ? 'log-input-error' : ''; ?>"
                                       placeholder="Enter your username"
                                       value="<?php echo htmlspecialchars($old_username); ?>" required>
                            </div>
                            <?php if (isset($errors['username'])): ?>
                                    <p class="log-error-message"><i class="fas fa-circle-exclamation"></i><?php echo $errors['username']; ?></p>
                                <?php endif; ?>
                        </div>

                        <div class="log-form-group">
                            <label class="log-label">Password</label>
                            <div class="log-input-wrapper">
                                <span class="log-field-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="loginPassword"
                                       class="log-input <?php echo isset($errors['password']) ? 'log-input-error' : ''; ?>"
                                       placeholder="Enter your password" required>
                                <button class="log-eye-toggle" type="button" onclick="togglePassword('loginPassword', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                            </div>
                             <?php if (isset($errors['password'])): ?>
                                    <p class="log-error-message"><i class="fas fa-circle-exclamation"></i><?php echo $errors['password']; ?></p>
                                <?php endif; ?>
                            <div class="log-forgot-wrapper">
                                <a href="forgot_password.php" class="log-forgot-link">Forgot Password?</a>
                            </div>
                        </div>

                        <button type="submit" name="login_btn" class="log-btn-submit">
                            Log In <i class="fas fa-arrow-right"></i>
                        </button>
                    </form>

                    <p class="log-footer-text">
                        Don't have an account? <a href="register.php" class="log-register-link">Register Here</a>
                    </p>
                </div>
            </div>

            <!-- Image Side -->
            <div class="log-image-side"></div>

        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>