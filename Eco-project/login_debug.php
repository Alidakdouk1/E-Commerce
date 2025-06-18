<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include configuration
require_once 'backend/config.php';

// Debug information
echo "<div style='background-color: #f8f9fa; padding: 10px; margin-bottom: 20px; border: 1px solid #ddd;'>";
echo "<h3>Debug Information</h3>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not Active') . "</p>";
echo "<p>Session ID: " . session_id() . "</p>";

if (isset($_SESSION)) {
    echo "<p>Session Variables:</p>";
    echo "<pre>" . print_r($_SESSION, true) . "</pre>";
}

echo "</div>";

// Check if user is already logged in
if (isLoggedIn()) {
    echo "<div style='background-color: #d4edda; padding: 10px; margin-bottom: 20px; border: 1px solid #c3e6cb;'>";
    echo "<p>User is logged in with ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Is Admin: " . (isAdmin() ? 'Yes' : 'No') . "</p>";
    echo "</div>";
    
    // Redirect to home page or requested page
    $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'home.php';
    echo "<p>Would redirect to: " . htmlspecialchars($redirect) . " (redirects disabled in debug mode)</p>";
    // Uncomment to enable redirect
    // header("Location: $redirect");
    // exit;
}

// Process login form
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    echo "<div style='background-color: #fff3cd; padding: 10px; margin-bottom: 20px; border: 1px solid #ffeeba;'>";
    echo "<h3>Login Attempt</h3>";
    echo "<p>Email: " . htmlspecialchars($email) . "</p>";
    echo "<p>Password: " . str_repeat('*', strlen($password)) . " (Length: " . strlen($password) . ")</p>";
    
    // Include user functions
    require_once 'backend/user.php';
    
    // Check if user exists in database
    try {
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id, first_name, last_name, email, password, is_admin FROM users WHERE email = ?");
        $stmt->execute([$email]);
        
        if ($stmt->rowCount() == 0) {
            echo "<p style='color: red;'>User not found in database</p>";
        } else {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "<p>User found in database:</p>";
            echo "<pre>" . print_r(array_merge(array_diff_key($user, ['password' => '']), ['password_hash_length' => strlen($user['password'])]), true) . "</pre>";
            
            // Test password verification
            $passwordVerified = password_verify($password, $user['password']);
            echo "<p>Password verification result: " . ($passwordVerified ? 'Success' : 'Failed') . "</p>";
            
            if (!$passwordVerified) {
                // For admin@example.com, provide option to reset password
                if ($email === 'admin@example.com') {
                    echo "<p>Admin account detected. Password hash in database may be incompatible with current PHP version.</p>";
                    echo "<p><a href='admin_reset.php' style='color: blue;'>Click here to reset admin password</a></p>";
                }
            }
        }
    } catch (PDOException $e) {
        echo "<p style='color: red;'>Database error: " . $e->getMessage() . "</p>";
    }
    
    // Attempt login
    $result = loginUser($email, $password);
    
    echo "<p>Login result:</p>";
    echo "<pre>" . print_r($result, true) . "</pre>";
    
    if ($result['success']) {
        echo "<p style='color: green;'>Login successful!</p>";
        echo "<p>Session after login:</p>";
        echo "<pre>" . print_r($_SESSION, true) . "</pre>";
        
        // Redirect to home page or requested page
        $redirect = isset($_GET['redirect']) ? $_GET['redirect'] : 'home.php';
        echo "<p>Would redirect to: " . htmlspecialchars($redirect) . " (redirects disabled in debug mode)</p>";
        // Uncomment to enable redirect
        // header("Location: $redirect");
        // exit;
    } else {
        $error = $result['message'];
        echo "<p style='color: red;'>Login failed: " . $error . "</p>";
    }
    
    echo "</div>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Debug - Adidas Store</title>
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
    <section class="login-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6 col-lg-5">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h1 class="card-title text-center mb-4">Login Debug Mode</h1>
                            
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo $error; ?></div>
                            <?php endif; ?>
                            
                            <form method="post" action="login_debug.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                </div>
                                <div class="mb-3 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                    <label class="form-check-label" for="remember_me">Remember me</label>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" class="btn btn-dark">Login</button>
                                </div>
                            </form>
                            
                            <div class="text-center mt-4">
                                <p>Don't have an account? <a href="register.php">Register</a></p>
                            </div>
                            
                            <div class="text-center mt-3">
                                <p class="text-muted">Demo Accounts:</p>
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
