<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$pageTitle = 'Create Department';
require __DIR__ . '/../layout/wrapper-start.php';

?>

<div class="dashboard-content">

    <div class="mb-4">
        <h2>Add department</h2>
        <p class="text-muted">Create a new department</p>
    </div>

    <form id="addDepartmentForm" class="card">
        <div class="card-body">

            <div class="row g-3">

                <div class="col-md-6">
                    <label class="form-label">Department Name</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="1" selected>Active</option>
                        <option value="0">Disabled</option>
                    </select>
                </div>

            </div>

            <div id="departmentAlert" class="mt-3"></div>

        </div>

        <div class="card-footer text-end">
            <a href="<?= BASE_URL ?>admin/departments" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Department</button>
        </div>
    </form>

</div>
<?php
require __DIR__ . '/../layout/wrapper-end.php';
?>