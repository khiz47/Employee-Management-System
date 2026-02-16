<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();
$pageTitle = 'Task Management';
require __DIR__ . '/../layout/wrapper-start.php';

// Fetch tasks
$stmt = $conn->query("
    SELECT t.*, 
           u.name AS employee_name,
           c.name AS creator_name
    FROM tasks t
    JOIN users u ON u.id = t.assigned_to
    JOIN users c ON c.id = t.created_by
    ORDER BY t.id DESC
");

$tasks = $stmt->fetchAll();
?>

<div class="dashboard-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tasks</h2>
        <a href="<?= BASE_URL ?>admin/tasks/create" class="btn btn-primary">
            + Create Task
        </a>
    </div>

    <div class="row g-4">
        <?php foreach ($tasks as $task):

            $isOverdue = ($task['due_date'] < date('Y-m-d') && $task['status'] !== 'completed');

            $priorityColor = match ($task['priority']) {
                'high' => 'danger',
                'medium' => 'warning',
                default => 'primary'
            };
        ?>

            <div class="col-md-6 col-lg-4">
                <div class="card task-card <?= $isOverdue ? 'border-danger' : '' ?>">
                    <div class="card-body">

                        <div class="d-flex justify-content-between mb-2">
                            <span class="badge bg-<?= $priorityColor ?>">
                                <?= ucfirst($task['priority']) ?>
                            </span>

                            <span class="badge bg-secondary">
                                <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                            </span>
                        </div>

                        <h5 class="mb-2"><?= htmlspecialchars($task['title']) ?></h5>

                        <small class="text-muted">
                            Assigned to: <?= htmlspecialchars($task['employee_name']) ?>
                        </small>

                        <div class="mt-2">
                            <small>
                                Due: <?= $task['due_date'] ?>
                                <?php if ($isOverdue): ?>
                                    <span class="text-danger">(Overdue)</span>
                                <?php endif; ?>
                            </small>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress mt-3" style="height: 6px;">
                            <div class="progress-bar bg-success" style="width: <?= $task['progress'] ?>%;">
                            </div>
                        </div>
                        <small><?= $task['progress'] ?>% completed</small>

                        <div class="mt-3 d-flex gap-2">
                            <a href="<?= BASE_URL ?>admin/tasks/view?id=<?= $task['id'] ?>"
                                class="btn btn-sm btn-outline-secondary">View</a>

                            <a href="<?= BASE_URL ?>admin/tasks/edit?id=<?= $task['id'] ?>"
                                class="btn btn-sm btn-outline-primary">Edit</a>

                            <button class="btn btn-sm btn-outline-danger deleteTaskBtn" data-id="<?= $task['id'] ?>">
                                Delete
                            </button>
                        </div>

                    </div>
                </div>
            </div>

        <?php endforeach; ?>
    </div>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>