<?php
// Admin login bypass - temporary solution
// This file directly sets admin session variables without password verification

// Include configuration
require_once 'backend/config.php';

// Message variables
$message = '';
$success = false;

// Process login bypass
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get admin user from database
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email FROM users WHERE email = 'admin@example.com'");
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Set session variables directly
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['first_name'] . ' ' . $admin['last_name'];
            $_SESSION['user_first_name'] = $admin['first_name'];
            $_SESSION['user_email'] = $admin['email'];
            $_SESSION['is_admin'] = 1; // Force admin privileges
            
            $success = true;
            $message = 'Admin session created successfully. You can now access the admin area.';
        } else {
            $message = 'Admin user not found in database. Please check your database setup.';
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login Bypass - Adidas Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Admin Login Bypass Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h1 class="card-title text-center mb-4">Admin Login Bypass</h1>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>"><?php echo $message; ?></div>
                            <?php endif; ?>
                            
                            <p class="mb-4">This is a temporary solution to bypass the login process and directly access the admin area. Use this if you're unable to login with the admin credentials.</p>
                            
                            <div class="alert alert-warning">
                                <strong>Note:</strong> This is a temporary solution and should be removed in production.
                            </div>
                            
                            <form method="post" action="admin_bypass.php">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-dark">Create Admin Session</button>
                                </div>
                            </form>
                            
                            <?php if ($success): ?>
                                <div class="text-center mt-4">
                                    <a href="admin.php" class="btn btn-primary">Go to Admin Dashboard</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
</body>
</html>
