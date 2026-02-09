<?php

require_once __DIR__ . '/includes/config.php';

$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$request = str_replace(trim(BASE_URL, '/'), '', $request);
$request = trim($request, '/');

$routes = [

    // Auth
    ''       => 'auth/login.php',
    'login'  => 'auth/login.php',
    'logout' => 'auth/logout.php',

    // Admin
    'admin/dashboard'  => 'admin/dashboard.php',
    'admin/employees'  => 'admin/employees.php',
    'admin/tasks'      => 'admin/tasks.php',

    // Employee
    'employee/dashboard' => 'employee/dashboard.php',
    'employee/my-tasks'  => 'employee/my_tasks.php',
];

$page = $routes[$request] ?? 'auth/login.php';

// Header decision
$isAdminPage    = str_starts_with($page, 'admin/');
$isEmployeePage = str_starts_with($page, 'employee/');

require 'includes/header.php';
?>

<main class="container py-4">
    <?php require $page; ?>
</main>

<?php require 'includes/footer.php'; ?>