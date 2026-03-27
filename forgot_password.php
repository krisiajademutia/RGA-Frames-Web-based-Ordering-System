<?php 
session_start(); 
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="fgt-body">
    <?php include 'includes/guest_header.php'; ?>

   <div class="fgt-page-wrapper">
        <div class="fgt-form-container">
            <div class="text-center mb-4">
                <div class="fgt-icon-container">
                    <i class="fas fa-fingerprint"></i>
                </div>

                <h2 class="fgt-title">Forgot Password?</h2>
                <p class="fgt-subtitle">Enter your email address to receive a verification code for password reset.</p>
            </div>

            <form method="POST" action="process/forgot_password_process.php">
                <div class="mb-3">
                    <label class="fgt-label">Email Address</label>
                    <div class="fgt-input-wrapper">
                        <span class="fgt-field-icon"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" 
                               class="form-control fgt-input <?php echo !empty($error) ? 'is-invalid' : ''; ?>" 
                               placeholder="example@gmail.com" 
                               value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required autofocus>
                        
                        <div class="invalid-feedback">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    </div>
                </div>

                <button type="submit" name="send_otp" class="fgt-btn-submit">
                    Send OTP
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="login.php" class="fgt-back-link">
                    <i class="fas fa-arrow-left me-2"></i>Back to Login
                </a>
            </div>
        </div>
    </div>
</body>
</html>