<?php
// Include configuration
require_once 'backend/config.php';

// Include admin functions
require_once 'backend/admin.php';
require_once 'backend/cart.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    // Redirect to login page
    header('Location: login.php?redirect=admin_orders.php');
    exit;
}

// Process order actions
$message = '';
$messageType = '';

// Update order status
if (isset($_GET['action']) && $_GET['action'] == 'update_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $orderId = (int)$_GET['id'];
    $status = sanitize($_GET['status']);
    
    $result = updateOrderStatus($orderId, $status);
    
    if ($result['success']) {
        $message = 'Order status updated successfully!';
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// Get all orders
$ordersResult = getAllOrders();
$orders = $ordersResult['success'] ? $ordersResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Admin Header -->
            <?php include 'includes/admin_header.php'; ?>
            
            <!-- Orders Content -->
            <div class="container-fluid px-4">
                <h1 class="mt-4">Manage Orders</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Orders</li>
                </ol>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-table me-1"></i> Orders
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="ordersTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Customer</th>
                                        <th>Email</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id']; ?></td>
                                            <td><?php echo $order['first_name'] . ' ' . $order['last_name']; ?></td>
                                            <td><?php echo $order['email']; ?></td>
                                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?>">
                                                    <?php echo ucfirst($order['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton<?php echo $order['id']; ?>" data-bs-toggle="dropdown" aria-expanded="false">
                                                        Actions
                                                    </button>
                                                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton<?php echo $order['id']; ?>">
                                                        <li><a class="dropdown-item" href="admin_order_detail.php?id=<?php echo $order['id']; ?>">View Details</a></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><h6 class="dropdown-header">Update Status</h6></li>
                                                        <li><a class="dropdown-item" href="admin_orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=pending">Pending</a></li>
                                                        <li><a class="dropdown-item" href="admin_orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=processing">Processing</a></li>
                                                        <li><a class="dropdown-item" href="admin_orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=shipped">Shipped</a></li>
                                                        <li><a class="dropdown-item" href="admin_orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=delivered">Delivered</a></li>
                                                        <li><a class="dropdown-item" href="admin_orders.php?action=update_status&id=<?php echo $order['id']; ?>&status=cancelled">Cancelled</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#ordersTable').DataTable({
                order: [[0, 'desc']]
            });
        });
    </script>
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
