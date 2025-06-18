<?php
// Modified login page that redirects admin users to the enhanced admin panel
// Include configuration file
require_once 'backend/config.php';

// Initialize variables
$error = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    
    // Validate form data
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        try {
            // Connect to database directly (bypassing session issues)
            $conn = getDbConnection();
            
            // Check if user exists
            $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password - using direct comparison for demo accounts
                if (($email === 'admin@example.com' && $password === 'password123') || 
                    ($email === 'john@example.com' && $password === 'password123') ||
                    password_verify($password, $user['password'])) {
                    
                    // Set session variables
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                    $_SESSION['user_first_name'] = $user['first_name']; // Added for header compatibility
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    
                    // If user is admin, redirect to enhanced admin panel
                    if ($user['is_admin']) {
                        header('Location: enhanced_admin.php');
                        exit;
                    }
                    
                    // Redirect to home page for regular users
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Invalid email or password';
                }
            } else {
                $error = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Adidas Store</title>
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
    
    <!-- Login Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h1 class="card-title text-center mb-4">Login</h1>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger">
                                    <?php echo $error; ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post" action="login.php">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Remember me</label>
                                </div>
                                
                                <div class="d-grid mb-4">
                                    <button type="submit" class="btn btn-dark btn-lg">Login</button>
                                </div>
                                
                                <div class="text-center">
                                    <p>Don't have an account? <a href="register.php">Register</a></p>
                                </div>
                            </form>
                            
                            <hr class="my-4">
                            
                            <div class="text-center">
                                <p class="mb-2">Demo Accounts:</p>
                                <p class="small text-muted">Admin: admin@example.com / password123</p>
                                <p class="small text-muted">User: john@example.com / password123</p>
                            </div>
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
