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
           c.name AS creator_name
    FROM tasks t
    JOIN users c ON c.id = t.created_by
    WHERE t.id = ?
");
$stmt->execute([$id]);
$task = $stmt->fetch();
$assignedUsersStmt = $conn->prepare("
    SELECT u.id, u.name
    FROM task_assignments ta
    JOIN users u ON u.id = ta.user_id
    WHERE ta.task_id = ?
");
$assignedUsersStmt->execute([$id]);
$assignedUsers = $assignedUsersStmt->fetchAll();


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
    <div class="card task-header-card mb-4">
        <div class="card-body">

            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

                <div>
                    <h3 class="task-title"><?= htmlspecialchars($task['title']) ?></h3>

                    <div class="task-meta mt-2">
                        <span class="badge priority-badge bg-<?= $priorityColor ?>">
                            <?= ucfirst($task['priority']) ?>
                        </span>

                        <span class="badge status-badge">
                            <?= ucfirst(str_replace('_', ' ', $task['status'])) ?>
                        </span>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <a href="<?= BASE_URL ?>admin/chat?task=<?= $task['id'] ?>" class="btn btn-sm btn-outline-primary">
                        💬 Chat
                    </a>
                </div>

            </div>

            <div class="task-description mt-4">
                <?= nl2br(htmlspecialchars($task['description'])) ?>
            </div>

            <div class="row mt-4 task-info-row">
                <div class="col-md-4">
                    <span class="info-label">Assigned To</span>
                    <div class="info-value">
                        <?php if (!$assignedUsers): ?>
                        <span class="text-muted">No employees assigned</span>
                        <?php else: ?>
                        <?php foreach ($assignedUsers as $user): ?>
                        <span class="badge bg-light text-dark me-1">
                            <?= htmlspecialchars($user['name']) ?>
                        </span>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                </div>

                <div class="col-md-4">
                    <span class="info-label">Created By</span>
                    <div class="info-value"><?= htmlspecialchars($task['creator_name']) ?></div>
                </div>

                <div class="col-md-4">
                    <span class="info-label">Due Date</span>
                    <div class="info-value <?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                        <?= $task['due_date'] ?>
                        <?php if ($isOverdue): ?>
                        <span class="badge bg-danger ms-2">Overdue</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Progress -->
            <div class="task-progress mt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span>Progress</span>
                    <span><?= $task['progress'] ?>%</span>
                </div>

                <div class="progress progress-modern">
                    <div class="progress-bar" style="width: <?= $task['progress'] ?>%;">
                    </div>
                </div>
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
                        <div class="comment-item" data-id="<?= $comment['id'] ?>">
                            <div class="d-flex justify-content-between">
                                <strong><?= htmlspecialchars($comment['name']) ?></strong>
                                <small class="text-muted">
                                    <?= $comment['created_at'] ?>
                                </small>
                            </div>

                            <div class="comment-text comment-body">
                                <?= nl2br(htmlspecialchars($comment['comment'])) ?>
                            </div>

                            <?php if ($comment['user_id'] == currentUser()['id']): ?>
                            <div class="comment-actions">
                                <button class="btn btn-sm btn-link editCommentBtn p-0">
                                    Edit
                                </button>
                            </div>
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
                    <div class="attachment-item">
                        <a href="<?= BASE_URL . $file['file_path'] ?>" target="_blank">
                            <?= htmlspecialchars($file['file_name']) ?>
                        </a>
                        <small>
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

            <div class="timeline">

                <?php foreach ($logs as $log): ?>
                <div class="timeline-item">
                    <div class="timeline-dot"></div>
                    <div class="timeline-content">
                        <div><?= htmlspecialchars($log['action']) ?></div>
                        <small>
                            <?= htmlspecialchars($log['name']) ?> —
                            <?= $log['created_at'] ?>
                        </small>
                    </div>
                </div>
                <?php endforeach; ?>

            </div>


        </div>
    </div>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>