<?php
session_start();
$errors = $_SESSION['errors'] ?? [];
$old_email = $_SESSION['old_email'] ?? '';
unset($_SESSION['errors'], $_SESSION['old_email']);
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
<body class="log-body-reset">


    <?php include 'includes/guest_header.php'; ?>

    <div class="container-fluid p-0">
        <div class="row g-0 min-vh-100">
            
            <div class="col-lg-6 d-flex flex-column p-4 p-md-5">
                
                <div class="log-header-area">
                    <div class="log-brand-wrapper">
                        <i class="fas fa-box-open me-2"></i> 
                        <span class="log-brand-text">RGA Frames</span>
                    </div>
                </div>

                <div class="log-form-container my-auto mx-auto">
                    <div class="mb-5">
                        <h1 class="log-title">Welcome Back!</h1>
                        <p class="log-subtitle">Log in to access order history, view frame designs, and manage custom frame selections.</p>
                    </div>

                    <form action="process/login_process.php" method="POST" novalidate>
                        
                        <div class="mb-4">
                            <label class="log-label">Username</label>
                            <div class="log-input-wrapper">
                                <span class="log-field-icon">@</span>
                                <input type="email" name="email" 
                                       class="form-control log-input <?php echo isset($errors['email']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="Enter your username" 
                                       value="<?php echo htmlspecialchars($old_email); ?>" required>
                                <div class="invalid-feedback"><?php echo $errors['email'] ?? ''; ?></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="log-label">Password</label>
                            <div class="log-input-wrapper">
                                <span class="log-field-icon"><i class="fas fa-lock"></i></span>
                                <input type="password" name="password" id="loginPassword" 
                                       class="form-control log-input <?php echo isset($errors['password']) ? 'is-invalid' : ''; ?>" 
                                       placeholder="Enter your password" required>
                                
                                <button class="log-eye-toggle" type="button" onclick="togglePassword('loginPassword', this)">
                                    <i class="fas fa-eye-slash"></i>
                                </button>
                                
                                <div class="invalid-feedback"><?php echo $errors['password'] ?? ''; ?></div>
                            </div>
                            <div class="text-end mt-2">
                                <a href="forgot_password.php" class="log-forgot-link">Forgot Password?</a>
                            </div>
                        </div>

                        <button type="submit" name="login_btn" class="log-btn-submit">Log-in</button>
                    </form>

                    <p class="log-footer-text mt-5 text-center">
                        Don't have an account? <a href="register.php" class="log-register-link">Register Here</a>
                    </p>
                </div>
            </div>

            <div class="col-lg-6 d-none d-lg-block log-image-side">
                </div>

        </div>
    </div>

    <script src="assets/js/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>