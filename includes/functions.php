<?php
require_once __DIR__ . '/db.php';
include __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| ACTION ROUTER
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'login':
            loginUser($conn);
            break;

        case 'add_employee':
            addEmployee($conn);
            break;

        case 'update_employee':
            updateEmployee($conn);
            break;

        case 'toggle_employee_status':
            toggleEmployeeStatus($conn);
            break;

        case 'fetch_employees':
            fetchEmployees($conn);
            break;



        default:
            sendResponse('error', null, 'Invalid action.');
    }
}

/*
|--------------------------------------------------------------------------
| JSON RESPONSE HELPER
|--------------------------------------------------------------------------
*/
function sendResponse($status, $payload, $message)
{
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'data' => $payload,
        'message' => $message
    ]);
    exit;
}

/*
|--------------------------------------------------------------------------
| FUNCTIONS
|--------------------------------------------------------------------------
*/

function loginUser($conn)
{
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        sendResponse(false, null, 'Email and password are required');
    }

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role 
         FROM users 
         WHERE email = ? AND status = 1 
         LIMIT 1"
    );
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        sendResponse(false, null, 'Invalid email or password');
    }

    // Save session (minimal data only)
    $_SESSION['user'] = [
        'id'   => $user['id'],
        'name' => $user['name'],
        'role' => $user['role']
    ];

    // Decide redirect
    $redirect = ($user['role'] === 'admin')
        ? ADMIN_DASHBOARD
        : EMPLOYEE_DASHBOARD;

    sendResponse(true, ['redirect' => $redirect], 'Login successful');
}

function addEmployee($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized access.');
    }

    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $departmentId  = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $phone         = trim($_POST['phone'] ?? '');
    $designation   = trim($_POST['designation'] ?? '');
    $joiningDate   = !empty($_POST['joining_date']) ? $_POST['joining_date'] : null;
    $empStatus     = isset($_POST['employee_status']) ? (int)$_POST['employee_status'] : 1;

    if ($name === '' || $email === '') {
        sendResponse(false, null, 'Name and email are required.');
    }

    // Check email
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
        sendResponse(false, null, 'Email already exists.');
    }

    // Auto password (internal)
    $hashedPassword = password_hash(bin2hex(random_bytes(4)), PASSWORD_DEFAULT);

    $conn->beginTransaction();

    try {
        // USERS
        $stmt = $conn->prepare("
            INSERT INTO users (name, email, password, role, status)
            VALUES (?, ?, ?, 'employee', 1)
        ");
        $stmt->execute([$name, $email, $hashedPassword]);

        $userId = $conn->lastInsertId();

        // EMPLOYEES
        $stmt2 = $conn->prepare("
            INSERT INTO employees 
            (user_id, department_id, phone, joining_date, designation, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt2->execute([
            $userId,
            $departmentId,
            $phone,
            $joiningDate,
            $designation,
            $empStatus
        ]);

        $conn->commit();

        sendResponse(true, [
            'redirect' => BASE_URL . 'admin/employees'
        ], 'Employee created successfully.');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to create employee.');
    }
}

function updateEmployee($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized access.');
    }

    $userId        = (int)($_POST['user_id'] ?? 0);
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $departmentId  = !empty($_POST['department_id']) ? (int)$_POST['department_id'] : null;
    $phone         = trim($_POST['phone'] ?? '');
    $designation   = trim($_POST['designation'] ?? '');
    $joiningDate   = !empty($_POST['joining_date']) ? $_POST['joining_date'] : null;
    $empStatus     = (int)($_POST['employee_status'] ?? 1);
    $userStatus    = (int)($_POST['user_status'] ?? 1);

    if (!$userId || $name === '' || $email === '') {
        sendResponse(false, null, 'Required fields are missing.');
    }

    // Check email conflict
    $check = $conn->prepare("
        SELECT id FROM users WHERE email = ? AND id != ?
    ");
    $check->execute([$email, $userId]);
    if ($check->fetch()) {
        sendResponse(false, null, 'Email already exists.');
    }

    $conn->beginTransaction();

    try {
        // USERS
        $stmt = $conn->prepare("
            UPDATE users 
            SET name = ?, email = ?, status = ?
            WHERE id = ? AND role = 'employee'
        ");
        $stmt->execute([$name, $email, $userStatus, $userId]);

        // EMPLOYEES
        $stmt2 = $conn->prepare("
            UPDATE employees
            SET department_id = ?, phone = ?, joining_date = ?, designation = ?, status = ?
            WHERE user_id = ?
        ");
        $stmt2->execute([
            $departmentId,
            $phone,
            $joiningDate,
            $designation,
            $empStatus,
            $userId
        ]);

        $conn->commit();

        sendResponse(true, [
            'redirect' => BASE_URL . 'admin/employees'
        ], 'Employee updated successfully.');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to update employee.');
    }
}

function toggleEmployeeStatus($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized access.');
    }

    $userId = (int)($_POST['user_id'] ?? 0);
    $currentStatus = (int)($_POST['current_status'] ?? -1);

    if (!$userId || !in_array($currentStatus, [0, 1], true)) {
        sendResponse(false, null, 'Invalid request.');
    }

    $newStatus = $currentStatus ? 0 : 1;

    $conn->beginTransaction();

    try {
        // USERS table
        $stmt = $conn->prepare("
            UPDATE users SET status = ?
            WHERE id = ? AND role = 'employee'
        ");
        $stmt->execute([$newStatus, $userId]);

        // EMPLOYEES table
        $stmt2 = $conn->prepare("
            UPDATE employees SET status = ?
            WHERE user_id = ?
        ");
        $stmt2->execute([$newStatus, $userId]);

        $conn->commit();

        sendResponse(true, [
            'new_status' => $newStatus
        ], 'Status updated.');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to update status.');
    }
}

function fetchEmployees($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $page       = max(1, (int)($_POST['page'] ?? 1));
    $limit      = $_POST['limit'] === 'all' ? null : (int)($_POST['limit'] ?? 10);
    $search     = trim($_POST['search'] ?? '');
    $filterBy   = $_POST['filterBy'] ?? 'all';
    $status     = $_POST['status'] ?? 'all';
    $department = $_POST['department'] ?? 'all';

    $where = ["u.role = 'employee'"];
    $params = [];

    // Status filter
    if ($status !== 'all') {
        $where[] = "u.status = ?";
        $params[] = $status;
    }

    // Department filter
    if ($department !== 'all') {
        $where[] = "LOWER(d.name) = ?";
        $params[] = strtolower($department);
    }

    // Search
    if ($search !== '') {
        switch ($filterBy) {
            case 'name':
                $where[] = "u.name LIKE ?";
                $params[] = "%$search%";
                break;
            case 'email':
                $where[] = "u.email LIKE ?";
                $params[] = "%$search%";
                break;
            case 'department':
                $where[] = "d.name LIKE ?";
                $params[] = "%$search%";
                break;
            default:
                $where[] = "(u.name LIKE ? OR u.email LIKE ? OR d.name LIKE ?)";
                $params[] = "%$search%";
                $params[] = "%$search%";
                $params[] = "%$search%";
        }
    }

    $whereSql = implode(" AND ", $where);

    // TOTAL COUNT
    $countStmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM users u
        LEFT JOIN employees e ON e.user_id = u.id
        LEFT JOIN departments d ON d.id = e.department_id
        WHERE $whereSql
    ");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // PAGINATION
    $offset = $limit ? ($page - 1) * $limit : 0;
    $limitSql = $limit ? "LIMIT $limit OFFSET $offset" : "";

    // DATA QUERY
    $stmt = $conn->prepare("
        SELECT u.id, u.name, u.email, u.status, d.name AS department
        FROM users u
        LEFT JOIN employees e ON e.user_id = u.id
        LEFT JOIN departments d ON d.id = e.department_id
        WHERE $whereSql
        ORDER BY u.id DESC
        $limitSql
    ");
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    sendResponse(true, [
        'rows' => $rows,
        'total' => $total
    ], 'OK');
}
