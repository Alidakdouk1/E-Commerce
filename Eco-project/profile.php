<?php
// Include configuration
require_once 'backend/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page
    header('Location: login.php?redirect=profile.php');
    exit;
}

// Get user ID from session
$userId = $_SESSION['user_id'];

// Include user functions
require_once 'backend/user.php';

// Process profile update
$profileUpdateSuccess = false;
$profileUpdateError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '';
    $lastName = isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    
    // Update profile
    $result = updateUserProfile($userId, $firstName, $lastName, $phone);
    
    if ($result['success']) {
        $profileUpdateSuccess = true;
    } else {
        $profileUpdateError = $result['message'];
    }
}

// Process password change
$passwordChangeSuccess = false;
$passwordChangeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    
    // Validate passwords
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordChangeError = 'All password fields are required';
    } elseif ($newPassword !== $confirmPassword) {
        $passwordChangeError = 'New passwords do not match';
    } elseif (strlen($newPassword) < 6) {
        $passwordChangeError = 'Password must be at least 6 characters';
    } else {
        // Change password
        $result = changePassword($userId, $currentPassword, $newPassword);
        
        if ($result['success']) {
            $passwordChangeSuccess = true;
        } else {
            $passwordChangeError = $result['message'];
        }
    }
}

// Get user profile
$userResult = getUserProfile($userId);
$user = $userResult['success'] ? $userResult['data'] : [];

// Include cart functions to get order history
require_once 'backend/cart.php';
$ordersResult = getUserOrders($userId);
$orders = $ordersResult['success'] ? $ordersResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Adidas Store</title>
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
    
    <!-- Profile Section -->
    <section class="profile-section py-5">
        <div class="container">
            <h1 class="section-title">My Profile</h1>
            
            <div class="row">
                <!-- Sidebar -->
                <div class="col-lg-3 mb-4">
                    <div class="profile-sidebar p-3 border rounded">
                        <div class="profile-user mb-4 text-center">
                            <div class="profile-avatar mb-3">
                                <i class="fas fa-user-circle fa-5x text-secondary"></i>
                            </div>
                            <h4 class="profile-name"><?php echo isset($user['first_name']) ? $user['first_name'] . ' ' . $user['last_name'] : 'User'; ?></h4>
                            <p class="text-muted"><?php echo isset($user['email']) ? $user['email'] : ''; ?></p>
                        </div>
                        <div class="profile-nav">
                            <ul class="nav flex-column">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#profile-info" data-bs-toggle="tab">
                                        <i class="fas fa-user me-2"></i> Personal Information
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#change-password" data-bs-toggle="tab">
                                        <i class="fas fa-lock me-2"></i> Change Password
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#order-history" data-bs-toggle="tab">
                                        <i class="fas fa-history me-2"></i> Order History
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="backend/logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i> Logout
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Content -->
                <div class="col-lg-9">
                    <div class="tab-content">
                        <!-- Profile Information -->
                        <div class="tab-pane fade show active" id="profile-info">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">Personal Information</h4>
                                </div>
                                <div class="card-body">
                                    <?php if ($profileUpdateSuccess): ?>
                                        <div class="alert alert-success">Profile updated successfully!</div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($profileUpdateError)): ?>
                                        <div class="alert alert-danger"><?php echo $profileUpdateError; ?></div>
                                    <?php endif; ?>
                                    
                                    <form method="post" action="profile.php">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="first_name" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo isset($user['first_name']) ? $user['first_name'] : ''; ?>" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="last_name" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo isset($user['last_name']) ? $user['last_name'] : ''; ?>" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email</label>
                                            <input type="email" class="form-control" id="email" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" readonly>
                                            <div class="form-text">Email cannot be changed.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone</label>
                                            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo isset($user['phone']) ? $user['phone'] : ''; ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="created_at" class="form-label">Member Since</label>
                                            <input type="text" class="form-control" id="created_at" value="<?php echo isset($user['created_at']) ? date('F j, Y', strtotime($user['created_at'])) : ''; ?>" readonly>
                                        </div>
                                        <button type="submit" name="update_profile" class="btn btn-dark">Update Profile</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Change Password -->
                        <div class="tab-pane fade" id="change-password">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">Change Password</h4>
                                </div>
                                <div class="card-body">
                                    <?php if ($passwordChangeSuccess): ?>
                                        <div class="alert alert-success">Password changed successfully!</div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($passwordChangeError)): ?>
                                        <div class="alert alert-danger"><?php echo $passwordChangeError; ?></div>
                                    <?php endif; ?>
                                    
                                    <form method="post" action="profile.php">
                                        <div class="mb-3">
                                            <label for="current_password" class="form-label">Current Password</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="new_password" class="form-label">New Password</label>
                                            <input type="password" class="form-control" id="new_password" name="new_password" required>
                                            <div class="form-text">Password must be at least 6 characters long.</div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                        </div>
                                        <button type="submit" name="change_password" class="btn btn-dark">Change Password</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Order History -->
                        <div class="tab-pane fade" id="order-history">
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="mb-0">Order History</h4>
                                </div>
                                <div class="card-body">
                                    <?php if (!empty($orders)): ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Order ID</th>
                                                        <th>Date</th>
                                                        <th>Total</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($orders as $order): ?>
                                                        <tr>
                                                            <td>#<?php echo $order['id']; ?></td>
                                                            <td><?php echo date('M j, Y', strtotime($order['created_at'])); ?></td>
                                                            <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                                            <td>
                                                                <span class="badge bg-<?php echo getStatusBadgeClass($order['status']); ?>">
                                                                    <?php echo ucfirst($order['status']); ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-dark">View Details</a>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-4">
                                            <p>You haven't placed any orders yet.</p>
                                            <a href="products.php" class="btn btn-dark">Start Shopping</a>
                                        </div>
                                    <?php endif; ?>
                                </div>
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

<?php
// Helper function to get badge class based on order status
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'warning';
        case 'processing':
            return 'info';
        case 'shipped':
            return 'primary';
        case 'delivered':
            return 'success';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}
?>
