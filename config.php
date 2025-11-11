<?php
// config.php - mail / smtp settings for SoloTech
// Update these values with your SMTP provider credentials.

return [
    // Admin email to receive new order notifications
    'admin_email' => 'landrysobjio@gmail.com',

    // From address used for outgoing mails
    'from_email' => 'no-reply@localhost',
    'from_name' => 'SoloTech',

    // SMTP settings (used by PHPMailer)
    'smtp' => [
        'host' => 'smtp.example.com',
        'port' => 587,
        'username' => 'smtp-user@example.com',
        'password' => 'supersecret',
        'secure' => 'tls' // 'tls' or 'ssl' or empty for none
    ]
];

// Note: Do NOT commit real credentials to version control. Use environment variables
// or a secure local config and adjust php.ini/sendmail settings on Windows/XAMPP when testing.
