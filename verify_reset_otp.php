<?php
session_start();
// Security: If we don't know who is resetting, send them back
if (!isset($_SESSION['reset_email'])) {
    header("Location: forgot_password.php");
    exit();
}
$email = $_SESSION['reset_email'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify OTP - RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="votp-body">
    <?php include 'includes/guest_header.php'; ?>

    <div class="votp-page-wrapper">
        <div class="votp-form-container">
            <div class="text-center mb-5">
                <h2 class="votp-title">Verify Your Email</h2>
                <p class="votp-subtitle">We sent a 6-digit code to<br><strong><?php echo htmlspecialchars($email); ?></strong></p>
            </div>

            <form action="process/verify_otp_process.php" method="POST" id="otpForm">
                <div class="mb-4">
                    <label class="votp-label text-center">Enter 6-Digit Code</label>
                    <div class="votp-inputs-group">
                        <input type="text" maxlength="1" class="votp-digit" pattern="\d*" inputmode="numeric" required autofocus>
                        <input type="text" maxlength="1" class="votp-digit" pattern="\d*" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="votp-digit" pattern="\d*" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="votp-digit" pattern="\d*" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="votp-digit" pattern="\d*" inputmode="numeric" required>
                        <input type="text" maxlength="1" class="votp-digit" pattern="\d*" inputmode="numeric" required>
                    </div>

                    <input type="hidden" name="otp" id="full_otp">

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger mt-3 text-center">
                            <i class="fas fa-exclamation-circle me-1"></i> 
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <button type="submit" name="verify_otp" class="votp-btn-submit">Verify Code</button>
            </form>

            <p class="text-center mt-4">
                Didn't receive the code? <a href="forgot_password.php" class="votp-link">Resend</a>
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/otp-handler.js"></script>
    
    <script>
        document.getElementById('otpForm').addEventListener('submit', function(e) {
            let otp = '';
            document.querySelectorAll('.votp-digit').forEach(input => {
                otp += input.value;
            });
            document.getElementById('full_otp').value = otp;
        });
    </script>
</body>
</html>