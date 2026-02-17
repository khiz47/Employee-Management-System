<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();
$pageTitle = 'update employee';
require __DIR__ . '/../layout/wrapper-start.php';
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die('Invalid employee ID');
}

// Fetch employee
$stmt = $conn->prepare("
    SELECT 
        u.id AS user_id,
        u.name,
        u.email,
        u.status AS user_status,
        e.department_id,
        e.phone,
        e.joining_date,
        e.designation,
        e.status AS employee_status
    FROM users u
    JOIN employees e ON e.user_id = u.id
    WHERE u.id = ? AND u.role = 'employee'
");
$stmt->execute([$id]);
$employee = $stmt->fetch();

if (!$employee) {
    die('Employee not found');
}

// Departments
$departments = $conn->query("
    SELECT id, name 
    FROM departments 
    WHERE status = 1 
    ORDER BY name
")->fetchAll();
?>

<div class="dashboard-content">

    <div class="mb-4">
        <h2>Edit Employee</h2>
        <p class="text-muted">Update employee details</p>
    </div>

    <form id="editEmployeeForm" class="card">
        <input type="hidden" name="user_id" value="<?= $employee['user_id'] ?>">

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control"
                        value="<?= htmlspecialchars($employee['name']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control"
                        value="<?= htmlspecialchars($employee['email']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" class="form-control" minlength="6">
                    <small class="text-muted">Leave blank to keep current password</small>
                </div>


                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">Select department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"
                                <?= $dept['id'] == $employee['department_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control"
                        value="<?= htmlspecialchars($employee['designation']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control"
                        value="<?= htmlspecialchars($employee['phone']) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control"
                        value="<?= $employee['joining_date'] ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Employee Status</label>
                    <select name="employee_status" class="form-control">
                        <option value="1" <?= $employee['employee_status'] ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= !$employee['employee_status'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">User Status</label>
                    <select name="user_status" class="form-control">
                        <option value="1" <?= $employee['user_status'] ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= !$employee['user_status'] ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>

            </div>

            <div id="employeeAlert" class="mt-3"></div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= BASE_URL ?>admin/employees" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Employee</button>
        </div>
    </form>
</div>
<?php
require __DIR__ . '/../layout/wrapper-end.php';
?>