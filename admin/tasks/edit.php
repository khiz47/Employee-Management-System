<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('Invalid Task ID');

/* ---------------- TASK ---------------- */
$stmt = $conn->prepare("
    SELECT t.*, e.department_id
    FROM tasks t
    LEFT JOIN task_assignments ta ON ta.task_id = t.id
    LEFT JOIN employees e ON e.user_id = ta.user_id
    WHERE t.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$task = $stmt->fetch();

if (!$task) die('Task not found');


/* ---------------- DEPARTMENTS ---------------- */
$departments = $conn->query("
    SELECT id, name 
    FROM departments 
    WHERE status = 1
    ORDER BY name
")->fetchAll();


$pageTitle = 'Edit Task';
require __DIR__ . '/../layout/wrapper-start.php';
?>

<div class="dashboard-content">

    <div class="card">
        <div class="card-body">

            <form id="editTaskForm">
                <input type="hidden" name="task_id" value="<?= $task['id'] ?>">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label>Title</label>
                        <input type="text" name="title" class="form-control"
                            value="<?= htmlspecialchars($task['title']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label>Priority</label>
                        <select name="priority" class="form-select">
                            <option value="low" <?= $task['priority'] == 'low' ? 'selected' : '' ?>>Low</option>
                            <option value="medium" <?= $task['priority'] == 'medium' ? 'selected' : '' ?>>Medium
                            </option>
                            <option value="high" <?= $task['priority'] == 'high' ? 'selected' : '' ?>>High</option>
                        </select>
                    </div>

                    <div class="col-md-12">
                        <label>Description</label>
                        <textarea name="description" class="form-control"
                            rows="4"><?= htmlspecialchars($task['description']) ?></textarea>
                    </div>

                    <div class="col-md-6">
                        <label>Due Date</label>
                        <input type="date" name="due_date" class="form-control" value="<?= $task['due_date'] ?>">
                    </div>

                    <!-- Department -->
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select id="departmentSelect" class="form-select">
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $dept): ?>
                                <option value="<?= $dept['id'] ?>"
                                    <?= $dept['id'] == $task['department_id'] ? 'selected' : '' ?>>
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

                <div class="mt-4 text-end">
                    <button class="btn btn-primary">Update Task</button>
                </div>

            </form>

        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>