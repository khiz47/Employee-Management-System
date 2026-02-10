<?php

function isLoggedIn()
{
    return isset($_SESSION['user']);
}

function currentUser()
{
    return $_SESSION['user'] ?? null;
}

function requireGuest()
{
    if (isLoggedIn()) {
        redirectByRole();
    }
}

function requireLogin()
{
    if (!isLoggedIn()) {
        header("Location: " . LOGIN_URL);
        exit;
    }
}

function requireAdmin()
{
    requireLogin();

    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        header("Location: " . EMPLOYEE_DASHBOARD);
        exit;
    }
}

function requireEmployee()
{
    requireLogin();

    if (($_SESSION['user']['role'] ?? '') !== 'employee') {
        header("Location: " . ADMIN_DASHBOARD);
        exit;
    }
}

function redirectByRole()
{
    if (($_SESSION['user']['role'] ?? '') === 'admin') {
        header("Location: " . ADMIN_DASHBOARD);
    } else {
        header("Location: " . EMPLOYEE_DASHBOARD);
    }
    exit;
}