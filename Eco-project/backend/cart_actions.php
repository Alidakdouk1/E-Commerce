<?php
// Backend API for cart actions
require_once 'config.php';
require_once 'cart.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Check if action is set
if (!isset($_POST['action'])) {
    // Return error response
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

// Process action
$action = $_POST['action'];
$response = [];

switch ($action) {
    case 'add':
        // Add item to cart
        $productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        $result = addToCart($userId, $productId, $quantity);
        $response = $result;
        break;
        
    case 'update':
        // Update cart item quantity
        $cartItemId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
        $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
        
        $result = updateCartItemQuantity($userId, $cartItemId, $quantity);
        $response = $result;
        break;
        
    case 'remove':
        // Remove item from cart
        $cartItemId = isset($_POST['cart_id']) ? (int)$_POST['cart_id'] : 0;
        
        $result = removeFromCart($userId, $cartItemId);
        $response = $result;
        break;
        
    case 'clear':
        // Clear cart
        $result = clearCart($userId);
        $response = $result;
        break;
        
    default:
        // Invalid action
        $response = ['success' => false, 'message' => 'Invalid action'];
        break;
}

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;
