<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('Invalid Task ID');

/* ---------------- TASK ---------------- */
$stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
$stmt->execute([$id]);
$task = $stmt->fetch();
if (!$task) die('Task not found');

/* ---------------- ASSIGNED USERS ---------------- */
$assignedStmt = $conn->prepare("
    SELECT u.id, u.name 
    FROM task_assignments ta
    JOIN users u ON u.id = ta.user_id
    WHERE ta.task_id = ?
");
$assignedStmt->execute([$id]);
$assignedUsers = $assignedStmt->fetchAll();

/* ---------------- ALL EMPLOYEES ---------------- */
$employees = $conn->query("
    SELECT id, name 
    FROM users 
    WHERE role='employee' AND status=1
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

                    <div class="col-md-12">
                        <label>Assign Employees</label>
                        <select name="assigned_users[]" class="form-select" multiple size="6">
                            <?php foreach ($employees as $emp):
                                $isSelected = in_array($emp['id'], array_column($assignedUsers, 'id'));
                            ?>
                            <option value="<?= $emp['id'] ?>" <?= $isSelected ? 'selected' : '' ?>>
                                <?= htmlspecialchars($emp['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted">Hold Ctrl to select multiple</small>
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