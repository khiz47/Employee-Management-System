<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireAdmin();
?>

<div class="admin-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-users-gear"></i>
            <span>EMS Admin</span>
        </div>

        <nav class="sidebar-nav">
            <a href="<?= ADMIN_DASHBOARD ?>" class="active">
                <i class="fa-solid fa-chart-line"></i>
                Dashboard
            </a>

            <a href="<?= BASE_URL ?>admin/employees/list">
                <i class="fa-solid fa-users"></i>
                Employees
            </a>

            <a href="<?= BASE_URL ?>admin/tasks">
                <i class="fa-solid fa-list-check"></i>
                Tasks
            </a>

            <a href="<?= BASE_URL ?>admin/departments">
                <i class="fa-solid fa-building"></i>
                Departments
            </a>

            <a href="<?= BASE_URL ?>logout">
                <i class="fa-solid fa-right-from-bracket"></i>
                Logout
            </a>
        </nav>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOPBAR -->
        <header class="topbar">
            <h1>Dashboard</h1>

            <div class="topbar-user">
                <i class="fa-solid fa-user-circle"></i>
                <span><?= htmlspecialchars(currentUser()['name']) ?></span>
            </div>
        </header>

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
                        <p>24</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-green">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <h3>Tasks</h3>
                        <p>128</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-orange">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div>
                        <h3>Departments</h3>
                        <p>6</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon bg-purple">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <div>
                        <h3>Pending Tasks</h3>
                        <p>18</p>
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
    </div>

</div>