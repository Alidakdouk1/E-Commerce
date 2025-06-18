<?php
// Enhanced Emergency Admin Panel - Standalone File
// This file works independently of the rest of the system

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection details - make sure these match your config.php
$db_host = 'localhost';
$db_name = 'adidas_store';
$db_user = 'root'; // Change if different
$db_pass = ''; // Change if different

// Initialize variables
$message = '';
$messageType = '';
$action = isset($_GET['action']) ? $_GET['action'] : 'dashboard';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

try {
    // Connect to database directly
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Check if admin exists
    $stmt = $conn->query("SELECT * FROM users WHERE email = 'admin@example.com' AND is_admin = 1");
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$admin) {
        echo "<p>Admin user not found in database.</p>";
        exit;
    }
    
    // Process form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Product actions
        if (isset($_POST['product_action'])) {
            switch ($_POST['product_action']) {
                case 'update':
                    $productId = intval($_POST['product_id']);
                    $name = $_POST['name'];
                    $description = $_POST['description'];
                    $price = floatval($_POST['price']);
                    $category = $_POST['category'];
                    $subcategory = $_POST['subcategory'];
                    $stock = intval($_POST['stock']);
                    
                    $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category = ?, subcategory = ?, stock = ? WHERE id = ?");
                    $stmt->execute([$name, $description, $price, $category, $subcategory, $stock, $productId]);
                    
                    $message = "Product updated successfully.";
                    $messageType = "success";
                    $action = "products";
                    break;
                    
                case 'add':
                    $name = $_POST['name'];
                    $description = $_POST['description'];
                    $price = floatval($_POST['price']);
                    $category = $_POST['category'];
                    $subcategory = $_POST['subcategory'];
                    $stock = intval($_POST['stock']);
                    $image = $_POST['image'];
                    
                    $stmt = $conn->prepare("INSERT INTO products (name, description, price, category, subcategory, image, stock, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->execute([$name, $description, $price, $category, $subcategory, $image, $stock]);
                    
                    $message = "Product added successfully.";
                    $messageType = "success";
                    $action = "products";
                    break;
                    
                case 'delete':
                    $productId = intval($_POST['product_id']);
                    
                    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
                    $stmt->execute([$productId]);
                    
                    $message = "Product deleted successfully.";
                    $messageType = "success";
                    $action = "products";
                    break;
            }
        }
        
        // User actions
        if (isset($_POST['user_action'])) {
            switch ($_POST['user_action']) {
                case 'update':
                    $userId = intval($_POST['user_id']);
                    $firstName = $_POST['first_name'];
                    $lastName = $_POST['last_name'];
                    $email = $_POST['email'];
                    $phone = $_POST['phone'];
                    $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
                    
                    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, is_admin = ? WHERE id = ?");
                    $stmt->execute([$firstName, $lastName, $email, $phone, $isAdmin, $userId]);
                    
                    $message = "User updated successfully.";
                    $messageType = "success";
                    $action = "users";
                    break;
                    
                case 'reset_password':
                    $userId = intval($_POST['user_id']);
                    $newPassword = $_POST['new_password'];
                    
                    // Hash password
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([$hashedPassword, $userId]);
                    
                    $message = "Password reset successfully.";
                    $messageType = "success";
                    $action = "users";
                    break;
                    
                case 'delete':
                    $userId = intval($_POST['user_id']);
                    
                    // Don't allow deleting the admin user
                    if ($userId == $admin['id']) {
                        $message = "Cannot delete admin user.";
                        $messageType = "danger";
                    } else {
                        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                        $stmt->execute([$userId]);
                        
                        $message = "User deleted successfully.";
                        $messageType = "success";
                    }
                    $action = "users";
                    break;
            }
        }
        
        // Order actions
        if (isset($_POST['order_action'])) {
            switch ($_POST['order_action']) {
                case 'update_status':
                    $orderId = intval($_POST['order_id']);
                    $status = $_POST['status'];
                    
                    $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
                    $stmt->execute([$status, $orderId]);
                    
                    $message = "Order status updated successfully.";
                    $messageType = "success";
                    $action = "orders";
                    break;
            }
        }
    }
    
    // HTML header and styles
    echo '<!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Enhanced Admin Panel - Adidas Store</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                margin: 0;
                padding: 20px;
                color: #333;
            }
            h1, h2, h3 {
                color: #000;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
            }
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #ddd;
            }
            .nav {
                background: #f4f4f4;
                padding: 10px;
                margin-bottom: 20px;
            }
            .nav a {
                margin-right: 15px;
                text-decoration: none;
                color: #333;
                font-weight: bold;
            }
            .nav a:hover {
                color: #000;
                text-decoration: underline;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            table, th, td {
                border: 1px solid #ddd;
            }
            th, td {
                padding: 10px;
                text-align: left;
            }
            th {
                background-color: #f4f4f4;
            }
            tr:nth-child(even) {
                background-color: #f9f9f9;
            }
            .btn {
                display: inline-block;
                padding: 8px 12px;
                background: #333;
                color: white;
                border: none;
                cursor: pointer;
                text-decoration: none;
                font-size: 14px;
                border-radius: 4px;
            }
            .btn:hover {
                background: #555;
            }
            .btn-danger {
                background: #dc3545;
            }
            .btn-danger:hover {
                background: #c82333;
            }
            .btn-success {
                background: #28a745;
            }
            .btn-success:hover {
                background: #218838;
            }
            .alert {
                padding: 15px;
                margin-bottom: 20px;
                border: 1px solid transparent;
                border-radius: 4px;
            }
            .alert-success {
                color: #155724;
                background-color: #d4edda;
                border-color: #c3e6cb;
            }
            .alert-danger {
                color: #721c24;
                background-color: #f8d7da;
                border-color: #f5c6cb;
            }
            form {
                background: #f9f9f9;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 4px;
            }
            .form-group {
                margin-bottom: 15px;
            }
            label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            input[type="text"],
            input[type="email"],
            input[type="number"],
            input[type="password"],
            textarea,
            select {
                width: 100%;
                padding: 8px;
                border: 1px solid #ddd;
                border-radius: 4px;
                box-sizing: border-box;
            }
            textarea {
                height: 100px;
            }
            .actions {
                white-space: nowrap;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Enhanced Admin Panel</h1>
                <div>
                    <p>Admin user: ' . htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) . '</p>
                </div>
            </div>
            
            <div class="nav">
                <a href="?action=dashboard">Dashboard</a>
                <a href="?action=products">Products</a>
                <a href="?action=users">Users</a>
                <a href="?action=orders">Orders</a>
                <a href="?action=add_product">Add New Product</a>
            </div>';
    
    // Display messages
    if (!empty($message)) {
        echo '<div class="alert alert-' . $messageType . '">' . $message . '</div>';
    }
    
    // Dashboard
    if ($action === 'dashboard') {
        // Get stats
        $userCount = $conn->query("SELECT COUNT(*) FROM users")->fetchColumn();
        $productCount = $conn->query("SELECT COUNT(*) FROM products")->fetchColumn();
        $orderCount = $conn->query("SELECT COUNT(*) FROM orders")->fetchColumn();
        $revenue = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'")->fetchColumn() ?: 0;
        
        echo '<h2>Dashboard</h2>
        <div style="display: flex; justify-content: space-between; margin-bottom: 20px;">
            <div style="flex: 1; background: #f4f4f4; padding: 20px; margin-right: 10px; text-align: center;">
                <h3>' . $userCount . '</h3>
                <p>Total Users</p>
            </div>
            <div style="flex: 1; background: #f4f4f4; padding: 20px; margin-right: 10px; text-align: center;">
                <h3>' . $productCount . '</h3>
                <p>Total Products</p>
            </div>
            <div style="flex: 1; background: #f4f4f4; padding: 20px; margin-right: 10px; text-align: center;">
                <h3>' . $orderCount . '</h3>
                <p>Total Orders</p>
            </div>
            <div style="flex: 1; background: #f4f4f4; padding: 20px; text-align: center;">
                <h3>$' . number_format($revenue, 2) . '</h3>
                <p>Total Revenue</p>
            </div>
        </div>';
        
        // Recent orders
        $stmt = $conn->query("
            SELECT o.id, o.total_amount, o.status, o.created_at, u.first_name, u.last_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<h3>Recent Orders</h3>';
        if (count($recentOrders) > 0) {
            echo '<table>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>';
            
            foreach ($recentOrders as $order) {
                echo '<tr>
                    <td>#' . $order['id'] . '</td>
                    <td>' . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . '</td>
                    <td>$' . number_format($order['total_amount'], 2) . '</td>
                    <td>' . ucfirst($order['status']) . '</td>
                    <td>' . date('M j, Y', strtotime($order['created_at'])) . '</td>
                    <td><a href="?action=view_order&id=' . $order['id'] . '" class="btn">View</a></td>
                </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No recent orders found.</p>';
        }
        
        // Low stock products
        $stmt = $conn->query("
            SELECT id, name, stock
            FROM products
            WHERE stock < 10
            ORDER BY stock ASC
            LIMIT 5
        ");
        $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo '<h3>Low Stock Products</h3>';
        if (count($lowStockProducts) > 0) {
            echo '<table>
                <tr>
                    <th>ID</th>
                    <th>Product</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>';
            
            foreach ($lowStockProducts as $product) {
                echo '<tr>
                    <td>' . $product['id'] . '</td>
                    <td>' . htmlspecialchars($product['name']) . '</td>
                    <td>' . ($product['stock'] == 0 ? '<span style="color: red;">Out of Stock</span>' : $product['stock']) . '</td>
                    <td><a href="?action=edit_product&id=' . $product['id'] . '" class="btn">Edit</a></td>
                </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No low stock products found.</p>';
        }
    }
    
    // Products list
    elseif ($action === 'products') {
        echo '<h2>Products</h2>';
        
        // Get products
        $stmt = $conn->query("SELECT * FROM products ORDER BY id DESC");
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($products) > 0) {
            echo '<table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>';
            
            foreach ($products as $product) {
                echo '<tr>
                    <td>' . $product['id'] . '</td>
                    <td>' . htmlspecialchars($product['name']) . '</td>
                    <td>' . htmlspecialchars($product['category']) . ' / ' . htmlspecialchars($product['subcategory']) . '</td>
                    <td>$' . number_format($product['price'], 2) . '</td>
                    <td>' . $product['stock'] . '</td>
                    <td class="actions">
                        <a href="?action=edit_product&id=' . $product['id'] . '" class="btn">Edit</a>
                        <form method="post" style="display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this product?\');">
                            <input type="hidden" name="product_action" value="delete">
                            <input type="hidden" name="product_id" value="' . $product['id'] . '">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </td>
                </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No products found.</p>';
        }
        
        echo '<p><a href="?action=add_product" class="btn btn-success">Add New Product</a></p>';
    }
    
    // Edit product
    elseif ($action === 'edit_product' && $id > 0) {
        // Get product
        $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            echo '<h2>Edit Product</h2>
            <form method="post">
                <input type="hidden" name="product_action" value="update">
                <input type="hidden" name="product_id" value="' . $product['id'] . '">
                
                <div class="form-group">
                    <label for="name">Product Name</label>
                    <input type="text" id="name" name="name" value="' . htmlspecialchars($product['name']) . '" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description">' . htmlspecialchars($product['description']) . '</textarea>
                </div>
                
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" value="' . $product['price'] . '" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <input type="text" id="category" name="category" value="' . htmlspecialchars($product['category']) . '" required>
                </div>
                
                <div class="form-group">
                    <label for="subcategory">Subcategory</label>
                    <input type="text" id="subcategory" name="subcategory" value="' . htmlspecialchars($product['subcategory']) . '">
                </div>
                
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" value="' . $product['stock'] . '" required>
                </div>
                
                <button type="submit" class="btn btn-success">Update Product</button>
                <a href="?action=products" class="btn">Cancel</a>
            </form>';
        } else {
            echo '<p>Product not found.</p>';
            echo '<p><a href="?action=products" class="btn">Back to Products</a></p>';
        }
    }
    
    // Add product
    elseif ($action === 'add_product') {
        echo '<h2>Add New Product</h2>
        <form method="post">
            <input type="hidden" name="product_action" value="add">
            
            <div class="form-group">
                <label for="name">Product Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description"></textarea>
            </div>
            
            <div class="form-group">
                <label for="price">Price</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>
            
            <div class="form-group">
                <label for="category">Category</label>
                <input type="text" id="category" name="category" required>
            </div>
            
            <div class="form-group">
                <label for="subcategory">Subcategory</label>
                <input type="text" id="subcategory" name="subcategory">
            </div>
            
            <div class="form-group">
                <label for="image">Image Path</label>
                <input type="text" id="image" name="image" placeholder="images/products/example.jpg">
            </div>
            
            <div class="form-group">
                <label for="stock">Stock</label>
                <input type="number" id="stock" name="stock" value="0" required>
            </div>
            
            <button type="submit" class="btn btn-success">Add Product</button>
            <a href="?action=products" class="btn">Cancel</a>
        </form>';
    }
    
    // Users list
    elseif ($action === 'users') {
        echo '<h2>Users</h2>';
        
        // Get users
        $stmt = $conn->query("SELECT * FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($users) > 0) {
            echo '<table>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Admin</th>
                    <th>Actions</th>
                </tr>';
            
            foreach ($users as $user) {
                echo '<tr>
                    <td>' . $user['id'] . '</td>
                    <td>' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</td>
                    <td>' . htmlspecialchars($user['email']) . '</td>
                    <td>' . htmlspecialchars($user['phone']) . '</td>
                    <td>' . ($user['is_admin'] ? 'Yes' : 'No') . '</td>
                    <td class="actions">
                        <a href="?action=edit_user&id=' . $user['id'] . '" class="btn">Edit</a>
                        <a href="?action=reset_user_password&id=' . $user['id'] . '" class="btn">Reset Password</a>';
                        
                if ($user['id'] != $admin['id']) {
                    echo '<form method="post" style="display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this user?\');">
                            <input type="hidden" name="user_action" value="delete">
                            <input type="hidden" name="user_id" value="' . $user['id'] . '">
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>';
                }
                        
                echo '</td>
                </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No users found.</p>';
        }
    }
    
    // Edit user
    elseif ($action === 'edit_user' && $id > 0) {
        // Get user
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo '<h2>Edit User</h2>
            <form method="post">
                <input type="hidden" name="user_action" value="update">
                <input type="hidden" name="user_id" value="' . $user['id'] . '">
                
                <div class="form-group">
                    <label for="first_name">First Name</label>
                    <input type="text" id="first_name" name="first_name" value="' . htmlspecialchars($user['first_name']) . '" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name</label>
                    <input type="text" id="last_name" name="last_name" value="' . htmlspecialchars($user['last_name']) . '" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="' . htmlspecialchars($user['email']) . '" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="text" id="phone" name="phone" value="' . htmlspecialchars($user['phone']) . '">
                </div>
                
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="is_admin" ' . ($user['is_admin'] ? 'checked' : '') . '>
                        Admin User
                    </label>
                </div>
                
                <button type="submit" class="btn btn-success">Update User</button>
                <a href="?action=users" class="btn">Cancel</a>
            </form>';
        } else {
            echo '<p>User not found.</p>';
            echo '<p><a href="?action=users" class="btn">Back to Users</a></p>';
        }
    }
    
    // Reset user password
    elseif ($action === 'reset_user_password' && $id > 0) {
        // Get user
        $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo '<h2>Reset Password for ' . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . '</h2>
            <form method="post">
                <input type="hidden" name="user_action" value="reset_password">
                <input type="hidden" name="user_id" value="' . $user['id'] . '">
                
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>
                
                <button type="submit" class="btn btn-success">Reset Password</button>
                <a href="?action=users" class="btn">Cancel</a>
            </form>';
        } else {
            echo '<p>User not found.</p>';
            echo '<p><a href="?action=users" class="btn">Back to Users</a></p>';
        }
    }
    
    // Orders list
    elseif ($action === 'orders') {
        echo '<h2>Orders</h2>';
        
        // Get orders
        $stmt = $conn->query("
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
        ");
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($orders) > 0) {
            echo '<table>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>';
            
            foreach ($orders as $order) {
                echo '<tr>
                    <td>#' . $order['id'] . '</td>
                    <td>' . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . '</td>
                    <td>$' . number_format($order['total_amount'], 2) . '</td>
                    <td>' . ucfirst($order['status']) . '</td>
                    <td>' . date('M j, Y', strtotime($order['created_at'])) . '</td>
                    <td><a href="?action=view_order&id=' . $order['id'] . '" class="btn">View</a></td>
                </tr>';
            }
            
            echo '</table>';
        } else {
            echo '<p>No orders found.</p>';
        }
    }
    
    // View order
    elseif ($action === 'view_order' && $id > 0) {
        // Get order
        $stmt = $conn->prepare("
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ");
        $stmt->execute([$id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($order) {
            // Get order items
            $stmt = $conn->prepare("
                SELECT oi.*, p.name as product_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$id]);
            $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo '<h2>Order #' . $order['id'] . '</h2>
            
            <div style="display: flex; margin-bottom: 20px;">
                <div style="flex: 1; margin-right: 20px;">
                    <h3>Order Details</h3>
                    <p><strong>Customer:</strong> ' . htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($order['email']) . '</p>
                    <p><strong>Total Amount:</strong> $' . number_format($order['total_amount'], 2) . '</p>
                    <p><strong>Date:</strong> ' . date('M j, Y', strtotime($order['created_at'])) . '</p>
                    
                    <form method="post">
                        <input type="hidden" name="order_action" value="update_status">
                        <input type="hidden" name="order_id" value="' . $order['id'] . '">
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="pending" ' . ($order['status'] == 'pending' ? 'selected' : '') . '>Pending</option>
                                <option value="processing" ' . ($order['status'] == 'processing' ? 'selected' : '') . '>Processing</option>
                                <option value="shipped" ' . ($order['status'] == 'shipped' ? 'selected' : '') . '>Shipped</option>
                                <option value="delivered" ' . ($order['status'] == 'delivered' ? 'selected' : '') . '>Delivered</option>
                                <option value="cancelled" ' . ($order['status'] == 'cancelled' ? 'selected' : '') . '>Cancelled</option>
                            </select>
                        </div>
                        
                        <button type="submit" class="btn btn-success">Update Status</button>
                    </form>
                </div>
                
                <div style="flex: 1;">
                    <h3>Shipping Address</h3>
                    <p>' . htmlspecialchars($order['shipping_address']) . '</p>
                    <p>' . htmlspecialchars($order['shipping_city']) . ', ' . htmlspecialchars($order['shipping_state']) . ' ' . htmlspecialchars($order['shipping_zip']) . '</p>
                    
                    <h3>Payment Method</h3>
                    <p>' . htmlspecialchars($order['payment_method']) . '</p>
                </div>
            </div>
            
            <h3>Order Items</h3>';
            
            if (count($orderItems) > 0) {
                echo '<table>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>';
                
                foreach ($orderItems as $item) {
                    echo '<tr>
                        <td>' . htmlspecialchars($item['product_name']) . '</td>
                        <td>' . $item['quantity'] . '</td>
                        <td>$' . number_format($item['price'], 2) . '</td>
                        <td>$' . number_format($item['quantity'] * $item['price'], 2) . '</td>
                    </tr>';
                }
                
                echo '</table>';
            } else {
                echo '<p>No items found for this order.</p>';
            }
            
            echo '<p><a href="?action=orders" class="btn">Back to Orders</a></p>';
        } else {
            echo '<p>Order not found.</p>';
            echo '<p><a href="?action=orders" class="btn">Back to Orders</a></p>';
        }
    }
    
    echo '</div>
    </body>
    </html>';
    
} catch (PDOException $e) {
    echo '<h1>Database Error</h1>';
    echo '<p>Error: ' . $e->getMessage() . '</p>';
    echo '<p>Make sure your database connection details are correct.</p>';
    
    echo '<h2>Database Connection Details</h2>';
    echo '<p>Host: ' . $db_host . '</p>';
    echo '<p>Database: ' . $db_name . '</p>';
    echo '<p>Username: ' . $db_user . '</p>';
    echo '<p>Password: ' . str_repeat('*', strlen($db_pass)) . '</p>';
}
?>
