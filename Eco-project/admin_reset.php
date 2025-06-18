<?php
// Admin password reset page
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once 'backend/config.php';

$message = '';
$success = false;

// Process reset form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate input
    if (empty($newPassword) || empty($confirmPassword)) {
        $message = 'All fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $message = 'Passwords do not match';
    } elseif (strlen($newPassword) < 6) {
        $message = 'Password must be at least 6 characters';
    } else {
        try {
            $conn = getDbConnection();
            
            // Hash new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update admin password
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE email = 'admin@example.com'");
            $stmt->execute([$hashedPassword]);
            
            if ($stmt->rowCount() > 0) {
                $message = 'Admin password has been reset successfully. You can now login with the new password.';
                $success = true;
            } else {
                $message = 'Failed to reset password. Admin account not found.';
            }
        } catch (PDOException $e) {
            $message = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset - Adidas Store</title>
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
    
    <!-- Reset Password Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h1 class="card-title text-center mb-4">Admin Password Reset</h1>
                            
                            <?php if (!empty($message)): ?>
                                <div class="alert alert-<?php echo $success ? 'success' : 'danger'; ?>"><?php echo $message; ?></div>
                            <?php endif; ?>
                            
                            <?php if (!$success): ?>
                                <p class="mb-4">Use this form to reset the admin account password. This will allow you to login if the default password is not working.</p>
                                
                                <form method="post" action="admin_reset.php">
                                    <div class="mb-3">
                                        <label for="new_password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-dark">Reset Password</button>
                                    </div>
                                </form>
                            <?php else: ?>
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
