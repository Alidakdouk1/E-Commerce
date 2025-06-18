<?php
// Get cart count API
require_once 'config.php';
require_once 'cart.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Return empty cart count
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'count' => 0]);
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Get user's cart
$cartResult = getUserCart($userId);

// Return cart count
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'count' => $cartResult['success'] ? count($cartResult['data']['items']) : 0
]);
exit;
