<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

requireEmployee();
?>

<h1>Employee Dashboard</h1>
<p>Hello, <?= htmlspecialchars(currentUser()['name']) ?></p>