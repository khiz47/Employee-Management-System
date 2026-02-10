<?php

require_once __DIR__ . '/../includes/db.php';

// ============================
// ADMIN DETAILS (CHANGE THESE)
// ============================
$name     = 'Super Admin';
$email    = 'admin@company.com';
$password = 'admin123'; // change later
$role     = 'admin';
$status   = 1;

// ============================
// HASH PASSWORD
// ============================
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ============================
// CHECK IF ADMIN EXISTS
// ============================
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);

if ($check->fetch()) {
    die('❌ Admin already exists.');
}

// ============================
// INSERT ADMIN
// ============================
$stmt = $conn->prepare("
    INSERT INTO users (name, email, password, role, status)
    VALUES (?, ?, ?, ?, ?)
");

$result = $stmt->execute([
    $name,
    $email,
    $hashedPassword,
    $role,
    $status
]);

if ($result) {
    echo "✅ Admin created successfully.<br>";
    echo "Email: <b>$email</b><br>";
    echo "Password: <b>$password</b><br>";
    echo "<br><strong>Delete this file now.</strong>";
} else {
    echo "❌ Failed to create admin.";
}