<?php
// Admin dashboard functions
require_once 'config.php';
require_once 'user.php';
require_once 'product.php';
require_once 'cart.php';

/**
 * Get dashboard statistics
 * 
 * @return array Dashboard statistics
 */
function getDashboardStats() {
    try {
        $conn = getDbConnection();
        
        // Get total users
        $stmt = $conn->query("SELECT COUNT(*) FROM users");
        $totalUsers = $stmt->fetchColumn();
        
        // Get total products
        $stmt = $conn->query("SELECT COUNT(*) FROM products");
        $totalProducts = $stmt->fetchColumn();
        
        // Get total orders
        $stmt = $conn->query("SELECT COUNT(*) FROM orders");
        $totalOrders = $stmt->fetchColumn();
        
        // Get total revenue
        $stmt = $conn->query("SELECT SUM(total_amount) FROM orders WHERE status != 'cancelled'");
        $totalRevenue = $stmt->fetchColumn() ?: 0;
        
        // Get recent orders
        $stmt = $conn->query("
            SELECT o.id, o.total_amount, o.status, o.created_at, u.first_name, u.last_name
            FROM orders o
            JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC
            LIMIT 5
        ");
        $recentOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get low stock products
        $stmt = $conn->query("
            SELECT id, name, stock
            FROM products
            WHERE stock < 10
            ORDER BY stock ASC
            LIMIT 5
        ");
        $lowStockProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => [
                'total_users' => $totalUsers,
                'total_products' => $totalProducts,
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'recent_orders' => $recentOrders,
                'low_stock_products' => $lowStockProducts
            ]
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get sales report by date range
 * 
 * @param string $startDate Start date (YYYY-MM-DD)
 * @param string $endDate End date (YYYY-MM-DD)
 * @return array Sales report data
 */
function getSalesReport($startDate, $endDate) {
    try {
        $conn = getDbConnection();
        
        // Validate dates
        $startDateTime = new DateTime($startDate);
        $endDateTime = new DateTime($endDate);
        $endDateTime->setTime(23, 59, 59); // End of day
        
        $startDateFormatted = $startDateTime->format('Y-m-d H:i:s');
        $endDateFormatted = $endDateTime->format('Y-m-d H:i:s');
        
        // Get orders in date range
        $stmt = $conn->prepare("
            SELECT o.id, o.total_amount, o.status, o.created_at, u.first_name, u.last_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.created_at BETWEEN ? AND ?
            ORDER BY o.created_at DESC
        ");
        $stmt->execute([$startDateFormatted, $endDateFormatted]);
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate totals
        $totalOrders = count($orders);
        $totalRevenue = 0;
        $statusCounts = [
            'pending' => 0,
            'processing' => 0,
            'shipped' => 0,
            'delivered' => 0,
            'cancelled' => 0
        ];
        
        foreach ($orders as $order) {
            if ($order['status'] != 'cancelled') {
                $totalRevenue += $order['total_amount'];
            }
            
            if (isset($statusCounts[$order['status']])) {
                $statusCounts[$order['status']]++;
            }
        }
        
        // Get top selling products
        $stmt = $conn->prepare("
            SELECT p.id, p.name, SUM(oi.quantity) as total_quantity, SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.created_at BETWEEN ? AND ? AND o.status != 'cancelled'
            GROUP BY p.id
            ORDER BY total_quantity DESC
            LIMIT 10
        ");
        $stmt->execute([$startDateFormatted, $endDateFormatted]);
        
        $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => [
                'date_range' => [
                    'start' => $startDate,
                    'end' => $endDate
                ],
                'total_orders' => $totalOrders,
                'total_revenue' => $totalRevenue,
                'status_counts' => $statusCounts,
                'orders' => $orders,
                'top_products' => $topProducts
            ]
        ];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get product sales report
 * 
 * @param int $productId Optional product ID filter
 * @param string $startDate Optional start date (YYYY-MM-DD)
 * @param string $endDate Optional end date (YYYY-MM-DD)
 * @return array Product sales report data
 */
function getProductSalesReport($productId = null, $startDate = null, $endDate = null) {
    try {
        $conn = getDbConnection();
        
        $sql = "
            SELECT p.id, p.name, p.category, p.subcategory, 
                   SUM(oi.quantity) as total_quantity, 
                   SUM(oi.quantity * oi.price) as total_revenue
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            JOIN orders o ON oi.order_id = o.id
            WHERE o.status != 'cancelled'
        ";
        $params = [];
        
        // Add product filter if provided
        if ($productId !== null) {
            $sql .= " AND p.id = ?";
            $params[] = $productId;
        }
        
        // Add date range if provided
        if ($startDate !== null && $endDate !== null) {
            $startDateTime = new DateTime($startDate);
            $endDateTime = new DateTime($endDate);
            $endDateTime->setTime(23, 59, 59); // End of day
            
            $sql .= " AND o.created_at BETWEEN ? AND ?";
            $params[] = $startDateTime->format('Y-m-d H:i:s');
            $params[] = $endDateTime->format('Y-m-d H:i:s');
        }
        
        $sql .= " GROUP BY p.id ORDER BY total_quantity DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $productSales = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $productSales];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

/**
 * Get customer report
 * 
 * @return array Customer report data
 */
function getCustomerReport() {
    try {
        $conn = getDbConnection();
        
        // Get top customers by order count
        $stmt = $conn->query("
            SELECT u.id, u.first_name, u.last_name, u.email, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
            GROUP BY u.id
            ORDER BY order_count DESC
            LIMIT 10
        ");
        
        $topCustomersByOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get top customers by amount spent
        $stmt = $conn->query("
            SELECT u.id, u.first_name, u.last_name, u.email, COUNT(o.id) as order_count, SUM(o.total_amount) as total_spent
            FROM users u
            LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
            GROUP BY u.id
            ORDER BY total_spent DESC
            LIMIT 10
        ");
        
        $topCustomersBySpent = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Get new customers in last 30 days
        $stmt = $conn->query("
            SELECT id, first_name, last_name, email, created_at
            FROM users
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            ORDER BY created_at DESC
        ");
        
        $newCustomers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true,
            'data' => [
                'top_by_orders' => $topCustomersByOrders,
                'top_by_spent' => $topCustomersBySpent,
                'new_customers' => $newCustomers,
                'new_customer_count' => count($newCustomers)
            ]
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get inventory report
 * 
 * @param string $category Optional category filter
 * @return array Inventory report data
 */
function getInventoryReport($category = null) {
    try {
        $conn = getDbConnection();
        
        $sql = "
            SELECT p.id, p.name, p.category, p.subcategory, p.price, p.stock,
                   (SELECT SUM(oi.quantity) FROM order_items oi JOIN orders o ON oi.order_id = o.id WHERE oi.product_id = p.id AND o.status != 'cancelled') as total_sold
            FROM products p
        ";
        $params = [];
        
        // Add category filter if provided
        if ($category !== null) {
            $sql .= " WHERE p.category = ?";
            $params[] = $category;
        }
        
        $sql .= " ORDER BY p.stock ASC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $inventory = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate totals
        $totalProducts = count($inventory);
        $totalValue = 0;
        $outOfStock = 0;
        $lowStock = 0;
        
        foreach ($inventory as &$product) {
            // Handle NULL for products that haven't been sold
            $product['total_sold'] = $product['total_sold'] ?: 0;
            
            $totalValue += $product['price'] * $product['stock'];
            
            if ($product['stock'] == 0) {
                $outOfStock++;
            } else if ($product['stock'] < 10) {
                $lowStock++;
            }
        }
        
        return [
            'success' => true,
            'data' => [
                'inventory' => $inventory,
                'total_products' => $totalProducts,
                'total_value' => $totalValue,
                'out_of_stock' => $outOfStock,
                'low_stock' => $lowStock
            ]
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
