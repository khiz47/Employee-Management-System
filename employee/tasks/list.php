<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireEmployee();
$pageTitle = 'My Tasks';
require __DIR__ . '/../layouts/wrapper-start.php';

?>

<div class="dashboard-content">

    <!-- FILTERS -->
    <div class="card mb-3">
        <div class="card-body row g-3">

            <div class="col-md-4">
                <select id="employeeTaskStatus" class="form-select">
                    <option value="all">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div class="col-md-4">
                <select id="employeeTaskPriority" class="form-select">
                    <option value="all">All Priority</option>
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                </select>
            </div>

        </div>
    </div>

    <div class="row g-4" id="employeeTaskContainer"></div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div id="employeeTaskPaginationInfo" class="text-muted"></div>
        <nav>
            <ul class="pagination mb-0" id="employeeTaskPagination"></ul>
        </nav>
    </div>

</div>


<?php require __DIR__ . '/../layouts/wrapper-end.php'; ?>