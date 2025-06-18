<?php
// Include configuration
require_once 'backend/config.php';

// Check if user is logged in
if (!isLoggedIn()) {
    // Redirect to login page
    header('Location: login.php?redirect=checkout.php');
    exit;
}

// Include cart functions
require_once 'backend/cart.php';

// Get user's cart
$userId = $_SESSION['user_id'];
$cartResult = getUserCart($userId);

// Check if cart is empty
if (!$cartResult['success'] || empty($cartResult['data']['items'])) {
    // Redirect to cart page
    header('Location: cart.php');
    exit;
}

// Process checkout form submission
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate form data
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitize($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitize($_POST['state']) : '';
    $zipCode = isset($_POST['zip_code']) ? sanitize($_POST['zip_code']) : '';
    $paymentMethod = isset($_POST['payment_method']) ? sanitize($_POST['payment_method']) : '';
    
    // Check required fields
    if (empty($address)) {
        $errors[] = 'Address is required';
    }
    if (empty($city)) {
        $errors[] = 'City is required';
    }
    if (empty($state)) {
        $errors[] = 'State is required';
    }
    if (empty($zipCode)) {
        $errors[] = 'Zip code is required';
    }
    if (empty($paymentMethod)) {
        $errors[] = 'Payment method is required';
    }
    
    // If no errors, create order
    if (empty($errors)) {
        $orderResult = createOrder($userId, $address, $city, $state, $zipCode, $paymentMethod);
        
        if ($orderResult['success']) {
            // Set success message
            setFlashMessage('success', 'Order placed successfully! Your order number is: ' . $orderResult['order_id']);
            
            // Redirect to order confirmation page
            header('Location: order-confirmation.php?id=' . $orderResult['order_id']);
            exit;
        } else {
            $errors[] = $orderResult['message'];
        }
    }
}

// Get user profile for pre-filling form
require_once 'backend/user.php';
$userResult = getUserProfile($userId);
$user = $userResult['success'] ? $userResult['data'] : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Adidas Store</title>
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
    
    <!-- Checkout Section -->
    <section class="checkout-section py-5">
        <div class="container">
            <h1 class="section-title">Checkout</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Checkout Form -->
                <div class="col-lg-8">
                    <div class="checkout-form p-4 border rounded">
                        <form method="post" action="checkout.php">
                            <!-- Customer Information -->
                            <div class="mb-4">
                                <h3>Customer Information</h3>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="first_name" class="form-label">First Name</label>
                                        <input type="text" class="form-control" id="first_name" value="<?php echo isset($user['first_name']) ? $user['first_name'] : ''; ?>" readonly>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="last_name" class="form-label">Last Name</label>
                                        <input type="text" class="form-control" id="last_name" value="<?php echo isset($user['last_name']) ? $user['last_name'] : ''; ?>" readonly>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="email" value="<?php echo isset($user['email']) ? $user['email'] : ''; ?>" readonly>
                                </div>
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Phone</label>
                                    <input type="tel" class="form-control" id="phone" value="<?php echo isset($user['phone']) ? $user['phone'] : ''; ?>" readonly>
                                </div>
                            </div>
                            
                            <!-- Shipping Address -->
                            <div class="mb-4">
                                <h3>Shipping Address</h3>
                                <div class="mb-3">
                                    <label for="address" class="form-label">Address</label>
                                    <input type="text" class="form-control" id="address" name="address" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="city" class="form-label">City</label>
                                        <input type="text" class="form-control" id="city" name="city" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="state" class="form-label">State</label>
                                        <input type="text" class="form-control" id="state" name="state" required>
                                    </div>
                                    <div class="col-md-3 mb-3">
                                        <label for="zip_code" class="form-label">Zip Code</label>
                                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Payment Method -->
                            <div class="mb-4">
                                <h3>Payment Method</h3>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="credit_card" value="credit_card" checked>
                                    <label class="form-check-label" for="credit_card">
                                        Credit Card
                                    </label>
                                </div>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="payment_method" id="paypal" value="paypal">
                                    <label class="form-check-label" for="paypal">
                                        PayPal
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" id="cash_on_delivery" value="cash_on_delivery">
                                    <label class="form-check-label" for="cash_on_delivery">
                                        Cash on Delivery
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Credit Card Details (shown only when credit card is selected) -->
                            <div id="credit_card_details" class="mb-4">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="card_number" class="form-label">Card Number</label>
                                        <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="card_name" class="form-label">Name on Card</label>
                                        <input type="text" class="form-control" id="card_name">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_date" class="form-label">Expiry Date</label>
                                        <input type="text" class="form-control" id="expiry_date" placeholder="MM/YY">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" placeholder="123">
                                    </div>
                                </div>
                                <div class="form-text">
                                    <small class="text-muted">For demo purposes only. No actual payment will be processed.</small>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between mt-4">
                                <a href="cart.php" class="btn btn-outline-dark">Back to Cart</a>
                                <button type="submit" class="btn btn-dark">Place Order</button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="col-lg-4">
                    <div class="order-summary p-4 border rounded">
                        <h3>Order Summary</h3>
                        <div class="order-items mb-4">
                            <?php foreach ($cartResult['data']['items'] as $item): ?>
                                <div class="d-flex justify-content-between mb-2">
                                    <span><?php echo $item['name']; ?> x <?php echo $item['quantity']; ?></span>
                                    <span><?php echo formatCurrency($item['price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?php echo formatCurrency($cartResult['data']['total']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-0">
                            <strong>Total</strong>
                            <strong><?php echo formatCurrency($cartResult['data']['total']); ?></strong>
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
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Show/hide credit card details based on payment method selection
            const paymentMethods = document.querySelectorAll('input[name="payment_method"]');
            const creditCardDetails = document.getElementById('credit_card_details');
            
            paymentMethods.forEach(method => {
                method.addEventListener('change', function() {
                    if (this.value === 'credit_card') {
                        creditCardDetails.style.display = 'block';
                    } else {
                        creditCardDetails.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html>
