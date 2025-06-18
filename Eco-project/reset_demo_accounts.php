<?php
// Demo account password reset page
// This page allows users to easily reset the demo account passwords

// Include configuration
require_once 'backend/config.php';

$message = '';
$success = false;

// Process reset request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Include the reset script
    require_once 'backend/reset_demo_passwords.php';
    
    // Check if reset was successful
    if ($result['success']) {
        $success = true;
        $message = 'Demo account passwords have been reset successfully. You can now login with the default passwords.';
    } else {
        $message = 'Failed to reset demo account passwords: ' . $result['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Demo Accounts - Adidas Store</title>
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
    
    <!-- Reset Demo Accounts Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h1 class="card-title text-center mb-4">Reset Demo Accounts</h1>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>"><?php echo $message; ?></div>
                            <?php endif; ?>
                            
                            <p class="mb-4">This tool will reset the passwords for the demo accounts to ensure they work with your current PHP environment. Use this if you're having trouble logging in with the demo accounts.</p>
                            
                            <div class="mb-4">
                                <h5>Demo Accounts:</h5>
                                <ul>
                                    <li>Admin: admin@example.com / password123</li>
                                    <li>User: john@example.com / password123</li>
                                </ul>
                            </div>
                            
                            <form method="post" action="reset_demo_accounts.php">
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-dark">Reset Demo Account Passwords</button>
                                </div>
                            </form>
                            
                            <?php if ($success): ?>
                                <div class="text-center mt-4">
                                    <a href="login.php" class="btn btn-primary">Go to Login</a>
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
