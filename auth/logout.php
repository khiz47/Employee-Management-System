<?php

require_once __DIR__ . '/../includes/config.php';

// Unset user session only (safer than session_destroy in some apps)
unset($_SESSION['user']);

// Optional: regenerate session ID for security
session_regenerate_id(true);

// Redirect to login
header("Location: " . LOGIN_URL);
exit;