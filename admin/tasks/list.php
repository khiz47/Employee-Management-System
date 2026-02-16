<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();
$pageTitle = 'Task Management';
require __DIR__ . '/../layout/wrapper-start.php';

?>

<div class="dashboard-content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tasks</h2>
        <div>
            <a href="<?= BASE_URL ?>admin/tasks/create" class="btn btn-primary">
                + Create Task
            </a>
            <a href="<?= BASE_URL ?>admin/tasks/history" class="btn btn-outline-success">
                Task History
            </a>
        </div>
    </div>

    <div class="row g-4" id="taskListContainer"></div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div id="taskPaginationInfo" class="text-muted"></div>
        <nav>
            <ul class="pagination mb-0" id="taskPagination"></ul>
        </nav>
    </div>


</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>