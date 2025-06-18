<?php
// Include configuration
require_once 'backend/config.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    // Redirect to login page
    header('Location: login.php?redirect=admin.php');
    exit;
}

// Include admin functions
require_once 'backend/admin.php';

// Get dashboard stats
$statsResult = getDashboardStats();
$stats = $statsResult['success'] ? $statsResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Adidas Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Admin Header -->
            <?php include 'includes/admin_header.php'; ?>
            
            <!-- Dashboard Content -->
            <div class="container-fluid px-4">
                <h1 class="mt-4">Dashboard</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
                
                <!-- Stats Cards -->
                <div class="row">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0"><?php echo isset($stats['total_users']) ? $stats['total_users'] : 0; ?></h4>
                                        <div>Total Users</div>
                                    </div>
                                    <div>
                                        <i class="fas fa-users fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="admin_users.php">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0"><?php echo isset($stats['total_products']) ? $stats['total_products'] : 0; ?></h4>
                                        <div>Total Products</div>
                                    </div>
                                    <div>
                                        <i class="fas fa-box fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="admin_products.php">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0"><?php echo isset($stats['total_orders']) ? $stats['total_orders'] : 0; ?></h4>
                                        <div>Total Orders</div>
                                    </div>
                                    <div>
                                        <i class="fas fa-shopping-cart fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="admin_orders.php">View Details</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-danger text-white mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h4 class="mb-0"><?php echo isset($stats['total_revenue']) ? formatCurrency($stats['total_revenue']) : '$0.00'; ?></h4>
                                        <div>Total Revenue</div>
                                    </div>
                                    <div>
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer d-flex align-items-center justify-content-between">
                                <a class="small text-white stretched-link" href="admin_reports.php">View Reports</a>
                                <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Orders and Low Stock Products -->
                <div class="row">
                    <!-- Recent Orders -->
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-table me-1"></i>
                                Recent Orders
                            </div>
                            <div class="card-body">
                                <?php if (isset($stats['recent_orders']) && !empty($stats['recent_orders'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Order ID</th>
                                                    <th>Customer</th>
                                                    <th>Amount</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($stats['recent_orders'] as $order): ?>
                                                    <tr>
                                                        <td><a href="admin_order_detail.php?id=<?php echo $order['id']; ?>">#<?php echo $order['id']; ?></a></td>
                                                        <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                                                        <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                                        <td>
                                                            <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?>">
                                                                <?php echo ucfirst($order['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>No recent orders found.</p>
                                <?php endif; ?>
                                <div class="text-end mt-3">
                                    <a href="admin_orders.php" class="btn btn-sm btn-primary">View All Orders</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Low Stock Products -->
                    <div class="col-xl-6">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Low Stock Products
                            </div>
                            <div class="card-body">
                                <?php if (isset($stats['low_stock_products']) && !empty($stats['low_stock_products'])): ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Product ID</th>
                                                    <th>Name</th>
                                                    <th>Stock</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($stats['low_stock_products'] as $product): ?>
                                                    <tr>
                                                        <td><?php echo $product['id']; ?></td>
                                                        <td><?php echo $product['name']; ?></td>
                                                        <td>
                                                            <?php if ($product['stock'] == 0): ?>
                                                                <span class="badge bg-danger">Out of Stock</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-warning"><?php echo $product['stock']; ?> left</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <a href="admin_product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">Update Stock</a>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php else: ?>
                                    <p>No low stock products found.</p>
                                <?php endif; ?>
                                <div class="text-end mt-3">
                                    <a href="admin_products.php" class="btn btn-sm btn-primary">View All Products</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/admin.js"></script>
</body>
</html>

<?php
// Helper function to get badge class based on order status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
