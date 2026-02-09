<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================
// APP CONFIG
// ============================

define('BASE_URL', '/'); // change if project is in subfolder

define('APP_NAME', 'Employee Management System');

// ============================
// DATABASE (later)
// ============================

define('DB_HOST', 'localhost');
define('DB_NAME', 'ems_db');
define('DB_USER', 'root');
define('DB_PASS', '');
// ============================
// SMTP
// ============================
define('SMTP_EMAIL', 'khizerqureshi4774@gmail.com');
define('SMTP_PASS', 'mqmb eivs irmm hjwe');
// define('SMTP_DOMAIN', 'no-reply@yourdomain.com');