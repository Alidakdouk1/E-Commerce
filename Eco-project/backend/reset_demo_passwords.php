<?php
// Script to reset demo account passwords
// This ensures the demo accounts work with any PHP version

// Include configuration
require_once 'config.php';

// Define the demo accounts and their passwords
$demoAccounts = [
    ['email' => 'admin@example.com', 'password' => 'password123'],
    ['email' => 'john@example.com', 'password' => 'password123']
];

// Function to reset passwords
function resetDemoPasswords($accounts) {
    $results = [];
    
    try {
        $conn = getDbConnection();
        
        foreach ($accounts as $account) {
            // Hash the password using current PHP version's implementation
            $hashedPassword = password_hash($account['password'], PASSWORD_DEFAULT);
            
            // Update the user's password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $account['email']]);
            
            // Check if update was successful
            $results[] = [
                'email' => $account['email'],
                'success' => ($stmt->rowCount() > 0),
                'message' => ($stmt->rowCount() > 0) ? 'Password reset successfully' : 'User not found'
            ];
        }
        
        return ['success' => true, 'results' => $results];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

// Reset the demo account passwords
$result = resetDemoPasswords($demoAccounts);

// Output the results
header('Content-Type: application/json');
echo json_encode($result, JSON_PRETTY_PRINT);
