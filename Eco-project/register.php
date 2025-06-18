<?php
// Include configuration
require_once 'backend/config.php';

// Check if user is already logged in
if (isLoggedIn()) {
    // Redirect to home page
    header('Location: home.php');
    exit;
}

// Process registration form
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate input
    if (empty($firstName)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($lastName)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters';
    }
    
    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match';
    }
    
    // If no errors, register user
    if (empty($errors)) {
        // Include user functions
        require_once 'backend/user.php';
        
        // Register user
        $result = registerUser($firstName, $lastName, $email, $phone, $password);
        
        if ($result['success']) {
            $success = true;
        } else {
            $errors[] = $result['message'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Adidas Store</title>
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
    
    <!-- Register Section -->
    <section class="register-section py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">
                    <div class="card shadow">
                        <div class="card-body p-5">
                            <h1 class="card-title text-center mb-4">Create an Account</h1>
                            
                            <?php if ($success): ?>
                                <div class="alert alert-success">
                                    Registration successful! <a href="login.php">Click here to login</a>.
                                </div>
                            <?php else: ?>
                                <?php if (!empty($errors)): ?>
                                    <div class="alert alert-danger">
                                        <ul class="mb-0">
                                            <?php foreach ($errors as $error): ?>
                                                <li><?php echo $error; ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                                
                                <form method="post" action="register.php">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="first_name" class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="last_name" class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Phone (optional)</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" name="password" required>
                                        <div class="form-text">Password must be at least 6 characters long.</div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirm_password" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    </div>
                                    <div class="mb-3 form-check">
                                        <input type="checkbox" class="form-check-input" id="terms" name="terms" required>
                                        <label class="form-check-label" for="terms">I agree to the <a href="#">Terms and Conditions</a></label>
                                    </div>
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-dark">Register</button>
                                    </div>
                                </form>
                                
                                <div class="text-center mt-4">
                                    <p>Already have an account? <a href="login.php">Login</a></p>
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
