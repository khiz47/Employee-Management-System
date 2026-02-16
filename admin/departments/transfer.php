<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    die('Invalid Department ID');
}

// Fetch department
$stmt = $conn->prepare("
    SELECT id, name
    FROM departments
    WHERE id = ?
");
$stmt->execute([$id]);
$department = $stmt->fetch();

if (!$department) {
    die('Department not found');
}

// Count employees
$countStmt = $conn->prepare("
    SELECT COUNT(*) FROM employees WHERE department_id = ?
");
$countStmt->execute([$id]);
$employeeCount = (int)$countStmt->fetchColumn();

// Fetch other departments
$otherDepartments = $conn->prepare("
    SELECT id, name 
    FROM departments 
    WHERE id != ? AND status = 1
");
$otherDepartments->execute([$id]);
$departments = $otherDepartments->fetchAll();

$pageTitle = 'Transfer & Delete Department';
require __DIR__ . '/../layout/wrapper-start.php';
?>

<div class="dashboard-content">

    <div class="mb-4">
        <h2>Delete Department</h2>
        <p class="text-muted">
            To delete <strong><?= htmlspecialchars($department['name']) ?></strong>,
            you must transfer <?= $employeeCount ?> employee(s) first.
        </p>
    </div>

    <?php if ($employeeCount > 0): ?>

    <form id="transferDepartmentForm" class="card">
        <input type="hidden" name="from_department_id" value="<?= $department['id'] ?>">

        <div class="card-body">

            <div class="mb-4">
                <label class="form-label">Transfer Employees To</label>
                <select name="to_department_id" class="form-select" required>
                    <option value="">Select Department</option>
                    <?php foreach ($departments as $d): ?>
                    <option value="<?= $d['id'] ?>">
                        <?= htmlspecialchars($d['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <hr>

            <h5 class="mb-3">
                Employees in this department (<?= $employeeCount ?>)
            </h5>

            <div class="card">
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Designation</th>
                            </tr>
                        </thead>
                        <tbody id="transferEmployeeTableBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div id="transferPaginationInfo" class="text-muted"></div>
                <nav>
                    <ul class="pagination mb-0" id="transferPagination"></ul>
                </nav>
            </div>

            <div id="transferAlert" class="mt-3"></div>

        </div>

        <div class="card-footer text-end">
            <a href="<?= BASE_URL ?>admin/departments" class="btn btn-light">Cancel</a>
            <button type="button" id="confirmTransferBtn" class="btn btn-danger">
                Transfer & Delete
            </button>
        </div>
    </form>
    <div class="modal fade" id="confirmTransferModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">Confirm Transfer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to transfer all employees and permanently delete this department?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" id="finalTransferBtn" class="btn btn-danger">
                        Yes, Transfer & Delete
                    </button>
                </div>
            </div>
        </div>
    </div>


    <?php else: ?>

    <div class="card">
        <div class="card-body">
            <p>No employees found. You can safely delete this department.</p>
            <button id="deleteEmptyDepartment" data-id="<?= $department['id'] ?>" class="btn btn-danger">
                Delete Department
            </button>
        </div>
    </div>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>