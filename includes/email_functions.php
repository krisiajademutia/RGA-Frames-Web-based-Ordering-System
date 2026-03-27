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
            
            // PASSWORD RESET TEMPLATE
            $mail->Subject = 'RGA Frames - Password Reset Request';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;'>
                    <h2 style='color: #0d9488; text-align: center;'>Password Reset Request</h2>
                    <p style='color: #4b5563; font-size: 16px;'>Hello,</p>
                    <p style='color: #4b5563; font-size: 16px;'>We received a request to reset the password for your RGA Frames account. If you made this request, please use the 6-digit verification code below:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #111827; background-color: #f3f4f6; padding: 15px 25px; border-radius: 8px;'>{$otp_code}</span>
                    </div>
                    
                    <p style='color: #4b5563; font-size: 14px; text-align: center;'><em>This code will expire in 15 minutes.</em></p>
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                    <p style='color: #6b7280; font-size: 12px; text-align: center;'>If you did not request a password reset, please ignore this email. Your password is safe and will remain unchanged.</p>
                </div>
            ";
            
        } else {
            
            // REGISTRATION / VERIFY ACCOUNT TEMPLATE
            $mail->Subject = 'RGA Frames - Verify Your Account';
            $mail->Body    = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e5e7eb; border-radius: 8px;'>
                    <h2 style='color: #0d9488; text-align: center;'>Welcome to RGA Frames!</h2>
                    <p style='color: #4b5563; font-size: 16px;'>Hello,</p>
                    <p style='color: #4b5563; font-size: 16px;'>Thank you for registering an account with us. To complete your setup and verify your email address, please enter the 6-digit code below:</p>
                    
                    <div style='text-align: center; margin: 30px 0;'>
                        <span style='font-size: 32px; font-weight: bold; letter-spacing: 5px; color: #111827; background-color: #f3f4f6; padding: 15px 25px; border-radius: 8px;'>{$otp_code}</span>
                    </div>
                    
                    <p style='color: #4b5563; font-size: 14px; text-align: center;'><em>This code will expire in 15 minutes.</em></p>
                    <hr style='border: none; border-top: 1px solid #e5e7eb; margin: 20px 0;'>
                    <p style='color: #6b7280; font-size: 12px; text-align: center;'>If you did not create an account using this email address, please ignore this email.</p>
                </div>
            ";
        }
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        // This logs errors to your server's error log
        error_log("PHPMailer Error: " . $mail->ErrorInfo);
        return false;
    }
}