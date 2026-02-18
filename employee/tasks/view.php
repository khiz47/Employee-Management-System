<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireEmployee();

$id = (int)($_GET['id'] ?? 0);
if (!$id) die('Invalid Task ID');

/*
|--------------------------------------------------------------------------
| VERIFY EMPLOYEE IS ASSIGNED TO THIS TASK
|--------------------------------------------------------------------------
*/
$verify = $conn->prepare("
    SELECT t.*, u.name AS creator_name
    FROM tasks t
    JOIN task_assignments ta ON ta.task_id = t.id
    JOIN users u ON u.id = t.created_by
    WHERE t.id = ?
    AND ta.user_id = ?
");
$verify->execute([$id, currentUser()['id']]);
$task = $verify->fetch();

if (!$task) {
    die('Access denied');
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

$pageTitle = 'Task Details';
require __DIR__ . '/../layouts/wrapper-start.php';

$isOverdue = ($task['due_date'] < date('Y-m-d') && $task['status'] !== 'completed');
?>

<div class="dashboard-content">

    <div class="card mb-4">
        <div class="card-body">

            <h3><?= htmlspecialchars($task['title']) ?></h3>

            <div class="mb-3">
                <span class="badge bg-primary"><?= ucfirst($task['priority']) ?></span>

                <?php if ($isOverdue): ?>
                    <span class="badge bg-danger">Overdue</span>
                <?php endif; ?>
            </div>

            <p><?= nl2br(htmlspecialchars($task['description'])) ?></p>

            <hr>

            <div class="row">

                <div class="col-md-6">
                    <label>Status</label>
                    <select class="form-select taskStatusDropdown" data-task="<?= $task['id'] ?>">
                        <option value="pending" <?= $task['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                        <option value="in_progress" <?= $task['status'] == 'in_progress' ? 'selected' : '' ?>>In
                            Progress
                        </option>
                        <option value="completed" <?= $task['status'] == 'completed' ? 'selected' : '' ?>>Completed
                        </option>
                    </select>
                </div>

                <div class="col-md-6">
                    <label>Progress</label>
                    <input type="range" min="0" max="100" value="<?= $task['progress'] ?>"
                        class="form-range taskProgressSlider" data-task="<?= $task['id'] ?>">

                    <div class="progress mt-2">
                        <div class="progress-bar taskProgressBar" style="width: <?= $task['progress'] ?>%;">
                        </div>
                    </div>

                    <small class="progressText">
                        <?= $task['progress'] ?>% Completed
                    </small>

                </div>

            </div>

        </div>
    </div>

    <div class="row g-4">

        <!-- COMMENTS -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">

                    <h5>Comments</h5>

                    <div class="task-comments mb-3">

                        <?php foreach ($comments as $comment): ?>
                            <div class="comment-item" data-id="<?= $comment['id'] ?>">

                                <div class="d-flex justify-content-between">
                                    <strong><?= htmlspecialchars($comment['name']) ?></strong>
                                    <small><?= $comment['created_at'] ?></small>
                                </div>

                                <div class="comment-text comment-body">
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

                    <form class="addCommentForm">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <textarea name="comment" class="form-control mb-2" placeholder="Write comment..."
                            required></textarea>
                        <button class="btn btn-primary btn-sm">
                            Add Comment
                        </button>
                    </form>

                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-body">

                    <h5>Attachments</h5>

                    <?php foreach ($attachments as $file): ?>
                        <div class="attachment-item">
                            <a href="<?= BASE_URL . $file['file_path'] ?>" target="_blank">
                                <?= htmlspecialchars($file['file_name']) ?>
                            </a>
                            <small>by <?= htmlspecialchars($file['name']) ?></small>
                        </div>
                    <?php endforeach; ?>

                    <form class="uploadAttachmentForm mt-3" enctype="multipart/form-data">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <input type="file" name="file" class="form-control mb-2" required>
                        <button class="btn btn-secondary btn-sm">
                            Upload
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../layouts/wrapper-end.php'; ?>