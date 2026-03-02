<?php
$pageTitle = 'Checkout';
require_once 'includes/db_connect.php';

// Redirect if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    $checkCart = $conn->query("SELECT COUNT(*) as count FROM cart WHERE user_id = " . ($_SESSION['user_id'] ?? 0));
    if ($checkCart->fetch_assoc()['count'] == 0) {
        setFlashMessage('Your cart is empty', 'warning');
        redirect('cart.php');
    }
}

// Redirect if not logged in
if (!isLoggedIn()) {
    $_SESSION['redirect_after_login'] = 'checkout.php';
    setFlashMessage('Please login to checkout', 'info');
    redirect('login.php');
}

$userId = $_SESSION['user_id'];
$user = $conn->query("SELECT * FROM users WHERE id = $userId")->fetch_assoc();

// Get cart items
$cartItems = [];
$subtotal = 0;
$result = $conn->query("SELECT c.*, p.* FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = $userId");
while ($row = $result->fetch_assoc()) {
    $cartItems[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
}

$shipping = $subtotal > 50 ? 0 : 10;
$total = $subtotal + $shipping;

// Process checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $shippingName = sanitize($conn, $_POST['shipping_name']);
    $shippingEmail = sanitize($conn, $_POST['shipping_email']);
    $shippingPhone = sanitize($conn, $_POST['shipping_phone']);
    $shippingAddress = sanitize($conn, $_POST['shipping_address']);
    $shippingCity = sanitize($conn, $_POST['shipping_city']);
    $shippingZip = sanitize($conn, $_POST['shipping_zip']);
    $paymentMethod = sanitize($conn, $_POST['payment_method']);
    $notes = sanitize($conn, $_POST['notes'] ?? '');
    
    // Validate required fields (zip is optional)
    if (empty($shippingName) || empty($shippingEmail) || empty($shippingPhone) || empty($shippingAddress) || empty($shippingCity)) {
        setFlashMessage('Please fill in all required fields', 'danger');
    } else {
        // Create order
        $orderNumber = generateOrderNumber();
        $sql = "INSERT INTO orders (user_id, order_number, total_amount, payment_method, shipping_name, shipping_email, shipping_phone, shipping_address, shipping_city, shipping_zip, notes) 
                VALUES ($userId, '$orderNumber', $total, '$paymentMethod', '$shippingName', '$shippingEmail', '$shippingPhone', '$shippingAddress', '$shippingCity', '$shippingZip', '$notes')";
        
        if ($conn->query($sql)) {
            $orderId = $conn->insert_id;
            
            // Add order items
            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];
                $price = $item['price'];
                $itemTotal = $price * $quantity;
                $productName = sanitize($conn, $item['name']);
                
                $conn->query("INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) 
                            VALUES ($orderId, $productId, '$productName', $quantity, $price, $itemTotal)");
                
                // Update product stock
                $conn->query("UPDATE products SET stock = stock - $quantity WHERE id = $productId");
            }
            
            // Clear cart
            $conn->query("DELETE FROM cart WHERE user_id = $userId");
            
            // Set order ID for confirmation page
            $_SESSION['last_order_id'] = $orderId;
            
            setFlashMessage('Order placed successfully! Order #: ' . $orderNumber, 'success');
            redirect('order_confirmation.php');
        } else {
            setFlashMessage('Error processing order. Please try again.', 'danger');
        }
    }
}

require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <h2 class="mb-4">Checkout</h2>
        
        <div class="row g-4">
            <!-- Checkout Form -->
            <div class="col-lg-8">
                <form method="POST" action="checkout.php" class="needs-validation" novalidate>
                    <!-- Shipping Information -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-shipping-fast me-2 text-primary"></i>Shipping Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name *</label>
                                    <input type="text" name="shipping_name" class="form-control" value="<?php echo $user['name']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email *</label>
                                    <input type="email" name="shipping_email" class="form-control" value="<?php echo $user['email']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone *</label>
                                    <input type="tel" name="shipping_phone" class="form-control" value="<?php echo $user['phone']; ?>" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address *</label>
                                    <textarea name="shipping_address" class="form-control" rows="2" required><?php echo $user['address']; ?></textarea>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">City *</label>
                                    <input type="text" name="shipping_city" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ZIP Code (Optional)</label>
                                    <input type="text" name="shipping_zip" class="form-control" placeholder="e.g. 10001">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Order Notes (Optional)</label>
                                    <textarea name="notes" class="form-control" rows="2" placeholder="Special instructions for delivery..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Method -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h5 class="mb-0"><i class="fas fa-credit-card me-2 text-primary"></i>Payment Method</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cash_on_delivery" checked>
                                <label class="form-check-label d-flex align-items-center" for="cod">
                                    <i class="fas fa-money-bill-wave fa-2x me-3 text-success"></i>
                                    <div>
                                        <strong>Cash on Delivery</strong>
                                        <p class="mb-0 text-muted small">Pay when your order arrives</p>
                                    </div>
                                </label>
                            </div>
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="credit_card">
                                <label class="form-check-label d-flex align-items-center" for="card">
                                    <i class="fas fa-credit-card fa-2x me-3 text-primary"></i>
                                    <div>
                                        <strong>Credit/Debit Card</strong>
                                        <p class="mb-0 text-muted small">Pay securely with your card (Demo)</p>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="fas fa-lock me-2"></i>Place Order
                    </button>
                </form>
            </div>
            
            <!-- Order Summary -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 100px;">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($cartItems as $item): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <div>
                                <span class="fw-bold"><?php echo $item['name']; ?></span>
                                <small class="d-block text-muted">Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                            <span><?php echo formatPrice($item['price'] * $item['quantity']); ?></span>
                        </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal</span>
                            <span><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Shipping</span>
                            <span><?php echo $shipping == 0 ? 'Free' : formatPrice($shipping); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax</span>
                            <span>Included</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span class="h5">Total</span>
                            <span class="h5 text-primary"><?php echo formatPrice($total); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
