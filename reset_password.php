<?php include 'process/reset_password_process.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="vpsw-body">
    <?php include 'includes/guest_header.php'; ?>

    <div class="vpsw-page-wrapper">
        <div class="vpsw-form-container">
            <div class="text-center mb-4">
                <div class="vpsw-icon-box">
                    <i class="fas fa-lock"></i>
                </div>
                <h2 class="vpsw-title">Set New Password</h2>
                <p class="vpsw-subtitle">Password must be at least 8 characters.</p>
            </div>

            <?php if ($success): ?>
                <div class="vpsw-success-card text-center">
                    <i class="fas fa-check-circle mb-3"></i>
                    <p><?php echo $success; ?></p>
                    <a href="login.php" class="vpsw-btn-submit text-decoration-none d-block">Go to Login</a>
                </div>
            <?php else: ?>
                <form method="POST">
                    <div class="mb-4">
                        <label class="vpsw-label">Password</label>
                        <div class="vpsw-input-wrapper <?php echo $error ? 'vpsw-is-invalid' : ''; ?>">
                            <span class="vpsw-field-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" name="new_password" class="form-control vpsw-input" placeholder="********" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="vpsw-label">Confirm Password</label>
                        <div class="vpsw-input-wrapper <?php echo $error ? 'vpsw-is-invalid' : ''; ?>">
                            <span class="vpsw-field-icon"><i class="fas fa-lock"></i></span>
                            <input type="password" name="confirm_password" class="form-control vpsw-input" placeholder="********" required>
                        </div>
                        <?php if ($error): ?>
                            <div class="vpsw-error-msg mt-2">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" name="reset_password" class="vpsw-btn-submit">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>

            <div class="text-center mt-4">
                <a href="login.php" class="vpsw-back-link"><i class="fas fa-arrow-left me-2"></i>back to login</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>