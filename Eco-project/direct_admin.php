<?php
// Direct Admin Dashboard - No Sessions Required
// This file uses cookies instead of PHP sessions for authentication

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'backend/config.php';

// Check authentication
$isAuthenticated = false;
$admin = null;
$debug_info = '';

try {
    // Check if admin_auth cookie exists
    if (isset($_COOKIE['admin_auth'])) {
        $token = $_COOKIE['admin_auth'];
        
        // Get database connection
        $conn = getDbConnection();
        
        // Find user with this token
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM users WHERE remember_token = ? AND is_admin = 1");
        $stmt->execute([$token]);
        
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            $isAuthenticated = true;
            $debug_info .= "Authentication successful using token.\n";
        } else {
            $debug_info .= "Invalid or expired token.\n";
        }
    } else {
        $debug_info .= "No authentication token found.\n";
    }
} catch (PDOException $e) {
    $debug_info .= "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    $debug_info .= "Error: " . $e->getMessage() . "\n";
}

// Redirect if not authenticated
if (!$isAuthenticated) {
    $debug_info .= "Redirecting to login page...\n";
    header('Location: direct_admin_login.php');
    exit;
}

// Get dashboard stats
$stats = [
    'total_users' => 0,
    'total_products' => 0,
    'total_orders' => 0,
    'total_revenue' => 0,
    'recent_orders' => [],
    'low_stock_products' => []
];

try {
    $conn = getDbConnection();
    
    // Get total users
    $stmt = $conn->query("SELECT COUNT(*) FROM users");
    $stats['total_users'] = $stmt->fetchColumn();
    
    // Get total products
    $stmt = $conn->query("SELECT COUNT(*) FROM products");
    $stats['total_products'] = $stmt->fetchColumn();
    
    // Get total orders
    $stmt = $conn->query("SELECT COUNT(*) FROM orders");
    $stats['total_orders'] = $stmt->fetchColumn();
    
    // Get total revenue
    $stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
    $stats['total_revenue'] = $stmt->fetchColumn() ?: 0;
    
    // Get recent orders
    $stmt = $conn->query("
        SELECT o.id, o.total_amount, o.status, o.created_at, u.first_name, u.last_name
        FROM orders o
        JOIN users u ON o.user_id = u.id
        ORDER BY o.created_at DESC
        LIMIT 5
    ");
    $stats['recent_orders'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get low stock products
    $stmt = $conn->query("
        SELECT id, name, stock
        FROM products
        WHERE stock < 10
        ORDER BY stock ASC
        LIMIT 5
    ");
    $stats['low_stock_products'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $debug_info .= "Dashboard stats loaded successfully.\n";
} catch (PDOException $e) {
    $debug_info .= "Error loading dashboard stats: " . $e->getMessage() . "\n";
}

// Helper function to format currency
function formatCurrency($amount) {
    return '$' . number_format($amount, 2);
}

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
    <style>
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            white-space: pre-wrap;
        }
        .admin-header {
            background-color: #343a40;
            color: white;
            padding: 1rem;
            margin-bottom: 2rem;
        }
        .stats-card {
            transition: transform 0.3s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Admin Header -->
    <header class="admin-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1>Adidas Store Admin</h1>
                <div>
                    <span class="me-3">Welcome, <?php echo htmlspecialchars($admin['first_name']); ?></span>
                    <a href="index.php" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-home"></i> Back to Store
                    </a>
                </div>
            </div>
        </div>
    </header>
    
    <!-- Dashboard Content -->
    <div class="container">
        <div class="alert alert-info">
            <p><strong>Direct Admin Dashboard</strong> - This page uses cookie-based authentication instead of PHP sessions.</p>
        </div>
        
        <h2 class="mb-4">Dashboard</h2>
        
        <!-- Stats Cards -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2 stats-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Users</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_users']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2 stats-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Products</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_products']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-box fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2 stats-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Orders</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $stats['total_orders']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2 stats-card">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Total Revenue</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo formatCurrency($stats['total_revenue']); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Recent Orders and Low Stock Products -->
        <div class="row">
            <!-- Recent Orders -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h6 class="m-0 font-weight-bold">Recent Orders</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($stats['recent_orders'])): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($stats['recent_orders'] as $order): ?>
                                            <tr>
                                                <td>#<?php echo $order['id']; ?></td>
                                                <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?>">
                                                        <?php echo ucfirst($order['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No recent orders found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Low Stock Products -->
            <div class="col-xl-6 mb-4">
                <div class="card shadow">
                    <div class="card-header bg-danger text-white">
                        <h6 class="m-0 font-weight-bold">Low Stock Products</h6>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($stats['low_stock_products'])): ?>
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Product</th>
                                            <th>Stock</th>
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
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p>No low stock products found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($debug_info)): ?>
            <div class="debug-info">
                <h5>Debug Information:</h5>
                <?php echo $debug_info; ?>
                <p>PHP Version: <?php echo phpversion(); ?></p>
                <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                <p>Cookie: <?php echo isset($_COOKIE['admin_auth']) ? 'admin_auth=' . substr($_COOKIE['admin_auth'], 0, 10) . '...' : 'No admin_auth cookie'; ?></p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
