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

        case 'fetch_departments':
            fetchDepartments($conn);
            break;

        case 'toggle_department_status':
            toggleDepartmentStatus($conn);
            break;

        case 'add_department':
            addDepartment($conn);
            break;

        case 'update_department':
            updateDepartment($conn);
            break;

        case 'transfer_and_delete_department':
            transferAndDeleteDepartment($conn);
            break;

        case 'fetch_department_employees':
            fetchDepartmentEmployees($conn);
            break;

        case 'create_task':
            createTask($conn);
            break;

        case 'fetch_tasks':
            fetchTasks($conn);
            break;

        case 'fetch_task_history':
            fetchTaskHistory($conn);
            break;

        case 'add_task_comment':
            addTaskComment($conn);
            break;

        case 'update_task_comment':
            updateTaskComment($conn);
            break;

        case 'upload_task_attachment':
            uploadTaskAttachment($conn);
            break;

        case 'update_task_progress':
            updateTaskProgress($conn);
            break;

        case 'update_task_status':
            updateTaskStatus($conn);
            break;

        case 'update_task':
            updateTask($conn);
            break;

        case 'fetch_task_department_employees':
            fetchTaskDepartmentEmployees($conn);
            break;

        case 'delete_task':
            deleteTask($conn);
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
// --------------------------------------------EMPLOYEES-------------------------------
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

// --------------------------DEPARTMENTS------------------------------
function fetchDepartments($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $page  = max(1, (int)($_POST['page'] ?? 1));
    $limit = $_POST['limit'] === 'all' ? null : (int)($_POST['limit'] ?? 10);
    $search = trim($_POST['search'] ?? '');

    $where = [];
    $params = [];

    if ($search !== '') {
        $where[] = "name LIKE ?";
        $params[] = "%$search%";
    }

    $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

    // TOTAL
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM departments d $whereSql");
    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    // PAGINATION
    $offset = $limit ? ($page - 1) * $limit : 0;
    $limitSql = $limit ? "LIMIT $limit OFFSET $offset" : "";

    $stmt = $conn->prepare("
    SELECT 
        d.id,
        d.name,
        d.status,
        COUNT(e.id) AS employee_count
    FROM departments d
    LEFT JOIN employees e ON e.department_id = d.id
    $whereSql
    GROUP BY d.id
    ORDER BY d.id DESC
    $limitSql
    ");

    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    sendResponse(true, [
        'rows' => $rows,
        'total' => $total
    ], 'OK');
}

function toggleDepartmentStatus($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $id = (int)($_POST['id'] ?? 0);
    $currentStatus = (int)($_POST['current_status'] ?? -1);

    if (!$id || !in_array($currentStatus, [0, 1], true)) {
        sendResponse(false, null, 'Invalid request');
    }

    $newStatus = $currentStatus ? 0 : 1;

    // 🔥 Prevent disabling if employees exist
    if ($currentStatus == 1) { // trying to disable
        $check = $conn->prepare("
            SELECT COUNT(*) 
            FROM employees 
            WHERE department_id = ?
        ");
        $check->execute([$id]);

        $employeeCount = (int)$check->fetchColumn();

        if ($employeeCount > 0) {
            sendResponse(false, null, 'Cannot disable department. Employees are assigned.');
        }
    }

    $stmt = $conn->prepare("UPDATE departments SET status = ? WHERE id = ?");
    $stmt->execute([$newStatus, $id]);

    sendResponse(true, ['new_status' => $newStatus], 'Updated');
}


function addDepartment($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized access.');
    }

    $name   = trim($_POST['name'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    if ($name === '') {
        sendResponse(false, null, 'Department name is required.');
    }

    // Prevent duplicate names
    $check = $conn->prepare("SELECT id FROM departments WHERE name = ?");
    $check->execute([$name]);

    if ($check->fetch()) {
        sendResponse(false, null, 'Department already exists.');
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO departments (name, status)
            VALUES (?, ?)
        ");

        $stmt->execute([$name, $status]);

        sendResponse(true, [
            'redirect' => BASE_URL . 'admin/departments'
        ], 'Department created successfully.');
    } catch (Exception $e) {
        sendResponse(false, null, 'Failed to create department.');
    }
}

function updateDepartment($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized access.');
    }

    $id     = (int)($_POST['id'] ?? 0);
    $name   = trim($_POST['name'] ?? '');
    $status = isset($_POST['status']) ? (int)$_POST['status'] : 1;

    if (!$id || $name === '') {
        sendResponse(false, null, 'Invalid input.');
    }

    // Prevent duplicate name (except itself)
    $check = $conn->prepare("
        SELECT id 
        FROM departments 
        WHERE name = ? AND id != ?
    ");
    $check->execute([$name, $id]);

    if ($check->fetch()) {
        sendResponse(false, null, 'Department name already exists.');
    }

    try {
        $stmt = $conn->prepare("
            UPDATE departments
            SET name = ?, status = ?
            WHERE id = ?
        ");
        if ($status == 0) {
            $checkEmployees = $conn->prepare("
                SELECT COUNT(*) FROM employees WHERE department_id = ?
            ");
            $checkEmployees->execute([$id]);
            if ($checkEmployees->fetchColumn() > 0) {
                sendResponse(false, null, 'Cannot disable department with assigned employees.');
            }
        }

        $stmt->execute([$name, $status, $id]);

        sendResponse(true, [
            'redirect' => BASE_URL . 'admin/departments'
        ], 'Department updated successfully.');
    } catch (Exception $e) {
        sendResponse(false, null, 'Failed to update department.');
    }
}

function transferAndDeleteDepartment($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $from = (int)($_POST['from_department_id'] ?? 0);
    $to   = (int)($_POST['to_department_id'] ?? 0);

    if (!$from || !$to || $from == $to) {
        sendResponse(false, null, 'Invalid department selection.');
    }

    $conn->beginTransaction();

    try {
        // Transfer employees
        $stmt = $conn->prepare("
            UPDATE employees
            SET department_id = ?
            WHERE department_id = ?
        ");
        $stmt->execute([$to, $from]);

        // Delete department
        $delete = $conn->prepare("DELETE FROM departments WHERE id = ?");
        $delete->execute([$from]);

        $conn->commit();

        sendResponse(true, [
            'redirect' => BASE_URL . 'admin/departments'
        ], 'Department transferred and deleted.');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Operation failed.');
    }
}

function fetchDepartmentEmployees($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $departmentId = (int)($_POST['department_id'] ?? 0);
    $page = max(1, (int)($_POST['page'] ?? 1));
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $countStmt = $conn->prepare("
        SELECT COUNT(*)
        FROM employees
        WHERE department_id = ?
    ");
    $countStmt->execute([$departmentId]);
    $total = (int)$countStmt->fetchColumn();

    $stmt = $conn->prepare("
        SELECT u.name, u.email, e.designation
        FROM employees e
        JOIN users u ON u.id = e.user_id
        WHERE e.department_id = ?
        ORDER BY u.name ASC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute([$departmentId]);
    $rows = $stmt->fetchAll();

    sendResponse(true, [
        'rows' => $rows,
        'total' => $total
    ], 'OK');
}

function createTask($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = $_POST['priority'] ?? 'medium';
    $dueDate     = $_POST['due_date'] ?? null;
    $assignedUsers = $_POST['assigned_users'] ?? [];
    $createdBy   = currentUser()['id'];

    if ($title === '' || empty($assignedUsers)) {
        sendResponse(false, null, 'Title and employees required.');
    }

    $conn->beginTransaction();

    try {

        // Insert task
        $stmt = $conn->prepare("
            INSERT INTO tasks 
            (title, description, created_by, priority, status, progress, due_date)
            VALUES (?, ?, ?, ?, 'pending', 0, ?)
        ");

        $stmt->execute([
            $title,
            $description,
            $createdBy,
            $priority,
            $dueDate ?: null
        ]);

        $taskId = $conn->lastInsertId();

        // Insert assignments
        foreach ($assignedUsers as $userId) {
            $conn->prepare("
                INSERT INTO task_assignments (task_id, user_id)
                VALUES (?, ?)
            ")->execute([$taskId, $userId]);
        }

        // Log
        $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ")->execute([
            $taskId,
            "Task created & assigned to employees",
            $createdBy
        ]);

        $conn->commit();

        sendResponse(true, [
            'redirect' => BASE_URL . 'admin/tasks'
        ], 'Task created successfully.');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to create task.');
    }
}


function fetchTaskDepartmentEmployeesDemo($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $deptId = (int)($_POST['department_id'] ?? 0);

    if (!$deptId) {
        sendResponse(false, null, 'Invalid department');
    }

    $stmt = $conn->prepare("
        SELECT u.id, u.name
        FROM users u
        JOIN employees e ON e.user_id = u.id
        WHERE e.department_id = ?
        AND u.status = 1
        AND e.status = 1
        ORDER BY u.name
    ");

    $stmt->execute([$deptId]);
    $employees = $stmt->fetchAll();

    sendResponse(true, $employees, 'OK');
}
function fetchTaskDepartmentEmployees($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $deptId = (int)($_POST['department_id'] ?? 0);
    $taskId = (int)($_POST['task_id'] ?? 0);

    if (!$deptId) {
        sendResponse(false, null, 'Invalid department');
    }

    // Get assigned users for this task
    $assignedIds = [];
    if ($taskId) {
        $stmt = $conn->prepare("
            SELECT user_id 
            FROM task_assignments 
            WHERE task_id = ?
        ");
        $stmt->execute([$taskId]);
        $assignedIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    $stmt = $conn->prepare("
        SELECT u.id, u.name
        FROM users u
        JOIN employees e ON e.user_id = u.id
        WHERE e.department_id = ?
        AND u.status = 1
        AND e.status = 1
        ORDER BY u.name
    ");

    $stmt->execute([$deptId]);
    $employees = $stmt->fetchAll();

    foreach ($employees as &$emp) {
        $emp['assigned'] = in_array($emp['id'], $assignedIds);
    }

    sendResponse(true, $employees, 'OK');
}



function fetchTasks($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $page  = max(1, (int)($_POST['page'] ?? 1));
    $limit = (int)($_POST['limit'] ?? 6);

    $search = trim($_POST['search'] ?? '');
    $status = $_POST['status'] ?? 'all';
    $priority = $_POST['priority'] ?? 'all';

    $where = [];
    $params = [];
    $where = ["t.status != 'completed'"];

    if ($search !== '') {
        $where[] = "t.title LIKE ?";
        $params[] = "%$search%";
    }

    if ($status !== 'all') {
        $where[] = "t.status = ?";
        $params[] = $status;
    }

    if ($priority !== 'all') {
        $where[] = "t.priority = ?";
        $params[] = $priority;
    }

    $whereSql = $where ? "WHERE " . implode(" AND ", $where) : "";

    // TOTAL
    $countStmt = $conn->prepare("
    SELECT COUNT(DISTINCT t.id)
    FROM tasks t
    LEFT JOIN task_assignments ta ON ta.task_id = t.id
    $whereSql
");

    $countStmt->execute($params);
    $total = (int)$countStmt->fetchColumn();

    $offset = ($page - 1) * $limit;

    $stmt = $conn->prepare("
    SELECT 
        t.*,
        GROUP_CONCAT(u.name SEPARATOR ', ') AS employee_name
    FROM tasks t
    JOIN task_assignments ta ON ta.task_id = t.id
    JOIN users u ON u.id = ta.user_id
    $whereSql
    GROUP BY t.id
    ORDER BY t.id DESC
    LIMIT $limit OFFSET $offset
");

    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    sendResponse(true, [
        'rows' => $rows,
        'total' => $total
    ], 'OK');
}

function fetchTaskHistory($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $page  = max(1, (int)($_POST['page'] ?? 1));
    $limit = (int)($_POST['limit'] ?? 6);

    $offset = ($page - 1) * $limit;

    $countStmt = $conn->prepare("
        SELECT COUNT(*) FROM tasks WHERE status = 'completed'
    ");
    $countStmt->execute();
    $total = (int)$countStmt->fetchColumn();

    $stmt = $conn->prepare("
        SELECT t.*, GROUP_CONCAT(u.name SEPARATOR ', ') AS employee_name
        FROM tasks t
        JOIN task_assignments ta ON ta.task_id = t.id
        JOIN users u ON u.id = ta.user_id
        WHERE t.status = 'completed'
        GROUP BY t.id
        ORDER BY t.updated_at DESC
        LIMIT $limit OFFSET $offset
    ");
    $stmt->execute();
    $rows = $stmt->fetchAll();

    sendResponse(true, [
        'rows' => $rows,
        'total' => $total
    ], 'OK');
}

function addTaskComment($conn)
{
    if (!isLoggedIn()) {
        sendResponse(false, null, 'Unauthorized');
    }

    $taskId = (int)($_POST['task_id'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if (!$taskId || $comment === '') {
        sendResponse(false, null, 'Comment cannot be empty');
    }

    $conn->beginTransaction();

    try {

        // Insert comment
        $stmt = $conn->prepare("
            INSERT INTO task_comments (task_id, user_id, comment)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([
            $taskId,
            currentUser()['id'],
            $comment
        ]);

        // Insert log
        $log = $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ");
        $log->execute([
            $taskId,
            'Added a comment',
            currentUser()['id']
        ]);

        $conn->commit();

        sendResponse(true, null, 'Comment added successfully');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to add comment');
    }
}

function updateTaskComment($conn)
{
    if (!isLoggedIn()) {
        sendResponse(false, null, 'Unauthorized');
    }

    $commentId = (int)($_POST['comment_id'] ?? 0);
    $newComment = trim($_POST['comment'] ?? '');

    if (!$commentId || $newComment === '') {
        sendResponse(false, null, 'Invalid request');
    }

    // Verify ownership
    $check = $conn->prepare("
        SELECT task_id, user_id 
        FROM task_comments 
        WHERE id = ?
    ");
    $check->execute([$commentId]);
    $comment = $check->fetch();

    if (!$comment || $comment['user_id'] != currentUser()['id']) {
        sendResponse(false, null, 'You cannot edit this comment');
    }

    $conn->beginTransaction();

    try {

        // Update comment
        $update = $conn->prepare("
            UPDATE task_comments 
            SET comment = ?
            WHERE id = ?
        ");
        $update->execute([$newComment, $commentId]);

        // Add log
        $log = $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ");
        $log->execute([
            $comment['task_id'],
            'Edited a comment',
            currentUser()['id']
        ]);

        $conn->commit();

        sendResponse(true, null, 'Comment updated');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to update');
    }
}

function uploadTaskAttachment($conn)
{
    if (!isLoggedIn()) {
        sendResponse(false, null, 'Unauthorized');
    }

    $taskId = (int)($_POST['task_id'] ?? 0);

    if (!$taskId || !isset($_FILES['file'])) {
        sendResponse(false, null, 'Invalid request');
    }

    $file = $_FILES['file'];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        sendResponse(false, null, 'File upload failed');
    }

    // Security checks
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip'];
    $maxSize = 5 * 1024 * 1024; // 5MB

    $fileName = $file['name'];
    $fileTmp  = $file['tmp_name'];
    $fileSize = $file['size'];

    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($ext, $allowedExtensions)) {
        sendResponse(false, null, 'File type not allowed');
    }

    if ($fileSize > $maxSize) {
        sendResponse(false, null, 'File too large (Max 5MB)');
    }

    // Create upload folder
    $uploadDir = __DIR__ . '/../uploads/tasks/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = uniqid() . '.' . $ext;
    $destination = $uploadDir . $newFileName;

    if (!move_uploaded_file($fileTmp, $destination)) {
        sendResponse(false, null, 'Upload failed');
    }

    $relativePath = 'uploads/tasks/' . $newFileName;

    $conn->beginTransaction();

    try {

        // Insert attachment
        $stmt = $conn->prepare("
            INSERT INTO task_attachments (task_id, file_name, file_path, uploaded_by)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $taskId,
            $fileName,
            $relativePath,
            currentUser()['id']
        ]);

        // Insert log
        $log = $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ");
        $log->execute([
            $taskId,
            'Uploaded a file: ' . $fileName,
            currentUser()['id']
        ]);

        $conn->commit();

        sendResponse(true, null, 'File uploaded successfully');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Upload failed');
    }
}
function updateTaskProgress($conn)
{
    if (!isLoggedIn()) {
        sendResponse(false, null, 'Unauthorized');
    }

    $taskId = (int)($_POST['task_id'] ?? 0);
    $progress = (int)($_POST['progress'] ?? -1);

    if (!$taskId || $progress < 0 || $progress > 100) {
        sendResponse(false, null, 'Invalid data');
    }

    $conn->beginTransaction();

    try {

        $stmt = $conn->prepare("
            UPDATE tasks SET progress = ?
            WHERE id = ?
        ");
        $stmt->execute([$progress, $taskId]);

        $log = $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ");
        $log->execute([
            $taskId,
            "Updated progress to {$progress}%",
            currentUser()['id']
        ]);

        $conn->commit();
        sendResponse(true, null, 'Progress updated');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed');
    }
}
function updateTaskStatus($conn)
{
    if (!isLoggedIn()) {
        sendResponse(false, null, 'Unauthorized');
    }

    $taskId = (int)($_POST['task_id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $allowed = ['pending', 'in_progress', 'completed'];

    if (!$taskId || !in_array($status, $allowed)) {
        sendResponse(false, null, 'Invalid status');
    }

    $conn->beginTransaction();

    try {

        $stmt = $conn->prepare("
            UPDATE tasks SET status = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $taskId]);

        $log = $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ");
        $log->execute([
            $taskId,
            "Changed status to " . str_replace('_', ' ', $status),
            currentUser()['id']
        ]);

        $conn->commit();
        sendResponse(true, null, 'Status updated');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed');
    }
}

function updateTask($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $taskId = (int)$_POST['task_id'];
    $title = trim($_POST['title']);
    $desc = trim($_POST['description']);
    $priority = $_POST['priority'];
    $dueDate = $_POST['due_date'];
    $assignedUsers = $_POST['assigned_users'] ?? [];

    if (!$taskId || $title === '') {
        sendResponse(false, null, 'Invalid data');
    }

    $conn->beginTransaction();

    try {

        // Update task
        $stmt = $conn->prepare("
            UPDATE tasks 
            SET title=?, description=?, priority=?, due_date=?
            WHERE id=?
        ");
        $stmt->execute([$title, $desc, $priority, $dueDate, $taskId]);

        // Remove old assignments
        $conn->prepare("DELETE FROM task_assignments WHERE task_id=?")
            ->execute([$taskId]);

        // Insert new assignments
        foreach ($assignedUsers as $userId) {
            $conn->prepare("
                INSERT INTO task_assignments (task_id, user_id)
                VALUES (?,?)
            ")->execute([$taskId, $userId]);
        }

        // Log
        $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ")->execute([
            $taskId,
            'Task updated & assignments changed',
            currentUser()['id']
        ]);

        $conn->commit();
        sendResponse(true, [
            'redirect' => BASE_URL . 'admin/tasks/view?id=' . $taskId
        ], 'Task updated successfully');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to update task');
    }
}

function deleteTask($conn)
{
    if (!isLoggedIn() || currentUser()['role'] !== 'admin') {
        sendResponse(false, null, 'Unauthorized');
    }

    $taskId = (int)($_POST['task_id'] ?? 0);

    if (!$taskId) {
        sendResponse(false, null, 'Invalid Task ID');
    }

    $conn->beginTransaction();

    try {

        // Optional: Insert log BEFORE delete
        $log = $conn->prepare("
            INSERT INTO task_logs (task_id, action, performed_by)
            VALUES (?, ?, ?)
        ");
        $log->execute([
            $taskId,
            'Task deleted',
            currentUser()['id']
        ]);

        // Delete task (cascade will remove assignments, comments, attachments)
        $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
        $stmt->execute([$taskId]);

        $conn->commit();

        sendResponse(true, null, 'Task deleted successfully');
    } catch (Exception $e) {
        $conn->rollBack();
        sendResponse(false, null, 'Failed to delete task');
    }
}
