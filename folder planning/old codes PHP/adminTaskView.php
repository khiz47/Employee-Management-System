<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die('Invalid Task ID');
}

/*
|--------------------------------------------------------------------------
| FETCH TASK DETAILS
|--------------------------------------------------------------------------
*/
$stmt = $conn->prepare("
    SELECT t.*,
           u.name AS employee_name,
           c.name AS creator_name
    FROM tasks t
    JOIN users u ON u.id = t.assigned_to
    JOIN users c ON c.id = t.created_by
    WHERE t.id = ?
");
$stmt->execute([$id]);
$task = $stmt->fetch();

if (!$task) {
    die('Task not found');
}

/*
|--------------------------------------------------------------------------
| FETCH COMMENTS
|--------------------------------------------------------------------------
*/
$comments = $conn->prepare("
    SELECT tc.*, u.name
    FROM task_comments tc
    JOIN users u ON u.id = tc.user_id
    WHERE tc.task_id = ?
    ORDER BY tc.created_at ASC
");
$comments->execute([$id]);
$comments = $comments->fetchAll();

/*
|--------------------------------------------------------------------------
| FETCH ATTACHMENTS
|--------------------------------------------------------------------------
*/
$attachments = $conn->prepare("
    SELECT ta.*, u.name
    FROM task_attachments ta
    JOIN users u ON u.id = ta.uploaded_by
    WHERE ta.task_id = ?
    ORDER BY ta.created_at DESC
");
$attachments->execute([$id]);
$attachments = $attachments->fetchAll();

/*
|--------------------------------------------------------------------------
| FETCH LOGS
|--------------------------------------------------------------------------
*/
$logs = $conn->prepare("
    SELECT tl.*, u.name
    FROM task_logs tl
    JOIN users u ON u.id = tl.performed_by
    WHERE tl.task_id = ?
    ORDER BY tl.created_at DESC
");
$logs->execute([$id]);
$logs = $logs->fetchAll();

$pageTitle = 'Task Details';
require __DIR__ . '/../layout/wrapper-start.php';

$isOverdue = ($task['due_date'] < date('Y-m-d') && $task['status'] !== 'completed');

$priorityColor = match ($task['priority']) {
    'high' => 'danger',
    'medium' => 'warning',
    default => 'primary'
};
?>

<div class="dashboard-content">

    <!-- HEADER -->
    <div class="card mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between mb-3">
                <h3><?= htmlspecialchars($task['title']) ?></h3>
                <a href="<?= BASE_URL ?>admin/chat?task=<?= $task['id'] ?>" class="btn btn-sm btn-outline-dark">
                    💬 Open Chat
                </a>

                <div>
                    <span class="badge bg-<?= $priorityColor ?>">
                        <?= ucfirst($task['priority']) ?>
                    </span>

                    <select class="form-select form-select-sm taskStatusDropdown" data-task="<?= $task['id'] ?>"
                        style="width: 180px; display:inline-block;">
                        <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>
                            Pending
                        </option>
                        <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>
                            In Progress
                        </option>
                        <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>
                            Completed
                        </option>
                    </select>

                </div>
            </div>

            <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>

            <div class="row mt-3">
                <div class="col-md-4">
                    <small class="text-muted">Assigned To</small>
                    <div><?= htmlspecialchars($task['employee_name']) ?></div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Created By</small>
                    <div><?= htmlspecialchars($task['creator_name']) ?></div>
                </div>

                <div class="col-md-4">
                    <small class="text-muted">Due Date</small>
                    <div>
                        <?= $task['due_date'] ?>
                        <?php if ($isOverdue): ?>
                            <span class="text-danger">(Overdue)</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- PROGRESS -->
            <div class="mt-4">
                <label class="form-label">Progress</label>

                <input type="range" min="0" max="100" step="5" value="<?= $task['progress'] ?>"
                    class="form-range taskProgressSlider" data-task="<?= $task['id'] ?>">

                <div class="progress" style="height:8px;">
                    <div class="progress-bar bg-success taskProgressBar" style="width: <?= $task['progress'] ?>%;">
                    </div>
                </div>

                <small class="progressText">
                    <?= $task['progress'] ?>% Completed
                </small>
            </div>


        </div>
    </div>


    <!-- COMMENTS + ATTACHMENTS -->
    <div class="row g-4">

        <!-- COMMENTS -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5>Comments</h5>

                    <div class="task-comments mb-3">
                        <?php if (!$comments): ?>
                            <p class="text-muted">No comments yet.</p>
                        <?php endif; ?>

                        <?php foreach ($comments as $comment): ?>
                            <div class="border-bottom pb-2 mb-2 comment-item" data-id="<?= $comment['id'] ?>">
                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($comment['name']) ?></strong>
                                    <small class="text-muted">
                                        <?= $comment['created_at'] ?>
                                    </small>
                                </div>

                                <div class="comment-text">
                                    <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                                </div>

                                <?php if ($comment['user_id'] == currentUser()['id']): ?>
                                    <button class="btn btn-sm btn-link editCommentBtn p-0">
                                        Edit
                                    </button>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>

                    </div>

                    <!-- ADD COMMENT -->
                    <form class="addCommentForm">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <textarea name="comment" class="form-control mb-2" placeholder="Write comment..."
                            required></textarea>

                        <button class="btn btn-sm btn-primary">
                            Add Comment
                        </button>
                    </form>

                </div>
            </div>
        </div>


        <!-- ATTACHMENTS -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">
                    <h5>Attachments</h5>

                    <?php if (!$attachments): ?>
                        <p class="text-muted">No files uploaded.</p>
                    <?php endif; ?>

                    <?php foreach ($attachments as $file): ?>
                        <div class="mb-2">
                            <a href="<?= BASE_URL . $file['file_path'] ?>" target="_blank">
                                <?= htmlspecialchars($file['file_name']) ?>
                            </a>
                            <small class="text-muted">
                                by <?= htmlspecialchars($file['name']) ?>
                            </small>
                        </div>
                    <?php endforeach; ?>

                    <!-- UPLOAD -->
                    <form class="uploadAttachmentForm mt-3" enctype="multipart/form-data">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <input type="file" name="file" class="form-control mb-2" required>
                        <button class="btn btn-sm btn-secondary">
                            Upload File
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>


    <!-- ACTIVITY LOG -->
    <div class="card mt-4">
        <div class="card-body">
            <h5>Activity Timeline</h5>

            <?php if (!$logs): ?>
                <p class="text-muted">No activity yet.</p>
            <?php endif; ?>

            <?php foreach ($logs as $log): ?>
                <div class="border-start ps-3 mb-3">
                    <div><?= htmlspecialchars($log['action']) ?></div>
                    <small class="text-muted">
                        <?= htmlspecialchars($log['name']) ?> —
                        <?= $log['created_at'] ?>
                    </small>
                </div>
            <?php endforeach; ?>

        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>