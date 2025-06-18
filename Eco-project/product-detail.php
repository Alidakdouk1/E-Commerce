<?php
// Include configuration
require_once 'backend/config.php';

// Get product ID from URL
$productId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Include product functions
require_once 'backend/product.php';

// Get product details
$result = getProduct($productId);

// Check if product exists
if (!$result['success']) {
    header('Location: products.php');
    exit;
}

$product = $result['data'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Adidas Store</title>
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
    
    <!-- Product Detail Section -->
    <section class="product-detail-section py-5">
        <div class="container">
            <div class="row">
                <!-- Product Image -->
                <div class="col-md-6 mb-4">
                    <div class="product-image-container">
                        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>" class="img-fluid product-detail-image">
                    </div>
                </div>
                
                <!-- Product Info -->
                <div class="col-md-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="home.php">Home</a></li>
                            <li class="breadcrumb-item"><a href="products.php?category=<?php echo urlencode($product['category']); ?>"><?php echo $product['category']; ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo $product['name']; ?></li>
                        </ol>
                    </nav>
                    
                    <h1 class="product-title"><?php echo $product['name']; ?></h1>
                    <div class="product-price-large"><?php echo formatCurrency($product['price']); ?></div>
                    
                    <div class="product-description my-4">
                        <p><?php echo $product['description']; ?></p>
                    </div>
                    
                    <div class="product-meta">
                        <p><strong>Category:</strong> <?php echo $product['category']; ?></p>
                        <?php if (!empty($product['subcategory'])): ?>
                            <p><strong>Subcategory:</strong> <?php echo $product['subcategory']; ?></p>
                        <?php endif; ?>
                        <p><strong>Availability:</strong> 
                            <?php if ($product['stock'] > 0): ?>
                                <span class="text-success">In Stock (<?php echo $product['stock']; ?> available)</span>
                            <?php else: ?>
                                <span class="text-danger">Out of Stock</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <?php if ($product['stock'] > 0): ?>
                        <div class="product-actions mt-4">
                            <form id="add-to-cart-form" class="d-flex align-items-center">
                                <div class="input-group me-3" style="width: 130px;">
                                    <button type="button" class="btn btn-outline-secondary quantity-btn" data-action="decrease">-</button>
                                    <input type="number" class="form-control text-center quantity-input" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                    <button type="button" class="btn btn-outline-secondary quantity-btn" data-action="increase">+</button>
                                </div>
                                <button type="button" class="btn btn-dark add-to-cart-detail" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Product Share -->
                    <div class="product-share mt-4">
                        <p><strong>Share:</strong></p>
                        <div class="social-icons">
                            <a href="#" class="me-2"><i class="fab fa-facebook-f"></i></a>
                            <a href="#" class="me-2"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="me-2"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-pinterest"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Product Details Tabs -->
            <div class="row mt-5">
                <div class="col-12">
                    <ul class="nav nav-tabs" id="productTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">Description</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="details-tab" data-bs-toggle="tab" data-bs-target="#details" type="button" role="tab" aria-controls="details" aria-selected="false">Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="shipping-tab" data-bs-toggle="tab" data-bs-target="#shipping" type="button" role="tab" aria-controls="shipping" aria-selected="false">Shipping & Returns</button>
                        </li>
                    </ul>
                    <div class="tab-content p-4 border border-top-0 rounded-bottom" id="productTabsContent">
                        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
                            <p><?php echo $product['description']; ?></p>
                            <p>Experience the perfect blend of style, comfort, and performance that Adidas is known for worldwide. This product is designed to help you achieve your best, whether you're training, competing, or simply enjoying an active lifestyle.</p>
                        </div>
                        <div class="tab-pane fade" id="details" role="tabpanel" aria-labelledby="details-tab">
                            <h4>Product Features</h4>
                            <ul>
                                <li>Premium quality materials</li>
                                <li>Engineered for performance</li>
                                <li>Iconic Adidas design</li>
                                <li>Comfortable fit</li>
                                <li>Durable construction</li>
                            </ul>
                            <h4>Care Instructions</h4>
                            <p>Machine wash cold with like colors. Do not bleach. Tumble dry low. Do not iron print/decoration.</p>
                        </div>
                        <div class="tab-pane fade" id="shipping" role="tabpanel" aria-labelledby="shipping-tab">
                            <h4>Shipping Information</h4>
                            <p>Standard shipping: 3-5 business days</p>
                            <p>Express shipping: 1-2 business days</p>
                            
                            <h4>Return Policy</h4>
                            <p>If you're not completely satisfied with your purchase, you can return it within 30 days for a full refund. Items must be unworn and in original packaging with tags attached.</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <div class="related-products mt-5">
                <h2 class="section-title">You May Also Like</h2>
                <div class="row">
                    <?php
                    // Get products from same category
                    $relatedResult = getProductsByCategory($product['category']);
                    
                    if ($relatedResult['success'] && !empty($relatedResult['data'])) {
                        $count = 0;
                        foreach ($relatedResult['data'] as $relatedProduct) {
                            // Skip current product and limit to 4 products
                            if ($relatedProduct['id'] != $product['id'] && $count < 4) {
                                $count++;
                                ?>
                                <div class="col-md-3 col-sm-6 mb-4">
                                    <div class="product-card">
                                        <div class="product-image">
                                            <a href="product-detail.php?id=<?php echo $relatedProduct['id']; ?>">
                                                <img src="<?php echo $relatedProduct['image']; ?>" alt="<?php echo $relatedProduct['name']; ?>" class="img-fluid">
                                            </a>
                                        </div>
                                        <div class="product-info">
                                            <h3 class="product-title">
                                                <a href="product-detail.php?id=<?php echo $relatedProduct['id']; ?>"><?php echo $relatedProduct['name']; ?></a>
                                            </h3>
                                            <div class="product-price"><?php echo formatCurrency($relatedProduct['price']); ?></div>
                                            <div class="product-actions">
                                                <button class="btn btn-sm btn-dark add-to-cart" data-product-id="<?php echo $relatedProduct['id']; ?>">
                                                    <i class="fas fa-shopping-cart"></i> Add to Cart
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                            }
                        }
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
    <script>
        // Product quantity buttons
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.querySelector('.quantity-input');
            const quantityBtns = document.querySelectorAll('.quantity-btn');
            const maxStock = <?php echo $product['stock']; ?>;
            
            quantityBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const action = this.dataset.action;
                    let currentValue = parseInt(quantityInput.value);
                    
                    if (action === 'increase' && currentValue < maxStock) {
                        quantityInput.value = currentValue + 1;
                    } else if (action === 'decrease' && currentValue > 1) {
                        quantityInput.value = currentValue - 1;
                    }
                });
            });
            
            // Add to cart with quantity
            const addToCartBtn = document.querySelector('.add-to-cart-detail');
            addToCartBtn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                const quantity = parseInt(quantityInput.value);
                
                addToCart(productId, quantity);
            });
        });
    </script>
</body>
</html>
