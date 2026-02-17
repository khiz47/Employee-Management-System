<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireAdmin();

// Total Employees
$totalEmployees = $conn->query("
    SELECT COUNT(*) FROM users WHERE role = 'employee'
")->fetchColumn();

// Active Employees
$activeEmployees = $conn->query("
    SELECT COUNT(*) FROM users 
    WHERE role = 'employee' AND status = 1
")->fetchColumn();

// Departments
$totalDepartments = $conn->query("
    SELECT COUNT(*) FROM departments
")->fetchColumn();

// Total Tasks
$totalTasks = $conn->query("
    SELECT COUNT(*) FROM tasks
")->fetchColumn() ?? 0;

// Pending Tasks
$pendingTasks = $conn->query("
    SELECT COUNT(*) FROM tasks WHERE status = 'pending'
")->fetchColumn() ?? 0;

// Recent Employees
$recentEmployees = $conn->query("
    SELECT name, email 
    FROM users 
    WHERE role = 'employee'
    ORDER BY id DESC
    LIMIT 5
")->fetchAll();

$deptStats = $conn->query("
    SELECT d.name, COUNT(e.id) AS total
    FROM departments d
    LEFT JOIN employees e ON e.department_id = d.id
    GROUP BY d.id
")->fetchAll();

$deptNames = [];
$deptCounts = [];

foreach ($deptStats as $d) {
    $deptNames[] = $d['name'];
    $deptCounts[] = $d['total'];
}

$taskStats = $conn->query("
    SELECT status, COUNT(*) as total
    FROM tasks
    GROUP BY status
")->fetchAll();

$taskLabels = [];
$taskCounts = [];

foreach ($taskStats as $t) {
    $taskLabels[] = ucfirst($t['status']);
    $taskCounts[] = $t['total'];
}


require __DIR__ . '/layout/wrapper-start.php';
?>

<!-- DASHBOARD CONTENT -->
<section class="dashboard-content">

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div>
                <h3>Employees</h3>
                <p class="counter" data-target="<?= $totalEmployees ?>">0</p>

            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <h3>Tasks</h3>
                <p class="counter" data-target="<?= $totalTasks ?>">0</p>

            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-orange">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <h3>Departments</h3>
                <p class="counter" data-target="<?= $totalDepartments ?>">0</p>

            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h3>Pending Tasks</h3>
                <p class="counter" data-target="<?= $pendingTasks ?>">0</p>

            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Recent Employees</h5>

            <?php if (!$recentEmployees): ?>
                <p class="text-muted">No employees yet.</p>
            <?php else: ?>
                <ul class="list-group list-group-flush">
                    <div class="recent-employee-list">
                        <?php foreach ($recentEmployees as $emp): ?>
                            <div class="recent-employee-item">
                                <div class="recent-employee-name">
                                    <?= htmlspecialchars($emp['name']) ?>
                                </div>
                                <div class="recent-employee-email">
                                    <?= htmlspecialchars($emp['email']) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </ul>
            <?php endif; ?>

        </div>
    </div>

    <!-- CHARTs -->
    <div class="row mt-4 g-4">

        <!-- Employees by Department -->
        <div class="col-12 col-lg-6 mb-4 ">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Employees by Department</h5>
                    <div class="chart-wrapper">
                        <canvas id="departmentChart" data-labels='<?= json_encode($deptNames) ?>'
                            data-counts='<?= json_encode($deptCounts) ?>'>
                        </canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Task Status -->
        <div class="col-12 col-lg-6 mb-4 ">
            <div class="card h-100">
                <div class="card-body">
                    <h5 class="card-title">Task Status Overview</h5>
                    <div class="chart-wrapper">
                        <canvas id="taskStatusChart" data-labels='<?= json_encode($taskLabels) ?>'
                            data-counts='<?= json_encode($taskCounts) ?>'>
                        </canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- PLACEHOLDER CONTENT -->
    <div class="card mt-4">
        <div class="card-body">
            <h5 class="card-title">Welcome 👋</h5>
            <p class="text-muted">
                This is your admin dashboard. From here you can manage employees,
                assign tasks, create departments, and monitor activity.
            </p>
        </div>
    </div>

</section>

<?php
require __DIR__ . '/layout/wrapper-end.php';
?>