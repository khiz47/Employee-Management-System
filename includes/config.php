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

/*
|--------------------------------------------------------------------------
| ROUTE SHORTCUTS
|--------------------------------------------------------------------------
*/
define('LOGIN_URL', BASE_URL . 'login');
define('ADMIN_DASHBOARD', BASE_URL . 'admin/dashboard');
define('EMPLOYEE_DASHBOARD', BASE_URL . 'employee/dashboard');

// SMTP
// ============================
define('SMTP_EMAIL', 'khizerqureshi4774@gmail.com');
define('SMTP_PASS', 'mqmb eivs irmm hjwe');
// define('SMTP_DOMAIN', 'no-reply@yourdomain.com');