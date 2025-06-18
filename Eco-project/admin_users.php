<?php
// Include configuration
require_once 'backend/config.php';

// Include admin functions
require_once 'backend/admin.php';
require_once 'backend/user.php';

// Check if user is logged in and is admin
if (!isLoggedIn() || !isAdmin()) {
    // Redirect to login page
    header('Location: login.php?redirect=admin_users.php');
    exit;
}

// Process user actions
$message = '';
$messageType = '';

// Delete user
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    // Prevent admin from deleting themselves
    if ($userId == $_SESSION['user_id']) {
        $message = 'You cannot delete your own account!';
        $messageType = 'danger';
    } else {
        $result = deleteUser($userId);
        
        if ($result['success']) {
            $message = 'User deleted successfully!';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

// Toggle admin status
if (isset($_GET['action']) && $_GET['action'] == 'toggle_admin' && isset($_GET['id'])) {
    $userId = (int)$_GET['id'];
    
    // Prevent admin from removing their own admin status
    if ($userId == $_SESSION['user_id']) {
        $message = 'You cannot change your own admin status!';
        $messageType = 'danger';
    } else {
        $result = toggleAdminStatus($userId);
        
        if ($result['success']) {
            $message = 'User admin status updated successfully!';
            $messageType = 'success';
        } else {
            $message = $result['message'];
            $messageType = 'danger';
        }
    }
}

// Get all users
$usersResult = getAllUsers();
$users = $usersResult['success'] ? $usersResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/admin.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">
        <!-- Admin Sidebar -->
        <?php include 'includes/admin_sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Admin Header -->
            <?php include 'includes/admin_header.php'; ?>
            
            <!-- Users Content -->
            <div class="container-fluid px-4">
                <h1 class="mt-4">Manage Users</h1>
                <ol class="breadcrumb mb-4">
                    <li class="breadcrumb-item"><a href="admin.php">Dashboard</a></li>
                    <li class="breadcrumb-item active">Users</li>
                </ol>
                
                <?php if (!empty($message)): ?>
                    <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div><i class="fas fa-users me-1"></i> Users</div>
                        <a href="admin_user_add.php" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Add New User
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="usersTable" class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                        <tr>
                                            <td><?php echo $user['id']; ?></td>
                                            <td><?php echo $user['first_name'] . ' ' . $user['last_name']; ?></td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo !empty($user['phone']) ? $user['phone'] : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($user['is_admin']): ?>
                                                    <span class="badge bg-danger">Admin</span>
                                                <?php else: ?>
                                                    <span class="badge bg-info">Customer</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="admin_user_edit.php?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <a href="admin_users.php?action=toggle_admin&id=<?php echo $user['id']; ?>" class="btn btn-sm <?php echo $user['is_admin'] ? 'btn-warning' : 'btn-success'; ?>" title="<?php echo $user['is_admin'] ? 'Remove Admin' : 'Make Admin'; ?>">
                                                            <i class="fas <?php echo $user['is_admin'] ? 'fa-user-minus' : 'fa-user-plus'; ?>"></i>
                                                        </a>
                                                        <a href="admin_users.php?action=delete&id=<?php echo $user['id']; ?>" class="btn btn-sm btn-danger delete-user" data-user-name="<?php echo $user['first_name'] . ' ' . $user['last_name']; ?>">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <!-- Custom JS -->
    <script src="js/admin.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#usersTable').DataTable({
                order: [[0, 'desc']]
            });
            
            // Confirm delete
            $('.delete-user').on('click', function(e) {
                e.preventDefault();
                const userName = $(this).data('user-name');
                const deleteUrl = $(this).attr('href');
                
                if (confirm(`Are you sure you want to delete user "${userName}"? This action cannot be undone.`)) {
                    window.location.href = deleteUrl;
                }
            });
        });
    </script>
</body>
</html>
