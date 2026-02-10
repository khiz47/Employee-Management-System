<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$userId = (int)($_GET['id'] ?? 0);
if (!$userId) {
    die('Invalid employee ID');
}

// Fetch employee details
$stmt = $conn->prepare("
    SELECT 
        u.id,
        u.name,
        u.email,
        u.status AS user_status,
        u.created_at,
        u.last_login,

        e.department_id,
        e.phone,
        e.designation,
        e.joining_date,
        e.status AS employee_status,

        d.name AS department_name
    FROM users u
    INNER JOIN employees e ON e.user_id = u.id
    LEFT JOIN departments d ON d.id = e.department_id
    WHERE u.id = ? AND u.role = 'employee'
    LIMIT 1
");
$stmt->execute([$userId]);
$emp = $stmt->fetch();

if (!$emp) {
    die('Employee not found');
}
?>

<div class="dashboard-content">

    <!-- HEADER -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2><?= htmlspecialchars($emp['name']) ?></h2>
            <p class="text-muted mb-0"><?= htmlspecialchars($emp['email']) ?></p>
        </div>

        <div>
            <a href="<?= BASE_URL ?>admin/employees/edit?id=<?= $emp['id'] ?>" class="btn btn-outline-primary">
                Edit
            </a>

            <button
                class="btn <?= $emp['user_status'] ? 'btn-outline-danger' : 'btn-outline-success' ?> toggleEmployeeStatus"
                data-id="<?= $emp['id'] ?>" data-status="<?= $emp['user_status'] ?>">
                <?= $emp['user_status'] ? 'Deactivate' : 'Activate' ?>
            </button>
        </div>
    </div>

    <div class="row g-4">

        <!-- BASIC INFO -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Basic Information</h5>

                    <p><strong>Department:</strong> <?= $emp['department_name'] ?? '—' ?></p>
                    <p><strong>Designation:</strong> <?= $emp['designation'] ?: '—' ?></p>
                    <p><strong>Phone:</strong> <?= $emp['phone'] ?: '—' ?></p>
                    <p><strong>Joining Date:</strong>
                        <?= $emp['joining_date'] ? date('d M Y', strtotime($emp['joining_date'])) : '—' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- STATUS / META -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Account Status</h5>

                    <p>
                        <strong>User Status:</strong>
                        <?php if ($emp['user_status']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </p>

                    <p>
                        <strong>Employee Status:</strong>
                        <?php if ($emp['employee_status']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-danger">Inactive</span>
                        <?php endif; ?>
                    </p>

                    <p><strong>Created At:</strong>
                        <?= date('d M Y, h:i A', strtotime($emp['created_at'])) ?>
                    </p>

                    <p><strong>Last Login:</strong>
                        <?= $emp['last_login'] ? date('d M Y, h:i A', strtotime($emp['last_login'])) : 'Never' ?>
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- BACK -->
    <div class="mt-4">
        <a href="<?= BASE_URL ?>admin/employees" class="btn btn-light">
            ← Back to Employees
        </a>
    </div>

</div>