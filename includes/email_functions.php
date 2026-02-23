<?php
// includes/email_functions.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP; 

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

function sendOTP($to_email, $otp_code, $purpose = 'register') {
    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        
        $mail->Timeout = 60; 
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        $mail->setFrom(FROM_EMAIL, FROM_NAME);
        $mail->addAddress($to_email);
        $mail->isHTML(true);
        
        // --- STEP 3: SYNC PURPOSE LOGIC ---
        if ($purpose === 'reset_password') {
            $mail->Subject = 'RGA Frames - Reset Your Password';
            $mail->Body    = "<h2>Reset Password</h2><p>Your OTP code is: <strong>$otp_code</strong></p>";
        } else {
            $mail->Subject = 'RGA Frames - Verify Account';
            $mail->Body    = "<h2>Welcome</h2><p>Your verification code is: <strong>$otp_code</strong></p>";
        }
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // This logs errors to your server's error log
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}