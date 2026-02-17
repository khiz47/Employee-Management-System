<?php

// require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();
?>
<?php
$pageTitle = 'Employees Management';
require __DIR__ . '/../layout/wrapper-start.php';
?>

<div class="dashboard-content">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="hideHeader">Employees</h2>

        <div class="d-flex gap-2">
            <button id="exportCsvBtn" class="btn btn-outline-secondary">
                Export CSV
            </button>

            <a href="<?= BASE_URL ?>admin/employees/create" class="btn btn-primary">
                + Add Employee
            </a>
        </div>
    </div>

    <!-- FILTER BAR -->
    <div class="card mb-3">
        <div class="card-body">

            <!-- TABS -->
            <ul class="nav nav-pills mb-3" id="employeeFilterTabs">
                <li class="nav-item">
                    <button class="nav-link active" data-filter="all">All</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-filter="name">Name</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-filter="email">Email</button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-filter="department">Department</button>
                </li>
            </ul>

            <!-- SEARCH -->
            <input type="text" id="employeeSearch" class="form-control" placeholder="Type to search employees...">
            <div class="row g-3 mt-3">
                <div class="col-12 col-md-4">
                    <select id="statusFilter" class="form-select">
                        <option value="all">All Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <select id="departmentFilter" class="form-select">
                        <option value="all">All Departments</option>
                        <?php
                        $deptStmt = $conn->query("SELECT name FROM departments WHERE status = 1");
                        foreach ($deptStmt->fetchAll() as $d):
                        ?>
                            <option value="<?= strtolower($d['name']) ?>">
                                <?= htmlspecialchars($d['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <select id="rowsPerPage" class="form-select">
                        <option value="5">Show 5</option>
                        <option value="10" selected>Show 10</option>
                        <option value="25">Show 25</option>
                        <option value="50">Show 50</option>
                        <option value="all">Show All</option>
                    </select>
                </div>


            </div>

        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Status</th>
                            <th width="220">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="employeeTableBody">

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div id="paginationInfo" class="text-muted"></div>
        <nav>
            <ul class="pagination mb-0" id="pagination"></ul>
        </nav>
    </div>


</div>
<?php
require __DIR__ . '/../layout/wrapper-end.php';
?>