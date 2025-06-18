<?php
// Direct Admin Login - No Sessions Required
// This file uses cookies instead of PHP sessions for authentication

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once 'backend/config.php';

// Message variables
$message = '';
$success = false;
$debug_info = '';

// Process login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get database connection
        $conn = getDbConnection();
        
        // Check if admin user exists
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM users WHERE email = 'admin@example.com' AND is_admin = 1");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Generate a secure token
            $token = bin2hex(random_bytes(32));
            
            // Store token in database
            $stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
            $stmt->execute([$token, $admin['id']]);
            
            // Set cookie that expires in 1 day
            setcookie('admin_auth', $token, time() + 86400, '/');
            
            $success = true;
            $message = 'Admin authentication successful. You can now access the admin area.';
            $debug_info .= "Token generated and stored in database.\n";
            $debug_info .= "Cookie set: admin_auth = " . substr($token, 0, 10) . "...\n";
        } else {
            $message = 'Admin user not found in database. Please check your database setup.';
            $debug_info .= "Admin user not found in database.\n";
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
        $debug_info .= "Database error: " . $e->getMessage() . "\n";
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $debug_info .= "Error: " . $e->getMessage() . "\n";
    }
}

// Check if we need to add the remember_token column
try {
    $conn = getDbConnection();
    
    // Check if remember_token column exists
    $stmt = $conn->prepare("SHOW COLUMNS FROM users LIKE 'remember_token'");
    $stmt->execute();
    
    if ($stmt->rowCount() == 0) {
        // Add remember_token column
        $stmt = $conn->prepare("ALTER TABLE users ADD COLUMN remember_token VARCHAR(255) DEFAULT NULL");
        $stmt->execute();
        $debug_info .= "Added remember_token column to users table.\n";
    } else {
        $debug_info .= "remember_token column already exists.\n";
    }
} catch (PDOException $e) {
    $debug_info .= "Error checking/adding remember_token column: " . $e->getMessage() . "\n";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Direct Admin Login - Adidas Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <!-- Direct Admin Login Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h1 class="card-title text-center mb-4">Direct Admin Login</h1>
                            
                            <div class="alert alert-info">
                                <p><strong>This is a special login page that doesn't use PHP sessions.</strong></p>
                                <p>It uses cookies instead, which should work even if there are session configuration issues.</p>
                            </div>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>">
                                    <?php echo $message; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" action="direct_admin_login.php">
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-dark btn-lg">Login as Admin</button>
                                </div>
                            </form>
                            
                            <?php if ($success): ?>
                                <div class="text-center mt-4">
                                    <a href="direct_admin.php" class="btn btn-primary">Go to Admin Dashboard</a>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-4">
                                <h5>How this works:</h5>
                                <ol>
                                    <li>Click the "Login as Admin" button</li>
                                    <li>This will create a secure token and store it in your database</li>
                                    <li>A cookie will be set in your browser with this token</li>
                                    <li>You can then access the admin area without using PHP sessions</li>
                                </ol>
                            </div>
                            
                            <?php if (!empty($debug_info)): ?>
                                <div class="debug-info mt-4">
                                    <h5>Debug Information:</h5>
                                    <?php echo $debug_info; ?>
                                    <p>PHP Version: <?php echo phpversion(); ?></p>
                                    <p>Server: <?php echo $_SERVER['SERVER_SOFTWARE']; ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
