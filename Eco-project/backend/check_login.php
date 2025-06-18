<?php
// Check login status API
require_once 'config.php';

// Return login status as JSON
header('Content-Type: application/json');
echo json_encode([
    'logged_in' => isLoggedIn(),
    'is_admin' => isLoggedIn() ? isAdmin() : false
]);
exit;
