<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$pageTitle = 'Update Department';
require __DIR__ . '/../layout/wrapper-start.php';

$id = (int)($_GET['id'] ?? 0);

if (!$id) {
    die('Invalid Department ID');
}

// Fetch department
$stmt = $conn->prepare("
    SELECT id, name, status
    FROM departments
    WHERE id = ?
");
$stmt->execute([$id]);
$department = $stmt->fetch();

if (!$department) {
    die('Department not found');
}
?>

<div class="dashboard-content">

    <div class="mb-4">
        <h2>Edit Department</h2>
        <p class="text-muted">Update department details</p>
    </div>

    <form id="editDepartmentForm" class="card">
        <input type="hidden" name="id" value="<?= $department['id'] ?>">

        <div class="card-body">
            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="name" class="form-control"
                        value="<?= htmlspecialchars($department['name']) ?>" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" <?= $department['status'] ? 'selected' : '' ?>>
                            Active
                        </option>
                        <option value="0" <?= !$department['status'] ? 'selected' : '' ?>>
                            Disabled
                        </option>
                    </select>
                </div>

            </div>

            <div id="departmentAlert" class="mt-3"></div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= BASE_URL ?>admin/departments" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Department</button>
        </div>
    </form>

</div>

<?php require __DIR__ . '/../layout/wrapper-end.php'; ?>