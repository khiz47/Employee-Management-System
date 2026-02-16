<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$pageTitle = 'Create Task';
require __DIR__ . '/../layout/wrapper-start.php';

// Fetch active employees
$employees = $conn->query("
    SELECT u.id, u.name 
    FROM users u
    JOIN employees e ON e.user_id = u.id
    WHERE u.role = 'employee' AND u.status = 1 AND e.status = 1
    ORDER BY u.name
")->fetchAll();

/* ---------------- DEPARTMENTS ---------------- */
$departments = $conn->query("
    SELECT id, name 
    FROM departments 
    WHERE status = 1
    ORDER BY name
")->fetchAll();
?>

<div class="dashboard-content">

    <div class="mb-4">
        <h2>Create Task</h2>
        <p class="text-muted">Assign a new task to an employee</p>
    </div>

    <form id="createTaskForm" class="card">
        <div class="card-body">

            <div class="row g-3">

                <!-- Title -->
                <div class="col-md-6">
                    <label class="form-label">Task Title *</label>
                    <input type="text" name="title" class="form-control" required>
                </div>

                <!-- Priority -->
                <div class="col-md-6">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="low">Low</option>
                        <option value="medium" selected>Medium</option>
                        <option value="high">High</option>
                    </select>
                </div>

                <!-- Description -->
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" rows="4" class="form-control"></textarea>
                </div>

                <!-- Due Date -->
                <div class="col-md-6">
                    <label class="form-label">Due Date</label>
                    <input type="date" name="due_date" class="form-control">
                </div>

                <!-- Department -->
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select id="departmentSelect" class="form-select">
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>">
                                <?= htmlspecialchars($dept['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Employees Checkbox Area -->
                <div class="col-12">
                    <label class="form-label">Assign Employees</label>
                    <div id="employeeCheckboxContainer" class="employee-checkbox-grid">
                        <p class="text-muted">Select department first</p>
                    </div>
                </div>






            </div>

            <div id="taskAlert" class="mt-3"></div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= BASE_URL ?>admin/tasks" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Task</button>
        </div>
    </form>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>