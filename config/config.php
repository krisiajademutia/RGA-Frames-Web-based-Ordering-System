<?php
// Brevo SMTP Settings
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_USER', 'a158aa001@smtp-brevo.com');
// Securely fetch the password from Render's environment variables
define('SMTP_PASS', getenv('SMTP_PASS')); 
define('SMTP_PORT', 587);
define('FROM_EMAIL', 'mutiakrisiaj@gmail.com');
define('FROM_NAME', 'RGA Frames');
?>
