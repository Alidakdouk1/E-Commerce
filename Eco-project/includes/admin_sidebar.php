<?php
// Admin sidebar navigation
?>
<div class="admin-sidebar">
    <div class="admin-sidebar-brand">
        <a href="admin.php">
            <img src="images/logos/adidas-logo.png" alt="Adidas Logo" height="30">
            <span>Admin Panel</span>
        </a>
    </div>
    <ul class="admin-sidebar-nav">
        <li class="nav-item">
            <a href="admin.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_products.php' ? 'active' : ''; ?>">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_orders.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_orders.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_users.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Users</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_reports.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reports</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="admin_settings.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'admin_settings.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </li>
        <li class="nav-divider"></li>
        <li class="nav-item">
            <a href="index.php" class="nav-link">
                <i class="fas fa-store"></i>
                <span>View Store</span>
            </a>
        </li>
        <li class="nav-item">
            <a href="backend/logout.php" class="nav-link">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </li>
    </ul>
</div>
