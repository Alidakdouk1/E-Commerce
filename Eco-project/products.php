<?php
// Include configuration
require_once 'backend/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Adidas Store</title>
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
    
    <!-- Products Section -->
    <section class="products-section py-5">
        <div class="container">
            <div class="row">
                <!-- Sidebar Filters -->
                <div class="col-lg-3 mb-4">
                    <div class="filter-sidebar p-3 border rounded">
                        <h3 class="filter-title">Filters</h3>
                        
                        <!-- Category Filter -->
                        <div class="filter-group mb-4">
                            <h4>Categories</h4>
                            <div class="list-group">
                                <?php
                                // Include product functions
                                require_once 'backend/product.php';
                                
                                // Get all categories
                                $categoriesResult = getAllCategories();
                                
                                if ($categoriesResult['success'] && !empty($categoriesResult['data'])) {
                                    foreach ($categoriesResult['data'] as $category) {
                                        $active = (isset($_GET['category']) && $_GET['category'] == $category) ? 'active' : '';
                                        echo '<a href="products.php?category=' . urlencode($category) . '" class="list-group-item list-group-item-action ' . $active . '">' . $category . '</a>';
                                    }
                                }
                                ?>
                            </div>
                        </div>
                        
                        <!-- Price Range Filter -->
                        <div class="filter-group mb-4">
                            <h4>Price Range</h4>
                            <form action="products.php" method="get">
                                <?php if (isset($_GET['category'])) { ?>
                                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($_GET['category']); ?>">
                                <?php } ?>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <input type="number" name="min_price" class="form-control" placeholder="Min" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                                    </div>
                                    <div class="col-6">
                                        <input type="number" name="max_price" class="form-control" placeholder="Max" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-sm btn-dark mt-2">Apply</button>
                            </form>
                        </div>
                        
                        <!-- Sort Options -->
                        <div class="filter-group">
                            <h4>Sort By</h4>
                            <div class="list-group">
                                <a href="<?php echo addQueryParam('sort', 'price_asc'); ?>" class="list-group-item list-group-item-action <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_asc') ? 'active' : ''; ?>">Price: Low to High</a>
                                <a href="<?php echo addQueryParam('sort', 'price_desc'); ?>" class="list-group-item list-group-item-action <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'price_desc') ? 'active' : ''; ?>">Price: High to Low</a>
                                <a href="<?php echo addQueryParam('sort', 'newest'); ?>" class="list-group-item list-group-item-action <?php echo (isset($_GET['sort']) && $_GET['sort'] == 'newest') ? 'active' : ''; ?>">Newest</a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <div class="col-lg-9">
                    <?php
                    // Get category from URL
                    $category = isset($_GET['category']) ? $_GET['category'] : null;
                    $subcategory = isset($_GET['subcategory']) ? $_GET['subcategory'] : null;
                    $search = isset($_GET['search']) ? $_GET['search'] : null;
                    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
                    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;
                    $sort = isset($_GET['sort']) ? $_GET['sort'] : null;
                    
                    // Page title
                    if ($category) {
                        echo '<h2 class="section-title">' . htmlspecialchars($category) . ' Products</h2>';
                    } elseif ($subcategory) {
                        echo '<h2 class="section-title">' . htmlspecialchars($subcategory) . ' Products</h2>';
                    } elseif ($search) {
                        echo '<h2 class="section-title">Search Results for "' . htmlspecialchars($search) . '"</h2>';
                    } else {
                        echo '<h2 class="section-title">All Products</h2>';
                    }
                    
                    // Get products based on filters
                    if ($search) {
                        $result = searchProducts($search);
                    } elseif ($category) {
                        $result = getProductsByCategory($category);
                    } elseif ($subcategory) {
                        $result = getProductsBySubcategory($subcategory);
                    } else {
                        $result = getAllProducts();
                    }
                    
                    // Filter by price if set
                    if ($result['success'] && !empty($result['data'])) {
                        if ($minPrice !== null || $maxPrice !== null) {
                            $filteredProducts = [];
                            foreach ($result['data'] as $product) {
                                $price = (float)$product['price'];
                                if (($minPrice === null || $price >= $minPrice) && 
                                    ($maxPrice === null || $price <= $maxPrice)) {
                                    $filteredProducts[] = $product;
                                }
                            }
                            $result['data'] = $filteredProducts;
                        }
                        
                        // Sort products
                        if ($sort) {
                            $sortedProducts = $result['data'];
                            switch ($sort) {
                                case 'price_asc':
                                    usort($sortedProducts, function($a, $b) {
                                        return $a['price'] - $b['price'];
                                    });
                                    break;
                                case 'price_desc':
                                    usort($sortedProducts, function($a, $b) {
                                        return $b['price'] - $a['price'];
                                    });
                                    break;
                                case 'newest':
                                    usort($sortedProducts, function($a, $b) {
                                        return strtotime($b['created_at']) - strtotime($a['created_at']);
                                    });
                                    break;
                            }
                            $result['data'] = $sortedProducts;
                        }
                    }
                    
                    // Display products
                    if ($result['success'] && !empty($result['data'])) {
                        echo '<div class="row">';
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
                        echo '</div>';
                    } else {
                        echo '<div class="alert alert-info">No products found matching your criteria.</div>';
                    }
                    ?>
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

<?php
// Helper function to add or update query parameters
function addQueryParam($param, $value) {
    $params = $_GET;
    $params[$param] = $value;
    return '?' . http_build_query($params);
}
?>
