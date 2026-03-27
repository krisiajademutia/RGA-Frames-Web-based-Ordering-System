<?php
// verify_account.php
session_start();
require_once __DIR__ . '/config/config.php';

// Redirection check: If they don't have a pending registration, kick them out!
if (!isset($_SESSION['pending_user'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['pending_user']['email'];
// Simple email masking (e.g., test@gmail.com -> t***@gmail.com)
$parts = explode("@", $email);
$maskedEmail = substr($parts[0], 0, 1) . '***@' . $parts[1];

// Calculate remaining time for the JS timer (in seconds)
$expires_at = $_SESSION['pending_user']['expires_at'];
$time_left = max(0, $expires_at - time()); 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Account | RGA Frames</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    
</head>
<body class="vrfy-acc-body">
    <?php include 'includes/guest_header.php'; ?>

    <div class="vrfy-acc-page-wrap">

        <!-- Icon -->
        <div class="vrfy-acc-icon-wrap">
            <i class="fas fa-envelope-open-text vrfy-acc-icon"></i>
        </div>

        <!-- Heading -->
        <h2 class="vrfy-acc-title">Check your email</h2>
        <p class="vrfy-acc-subtitle">
            We've sent a 6-digit verification code to<br>
            <span class="vrfy-acc-email"><?= htmlspecialchars($maskedEmail) ?></span>
        </p>

        <!-- Error -->
        <?php if (isset($_SESSION['errors']['otp'])): ?>
        <div class="vrfy-acc-error">
            <?= htmlspecialchars($_SESSION['errors']['otp']) ?>
        </div>
        <?php unset($_SESSION['errors']['otp']); ?>
        <?php endif; ?>

        <!-- Form -->
        <form action="process/verify_account_process.php" method="POST" class="vrfy-acc-form">
            <div class="vrfy-acc-otp-box">
                <input type="text" name="otp_1" class="vrfy-acc-otp-digit" maxlength="1" required autocomplete="off">
                <input type="text" name="otp_2" class="vrfy-acc-otp-digit" maxlength="1" required autocomplete="off">
                <input type="text" name="otp_3" class="vrfy-acc-otp-digit" maxlength="1" required autocomplete="off">
                <input type="text" name="otp_4" class="vrfy-acc-otp-digit" maxlength="1" required autocomplete="off">
                <input type="text" name="otp_5" class="vrfy-acc-otp-digit" maxlength="1" required autocomplete="off">
                <input type="text" name="otp_6" class="vrfy-acc-otp-digit" maxlength="1" required autocomplete="off">
            </div>

            <button type="submit" class="vrfy-acc-btn">Verify Account</button>
        </form>

        <p class="vrfy-acc-timer-text">
            Code expires in <span class="vrfy-acc-timer-val" id="vrfy-acc-timer">--:--</span>
        </p>

        <!-- Back link -->
        <div class="vrfy-acc-back-wrap">
            <a href="register.php" class="vrfy-acc-back-link">
                <i class="fas fa-arrow-left"></i> Use a different email
            </a>
        </div>

    </div>

    <script>
        // --- OTP Auto-Focus & Paste Logic ---
        const inputs = document.querySelectorAll('.vrfy-acc-otp-box input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                // Force numbers only
                e.target.value = e.target.value.replace(/[^0-9]/g, '');
                
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            // Handle pasting the full 6-digit code
            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').replace(/[^0-9]/g, '').slice(0, 6);
                if (pastedData) {
                    for (let i = 0; i < pastedData.length; i++) {
                        if (inputs[i]) {
                            inputs[i].value = pastedData[i];
                            if (i < inputs.length - 1) inputs[i + 1].focus();
                        }
                    }
                }
            });
        });

        // --- Countdown Timer Logic (Synced with PHP) ---
        let timeLeft = <?= $time_left ?>; 
        const timerElement = document.getElementById('vrfy-acc-timer');
        const verifyBtn = document.querySelector('.vrfy-acc-btn');

        // Only start if there is time left
        if (timeLeft > 0) {
            const countdown = setInterval(() => {
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    timerElement.innerHTML = "<span class='text-red-500'>Expired!</span>";
                    verifyBtn.disabled = true;
                    verifyBtn.style.opacity = '0.5';
                    verifyBtn.style.cursor = 'not-allowed';
                    return;
                }

                const minutes = Math.floor(timeLeft / 60);
                const seconds = timeLeft % 60;
                timerElement.textContent = `${minutes}:${seconds.toString().padStart(2, '0')}`;
                timeLeft--;
            }, 1000);
        } else {
            timerElement.innerHTML = "<span class='text-red-500'>Expired!</span>";
            verifyBtn.disabled = true;
            verifyBtn.style.opacity = '0.5';
            verifyBtn.style.cursor = 'not-allowed';
        }
    </script>
</body>
</html>