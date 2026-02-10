<?php

require_once __DIR__ . '/../includes/db.php';

// ============================
// EMPLOYEE DETAILS (CHANGE)
// ============================
$name         = 'John Employee';
$email        = 'employee@company.com';
$password     = 'employee123';
$role         = 'employee';
$status       = 1;
$departmentId = 1; // must exist
$phone        = '9999999999';
$designation  = 'Software Engineer';

// ============================
// HASH PASSWORD
// ============================
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// ============================
// CHECK USER EXISTS
// ============================
$check = $conn->prepare("SELECT id FROM users WHERE email = ?");
$check->execute([$email]);

if ($check->fetch()) {
    die('❌ Employee already exists.');
}

// ============================
// INSERT USER + EMPLOYEE
// ============================
$conn->beginTransaction();

try {
    $stmt = $conn->prepare("
        INSERT INTO users (name, email, password, role, status)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $name,
        $email,
        $hashedPassword,
        $role,
        $status
    ]);

    $userId = $conn->lastInsertId();

    $stmt2 = $conn->prepare("
        INSERT INTO employees (user_id, department_id, phone, designation)
        VALUES (?, ?, ?, ?)
    ");
    $stmt2->execute([
        $userId,
        $departmentId,
        $phone,
        $designation
    ]);

    $conn->commit();

    echo "✅ Employee created successfully.<br>";
    echo "Email: <b>$email</b><br>";
    echo "Password: <b>$password</b><br>";
    echo "<br><strong>Delete this file now.</strong>";
} catch (Exception $e) {
    $conn->rollBack();
    echo "❌ Failed to create employee.";
}