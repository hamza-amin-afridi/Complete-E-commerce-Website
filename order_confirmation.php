<?php
$pageTitle = 'Order Confirmation';
require_once 'includes/db_connect.php';

// Check if user is logged in
if (!isLoggedIn()) {
    redirect('login.php');
}

// Get last order
if (!isset($_SESSION['last_order_id'])) {
    redirect('index.php');
}

$orderId = $_SESSION['last_order_id'];
$order = $conn->query("SELECT * FROM orders WHERE id = $orderId AND user_id = {$_SESSION['user_id']}")->fetch_assoc();

if (!$order) {
    redirect('index.php');
}

// Clear the session variable
unset($_SESSION['last_order_id']);

// Get order items
$items = $conn->query("SELECT * FROM order_items WHERE order_id = $orderId");

require_once 'includes/header.php';
?>

<section class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle fa-5x text-success"></i>
                        </div>
                        <h2 class="mb-3">Thank You for Your Order!</h2>
                        <p class="lead text-muted mb-4">
                            Your order has been placed successfully. We'll send you an email confirmation shortly.
                        </p>
                        
                        <div class="alert alert-light border mb-4">
                            <h5 class="mb-2">Order Details</h5>
                            <p class="mb-1"><strong>Order Number:</strong> <?php echo $order['order_number']; ?></p>
                            <p class="mb-1"><strong>Order Date:</strong> <?php echo date('F j, Y', strtotime($order['created_at'])); ?></p>
                            <p class="mb-0"><strong>Order Total:</strong> <span class="text-primary h5"><?php echo formatPrice($order['total_amount']); ?></span></p>
                        </div>
                        
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            <a href="shop.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag me-2"></i>Continue Shopping
                            </a>
                            <a href="orders.php" class="btn btn-primary">
                                <i class="fas fa-list me-2"></i>View My Orders
                            </a>
                            <a href="invoice.php?id=<?php echo $order['id']; ?>" class="btn btn-success">
                                <i class="fas fa-file-invoice me-2"></i>View Invoice
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Order Summary -->
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0">Order Summary</h5>
                    </div>
                    <div class="card-body">
                        <?php while($item = $items->fetch_assoc()): ?>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="mb-1"><?php echo $item['product_name']; ?></h6>
                                <small class="text-muted">Qty: <?php echo $item['quantity']; ?></small>
                            </div>
                            <span class="fw-bold"><?php echo formatPrice($item['total']); ?></span>
                        </div>
                        <?php endwhile; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Status</span>
                            <span class="badge bg-warning"><?php echo ucfirst($order['status']); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Payment Method</span>
                            <span class="text-muted"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
