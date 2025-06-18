<?php
// Include configuration
require_once 'backend/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page
    header('Location: login.php?redirect=cart.php');
    exit;
}

// Include cart functions
require_once 'backend/cart.php';

// Get user's cart
$userId = $_SESSION['user_id'];
$cartResult = getUserCart($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Adidas Store</title>
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
    
    <!-- Cart Section -->
    <section class="cart-section py-5">
        <div class="container">
            <h1 class="section-title">Shopping Cart</h1>
            
            <?php if ($cartResult['success'] && !empty($cartResult['data']['items'])): ?>
                <div class="row">
                    <!-- Cart Items -->
                    <div class="col-lg-8">
                        <div class="cart-items">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price</th>
                                            <th>Quantity</th>
                                            <th>Subtotal</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($cartResult['data']['items'] as $item): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <img src="<?php echo $item['image']; ?>" alt="<?php echo $item['name']; ?>" class="cart-item-image me-3">
                                                        <div>
                                                            <h5 class="mb-0"><a href="product-detail.php?id=<?php echo $item['product_id']; ?>"><?php echo $item['name']; ?></a></h5>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td><?php echo formatCurrency($item['price']); ?></td>
                                                <td>
                                                    <div class="input-group quantity-input-group" style="width: 120px;">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" data-action="decrease" data-cart-id="<?php echo $item['id']; ?>">-</button>
                                                        <input type="number" class="form-control form-control-sm text-center quantity-input" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>" data-cart-id="<?php echo $item['id']; ?>">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary quantity-btn" data-action="increase" data-cart-id="<?php echo $item['id']; ?>" data-max="<?php echo $item['stock']; ?>">+</button>
                                                    </div>
                                                </td>
                                                <td><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-sm btn-danger remove-from-cart" data-cart-id="<?php echo $item['id']; ?>">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="cart-actions d-flex justify-content-between mt-4">
                                <a href="products.php" class="btn btn-outline-dark">Continue Shopping</a>
                                <button type="button" class="btn btn-outline-danger clear-cart">Clear Cart</button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Cart Summary -->
                    <div class="col-lg-4">
                        <div class="cart-summary p-4 border rounded">
                            <h3>Order Summary</h3>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal (<?php echo $cartResult['data']['item_count']; ?> items)</span>
                                <span><?php echo formatCurrency($cartResult['data']['total']); ?></span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Shipping</span>
                                <span>Free</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <strong>Total</strong>
                                <strong><?php echo formatCurrency($cartResult['data']['total']); ?></strong>
                            </div>
                            <a href="checkout.php" class="btn btn-dark w-100">Proceed to Checkout</a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-shopping-cart fa-4x text-muted"></i>
                    </div>
                    <h3>Your cart is empty</h3>
                    <p class="text-muted">Looks like you haven't added any products to your cart yet.</p>
                    <a href="products.php" class="btn btn-dark mt-3">Start Shopping</a>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="js/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Update cart item quantity
            const quantityInputs = document.querySelectorAll('.quantity-input');
            const quantityBtns = document.querySelectorAll('.quantity-btn');
            
            quantityInputs.forEach(input => {
                input.addEventListener('change', function() {
                    const cartId = this.dataset.cartId;
                    const quantity = parseInt(this.value);
                    updateCartItemQuantity(cartId, quantity);
                });
            });
            
            quantityBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const action = this.dataset.action;
                    const cartId = this.dataset.cartId;
                    const input = document.querySelector(`.quantity-input[data-cart-id="${cartId}"]`);
                    let currentValue = parseInt(input.value);
                    
                    if (action === 'increase') {
                        const maxStock = parseInt(this.dataset.max);
                        if (currentValue < maxStock) {
                            input.value = currentValue + 1;
                            updateCartItemQuantity(cartId, currentValue + 1);
                        }
                    } else if (action === 'decrease' && currentValue > 1) {
                        input.value = currentValue - 1;
                        updateCartItemQuantity(cartId, currentValue - 1);
                    }
                });
            });
            
            // Remove item from cart
            const removeButtons = document.querySelectorAll('.remove-from-cart');
            removeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const cartId = this.dataset.cartId;
                    removeFromCart(cartId);
                });
            });
            
            // Clear cart
            const clearCartBtn = document.querySelector('.clear-cart');
            if (clearCartBtn) {
                clearCartBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to clear your cart?')) {
                        clearCart();
                    }
                });
            }
            
            // Functions to handle cart operations
            function updateCartItemQuantity(cartId, quantity) {
                fetch('backend/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=update&cart_id=${cartId}&quantity=${quantity}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
            
            function removeFromCart(cartId) {
                fetch('backend/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `action=remove&cart_id=${cartId}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
            
            function clearCart() {
                fetch('backend/cart_actions.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'action=clear'
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                });
            }
        });
    </script>
</body>
</html>
