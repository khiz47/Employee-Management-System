<?php
// require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();
$pageTitle = 'All Departments';
require __DIR__ . '/../layout/wrapper-start.php';
?>

<div class="dashboard-content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="hideHeader">Departments</h2>

        <a href="<?= BASE_URL ?>admin/departments/create" class="btn btn-primary">
            + Add Department
        </a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Employees</th>
                            <th>Status</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="departmentTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div id="departmentPaginationInfo" class="text-muted"></div>
        <nav>
            <ul class="pagination mb-0" id="departmentPagination"></ul>
        </nav>
    </div>

</div>

<?php
require __DIR__ . '/../layout/wrapper-end.php';
?>