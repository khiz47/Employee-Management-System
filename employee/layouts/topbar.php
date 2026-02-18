<header class="topbar">
    <h1><?= $pageTitle ?? 'Employee' ?></h1>

    <div class="topbar-user">
        <i class="fa-solid fa-user-circle"></i>
        <span><?= htmlspecialchars(currentUser()['name']) ?></span>
    </div>
</header>