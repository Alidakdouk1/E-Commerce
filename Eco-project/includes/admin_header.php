<?php
// Admin header
?>
<div class="admin-header">
    <div class="admin-header-content">
        <button class="btn sidebar-toggle" id="sidebarToggle">
            <i class="fas fa-bars"></i>
        </button>
        <div class="admin-header-right">
            <div class="dropdown">
                <button class="btn dropdown-toggle" type="button" id="adminUserDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                    <?php echo $_SESSION['user_first_name'] . ' ' . $_SESSION['user_last_name']; ?>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="adminUserDropdown">
                    <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="backend/logout.php">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</div>
