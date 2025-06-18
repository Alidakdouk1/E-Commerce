<?php
// Include configuration
require_once 'backend/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Adidas Store</title>
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
    
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1>Welcome to Adidas Store</h1>
                    <p class="lead">Explore our collection of premium sportswear and accessories</p>
                    <div class="mt-4">
                        <a href="products.php?category=Men" class="btn btn-dark me-2">Shop Men</a>
                        <a href="products.php?category=Women" class="btn btn-outline-dark me-2">Shop Women</a>
                        <a href="products.php?category=Kids" class="btn btn-outline-dark">Shop Kids</a>
                    </div>
                </div>
                <div class="col-md-6">
                    <img src="images/hero-banner.jpg" alt="Adidas Collection" class="img-fluid rounded">
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Products -->
    <section class="featured-products py-5">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="row">
                <?php
                // Include product functions
                require_once 'backend/product.php';
                
                // Get featured products
                $result = getFeaturedProducts(6);
                
                if ($result['success'] && !empty($result['data'])) {
                    foreach ($result['data'] as $product) {
                        ?>
                        <div class="col-md-4 col-sm-6 mb-4">
                            <div class="product-card">
                                <div class="product-image">
                                    <a href="product-detail.php?id=<?php echo $product['id']; ?>">
                                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid">
                                    </a>
                                </div>
                                <div class="product-info">
                                    <h3 class="product-title">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a>
                                    </h3>
                                    <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                                    <div class="product-category"><?php echo $product['category']; ?></div>
                                    <div class="product-actions">
                                        <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-sm btn-outline-dark">View Details</a>
                                        <button class="btn btn-sm btn-dark add-to-cart" data-product-id="<?php echo $product['id']; ?>">
                                            <i class="fas fa-shopping-cart"></i> Add to Cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-12"><p>No featured products available.</p></div>';
                }
                ?>
            </div>
            <div class="text-center mt-4">
                <a href="products.php" class="btn btn-dark">View All Products</a>
            </div>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories-section py-5 bg-light">
        <div class="container">
            <h2 class="section-title">Shop by Category</h2>
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="category-card">
                        <img src="images/category-men.jpg" alt="Men's Collection" class="img-fluid">
                        <div class="category-overlay">
                            <h3>Men's Collection</h3>
                            <a href="products.php?category=Men" class="btn btn-light">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="category-card">
                        <img src="images/category-women.jpg" alt="Women's Collection" class="img-fluid">
                        <div class="category-overlay">
                            <h3>Women's Collection</h3>
                            <a href="products.php?category=Women" class="btn btn-light">Shop Now</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="category-card">
                        <img src="images/category-kids.jpg" alt="Kids' Collection" class="img-fluid">
                        <div class="category-overlay">
                            <h3>Kids' Collection</h3>
                            <a href="products.php?category=Kids" class="btn btn-light">Shop Now</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Collection -->
    <section class="featured-collection py-5">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2>New Arrivals</h2>
                    <p class="lead">Discover our latest collection of premium sportswear designed for performance and style.</p>
                    <p>Experience the perfect blend of innovation, comfort, and iconic design that Adidas is known for worldwide.</p>
                    <a href="products.php?sort=newest" class="btn btn-dark mt-3">Explore Collection</a>
                </div>
                <div class="col-md-6">
                    <img src="images/featured-collection.jpg" alt="New Arrivals" class="img-fluid rounded">
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
    <script src="js/cart.js"></script>
</body>
</html>
