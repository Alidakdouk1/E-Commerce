<?php
// Cart and order management functions
require_once 'config.php';

/**
 * Add item to cart
 * 
 * @param int $userId User ID
 * @param int $productId Product ID
 * @param int $quantity Quantity
 * @return array Result with success status and message
 */
function addToCart($userId, $productId, $quantity = 1) {
    // Validate input
    if (empty($productId) || $quantity <= 0) {
        return ['success' => false, 'message' => 'Invalid product or quantity'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Check if product exists and has enough stock
        $stmt = $conn->prepare("SELECT id, stock FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product['stock'] < $quantity) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }
        
        // Check if item already in cart
        $stmt = $conn->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$userId, $productId]);
        
        if ($stmt->rowCount() > 0) {
            // Update quantity
            $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
            $newQuantity = $cartItem['quantity'] + $quantity;
            
            // Check if new quantity exceeds stock
            if ($newQuantity > $product['stock']) {
                return ['success' => false, 'message' => 'Not enough stock available'];
            }
            
            $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $stmt->execute([$newQuantity, $cartItem['id']]);
            
            return ['success' => true, 'message' => 'Cart updated successfully'];
        } else {
            // Add new item to cart
            $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$userId, $productId, $quantity]);
            
            return ['success' => true, 'message' => 'Item added to cart'];
        }
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update cart item quantity
 * 
 * @param int $userId User ID
 * @param int $cartItemId Cart item ID
 * @param int $quantity New quantity
 * @return array Result with success status and message
 */
function updateCartItemQuantity($userId, $cartItemId, $quantity) {
    // Validate input
    if (empty($cartItemId) || $quantity <= 0) {
        return ['success' => false, 'message' => 'Invalid cart item or quantity'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Check if cart item exists and belongs to user
        $stmt = $conn->prepare("SELECT c.id, c.product_id, p.stock FROM cart c JOIN products p ON c.product_id = p.id WHERE c.id = ? AND c.user_id = ?");
        $stmt->execute([$cartItemId, $userId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Cart item not found'];
        }
        
        $cartItem = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if quantity exceeds stock
        if ($quantity > $cartItem['stock']) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }
        
        // Update quantity
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
        $stmt->execute([$quantity, $cartItemId]);
        
        return ['success' => true, 'message' => 'Cart updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Remove item from cart
 * 
 * @param int $userId User ID
 * @param int $cartItemId Cart item ID
 * @return array Result with success status and message
 */
function removeFromCart($userId, $cartItemId) {
    try {
        $conn = getDbConnection();
        
        // Check if cart item exists and belongs to user
        $stmt = $conn->prepare("SELECT id FROM cart WHERE id = ? AND user_id = ?");
        $stmt->execute([$cartItemId, $userId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Cart item not found'];
        }
        
        // Remove item from cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ?");
        $stmt->execute([$cartItemId]);
        
        return ['success' => true, 'message' => 'Item removed from cart'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get user's cart
 * 
 * @param int $userId User ID
 * @return array Cart items with product details
 */
function getUserCart($userId) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("
            SELECT c.id, c.product_id, c.quantity, p.name, p.price, p.image, p.stock
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?
        ");
        $stmt->execute([$userId]);
        
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calculate total
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item['price'] * $item['quantity'];
        }
        
        return [
            'success' => true, 
            'data' => [
                'items' => $cartItems,
                'total' => $total,
                'item_count' => count($cartItems)
            ]
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Clear user's cart
 * 
 * @param int $userId User ID
 * @return array Result with success status and message
 */
function clearCart($userId) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        return ['success' => true, 'message' => 'Cart cleared successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Create a new order from cart
 * 
 * @param int $userId User ID
 * @param string $address Shipping address
 * @param string $city City
 * @param string $state State
 * @param string $zipCode Zip code
 * @param string $paymentMethod Payment method
 * @return array Result with success status, message and order ID
 */
function createOrder($userId, $address, $city, $state, $zipCode, $paymentMethod) {
    // Validate input
    if (empty($address) || empty($city) || empty($state) || empty($zipCode) || empty($paymentMethod)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Start transaction
        $conn->beginTransaction();
        
        // Get user's cart
        $cartResult = getUserCart($userId);
        
        if (!$cartResult['success'] || empty($cartResult['data']['items'])) {
            return ['success' => false, 'message' => 'Cart is empty'];
        }
        
        $cartItems = $cartResult['data']['items'];
        $total = $cartResult['data']['total'];
        
        // Create order
        $stmt = $conn->prepare("
            INSERT INTO orders (user_id, total_amount, shipping_address, shipping_city, shipping_state, shipping_zip, payment_method, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([$userId, $total, $address, $city, $state, $zipCode, $paymentMethod]);
        
        $orderId = $conn->lastInsertId();
        
        // Add order items
        $orderItemStmt = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, quantity, price)
            VALUES (?, ?, ?, ?)
        ");
        
        $updateStockStmt = $conn->prepare("
            UPDATE products SET stock = stock - ? WHERE id = ?
        ");
        
        foreach ($cartItems as $item) {
            // Add order item
            $orderItemStmt->execute([
                $orderId,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);
            
            // Update product stock
            $updateStockStmt->execute([
                $item['quantity'],
                $item['product_id']
            ]);
        }
        
        // Clear cart
        $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$userId]);
        
        // Commit transaction
        $conn->commit();
        
        return [
            'success' => true, 
            'message' => 'Order created successfully',
            'order_id' => $orderId
        ];
    } catch (PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get user's orders
 * 
 * @param int $userId User ID
 * @return array List of orders
 */
function getUserOrders($userId) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("
            SELECT id, total_amount, status, created_at
            FROM orders
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");
        $stmt->execute([$userId]);
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $orders];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get order details
 * 
 * @param int $orderId Order ID
 * @param int $userId User ID (for security check)
 * @return array Order details with items
 */
function getOrderDetails($orderId, $userId = null) {
    try {
        $conn = getDbConnection();
        
        // Get order
        $sql = "
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
            WHERE o.id = ?
        ";
        $params = [$orderId];
        
        // Add user check if provided (for security)
        if ($userId !== null) {
            $sql .= " AND o.user_id = ?";
            $params[] = $userId;
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Get order items
        $stmt = $conn->prepare("
            SELECT oi.*, p.name, p.image
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$orderId]);
        
        $orderItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return [
            'success' => true, 
            'data' => [
                'order' => $order,
                'items' => $orderItems
            ]
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update order status (admin function)
 * 
 * @param int $orderId Order ID
 * @param string $status New status
 * @return array Result with success status and message
 */
function updateOrderStatus($orderId, $status) {
    // Validate status
    $validStatuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        return ['success' => false, 'message' => 'Invalid status'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Check if order exists
        $stmt = $conn->prepare("SELECT id FROM orders WHERE id = ?");
        $stmt->execute([$orderId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Order not found'];
        }
        
        // Update status
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $orderId]);
        
        return ['success' => true, 'message' => 'Order status updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get all orders (admin function)
 * 
 * @param string $status Optional status filter
 * @return array List of orders
 */
function getAllOrders($status = null) {
    try {
        $conn = getDbConnection();
        
        $sql = "
            SELECT o.*, u.first_name, u.last_name, u.email
            FROM orders o
            JOIN users u ON o.user_id = u.id
        ";
        $params = [];
        
        if ($status !== null) {
            $sql .= " WHERE o.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY o.created_at DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $orders];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>
