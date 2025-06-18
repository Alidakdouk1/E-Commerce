<?php
// User authentication and management functions
require_once 'config.php';

/**
 * Register a new user
 * 
 * @param string $firstName First name
 * @param string $lastName Last name
 * @param string $email Email address
 * @param string $phone Phone number
 * @param string $password Password
 * @return array Result with success status and message
 */
function registerUser($firstName, $lastName, $email, $phone, $password) {
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($email) || empty($phone) || empty($password)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'message' => 'Invalid email format'];
    }
    
    if (strlen($password) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Email already registered'];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$firstName, $lastName, $email, $phone, $hashedPassword]);
        
        return ['success' => true, 'message' => 'Registration successful'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Login a user
 * 
 * @param string $email Email address
 * @param string $password Password
 * @return array Result with success status and message
 */
function loginUser($email, $password) {
    // Validate input
    if (empty($email) || empty($password)) {
        return ['success' => false, 'message' => 'Email and password are required'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Get user by email
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify password
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid email or password'];
        }
        
        // Set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['user_first_name'] = $user['first_name']; // Add first_name for header.php
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['is_admin'] = $user['is_admin'];
        
        return [
            'success' => true, 
            'message' => 'Login successful',
            'is_admin' => $user['is_admin'] == 1
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Logout the current user
 */
function logoutUser() {
    // Unset all session variables
    $_SESSION = [];
    
    // Delete the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

/**
 * Get user profile by ID
 * 
 * @param int $userId User ID
 * @return array User data
 */
function getUserProfile($userId) {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, phone, is_admin, created_at FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $user];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Update user profile
 * 
 * @param int $userId User ID
 * @param string $firstName First name
 * @param string $lastName Last name
 * @param string $phone Phone number
 * @return array Result with success status and message
 */
function updateUserProfile($userId, $firstName, $lastName, $phone) {
    // Validate input
    if (empty($firstName) || empty($lastName) || empty($phone)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, phone = ? WHERE id = ?");
        $stmt->execute([$firstName, $lastName, $phone, $userId]);
        
        // Update session variable
        $_SESSION['user_name'] = $firstName . ' ' . $lastName;
        
        return ['success' => true, 'message' => 'Profile updated successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Change user password
 * 
 * @param int $userId User ID
 * @param string $currentPassword Current password
 * @param string $newPassword New password
 * @return array Result with success status and message
 */
function changePassword($userId, $currentPassword, $newPassword) {
    // Validate input
    if (empty($currentPassword) || empty($newPassword)) {
        return ['success' => false, 'message' => 'All fields are required'];
    }
    
    if (strlen($newPassword) < 6) {
        return ['success' => false, 'message' => 'Password must be at least 6 characters'];
    }
    
    try {
        $conn = getDbConnection();
        
        // Get current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Verify current password
        if (!password_verify($currentPassword, $user['password'])) {
            return ['success' => false, 'message' => 'Current password is incorrect'];
        }
        
        // Hash new password
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Update password
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashedPassword, $userId]);
        
        return ['success' => true, 'message' => 'Password changed successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Get all users (admin function)
 * 
 * @return array List of users
 */
function getAllUsers() {
    try {
        $conn = getDbConnection();
        
        $stmt = $conn->query("SELECT id, first_name, last_name, email, phone, is_admin, created_at FROM users ORDER BY id DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return ['success' => true, 'data' => $users];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Delete a user (admin function)
 * 
 * @param int $userId User ID
 * @return array Result with success status and message
 */
function deleteUser($userId) {
    try {
        $conn = getDbConnection();
        
        // Check if user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        // Delete user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        return ['success' => true, 'message' => 'User deleted successfully'];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}

/**
 * Toggle admin status (admin function)
 * 
 * @param int $userId User ID
 * @return array Result with success status and message
 */
function toggleAdminStatus($userId) {
    try {
        $conn = getDbConnection();
        
        // Get current admin status
        $stmt = $conn->prepare("SELECT is_admin FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        
        if ($stmt->rowCount() == 0) {
            return ['success' => false, 'message' => 'User not found'];
        }
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $newStatus = $user['is_admin'] == 1 ? 0 : 1;
        
        // Update admin status
        $stmt = $conn->prepare("UPDATE users SET is_admin = ? WHERE id = ?");
        $stmt->execute([$newStatus, $userId]);
        
        return [
            'success' => true, 
            'message' => 'Admin status updated successfully',
            'is_admin' => $newStatus
        ];
    } catch (PDOException $e) {
        return ['success' => false, 'message' => 'Database error: ' . $e->getMessage()];
    }
}
?>
