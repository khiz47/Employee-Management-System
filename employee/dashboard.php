<?php
$pageTitle = 'Dashboard';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
requireEmployee();

$userId = currentUser()['id'];

// Task Stats
$taskStats = $conn->prepare("
    SELECT status, COUNT(*) as total
    FROM tasks t
    JOIN task_assignments ta ON ta.task_id = t.id
    WHERE ta.user_id = ?
    GROUP BY status
");
$taskStats->execute([$userId]);
$stats = $taskStats->fetchAll(PDO::FETCH_KEY_PAIR);

$totalTasks = array_sum($stats);
$pending = $stats['pending'] ?? 0;
$inProgress = $stats['in_progress'] ?? 0;
$completed = $stats['completed'] ?? 0;

require __DIR__ . '/layouts/wrapper-start.php';
?>

<section class="dashboard-content">

    <div class="mb-4">
        <h2>Welcome, <?= htmlspecialchars(currentUser()['name']) ?> 👋</h2>
        <p class="text-muted">Here’s your work overview.</p>
    </div>

    <div class="stats-grid">

        <div class="stat-card">
            <div class="stat-icon bg-blue">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div>
                <h3>Total Tasks</h3>
                <p><?= $totalTasks ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-orange">
                <i class="fa-solid fa-clock"></i>
            </div>
            <div>
                <h3>Pending</h3>
                <p><?= $pending ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-purple">
                <i class="fa-solid fa-spinner"></i>
            </div>
            <div>
                <h3>In Progress</h3>
                <p><?= $inProgress ?></p>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon bg-green">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <h3>Completed</h3>
                <p><?= $completed ?></p>
            </div>
        </div>

    </div>

</section>

<?php require __DIR__ . '/layouts/wrapper-end.php'; ?>