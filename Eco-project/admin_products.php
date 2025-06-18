<?php
// Include configuration
require_once 'backend/config.php';

// Include admin functions
require_once 'backend/admin.php';
require_once 'backend/product.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    // Redirect to login page
    header('Location: login.php?redirect=admin_products.php');
    exit;
}

// Process product actions
$message = '';
$messageType = '';

// Delete product
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $productId = (int)$_GET['id'];
    $result = deleteProduct($productId);
    
    if ($result['success']) {
        $message = 'Product deleted successfully!';
        $messageType = 'success';
    } else {
        $message = $result['message'];
        $messageType = 'danger';
    }
}

// Get all products
$productsResult = getAllProducts();
$products = $productsResult['success'] ? $productsResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin Dashboard</title>
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
            
            <!-- Products Content -->
            <div class="container-fluid px-4">
                <h1 class="mt-4">Manage Products</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Products</li>
                </ol>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-table me-1"></i> Products</div>
                        <a href="admin_product_add.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="productsTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Image</th>
                                        <th>Name</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Stock</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr>
                                            <td><?php echo $product['id']; ?></td>
                                            <td>
                                                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="admin-product-thumbnail">
                                            </td>
                                            <td><?php echo $product['name']; ?></td>
                                            <td><?php echo $product['category']; ?></td>
                                            <td><?php echo formatCurrency($product['price']); ?></td>
                                            <td>
                                                <?php if ($product['stock'] == 0): ?>
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                <?php elseif ($product['stock'] < 10): ?>
                                                    <span class="badge bg-warning"><?php echo $product['stock']; ?></span>
                                                <?php else: ?>
                                                    <span class="badge bg-success"><?php echo $product['stock']; ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="admin_product_edit.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="admin_products.php?action=delete&id=<?php echo $product['id']; ?>" class="btn btn-sm btn-danger delete-product" data-product-name="<?php echo $product['name']; ?>">
                                                    <i class="fas fa-trash"></i>
                                                </a>
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
            $('#productsTable').DataTable({
                order: [[0, 'desc']]
            });
            
            // Confirm delete
            $('.delete-product').on('click', function(e) {
                e.preventDefault();
                const productName = $(this).data('product-name');
                const deleteUrl = $(this).attr('href');
                
                if (confirm(`Are you sure you want to delete "${productName}"? This action cannot be undone.`)) {
                    window.location.href = deleteUrl;
                }
            });
        });
    </script>
</body>
</html>
