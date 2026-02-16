<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();
$pageTitle = 'Task History';
require __DIR__ . '/../layout/wrapper-start.php';
?>

<div class="dashboard-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Completed Task History</h2>
        <a href="<?= BASE_URL ?>admin/tasks" class="btn btn-outline-secondary">
            Back to Tasks
        </a>
    </div>

    <div class="row g-4" id="taskHistoryContainer"></div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div id="taskHistoryPaginationInfo" class="text-muted"></div>
        <nav>
            <ul class="pagination mb-0" id="taskHistoryPagination"></ul>
        </nav>
    </div>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>