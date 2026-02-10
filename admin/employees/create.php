<?php
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/db.php';

requireAdmin();

$departments = $conn->query("
    SELECT id, name 
    FROM departments 
    WHERE status = 1 
    ORDER BY name
")->fetchAll();
?>

<div class="dashboard-content">

    <div class="mb-4">
        <h2>Add Employee</h2>
        <p class="text-muted">Create a new employee account</p>
    </div>

    <form id="addEmployeeForm" class="card">
        <div class="card-body">
            <div class="row g-3">

                <!-- REQUIRED -->
                <div class="col-md-6">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="name" class="form-control" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required>
                </div>

                <!-- OPTIONAL -->
                <div class="col-md-6">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-control">
                        <option value="">Select department</option>
                        <?php foreach ($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>">
                            <?= htmlspecialchars($dept['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Designation</label>
                    <input type="text" name="designation" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Joining Date</label>
                    <input type="date" name="joining_date" class="form-control">
                </div>

                <!-- STATUS -->
                <div class="col-md-6">
                    <label class="form-label">Employee Status</label>
                    <select name="employee_status" class="form-control">
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>

            </div>

            <div id="employeeAlert" class="mt-3"></div>
        </div>

        <div class="card-footer text-end">
            <a href="<?= BASE_URL ?>admin/employees" class="btn btn-light">Cancel</a>
            <button type="submit" class="btn btn-primary">Create Employee</button>
        </div>
    </form>
</div>