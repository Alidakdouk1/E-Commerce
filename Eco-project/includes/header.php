<?php
// Include necessary files
require_once 'backend/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adidas Store</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="main-header">
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">
                <a class="navbar-brand" href="index.php">
                    <img src="images/logos/adidas-logo.png" alt="Adidas Logo" height="30">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php?category=Men">Men</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php?category=Women">Women</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php?category=Kids">Kids</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="products.php">All Products</a>
                        </li>
                    </ul>
                    <form class="d-flex me-2" action="products.php" method="get">
                        <input class="form-control me-2" type="search" name="search" placeholder="Search products..." aria-label="Search">
                        <button class="btn btn-outline-light" type="submit">Search</button>
                    </form>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i>
                                <?php if (isLoggedIn()): ?>
                                    <?php echo $_SESSION['user_first_name']; ?>
                                <?php else: ?>
                                    Account
                                <?php endif; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                <?php if (isLoggedIn()): ?>
                                    <li><a class="dropdown-item" href="profile.php">My Profile</a></li>
                                    <li><a class="dropdown-item" href="orders.php">My Orders</a></li>
                                    <?php if (isAdmin()): ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="admin.php">Admin Dashboard</a></li>
                                    <?php endif; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="backend/logout.php">Logout</a></li>
                                <?php else: ?>
                                    <li><a class="dropdown-item" href="login.php">Login</a></li>
                                    <li><a class="dropdown-item" href="register.php">Register</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i>
                                <span class="cart-count" id="cartCount">0</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
</body>
</html>
