<?php
// includes/email_functions.php
require_once __DIR__ . '/../config/config.php';

function sendOTP($to_email, $otp_code, $purpose = 'register') {
    $apiKey = getenv('BREVO_API_KEY');

    if ($purpose === 'reset_password') {
        $subject = 'RGA Frames - Password Reset Request';
        $body    = "
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
        $subject = 'RGA Frames - Verify Your Account';
        $body    = "
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

    $data = [
        'sender'      => ['name' => FROM_NAME, 'email' => FROM_EMAIL],
        'to'          => [['email' => $to_email]],
        'subject'     => $subject,
        'htmlContent' => $body
    ];

    $ch = curl_init('https://api.brevo.com/v3/smtp/email');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'api-key: ' . $apiKey
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    error_log("Brevo API Response Code: " . $httpCode);
    error_log("Brevo API Response: " . $response);

    return $httpCode === 201;
}
