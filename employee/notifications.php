<?php
$pageTitle = 'Notifications';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireEmployee();

require __DIR__ . '/layouts/wrapper-start.php';
?>
<div class="dashboard-content">

    <!-- FILTER BAR -->
    <div class="card mb-3">
        <div class="card-body">
            <h5 class="card-title">Filters</h5>

            <!-- SEARCH -->
            <input type="text" id="notificationSearch" class="form-control" placeholder="Search notifications...">
            <div class="row g-3 mt-3">
                <div class="col-12 col-md-3">
                    <select id="notifyStatusFilter" class="form-select">
                        <option value="all">All Notify</option>
                        <option value="1">Read</option>
                        <option value="0">Unread</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <select id="checkedFilter" class="form-select">
                        <option value="all">All Checked</option>
                        <option value="highlighted">highlighted</option>
                        <option value="pin">pin</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <select id="rowsPerNotifyPage" class="form-select">
                        <option value="5">Show 5</option>
                        <option value="10" selected>Show 10</option>
                        <option value="25">Show 25</option>
                        <option value="50">Show 50</option>
                        <option value="all">Show All</option>
                    </select>
                </div>

                <div class="col-12 col-md-3 mb-2 d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary" id="bulkMarkRead">
                        Mark as Read
                    </button>

                    <button class="btn btn-sm btn-outline-danger" id="bulkDelete">
                        Delete Selected
                    </button>
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
                            <th width="40">
                                <input type="checkbox" id="selectAllNotifications">
                            </th>
                            <th width="40"></th>
                            <th width="40"></th>
                            <th>Notification</th>
                            <th width="150">Date</th>
                            <th width="180">Actions</th>
                        </tr>
                    </thead>

                    <tbody id="notificationTableBody">

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-3">
        <div id="paginationInfo" class="text-muted"></div>
        <nav>
            <ul class="pagination mb-0" id="notifypagination"></ul>
        </nav>
    </div>
</div>

<div class="modal fade" id="deleteNotifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Notifications</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                Are you sure you want to delete selected notifications?
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteNotifications">
                    Delete
                </button>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/layouts/wrapper-end.php'; ?>