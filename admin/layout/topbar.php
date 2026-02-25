<header class="topbar">
    <h1><?= $pageTitle ?? 'Admin' ?></h1>

    <div class="topbar-user">
        <i class="fa-solid fa-user-circle"></i>
        <span><?= htmlspecialchars(currentUser()['name']) ?></span>
        <div class="topbar-actions">
            <div class="notification-wrapper">
                <div class="notification-bell" id="notificationBell">
                    <i class="fa-solid fa-bell"></i>
                    <span class="notification-count" id="notificationCount"></span>
                </div>

                <div class="notification-dropdown" id="notificationDropdown">
                    <div class="notification-empty">No notifications</div>
                </div>
            </div>
        </div>
    </div>

</header>