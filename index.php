<?php

// die('INDEX.PHP IS RUNNING');

require_once __DIR__ . '/includes/config.php';
include __DIR__ . '/includes/auth.php';
$request = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');


$routes = [

    // Auth
    ''       => 'auth/login.php',
    'login'  => 'auth/login.php',
    'logout' => 'auth/logout.php',

    // Admin
    'admin' => 'admin/index.php',
    'admin/dashboard' => 'admin/dashboard.php',

    // admin employees
    'admin/employees' => 'admin/employees/index.php',
    'admin/employees/list' => 'admin/employees/list.php',
    'admin/employees/create' => 'admin/employees/create.php',
    'admin/employees/edit' => 'admin/employees/edit.php',
    'admin/employees/view' => 'admin/employees/view.php',
    'admin/employees/toggle-status' => 'admin/employees/toggle-status.php',

    // admin departments
    'admin/departments' => 'admin/departments/index.php',
    'admin/departments/list' => 'admin/departments/list.php',
    'admin/departments/create' => 'admin/departments/create.php',
    'admin/departments/edit' => 'admin/departments/edit.php',
    'admin/departments/transfer' => 'admin/departments/transfer.php',

    // admin tasks
    'admin/tasks' => 'admin/tasks/index.php',
    'admin/tasks/list' => 'admin/tasks/list.php',
    'admin/tasks/create' => 'admin/tasks/create.php',
    'admin/tasks/view' => 'admin/tasks/view.php',
    'admin/tasks/edit' => 'admin/tasks/edit.php',
    'admin/tasks/history' => 'admin/tasks/history.php',


    // Employee
    'employee/dashboard' => 'employee/dashboard.php',
    'employee/my-tasks'  => 'employee/my_tasks.php',
];

$page = $routes[$request] ?? 'auth/login.php';

/*
|--------------------------------------------------------------------------
| GUEST PROTECTION
|--------------------------------------------------------------------------
*/
if (in_array($request, ['', 'login'])) {
    requireGuest();
}

require 'includes/header.php';
?>

<main>
    <?php require $page; ?>
</main>

<?php require 'includes/footer.php'; ?>